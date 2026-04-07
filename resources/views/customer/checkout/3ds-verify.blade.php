@extends('layouts.app')

@section('title', 'Verifying Payment')

@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
@endpush

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 py-12 flex items-center justify-center relative overflow-hidden">
    {{-- Decorative Background Blurs --}}
    <div class="absolute -top-32 -left-32 w-96 h-96 bg-emerald-400/20 dark:bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-blue-400/20 dark:bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-md w-full mx-auto px-4 sm:px-6 z-10">
        
        <div class="bg-white/80 dark:bg-gray-900/80 backdrop-blur-xl border border-white/50 dark:border-gray-700/50 rounded-3xl shadow-2xl overflow-hidden p-6 sm:p-8 ring-1 ring-black/5 dark:ring-white/10">
            <div class="mb-6 text-center">
                <div class="w-16 h-16 bg-gradient-to-tr from-emerald-500 to-teal-400 rounded-2xl rotate-3 flex items-center justify-center mx-auto mb-5 shadow-lg shadow-emerald-500/30 text-white animate-pulse">
                     <svg class="w-8 h-8 -rotate-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">Payment Verification</h1>
                <p class="text-gray-500 dark:text-gray-400 font-medium mt-1 text-sm">Verifying your payment with 3D Secure</p>
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
        <div>
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