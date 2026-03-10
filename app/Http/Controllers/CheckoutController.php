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
        $cartItems = Cart::with('menuItem')
            ->where('user_id', auth()->id())
            ->whereHas('menuItem')
            ->where('quantity', '>', 0)
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('menu')->with('error', 'Your cart is empty.');
        }

        $total = $cartItems->sum(fn ($item) => (int) $item->quantity * $item->menuItem->price);

        return view('customer.checkout.index', compact('cartItems', 'total'));
    }

    /**
     * Process checkout with payment
     */
    public function store(CheckoutRequest $request)
    {
        $cartItems = Cart::with('menuItem')
            ->where('user_id', auth()->id())
            ->whereHas('menuItem')
            ->where('quantity', '>', 0)
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('menu')->with('error', 'Your cart is empty.');
        }

        // Check stock availability
        $unavailable = StockService::checkStockAvailability($cartItems);

        if (! empty($unavailable)) {
            $messages = array_map(fn ($item) => "{$item['menu_item']} (needs {$item['required']}, only {$item['available']} available)",
                $unavailable
            );

            return redirect()->route('cart')->with('error', 'Some items are out of stock: '.implode(', ', $messages));
        }

        $total = $cartItems->sum(fn ($item) => (int) $item->quantity * $item->menuItem->price);

        try {
            $order = null;

            DB::transaction(function () use ($cartItems, $request, $total, &$order) {
                $orderData = [
                    'user_id' => auth()->id(),
                    'status' => 'pending',
                    'total' => $total,
                    'payment_method' => $request->payment_method,
                    'payment_status' => 'pending',
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
                        'payment_reference' => 'COD-' . $order->id . '-' . time(),
                    ]);
                }

                foreach ($cartItems as $cartItem) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'menu_item_id' => $cartItem->menu_item_id,
                        'quantity' => (int) $cartItem->quantity,
                        'price' => $cartItem->menuItem->price,
                    ]);
                }
            });

            // Dispatch order created event for email notification only if not using Stripe right now
            if ($request->payment_method !== 'stripe' && !str_starts_with($request->payment_method, 'saved_')) {
                event(new OrderCreated($order));
            }

            // Create kitchen ticket for the order
            KitchenTicket::create([
                'order_id' => $order->id,
                'status' => 'new',
            ]);

            // Handle payment based on method
            if (str_starts_with($request->payment_method, 'saved_')) {
                // Using saved payment method
                $paymentMethodId = str_replace('saved_', '', $request->payment_method);
                $paymentResult = $this->paymentService->createPaymentIntentWithSavedCard($order, $paymentMethodId, $cartItems);

                if ($paymentResult['status'] === 'succeeded') {
                    $order->update([
                        'payment_status' => 'paid',
                        'payment_reference' => $paymentResult['payment_intent'],
                        'status' => 'confirmed',
                    ]);
                    event(new OrderCreated($order));
                    Cart::where('user_id', auth()->id())->delete();

                    return redirect()->route('orders.show', $order->id)
                        ->with('success', 'Payment successful! Order confirmed.');
                }

                if ($paymentResult['status'] === 'requires_action') {
                    // Need 3D authentication - redirect to Stripe
                    return redirect($paymentResult['client_secret']);
                }

                throw new \Exception('Payment failed: ' . $paymentResult['status']);
            }

            if ($request->payment_method === 'stripe') {
                $saveCard = $request->boolean('save_card');

                // Only create customer when user wants to save card
                $customerId = null;
                if ($saveCard) {
                    $customerId = $this->paymentService->getOrCreateCustomer(auth()->user());
                }

                $paymentResult = $this->paymentService->processPayment($order, 'stripe', $cartItems, $saveCard, $customerId);

                if (isset($paymentResult['url'])) {
                    // Redirect to Stripe Checkout
                    return redirect($paymentResult['url']);
                }

                // If no URL returned, there was an error
                throw new \Exception($paymentResult['error'] ?? 'Failed to create payment session');
            }

            // For COD and other methods, clear cart and redirect to order page
            Cart::where('user_id', auth()->id())->delete();

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
        $orderId = $request->get('order_id');

        if (! $sessionId || ! $orderId) {
            return redirect()->route('cart')->with('error', 'Invalid payment verification');
        }

        try {
            $order = Order::findOrFail($orderId);

            // SECURITY: Ensure the order belongs to the currently authenticated user (IDOR fix)
            if ($order->user_id !== auth()->id()) {
                abort(403, 'This payment does not belong to your account.');
            }

            // Guard against already-processed orders
            if (in_array($order->payment_status, ['paid', 'verified'])) {
                return redirect()->route('orders.show', $order->id)
                    ->with('info', 'Payment was already confirmed.');
            }

            $paymentResult = $this->paymentService->verifyPayment($sessionId);

            if ($paymentResult['status'] === 'paid') {
                $order->update([
                    'payment_status' => 'paid',
                    'payment_reference' => $paymentResult['payment_intent'],
                    'status' => 'confirmed',
                ]);

                // Order is fully paid and confirmed via Stripe, so now we can emit the event
                event(new OrderCreated($order));

                Cart::where('user_id', auth()->id())->delete();

                return redirect()->route('orders.show', $order->id)
                    ->with('success', 'Payment successful! Order confirmed.');
            }

            return redirect()->route('cart')->with('error', 'Payment not completed');

        } catch (\Exception $e) {
            Log::error('Payment verification error: '.$e->getMessage());

            return redirect()->route('cart')->with('error', 'Payment verification failed');
        }
    }
}
