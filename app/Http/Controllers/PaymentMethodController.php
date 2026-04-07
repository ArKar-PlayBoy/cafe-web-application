<?php

namespace App\Http\Controllers;

use App\Exceptions\PaymentMethodOwnershipException;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
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
            'defaultCardId' => $user->default_payment_method_id,
        ]);
    }

    /**
     * Set a payment method as default
     */
    public function setDefault(Request $request): RedirectResponse
    {
        $request->validate([
            'payment_method_id' => 'required|string',
        ]);

        $user = auth()->user();

        if (! $user->stripe_customer_id) {
            return redirect()->route('payment-methods.index')
                ->with('error', 'No payment methods found.');
        }

        // Verify ownership of the payment method
        $paymentMethods = $this->paymentService->listSavedCards($user->stripe_customer_id);
        $validIds = array_column($paymentMethods, 'id');

        if (! in_array($request->payment_method_id, $validIds)) {
            abort(403, 'Invalid payment method.');
        }

        $user->update(['default_payment_method_id' => $request->payment_method_id]);

        return redirect()->route('payment-methods.index')
            ->with('success', 'Default payment method updated.');
    }

    /**
     * Delete a saved payment method
     */
    public function destroy(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'payment_method_id' => 'required|string',
        ]);

        $user = auth()->user();

        if (! $user->stripe_customer_id) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'No payment methods found.'], 404);
            }
            return redirect()->route('payment-methods.index')
                ->with('error', 'No payment methods found.');
        }

        // If deleting the default card, clear the default
        if ($user->default_payment_method_id === $request->payment_method_id) {
            $user->update(['default_payment_method_id' => null]);
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
                'blocked' => true,
            ]);

            if ($request->wantsJson()) {
                return response()->json(['error' => 'You are not allowed to remove this payment method.'], 403);
            }
            abort(403, 'You are not allowed to remove this payment method.');
        }

        if ($result) {
            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Payment method removed successfully.']);
            }
            return redirect()->route('payment-methods.index')
                ->with('success', 'Payment method removed successfully.');
        }

        Log::error('Payment method delete failed due to provider/service error.', [
            'user_id' => $user->id,
            'stripe_customer_id' => $user->stripe_customer_id,
            'failed' => true,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['error' => 'Failed to remove payment method.'], 500);
        }
        return redirect()->route('payment-methods.index')
            ->with('error', 'Failed to remove payment method.');
    }
}
