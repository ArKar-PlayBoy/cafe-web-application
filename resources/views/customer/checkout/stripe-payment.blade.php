@extends('layouts.app')

@section('title', 'Payment')

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
                <div class="w-16 h-16 bg-gradient-to-tr from-emerald-500 to-teal-400 rounded-2xl rotate-3 flex items-center justify-center mx-auto mb-5 shadow-lg shadow-emerald-500/30 text-white transform hover:rotate-6 transition-transform">
                     <svg class="w-8 h-8 -rotate-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </div>
                <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">Complete Payment</h1>
                <p class="text-gray-500 dark:text-gray-400 font-medium mt-1 text-sm">Order #{{ $orderId }}</p>
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
        <div class="bg-gray-50/50 dark:bg-gray-800/30 rounded-2xl border border-gray-100 dark:border-gray-700 p-5 mb-6">
            <div class="flex justify-between items-center">
                <span class="text-gray-600 dark:text-gray-400 font-semibold text-sm uppercase tracking-wider">Total Amount</span>
                <span class="text-3xl font-black text-emerald-600 dark:text-emerald-400">${{ number_format($amount, 2) }}</span>
            </div>
        </div>

        {{-- Stripe Payment Form --}}
        <div>
            <form id="payment-form" method="POST">
                @csrf
                <input type="hidden" name="client_secret" value="{{ $clientSecret }}">
                
                <div id="payment-element">
                    <!-- Stripe Elements will be inserted here -->
                </div>

                <div id="payment-message" class="hidden mt-4 p-3 rounded-lg text-sm"></div>

                <button type="submit" id="submit" class="w-full mt-6 bg-gradient-to-r from-emerald-600 to-teal-600 text-white py-3.5 rounded-xl font-bold text-lg hover:from-emerald-700 hover:to-teal-700 hover:shadow-lg hover:shadow-emerald-500/20 transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:shadow-none focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 dark:focus:ring-offset-gray-900">
                    <span id="button-text">Pay ${{ number_format($amount, 2) }}</span>
                    <span id="button-loading" class="hidden flex items-center justify-center gap-2">
                        <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Processing...
                    </span>
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="{{ route('cart') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 transition-colors font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Cancel and return to Cart
                </a>
            </div>
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
            theme: 'flat',
            variables: {
                colorPrimary: '#059669', // emerald-600
                colorBackground: 'transparent',
                colorText: '#1f2937',
                colorDanger: '#ef4444',
                fontFamily: 'system-ui, sans-serif',
                spacingUnit: '4px',
                borderRadius: '8px',
            },
            rules: {
                '.Input': {
                    backgroundColor: '#ffffff',
                    border: '1px solid #d1d5db',
                    boxShadow: '0 1px 2px 0 rgba(0, 0, 0, 0.05)',
                    padding: '12px',
                },
                '.Input:focus': {
                    border: '1px solid #059669',
                    boxShadow: '0 0 0 1px #059669',
                },
                '.Label': {
                    fontWeight: '500',
                    fontSize: '0.875rem',
                    marginBottom: '6px',
                }
            }
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
