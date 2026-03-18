<?php

namespace App\Services;

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
            'customer_email' => $order->user->email ?? null,
        ];

        // Only save card when user explicitly checks the checkbox
        if ($saveCard && $customerId) {
            $sessionParams['payment_intent_data'] = [
                'customer' => $customerId,
                'setup_future_usage' => 'on_session',
            ];
        }
        // If no saveCard, no customer attached - just normal payment

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
                'has_customer' => !empty($customerId),
            ]);
            
            return [
                'error' => 'Failed to create payment session: ' . $e->getMessage(),
            ];
        } catch (\Exception $e) {
            Log::error('Stripe session creation failed (general)', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
            ]);
            
            return [
                'error' => 'Failed to create payment session: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Verify Stripe payment session
     */
    public function verifyPayment(string $sessionId): array
    {
        try {
            $session = StripeSession::retrieve($sessionId);

            return [
                'status' => $session->payment_status,
                'amount_total' => $session->amount_total / 100,
                'payment_intent' => $session->payment_intent ?? null,
            ];
        } catch (ApiErrorException $e) {
            Log::error('Payment verification failed: '.$e->getMessage());
            throw new \Exception('Failed to verify payment: '.$e->getMessage());
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

            return $this->createCheckoutSession($cartItems->toArray(), $order, $saveCard, $customerId);
        }

        // For other payment methods (COD, etc.)
        return [
            'status' => 'pending',
            'amount' => $order->total,
        ];
    }

    /**
     * Handle webhook events from Stripe.
     * Verifies the Stripe-Signature header using the official SDK.
     */
    public function handleWebhook(string $payload, ?string $signature): array
    {
        $webhookSecret = config('stripe.webhook_secret');

        // --- Signature Verification ---
        if (empty($webhookSecret) || $webhookSecret === 'whsec_your_webhook_secret_here') {
            Log::warning('Stripe webhook secret is not configured. Skipping verification in non-production mode.');
            if (app()->environment('production')) {
                Log::error('Stripe webhook secret is not set in production!');
                throw new \Exception('Webhook secret not configured.');
            }
            // In non-production, parse the event without signature verification
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

        // Only update if not already processed (idempotency)
        if (in_array($order->payment_status, ['paid', 'verified'])) {
            return ['status' => 'already_processed', 'order_id' => $orderId];
        }

        $order->update([
            'payment_status' => 'paid',
            'payment_reference' => $session['payment_intent'] ?? null,
            'status' => 'confirmed',
        ]);

        // Create kitchen ticket after payment confirmed
        KitchenTicket::create([
            'order_id' => $order->id,
            'status' => 'new',
        ]);

        // Clear cart after successful payment
        Cart::where('user_id', $order->user_id)->delete();

        Log::info('Payment completed for order: '.$orderId);

        return ['status' => 'payment_completed', 'order_id' => $orderId];
    }

    /**
     * Handle payment succeeded
     */
    private function handlePaymentSucceeded(array $paymentIntent): array
    {
        Log::info('Payment succeeded', ['payment_intent' => $paymentIntent['id']]);

        return ['status' => 'success'];
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
                Customer::retrieve($user->stripe_customer_id);
                return $user->stripe_customer_id;
            } catch (\Exception $e) {
                Log::warning('Stripe customer retrieval failed, creating new one: ' . $e->getMessage());
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

            return $customer->id;
        } catch (ApiErrorException $e) {
            Log::error('Failed to create Stripe customer: ' . $e->getMessage());
            throw new \Exception('Failed to create payment customer: ' . $e->getMessage());
        }
    }

    /**
     * List saved payment methods for a customer
     */
    public function listSavedCards(string $customerId): array
    {
        try {
            $paymentMethods = PaymentMethod::all([
                'customer' => $customerId,
                'type' => 'card',
            ]);

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
            Log::error('Failed to list saved cards: ' . $e->getMessage());
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
            $paymentIntent = PaymentIntent::create([
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
                'return_url' => url('/checkout/verify') . '?payment_intent=' . '{PAYMENT_INTENT_ID}&order_id=' . $order->id,
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
            Log::error('PaymentIntent creation failed: ' . $e->getMessage());
            throw new \Exception('Payment failed: ' . $e->getMessage());
        }
    }

    /**
     * Delete a saved payment method
     */
    public function deletePaymentMethod(string $paymentMethodId): bool
    {
        try {
            $paymentMethod = PaymentMethod::retrieve($paymentMethodId);
            $paymentMethod->detach();
            return true;
        } catch (ApiErrorException $e) {
            Log::error('Failed to delete payment method: ' . $e->getMessage());
            return false;
        }
    }
}
