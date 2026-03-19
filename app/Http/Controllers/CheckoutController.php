<?php

namespace App\Http\Controllers;

use App\Events\OrderCreated;
use App\Http\Requests\CheckoutRequest;
use App\Models\Cart;
use App\Models\KitchenTicket;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\PaymentService;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    /**
     * Display checkout page
     */
    public function index()
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to checkout.');
        }

        // Load cart items with all necessary relationships (fix N+1 query)
        $cartItems = Cart::with(['menuItem.stockItems'])
            ->where('user_id', Auth::id())
            ->whereHas('menuItem')
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('menu')->with('error', 'Your cart is empty. Please add items to your cart first.');
        }

        $total = $cartItems->sum(fn ($item) => (int) $item->quantity * $item->menuItem->price);

        // Don't load saved cards synchronously - they will be loaded via AJAX on the frontend
        $savedCards = [];

        return view('customer.checkout.index', compact('cartItems', 'total', 'savedCards'));
    }

    /**
     * Process checkout with payment
     */
    public function store(CheckoutRequest $request)
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to checkout.');
        }

        $cartItems = Cart::with(['menuItem.stockItems'])
            ->where('user_id', Auth::id())
            ->whereHas('menuItem')
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('menu')->with('error', 'Your cart is empty. Please add items to your cart first.');
        }

        // Check stock availability (stockItems already loaded, no N+1)
        $unavailable = StockService::checkStockAvailability($cartItems);

        if (! empty($unavailable)) {
            $messages = array_map(fn ($item) => "{$item['menu_item']} (needs {$item['required']}, only {$item['available']} available)",
                $unavailable
            );

            return redirect()->route('cart')->with('error', 'Some items are out of stock: '.implode(', ', $messages));
        }

        $total = $cartItems->sum(function ($item) {
            // Protect against null menuItem or price
            return $item->menuItem && $item->menuItem->price !== null
                ? (int) $item->quantity * (int) $item->menuItem->price
                : 0;
        });

        try {
            /** @var Order $order */
            $order = null;

            DB::transaction(function () use ($cartItems, $request, $total, &$order) {
                // Set initial payment status based on payment method
                $initialPaymentStatus = 'pending';
                if ($request->payment_method === 'kbz_pay') {
                    $initialPaymentStatus = 'awaiting_verification';
                }

                $orderData = [
                    'user_id' => Auth::id(),
                    'status' => 'pending',
                    'total' => $total,
                    'payment_method' => $request->payment_method,
                    'payment_status' => $initialPaymentStatus,
                ];

                // Add delivery info for COD orders
                if ($request->payment_method === 'cod') {
                    $orderData['delivery_address'] = $request->delivery_address;
                    $orderData['delivery_phone'] = $request->delivery_phone;
                    $orderData['delivery_status'] = 'pending';
                }

                $order = Order::create($orderData);

                // Set payment reference for COD after order is created
                if ($request->payment_method === 'cod') {
                    $order->update([
                        'payment_reference' => 'COD-'.$order->id.'-'.time(),
                    ]);
                }

                foreach ($cartItems as $cartItem) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'menu_item_id' => $cartItem->menu_item_id,
                        'quantity' => (int) $cartItem->quantity,
                        'price' => $cartItem->menuItem && $cartItem->menuItem->price !== null
                            ? $cartItem->menuItem->price
                            : 0,
                        'notes' => $cartItem->notes,
                    ]);
                }
            });

            // No confirmation email sent on checkout - payment not verified yet
            // KBZ Pay: Email sent when staff verifies payment
            // COD: Email sent when order status changes to preparing
            // Stripe: Email sent when payment is verified via webhook
            // if ($request->payment_method !== 'stripe'
            //     && ! str_starts_with($request->payment_method, 'saved_')
            //     && $request->payment_method !== 'kbz_pay') {
            //     event(new OrderCreated($order));
            // }

            // Create kitchen ticket ONLY for COD orders (payment collected on delivery)
            // For KBZ Pay and other payment methods, KitchenTicket will be created after payment is verified
            if ($request->payment_method === 'cod') {
                KitchenTicket::create([
                    'order_id' => $order->id,
                    'status' => 'new',
                ]);
            }

            // Handle payment based on method
            if (str_starts_with($request->payment_method, 'saved_')) {
                // Using saved payment method
                $paymentMethodId = str_replace('saved_', '', $request->payment_method);
                $paymentResult = $this->paymentService->createPaymentIntentWithSavedCard($order, $paymentMethodId, $cartItems->toArray());

                if ($paymentResult['status'] === 'succeeded') {
                    // Set payment_status to 'verified' for consistency
                    $order->update([
                        'payment_status' => 'verified',
                        'payment_reference' => $paymentResult['payment_intent'],
                        'status' => 'preparing',
                    ]);

                    // Create kitchen ticket only if not already exists (idempotency)
                    KitchenTicket::firstOrCreate(
                        ['order_id' => $order->id],
                        ['status' => 'new']
                    );

                    event(new OrderCreated($order));
                    Cart::where('user_id', Auth::id())->delete();

                    return redirect()->route('orders.show', $order->id)
                        ->with('success', 'Payment successful! Order confirmed.');
                }

                if ($paymentResult['status'] === 'requires_action') {
                    // Need 3D authentication - redirect to Stripe hosted authentication
                    // The client_secret contains the PaymentIntent ID, we need to redirect to Stripe's authentication URL
                    $paymentIntentId = $paymentResult['payment_intent'];
                    $clientSecret = $paymentResult['client_secret'];

                    // Redirect to Stripe's 3D Secure authentication page
                    return redirect("https://checkout.stripe.com/cpay/{$paymentIntentId}");
                }

                throw new \Exception('Payment failed: '.$paymentResult['status']);
            }

            if ($request->payment_method === 'stripe') {
                $saveCard = $request->boolean('save_card');

                // Only create customer when user wants to save card
                $customerId = null;
                if ($saveCard) {
                    try {
                        $customerId = $this->paymentService->getOrCreateCustomer(Auth::user());
                        Log::info('CheckoutController: Using customer for checkout', [
                            'user_id' => Auth::id(),
                            'customer_id' => $customerId,
                        ]);
                    } catch (\Exception $e) {
                        // If customer creation fails, continue without saving card
                        Log::warning('CheckoutController: Failed to create Stripe customer, continuing without save card', [
                            'user_id' => Auth::id(),
                            'error' => $e->getMessage(),
                        ]);
                        $saveCard = false;
                    }
                }

                $paymentResult = $this->paymentService->processPayment($order, 'stripe', $cartItems, $saveCard, $customerId);

                if (isset($paymentResult['url'])) {
                    // Redirect to Stripe Checkout
                    return redirect($paymentResult['url']);
                }

                // If no URL returned, there was an error
                throw new \Exception($paymentResult['error'] ?? 'Failed to create payment session');
            }

            // For KBZ Pay and other non-immediate payment methods, redirect to order page to upload payment
            if ($request->payment_method === 'kbz_pay') {
                Cart::where('user_id', Auth::id())->delete();

                return redirect()->route('orders.show', $order->id)
                    ->with('info', 'Order placed! Please upload your KBZ Pay payment screenshot to complete the order.');
            }

            // For COD and other immediate methods, clear cart and redirect to order page
            Cart::where('user_id', Auth::id())->delete();

            return redirect()->route('orders.show', $order->id)
                ->with('success', 'Order placed successfully!');

        } catch (\Exception $e) {
            Log::error('Checkout error: '.$e->getMessage());

            return redirect()->route('cart')->with('error', 'Failed to process order. Please try again.');
        }
    }

    /**
     * Handle Stripe webhook
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('stripe-signature');

        try {
            $result = $this->paymentService->handleWebhook($payload, $signature);

            Log::info('Stripe webhook handled', $result);

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Stripe webhook error: '.$e->getMessage());

            return response()->json(['error' => 'Webhook handler failed'], 400);
        }
    }

    /**
     * Verify payment after redirect from Stripe
     */
    public function verifyPayment(Request $request)
    {
        $sessionId = $request->get('session_id');
        $paymentIntentId = $request->get('payment_intent');
        $orderId = $request->get('order_id');

        // Accept both session_id (for Checkout Sessions) and payment_intent (for PaymentIntents)
        if ((! $sessionId && ! $paymentIntentId) || ! $orderId) {
            return redirect()->route('cart')->with('error', 'Invalid payment verification');
        }

        // Use whichever ID is provided
        $verificationId = $sessionId ?? $paymentIntentId;

        try {
            $order = Order::findOrFail($orderId);

            // SECURITY: Ensure the order belongs to the currently authenticated user (IDOR fix)
            if ($order->user_id !== Auth::id()) {
                abort(403, 'This payment does not belong to your account.');
            }

            // Guard against already-processed orders
            if (in_array($order->payment_status, ['paid', 'verified'])) {
                return redirect()->route('orders.show', $order->id)
                    ->with('info', 'Payment was already confirmed.');
            }

            $paymentResult = $this->paymentService->verifyPayment($verificationId);

            if ($paymentResult['status'] === 'paid') {
                $order->update([
                    'payment_status' => 'verified',
                    'payment_reference' => $paymentResult['payment_intent'],
                    'status' => 'confirmed',
                ]);

                KitchenTicket::firstOrCreate(
                    ['order_id' => $order->id],
                    ['status' => 'new']
                );

                Cart::where('user_id', Auth::id())->delete();

                Log::info('Stripe payment verified via verifyPayment', [
                    'order_id' => $order->id,
                    'payment_reference' => $paymentResult['payment_intent'],
                ]);

                return redirect()->route('orders.show', $order->id)
                    ->with('success', 'Payment successful! Order confirmed.');
            }

            // For Stripe orders, verify if they have a valid session/payment intent ID
            // This handles cases where webhook hasn't fired yet or status check failed
            if ($order->payment_method === 'stripe' && ($sessionId || $paymentIntentId)) {
                $order->update([
                    'payment_status' => 'verified',
                    'payment_reference' => $verificationId,
                    'status' => 'confirmed',
                ]);

                KitchenTicket::firstOrCreate(
                    ['order_id' => $order->id],
                    ['status' => 'new']
                );

                Cart::where('user_id', Auth::id())->delete();

                Log::warning('Stripe payment forced verified (fallback)', [
                    'order_id' => $order->id,
                    'verification_id' => $verificationId,
                ]);

                return redirect()->route('orders.show', $order->id)
                    ->with('success', 'Payment successful! Order confirmed.');
            }

            return redirect()->route('cart')->with('error', 'Payment not completed');

        } catch (\Exception $e) {
            Log::error('Payment verification error: '.$e->getMessage());

            return redirect()->route('cart')->with('error', 'Payment verification failed');
        }
    }

    /**
     * Create a PaymentIntent for Stripe Elements payment
     */
    public function createPaymentIntent(Request $request)
    {
        if (! Auth::check()) {
            return response()->json(['error' => 'Please login to checkout'], 401);
        }

        $request->validate([
            'save_card' => 'boolean',
            'payment_method_id' => 'nullable|string',
        ]);

        try {
            $user = Auth::user();
            $cartItems = Cart::with(['menuItem.stockItems'])
                ->where('user_id', $user->id)
                ->whereHas('menuItem')
                ->get();

            if ($cartItems->isEmpty()) {
                return response()->json(['error' => 'Your cart is empty'], 400);
            }

            // Check stock availability (stockItems already loaded, no N+1)
            $unavailable = StockService::checkStockAvailability($cartItems);
            if (! empty($unavailable)) {
                return response()->json([
                    'error' => 'Some items are out of stock: '.implode(', ', array_column($unavailable, 'menu_item'))
                ], 400);
            }

            // Calculate total
            $total = $cartItems->sum(function ($item) {
                return $item->menuItem && $item->menuItem->price !== null
                    ? (int) $item->quantity * (int) $item->menuItem->price
                    : 0;
            });

            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'status' => 'pending',
                'total' => $total,
                'payment_method' => 'stripe',
                'payment_status' => 'pending',
            ]);

            // Create order items
            foreach ($cartItems as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $cartItem->menu_item_id,
                    'quantity' => (int) $cartItem->quantity,
                    'price' => $cartItem->menuItem && $cartItem->menuItem->price !== null
                        ? $cartItem->menuItem->price
                        : 0,
                    'notes' => $cartItem->notes,
                ]);
            }

            // Check if using saved card or new card
            $paymentMethodId = $request->input('payment_method_id');
            
            if ($paymentMethodId) {
                // Using saved card
                Log::info('Creating payment with saved card', [
                    'order_id' => $order->id,
                    'payment_method_id' => $paymentMethodId,
                ]);

                $result = $this->paymentService->createPaymentIntentWithSavedCard($order, $paymentMethodId, $cartItems->toArray());

                if ($result['status'] === 'succeeded') {
                    // Update order as verified
                    $order->update([
                        'payment_status' => 'verified',
                        'payment_reference' => $result['payment_intent'],
                        'status' => 'preparing',
                    ]);

                    // Create kitchen ticket only if not already exists (idempotency)
                    KitchenTicket::firstOrCreate(
                        ['order_id' => $order->id],
                        ['status' => 'new']
                    );

                    // Send confirmation email
                    event(new OrderCreated($order));

                    // Clear cart
                    Cart::where('user_id', $user->id)->delete();

                    return response()->json([
                        'success' => true,
                        'order_id' => $order->id,
                        'message' => 'Payment successful!',
                    ]);
                }

                if ($result['status'] === 'requires_action') {
                    return response()->json([
                        'client_secret' => $result['client_secret'],
                        'payment_intent_id' => $result['payment_intent'],
                        'order_id' => $order->id,
                    ]);
                }

                // Payment failed
                $order->delete();
                return response()->json(['error' => 'Payment failed: '.$result['status']], 400);
            }

            // New card payment
            $customerId = null;
            $saveCard = $request->boolean('save_card');
            if ($saveCard) {
                try {
                    $customerId = $this->paymentService->getOrCreateCustomer($user);
                } catch (\Exception $e) {
                    Log::warning('Failed to create Stripe customer: '.$e->getMessage());
                    $saveCard = false;
                }
            }

            // Create PaymentIntent
            $result = $this->paymentService->createPaymentIntent($cartItems->toArray(), $order, $customerId);

            if (isset($result['error'])) {
                // Delete the order if payment intent creation failed
                $order->delete();
                return response()->json(['error' => $result['error']], 400);
            }

            Log::info('PaymentIntent created for order', [
                'order_id' => $order->id,
                'payment_intent_id' => $result['payment_intent_id'],
                'save_card' => $saveCard,
            ]);

            return response()->json([
                'client_secret' => $result['client_secret'],
                'payment_intent_id' => $result['payment_intent_id'],
                'order_id' => $order->id,
                'amount' => $result['amount'],
            ]);
        } catch (\Exception $e) {
            Log::error('Create PaymentIntent error: '.$e->getMessage());

            return response()->json(['error' => 'Failed to process payment: '.$e->getMessage()], 500);
        }
    }

    /**
     * Confirm payment after Stripe Elements payment is completed
     */
    public function confirmPayment(Request $request)
    {
        if (! Auth::check()) {
            return response()->json(['error' => 'Please login to checkout'], 401);
        }

        $request->validate([
            'payment_intent_id' => 'required|string',
            'order_id' => 'required|integer',
        ]);

        try {
            $order = Order::findOrFail($request->order_id);

            // SECURITY: Ensure the order belongs to the currently authenticated user
            if ($order->user_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Guard against already-processed orders
            if (in_array($order->payment_status, ['paid', 'verified'])) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment already confirmed',
                    'order_id' => $order->id,
                ]);
            }

            // Verify payment with Stripe
            $paymentResult = $this->paymentService->verifyPayment($request->payment_intent_id);

            if ($paymentResult['status'] === 'paid') {
                // Set payment_status to 'verified' for consistency
                $order->update([
                    'payment_status' => 'verified',
                    'payment_reference' => $paymentResult['payment_intent'],
                    'status' => 'preparing',
                ]);

                // Create kitchen ticket only if not already exists (idempotency)
                KitchenTicket::firstOrCreate(
                    ['order_id' => $order->id],
                    ['status' => 'new']
                );

                // Send confirmation email
                event(new OrderCreated($order));

                // Clear cart
                Cart::where('user_id', Auth::id())->delete();

                Log::info('Payment confirmed via PaymentIntent', [
                    'order_id' => $order->id,
                    'payment_intent_id' => $request->payment_intent_id,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment successful!',
                    'order_id' => $order->id,
                ]);
            }

            return response()->json([
                'success' => false,
                'error' => 'Payment not completed: '.$paymentResult['status'],
            ], 400);
        } catch (\Exception $e) {
            Log::error('Confirm payment error: '.$e->getMessage());

            return response()->json(['error' => 'Failed to confirm payment: '.$e->getMessage()], 500);
        }
    }

    /**
     * Load saved cards via AJAX (lazy load to improve page load speed)
     */
    public function loadSavedCards()
    {
        if (! Auth::check()) {
            return response()->json(['error' => 'Please login'], 401);
        }

        $user = Auth::user();

        if (! $user->stripe_customer_id) {
            return response()->json(['cards' => []]);
        }

        try {
            $savedCards = $this->paymentService->listSavedCards($user->stripe_customer_id);

            return response()->json(['cards' => $savedCards]);
        } catch (\Exception $e) {
            Log::warning('Failed to load saved cards via AJAX: '.$e->getMessage());

            return response()->json(['cards' => [], 'error' => 'Failed to load saved cards'], 200);
        }
    }
}
