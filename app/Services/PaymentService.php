<?php

namespace App\Services;

use App\Events\OrderCreated;
use App\Exceptions\PaymentMethodOwnershipException;
use App\Models\Cart;
use App\Models\KitchenTicket;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\Stripe;
use Stripe\Webhook;

class PaymentService
{
    private string $currency;

    public function __construct()
    {
        Stripe::setApiKey(config('stripe.secret'));
        $this->currency = config('stripe.currency', 'usd');
    }

    /**
     * Create a Stripe Checkout Session
     */
    public function createCheckoutSession(array $items, Order $order, bool $saveCard = false, ?string $customerId = null): array
    {
        $lineItems = $this->prepareLineItems($items);

        if (empty($lineItems)) {
            throw new \Exception('No valid line items to checkout.');
        }

        $sessionParams = [
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => url('/checkout/verify').'?session_id={CHECKOUT_SESSION_ID}&order_id='.$order->id,
            'cancel_url' => url('/checkout').'?payment=cancelled',
            'metadata' => [
                'order_id' => (string) $order->id,
                'user_id' => (string) $order->user_id,
            ],
        ];

        // If saving card, use customer parameter directly (for Checkout Sessions)
        // Cards are automatically saved to the customer after successful payment
        // Note: setup_future_usage is NOT valid for Checkout Sessions, only for Payment Intents
        if ($saveCard && $customerId) {
            $sessionParams['customer'] = $customerId;
        } else {
            $sessionParams['customer_email'] = $order->user->email ?? null;
        }

        try {
            $session = StripeSession::create($sessionParams);

            return [
                'session_id' => $session->id,
                'url' => $session->url,
            ];
        } catch (ApiErrorException $e) {
            Log::error('Stripe session creation failed', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
                'stripe_code' => $e->getStripeCode(),
                'save_card' => $saveCard,
                'has_customer' => ! empty($customerId),
            ]);

            return [
                'error' => 'Failed to create payment session: '.$e->getMessage(),
            ];
        } catch (\Exception $e) {
            Log::error('Stripe session creation failed (general)', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
            ]);

            return [
                'error' => 'Failed to create payment session: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Verify Stripe Checkout Session.
     * Returns status, amount, payment_intent, and metadata for binding verification.
     */
    public function verifyPayment(string $sessionId): array
    {
        try {
            $session = StripeSession::retrieve($sessionId);

            return [
                'status' => $session->payment_status,
                'amount_total' => $session->amount_total / 100,
                'payment_intent' => $session->payment_intent ?? null,
                'metadata_order_id' => $session->metadata->order_id ?? null,
                'metadata_user_id' => $session->metadata->user_id ?? null,
            ];
        } catch (ApiErrorException $e) {
            Log::error('Payment verification failed: '.$e->getMessage());
            throw new \Exception('Failed to verify payment.');
        }
    }

    /**
     * Verify Stripe PaymentIntent.
     * Returns status, id, and metadata for binding verification.
     */
    public function verifyPaymentIntent(string $paymentIntentId): array
    {
        try {
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);

            return [
                'status' => $paymentIntent->status,
                'payment_intent' => $paymentIntent->id,
                'metadata_order_id' => $paymentIntent->metadata->order_id ?? null,
                'metadata_user_id' => $paymentIntent->metadata->user_id ?? null,
            ];
        } catch (ApiErrorException $e) {
            Log::error('PaymentIntent verification failed: '.$e->getMessage());
            throw new \Exception('Failed to verify payment intent.');
        }
    }

    /**
     * Process payment for an order using cart items already loaded by the controller.
     */
    public function processPayment(Order $order, string $paymentMethod, $cartItems = null, bool $saveCard = false, ?string $customerId = null): array
    {
        if ($paymentMethod === 'stripe') {
            // Use provided cart items or fetch from DB as fallback
            if ($cartItems === null) {
                $cartItems = Cart::with('menuItem')
                    ->where('user_id', $order->user_id)
                    ->get();
            }

            if ($cartItems->isEmpty()) {
                throw new \Exception('Cart is empty');
            }

            // If saving card, use Payment Intents (cards are saved properly)
            // Otherwise, use Checkout Sessions
            if ($saveCard) {
                return $this->createPaymentIntent($cartItems->toArray(), $order, $customerId);
            }

            return $this->createCheckoutSession($cartItems->toArray(), $order, false, null);
        }

        // For other payment methods (COD, etc.)
        return [
            'status' => 'pending',
            'amount' => $order->total,
        ];
    }

