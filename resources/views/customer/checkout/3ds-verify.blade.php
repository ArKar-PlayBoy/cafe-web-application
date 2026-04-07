@extends('layouts.app')

@section('title', 'Verifying Payment')

@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8 lg:py-12">
    <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="mb-8">
            <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">Payment Verification</h1>
            <p class="text-gray-500 dark:text-gray-400 font-medium mt-1">Verifying your payment with 3D Secure</p>
        </div>

        {{-- Error Messages --}}
        @if(session('error'))
        <div class="bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-800/50 rounded-lg p-4 mb-6">
            <p class="text-red-600 dark:text-red-400 font-medium text-sm">
                {{ session('error') }}
            </p>
        </div>
        @endif

        {{-- Loading State --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <div class="text-center py-8">
                <svg class="animate-spin w-12 h-12 mx-auto text-emerald-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-gray-700 dark:text-gray-300 font-medium mt-4">Please wait while we verify your payment...</p>
                <p class="text-gray-500 dark:text-gray-400 text-sm mt-2">You may be redirected to your bank for verification</p>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const stripe = Stripe('{{ config("stripe.publishable") }}');
    const clientSecret = '{{ $clientSecret }}';
    const paymentIntentId = '{{ $paymentIntentId }}';
    const orderId = '{{ $orderId }}';

    // Automatically confirm the payment with 3DS
    stripe.confirmPayment({
        clientSecret: clientSecret,
        confirmParams: {
            return_url: '{{ url("/checkout/verify") }}?payment_intent=' + paymentIntentId + '&order_id=' + orderId,
        },
        redirect: 'if_required'
    }).then(function(result) {
        if (result.error) {
            // Show error and redirect to checkout
            window.location.href = '{{ route("checkout") }}?error=' + encodeURIComponent(result.error.message);
        }
        // If successful, Stripe redirects automatically to return_url
    });
});
</script>
@endsection