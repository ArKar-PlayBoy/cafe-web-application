@extends('layouts.app')

@section('title', 'Checkout')

@push('scripts')
@vite(['resources/js/checkout.js'])
@endpush

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8 lg:py-12">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="mb-8">
            <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">Checkout</h1>
            <p class="text-gray-500 dark:text-gray-400 font-medium mt-1">Complete your order details below</p>
        </div>

        @if($errors->any())
        <div class="bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-800/50 rounded-lg p-4 mb-6">
            <ul class="list-disc list-inside text-red-600 dark:text-red-400 text-sm font-medium">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Error/Success Messages --}}
        <div id="payment-error" class="hidden bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-800/50 rounded-lg p-4 mb-6">
            <p id="payment-error-message" class="text-red-600 dark:text-red-400 font-medium text-sm flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span></span>
            </p>
        </div>

        <form id="checkout-form" method="POST" action="{{ route('checkout.store') }}">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
                
                {{-- Left Column: Payment Details --}}
                <div class="lg:col-span-2 space-y-6">
                    
                    {{-- Payment Methods Section --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                        <h2 class="font-bold text-lg text-gray-900 dark:text-white mb-1 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            Payment Method
                        </h2>
                        <p class="text-gray-500 dark:text-gray-400 mb-4 text-sm">How would you like to pay?</p>
                        
                        <div class="space-y-3">
                            <x-payment-option value="cod" label="Cash on Delivery (COD)" icon="cash" :checked="old('payment_method') === 'cod'" />
                            <x-payment-option value="kbz_pay" label="KBZ Pay" icon="mobile" :checked="old('payment_method') === 'kbz_pay'" />
                        </div>
                        
                        {{-- Divider --}}
                        <div class="flex items-center my-4">
                            <div class="flex-1 h-px bg-gray-200 dark:bg-gray-700"></div>
                            <span class="px-3 text-xs font-medium text-gray-400 dark:text-gray-500">Or pay online</span>
                            <div class="flex-1 h-px bg-gray-200 dark:bg-gray-700"></div>
                        </div>
                        
                        <div class="space-y-3">
                            <x-payment-option value="stripe" label="Credit/Debit Card (Stripe)" icon="credit-card" :checked="old('payment_method') === 'stripe'" />
                        </div>

                        {{-- Saved Cards (loaded via AJAX when Stripe is selected) --}}
                        <div id="saved-cards-container" class="hidden mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Saved Cards</p>
                            <div id="saved-cards-list" class="space-y-3">
                                {{-- Cards loaded via AJAX --}}
                            </div>
                        </div>
                    </div>
                    
                    {{-- Save Card Checkbox --}}
                    <div id="save-card-section" class="hidden bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input 
                                type="checkbox" 
                                name="save_card" 
                                id="save_card" 
                                value="1"
                                class="w-4 h-4 text-emerald-600 rounded border-gray-300 dark:border-gray-600 focus:ring-emerald-500 cursor-pointer"
                            >
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                Save card for future visits
                            </span>
                        </label>
                    </div>

                    {{-- Conditional Payment Sections (inside left column) --}}
                    <x-cod-section />
                    <x-kbz-section :total="$total" />

                    @error('payment_method')
                    <p class="text-red-500 dark:text-red-400 text-sm font-medium">{{ $message }}</p>
                    @enderror
                </div>
                
                {{-- Right Column: Order Summary --}}
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 sticky top-24">
                        <h3 class="font-bold text-lg text-gray-900 dark:text-white mb-4 pb-4 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            Order Summary
                        </h3>
                        
                        <div class="max-h-[30vh] overflow-y-auto pr-2 mb-4 space-y-3 custom-scrollbar">
                            @forelse($cartItems as $item)
                            <div class="flex justify-between items-start text-sm">
                                <div class="flex gap-2 items-start">
                                    <span class="font-bold text-gray-900 dark:text-white bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded text-xs">{{ $item->quantity }}x</span>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ $item->menuItem->name }}</p>
                                        @if($item->notes)
                                        <p class="text-xs text-gray-500 dark:text-gray-400 italic">"{{ $item->notes }}"</p>
                                        @endif
                                    </div>
                                </div>
                                <span class="font-bold text-gray-900 dark:text-white shrink-0">${{ number_format($item->quantity * $item->menuItem->price, 2) }}</span>
                            </div>
                            @empty
                            <p class="text-gray-500 dark:text-gray-400 text-sm text-center py-4">Your cart is empty.</p>
                            @endforelse
                        </div>
                        
                        <div class="space-y-2 mb-4 text-sm">
                            <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                <span>Subtotal</span>
                                <span class="text-gray-900 dark:text-gray-100">${{ number_format($total, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                <span>Tax</span>
                                <span>$0.00</span>
                            </div>
                        </div>

                        <div class="flex justify-between items-center pt-4 border-t border-gray-200 dark:border-gray-700 mb-6">
                            <span class="font-bold text-gray-900 dark:text-white">Total</span>
                            <span class="font-bold text-xl text-gray-900 dark:text-white">${{ number_format($total, 2) }}</span>
                        </div>

                        {{-- Submit Button --}}
                        <button type="submit" id="pay-button" class="w-full bg-emerald-600 dark:bg-emerald-600 text-white py-3 rounded-lg font-bold hover:bg-emerald-700 dark:hover:bg-emerald-500 transition-colors text-base flex items-center justify-center gap-2">
                            <span id="pay-button-text">Place Order - ${{ number_format($total, 2) }}</span>
                            <span id="pay-button-loading" class="hidden flex items-center gap-2">
                                <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                Processing...
                            </span>
                        </button>
                        
                        <div class="mt-4 flex items-center justify-center gap-2 text-gray-400 dark:text-gray-500 text-xs font-medium uppercase tracking-wider">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            Secure Checkout
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
.dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #4b5563; }
</style>
@endsection
