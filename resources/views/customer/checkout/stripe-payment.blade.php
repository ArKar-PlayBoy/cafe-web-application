@extends('layouts.app')

@section('title', 'Payment')

@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8 lg:py-12">
    <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="mb-8">
            <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">Complete Payment</h1>
            <p class="text-gray-500 dark:text-gray-400 font-medium mt-1">Order #{{ $orderId }}</p>
        </div>

        {{-- Error Messages --}}
        @if(session('error'))
        <div class="bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-800/50 rounded-lg p-4 mb-6">
            <p class="text-red-600 dark:text-red-400 font-medium text-sm">
                {{ session('error') }}
            </p>
        </div>
        @endif

        {{-- Success Messages --}}
        @if(session('success'))
        <div class="bg-green-50 dark:bg-green-900/10 border border-green-200 dark:border-green-800/50 rounded-lg p-4 mb-6">
            <p class="text-green-600 dark:text-green-400 font-medium text-sm">
                {{ session('success') }}
            </p>
        </div>
        @endif

        {{-- Order Summary --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 mb-6">
            <div class="flex justify-between items-center">
                <span class="text-gray-700 dark:text-gray-300 font-medium">Total Amount</span>
                <span class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">${{ number_format($amount, 2) }}</span>
            </div>
        </div>

        {{-- Stripe Payment Form --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <form id="payment-form" method="POST">
                @csrf
                <input type="hidden" name="client_secret" value="{{ $clientSecret }}">
                
                <div id="payment-element">
                    <!-- Stripe Elements will be inserted here -->
                </div>

                <div id="payment-message" class="hidden mt-4 p-3 rounded-lg text-sm"></div>

                <button type="submit" id="submit" class="w-full mt-6 bg-emerald-600 text-white py-3 rounded-lg font-medium hover:bg-emerald-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <span id="button-text">Pay ${{ number_format($amount, 2) }}</span>
                    <span id="button-loading" class="hidden">
                        <svg class="animate-spin inline-block w-5 h-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Processing...
                    </span>
                </button>
            </form>

            <div class="mt-4 text-center">
                <a href="{{ route('cart') }}" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    ← Back to Cart
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const stripe = Stripe('{{ config("stripe.publishable") }}');
    const clientSecret = '{{ $clientSecret }}';

    const elements = stripe.elements({
        clientSecret: clientSecret,
        appearance: {
            theme: 'stripe',
            variables: {
                colorPrimary: '#10b981',
                colorBackground: '#ffffff',
                colorText: '#1f2937',
                colorDanger: '#ef4444',
                fontFamily: 'system-ui, sans-serif',
                borderRadius: '0.5rem',
            },
        }
    });

    const paymentElement = elements.create('payment');
    paymentElement.mount('#payment-element');

    const form = document.getElementById('payment-form');
    const submitButton = document.getElementById('submit');
    const buttonText = document.getElementById('button-text');
    const buttonLoading = document.getElementById('button-loading');
    const messageDiv = document.getElementById('payment-message');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        setLoading(true);

        const { error } = await stripe.confirmPayment({
            elements,
            confirmParams: {
                return_url: '{{ url("/checkout/verify") }}?order_id={{ $orderId }}',
            },
        });

        if (error) {
            showMessage(error.message, 'error');
            setLoading(false);
        }
        // If successful, Stripe redirects automatically
    });

    function showMessage(messageText, type) {
        messageDiv.textContent = messageText;
        messageDiv.classList.remove('hidden', 'bg-green-50', 'text-green-700', 'bg-red-50', 'text-red-700');
        
        if (type === 'error') {
            messageDiv.classList.add('bg-red-50', 'text-red-700', 'dark:bg-red-900/30', 'dark:text-red-400');
        } else {
            messageDiv.classList.add('bg-green-50', 'text-green-700', 'dark:bg-green-900/30', 'dark:text-green-400');
        }
    }

    function setLoading(isLoading) {
        submitButton.disabled = isLoading;
        buttonText.classList.toggle('hidden', isLoading);
        buttonLoading.classList.toggle('hidden', !isLoading);
    }
});
</script>
@endsection
