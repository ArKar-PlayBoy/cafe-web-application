<?php

namespace App\Http\Controllers;

use App\Exceptions\PaymentMethodOwnershipException;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PaymentMethodController extends Controller
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    /**
     * List saved payment methods for the authenticated user
     */
    public function index(): View
    {
        $user = auth()->user();

        if (! $user->stripe_customer_id) {
            return view('customer.payment-methods.index', [
                'paymentMethods' => [],
                'hasStripeCustomer' => false,
            ]);
        }

        $paymentMethods = $this->paymentService->listSavedCards($user->stripe_customer_id);

        return view('customer.payment-methods.index', [
            'paymentMethods' => $paymentMethods,
            'hasStripeCustomer' => true,
        ]);
    }

    /**
     * Delete a saved payment method
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'payment_method_id' => 'required|string',
        ]);

        $user = auth()->user();

        if (! $user->stripe_customer_id) {
            return redirect()->route('payment-methods.index')
                ->with('error', 'No payment methods found.');
        }

        try {
            $result = $this->paymentService->deletePaymentMethod(
                $request->payment_method_id,
                $user->stripe_customer_id
            );
        } catch (PaymentMethodOwnershipException $e) {
            Log::warning('Payment method delete denied by ownership check.', [
                'user_id' => $user->id,
                'stripe_customer_id' => $user->stripe_customer_id,
                'payment_method_id' => $request->payment_method_id,
            ]);

            abort(403, 'You are not allowed to remove this payment method.');
        }

        if ($result) {
            return redirect()->route('payment-methods.index')
                ->with('success', 'Payment method removed successfully.');
        }

        Log::error('Payment method delete failed due to provider/service error.', [
            'user_id' => $user->id,
            'stripe_customer_id' => $user->stripe_customer_id,
            'payment_method_id' => $request->payment_method_id,
        ]);

        return redirect()->route('payment-methods.index')
            ->with('error', 'Failed to remove payment method.');
    }
}