    /**
     * Create a PaymentIntent for card-based payment with saved cards support
     * Payment Intents properly save cards to the customer when using setup_future_usage
     */
    public function createPaymentIntent(array $items, Order $order, ?string $customerId = null): array
    {
        try {
            // Get or create customer if saving card
            if ($customerId) {
                try {
                    $customer = Customer::retrieve($customerId);
                } catch (\Exception $e) {
                    // Customer doesn't exist, create new one
                    $customer = Customer::create([
                        'email' => $order->user->email,
                        'name' => $order->user->name,
                    ]);
                    $customerId = $customer->id;
                    $order->user->update(['stripe_customer_id' => $customerId]);
                }
            }

            // Calculate amount in cents
            $amount = (int) ($order->total * 100);

            // Create PaymentIntent with setup_future_usage to save the card
            $params = [
                'amount' => $amount,
                'currency' => strtolower($this->currency),
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
                'metadata' => [
                    'order_id' => (string) $order->id,
                    'user_id' => (string) $order->user_id,
                ],
            ];

            // If customer exists and we want to save card, attach customer and set setup_future_usage
            if ($customerId) {
                $params['customer'] = $customerId;
                $params['setup_future_usage'] = 'on_session';
            }

            $paymentIntent = PaymentIntent::create($params);

            Log::info('PaymentIntent created', [
                'order_id' => $order->id,
                'payment_intent_id' => $paymentIntent->id,
                'client_secret' => substr($paymentIntent->client_secret, 0, 20) . '...',
                'has_customer' => ! empty($customerId),
            ]);

            return [
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
                'amount' => $order->total,
            ];
        } catch (ApiErrorException $e) {
            Log::error('PaymentIntent creation failed', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
                'stripe_code' => $e->getStripeCode(),
            ]);

            return [
                'error' => 'Failed to create payment: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Handle webhook events from Stripe.
     * Verifies the Stripe-Signature header using the official SDK.
     */
    public function handleWebhook(string $payload, ?string $signature): array
    {
        $webhookSecret = config('stripe.webhook_secret');
        $isLocal = app()->environment('local');

        // --- Signature Verification ---
        if (empty($webhookSecret) || $webhookSecret === 'whsec_your_webhook_secret_here') {
            if (! $isLocal) {
                Log::error('Stripe webhook secret is not configured outside local environment.');
                throw new \Exception('Webhook secret not configured.');
            }

            Log::warning('Stripe webhook secret is not configured. Allowing unsigned webhook only in local environment.');
            $event = json_decode($payload, true);
        } else {
            try {
                $stripeEvent = Webhook::constructEvent($payload, $signature, $webhookSecret);
                $event = json_decode(json_encode($stripeEvent), true);
            } catch (SignatureVerificationException $e) {
                Log::warning('Stripe webhook signature verification failed: '.$e->getMessage());
                throw new \Exception('Invalid webhook signature.');
            } catch (\UnexpectedValueException $e) {
                Log::warning('Invalid webhook payload: '.$e->getMessage());
                throw new \Exception('Invalid webhook payload.');
            }
        }

        if (! $event || ! isset($event['type'])) {
            return ['status' => 'invalid_event'];
        }

        switch ($event['type']) {
            case 'checkout.session.completed':
                return $this->handleCheckoutCompleted($event['data']['object']);

            case 'payment_intent.succeeded':
                return $this->handlePaymentSucceeded($event['data']['object']);

            case 'payment_intent.payment_failed':
                return $this->handlePaymentFailed($event['data']['object']);

            default:
                return ['status' => 'unhandled_event', 'type' => $event['type']];
        }
    }

    /**
     * Handle checkout session completed (webhook)
     */
    private function handleCheckoutCompleted(array $session): array
    {
        $orderId = $session['metadata']['order_id'] ?? null;

        if (! $orderId) {
            return ['status' => 'no_order_found'];
        }

        $order = Order::find($orderId);

        if (! $order) {
            return ['status' => 'order_not_found'];
        }

        $this->markOrderAsPaid($order, $session['payment_intent'] ?? null);

        Log::info('Payment completed for order: '.$orderId);

        return ['status' => 'payment_completed', 'order_id' => $orderId];
    }

    /**
     * Handle payment succeeded (webhook - PaymentIntent)
     */
    private function handlePaymentSucceeded(array $paymentIntent): array
    {
        $orderId = $paymentIntent['metadata']['order_id'] ?? null;

        if (! $orderId) {
            return ['status' => 'no_order_found'];
        }

        $order = Order::find($orderId);

        if (! $order) {
            return ['status' => 'order_not_found'];
        }

        $this->markOrderAsPaid($order, $paymentIntent['id']);

        Log::info('Payment succeeded for order: '.$orderId, [
            'payment_intent' => $paymentIntent['id'],
        ]);

        return ['status' => 'success', 'order_id' => $orderId];
    }

    /**
     * Mark order as paid and queue kitchen work.
     * Idempotent: will not downgrade order status if already past pre-kitchen states.
     */
    private function markOrderAsPaid(Order $order, ?string $paymentReference): void
    {
        // Early return for terminal states — no side effects (ticket, event, cart)
        if (in_array($order->status, ['cancelled', 'completed'])) {
            return;
        }

        $update = ['payment_status' => 'verified', 'payment_reference' => $paymentReference];

        // Only advance status to 'preparing' if the order hasn't reached the kitchen yet
        if (in_array($order->status, ['pending', 'confirmed'])) {
            $update['status'] = 'preparing';
        }

        $order->update($update);

        KitchenTicket::firstOrCreate(
            ['order_id' => $order->id],
            ['status' => 'new']
        );

        Cart::where('user_id', $order->user_id)->delete();

        // Only fire event if not already confirmed/paid (avoid duplicate emails)
        if ($order->wasChanged('payment_status')) {
            event(new OrderCreated($order));
        }
    }

    /**
     * Handle payment failed
     */
    private function handlePaymentFailed(array $paymentIntent): array
    {
        Log::warning('Payment failed', [
            'payment_intent' => $paymentIntent['id'],
            'error' => $paymentIntent['last_payment_error']['message'] ?? 'Unknown error',
        ]);

        return ['status' => 'payment_failed'];
    }

    /**
     * Prepare line items for Stripe Checkout
     */
    private function prepareLineItems(array $items): array
    {
        $lineItems = [];

        foreach ($items as $item) {
            $menuItem = $item['menuItem'] ?? $item['menu_item'] ?? null;

            if (! $menuItem) {
                continue;
            }

            // Handle both array and object (Eloquent cast) formats
            $name = is_array($menuItem) ? ($menuItem['name'] ?? 'Product') : ($menuItem->name ?? 'Product');
            $description = is_array($menuItem) ? ($menuItem['description'] ?? null) : ($menuItem->description ?? null);
            $price = is_array($menuItem) ? ($menuItem['price'] ?? 0) : ($menuItem->price ?? 0);
            $quantity = (int) ($item['quantity'] ?? 1);

            if ($price <= 0 || $quantity <= 0) {
                continue;
            }

            $productData = ['name' => $name];
            if ($description) {
                $productData['description'] = $description;
            }

            $lineItems[] = [
                'price_data' => [
                    'currency' => $this->currency,
                    'product_data' => $productData,
                    'unit_amount' => (int) round($price * 100), // Convert to cents
                ],
                'quantity' => $quantity,
            ];
        }

        return $lineItems;
    }

    /**
     * Get or create Stripe Customer for a user
     */
    public function getOrCreateCustomer(User $user): string
    {
        if ($user->stripe_customer_id) {
            try {
                $customer = Customer::retrieve($user->stripe_customer_id);
                
                // Verify customer exists and email matches
                if ($customer && $customer->email === $user->email) {
                    Log::info('getOrCreateCustomer: Using existing customer', [
                        'user_id' => $user->id,
                        'customer_id' => $user->stripe_customer_id,
                    ]);
                    return $user->stripe_customer_id;
                }
                
                Log::warning('getOrCreateCustomer: Customer mismatch, creating new', [
                    'user_id' => $user->id,
                    'existing_customer_email' => $customer->email ?? 'unknown',
                    'user_email' => $user->email,
                ]);
            } catch (\Exception $e) {
                Log::warning('getOrCreateCustomer: Customer retrieval failed', [
                    'user_id' => $user->id,
                    'customer_id' => $user->stripe_customer_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        try {
            $customer = Customer::create([
                'email' => $user->email,
                'name' => $user->name,
                'metadata' => [
                    'user_id' => (string) $user->id,
                ],
            ]);

            $user->update(['stripe_customer_id' => $customer->id]);
            
            Log::info('getOrCreateCustomer: Created new customer', [
                'user_id' => $user->id,
                'customer_id' => $customer->id,
            ]);

            return $customer->id;
        } catch (ApiErrorException $e) {
            Log::error('Failed to create Stripe customer: '.$e->getMessage());
            throw new \Exception('Failed to create payment customer: '.$e->getMessage());
        }
    }

    /**
     * List saved payment methods for a customer
     */
    public function listSavedCards(string $customerId): array
    {
        try {
            Log::info('listSavedCards: Fetching cards for customer', ['customer_id' => $customerId]);
            
            $paymentMethods = PaymentMethod::all([
                'customer' => $customerId,
                'type' => 'card',
            ]);

            Log::info('listSavedCards: Stripe response', [
                'customer_id' => $customerId,
                'cards_count' => count($paymentMethods->data),
                'cards' => array_map(function ($pm) {
                    return [
                        'id' => $pm->id,
                        'brand' => $pm->card->brand ?? 'unknown',
                        'last4' => $pm->card->last4 ?? '****',
                    ];
                }, $paymentMethods->data)
            ]);

            if (empty($paymentMethods->data)) {
                Log::info('listSavedCards: No cards found for customer', ['customer_id' => $customerId]);
                return [];
            }

            return array_map(function ($pm) {
                return [
                    'id' => $pm->id,
                    'brand' => $pm->card->brand,
                    'last4' => $pm->card->last4,
                    'exp_month' => $pm->card->exp_month,
                    'exp_year' => $pm->card->exp_year,
                ];
            }, $paymentMethods->data);
        } catch (ApiErrorException $e) {
            Log::error('Failed to list saved cards: '.$e->getMessage(), [
                'customer_id' => $customerId,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Create PaymentIntent using a saved payment method
     */
    public function createPaymentIntentWithSavedCard(Order $order, string $paymentMethodId, array $cartItems): array
    {
        $customerId = $this->getOrCreateCustomer($order->user);
        $amount = (int) round($order->total * 100);

        try {
            $paymentMethod = $this->retrievePaymentMethod($paymentMethodId);
            $paymentMethodCustomerId = $this->extractPaymentMethodCustomerId($paymentMethod);

            if (! $paymentMethodCustomerId || $paymentMethodCustomerId !== $customerId) {
                Log::warning('Blocked saved-card payment attempt due to ownership mismatch.', [
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                    'payment_method_id' => $paymentMethodId,
                    'expected_customer_id' => $customerId,
                    'actual_customer_id' => $paymentMethodCustomerId,
                ]);

                return [
                    'status' => 'forbidden_payment_method',
                ];
            }

            $paymentIntent = $this->createStripePaymentIntent([
                'amount' => $amount,
                'currency' => $this->currency,
                'customer' => $customerId,
                'payment_method' => $paymentMethodId,
                'confirm' => true,
                'off_session' => false,
                'metadata' => [
                    'order_id' => (string) $order->id,
                    'user_id' => (string) $order->user_id,
                ],
                'return_url' => url('/checkout/verify').'?payment_intent='.'{PAYMENT_INTENT_ID}&order_id='.$order->id,
            ]);

            if ($paymentIntent->status === 'succeeded') {
                return [
                    'status' => 'succeeded',
                    'payment_intent' => $paymentIntent->id,
                ];
            }

            if ($paymentIntent->status === 'requires_action') {
                return [
                    'status' => 'requires_action',
                    'payment_intent' => $paymentIntent->id,
                    'client_secret' => $paymentIntent->client_secret,
                ];
            }

            return [
                'status' => $paymentIntent->status,
                'payment_intent' => $paymentIntent->id,
            ];
        } catch (ApiErrorException $e) {
            Log::error('PaymentIntent creation failed: '.$e->getMessage());
            throw new \Exception('Payment failed: '.$e->getMessage());
        }
    }

    /**
     * Delete a saved payment method
     */
    public function deletePaymentMethod(string $paymentMethodId, string $expectedCustomerId): bool
    {
        try {
            $paymentMethod = $this->retrievePaymentMethod($paymentMethodId);
            $paymentMethodCustomerId = $this->extractPaymentMethodCustomerId($paymentMethod);

            if (! $paymentMethodCustomerId || $paymentMethodCustomerId !== $expectedCustomerId) {
                Log::warning('Blocked payment method delete attempt due to ownership mismatch.', [
                    'payment_method_id' => $paymentMethodId,
                    'expected_customer_id' => $expectedCustomerId,
                    'actual_customer_id' => $paymentMethodCustomerId,
                ]);

                throw new PaymentMethodOwnershipException('Payment method ownership mismatch.');
            }

            $this->detachStripePaymentMethod($paymentMethod);

            return true;
        } catch (ApiErrorException $e) {
            Log::error('Failed to delete payment method: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Wrapper for Stripe API retrieval to simplify testing.
     */
    protected function retrievePaymentMethod(string $paymentMethodId)
    {
        return PaymentMethod::retrieve($paymentMethodId);
    }

    /**
     * Wrapper for Stripe API detach to simplify testing.
     */
    protected function detachStripePaymentMethod($paymentMethod): void
    {
        $paymentMethod->detach();
    }

    /**
     * Wrapper for Stripe PaymentIntent creation to simplify testing.
     */
    protected function createStripePaymentIntent(array $payload)
    {
        return PaymentIntent::create($payload);
    }

    /**
     * Extract customer id from a Stripe payment method object.
     */
    private function extractPaymentMethodCustomerId(object $paymentMethod): ?string
    {
        $customer = $paymentMethod->customer ?? null;

        if (is_string($customer)) {
            return $customer;
        }

        if (is_object($customer) && isset($customer->id) && is_string($customer->id)) {
            return $customer->id;
        }

        return null;
    }
}
