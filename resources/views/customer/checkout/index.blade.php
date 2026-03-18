@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
<div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-8 py-6 sm:py-8">
    <h1 class="text-2xl sm:text-3xl font-serif font-bold mb-4 sm:mb-6">Checkout</h1>

    @if($errors->any())
    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3 sm:p-4 mb-4 sm:mb-6">
        <ul class="list-disc list-inside text-red-600 dark:text-red-400 text-sm">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
        <div class="lg:col-span-2 order-2 lg:order-1">
            <form action="{{ route('checkout.store') }}" method="POST">
                @csrf
                
                {{-- Payment Method Section --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 sm:p-6 mb-4 sm:mb-6">
                    <h2 class="text-lg sm:text-xl font-semibold mb-4">Payment Method</h2>
                    <div class="space-y-2 sm:space-y-3">
                        {{-- Saved Cards Section --}}
                        @if(!empty($savedCards))
                        <div class="mb-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">Saved Payment Methods</p>
                            @foreach($savedCards as $card)
                            <label class="flex items-center p-3 sm:p-4 border dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 mb-2 {{ $errors->has('payment_method') ? 'border-red-500' : '' }}">
                                <input type="radio" name="payment_method" value="saved_{{ $card['id'] }}" class="mt-1 sm:mt-0 mr-3 text-green-600 payment-method-radio" {{ old('payment_method') == 'saved_'.$card['id'] ? 'checked' : '' }}>
                                <span class="font-medium flex items-center gap-2">
                                    <svg class="w-5 h-5 sm:w-6 sm:h-6 shrink-0" viewBox="0 0 24 24" fill="none">
                                        <path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <span class="text-sm sm:text-base">
                                        {{ ucfirst($card['brand']) }} **** {{ $card['last4'] }} (Expires {{ $card['exp_month'] }}/{{ $card['exp_year'] }})
                                    </span>
                                </span>
                            </label>
                            @endforeach
                        </div>
                        <div class="border-t dark:border-gray-600 my-4"></div>
                        @endif

                        {{-- New Card Option --}}
                        <label class="flex items-start sm:items-center p-3 sm:p-4 border dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 {{ $errors->has('payment_method') ? 'border-red-500' : '' }}">
                            <input type="radio" name="payment_method" value="stripe" class="mt-1 sm:mt-0 mr-3 text-green-600 payment-method-radio" {{ empty($savedCards) || old('payment_method') == 'stripe' ? 'checked' : '' }}>
                            <span class="font-medium flex items-center gap-2">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 shrink-0" viewBox="0 0 24 24" fill="none">
                                    <path d="M13.976 9.15c-2.172-.806-3.356-1.426-3.356-2.409 0-.831.683-1.305 1.901-1.305 2.227 0 4.515.858 6.09 1.631l.89-5.494C18.252.975 15.697 0 12.165 0 9.667 0 7.589.654 6.104 1.872 4.56 3.147 3.757 4.992 3.757 7.218c0 4.039 2.467 5.76 6.476 7.219 2.585.92 3.445 1.574 3.445 2.583 0 .98-.84 1.545-2.354 1.545-1.875 0-4.965-.921-6.99-2.109l-.9 5.555C5.175 22.99 8.385 24 11.714 24c2.641 0 4.843-.624 6.328-1.813 1.664-1.305 2.525-3.236 2.525-5.732 0-4.128-2.524-5.851-6.591-7.305z" fill="#635BFF"/>
                                </svg>
                                <span class="text-sm sm:text-base">Credit/Debit Card (Stripe)</span>
                            </span>
                        </label>
                        <label class="flex items-center p-3 sm:p-4 border dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 {{ $errors->has('payment_method') ? 'border-red-500' : '' }}">
                            <input type="radio" name="payment_method" value="cod" class="mr-3 text-green-600 payment-method-radio" {{ old('payment_method') == 'cod' ? 'checked' : '' }}>
                            <span class="font-medium text-sm sm:text-base">Cash on Delivery (COD)</span>
                        </label>
                        <label class="flex items-center p-3 sm:p-4 border dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 {{ $errors->has('payment_method') ? 'border-red-500' : '' }}">
                            <input type="radio" name="payment_method" value="kbz_pay" class="mr-3 text-green-600 payment-method-radio" {{ old('payment_method') == 'kbz_pay' ? 'checked' : '' }}>
                            <span class="font-medium text-sm sm:text-base">KBZ Pay</span>
                        </label>
                    </div>
                    @error('payment_method')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror

                    {{-- Save Card Checkbox - Only show when Stripe is selected --}}
                    <div class="mt-4 pt-4 border-t dark:border-gray-600" id="save-card-section">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="save_card" value="1" class="w-4 h-4 text-green-600 rounded border-gray-300 focus:ring-green-500" {{ old('save_card') ? 'checked' : '' }}>
                            <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Save this card for future purchases</span>
                        </label>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 ml-6">Manage saved cards at <a href="{{ route('payment-methods.index') }}" class="text-green-600 hover:underline">My Payment Methods</a></p>
                    </div>

                    {{-- KBZ Pay Instructions - TODO: Add QR Code manually --}}
                    <div class="mt-4 pt-4 border-t dark:border-gray-600 hidden" id="kbz-pay-section">
                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                            <h3 class="font-semibold text-blue-800 dark:text-blue-300 mb-2">Payment Transfer Instructions</h3>
                            <p class="text-sm text-blue-700 dark:text-blue-400 mb-3">Please transfer <strong>${{ number_format($total, 2) }}</strong> using your payment app:</p>
                            
                            {{-- TODO: Add your QR code image here --}}
                            {{-- Example: <img src="{{ asset('images/kbz-pay-qr.png') }}" alt="Payment QR Code" class="max-w-xs mx-auto"> --}}
                            
                            <p class="text-xs text-blue-600 dark:text-blue-400">After transfer, you can upload the payment screenshot on the order details page.</p>
                        </div>
                    </div>
                </div>

                {{-- Delivery Information (Only for COD) --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 sm:p-6 mb-4 sm:mb-6 hidden" id="cod-delivery-section">
                    <h2 class="text-lg sm:text-xl font-semibold mb-4">Customer Information</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Please provide delivery details for Cash on Delivery orders.</p>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="delivery_address" class="block text-sm font-medium mb-2">Customer Address <span class="text-red-500">*</span></label>
                            <textarea 
                                name="delivery_address" 
                                id="delivery_address" 
                                rows="3"
                                class="w-full border dark:border-gray-600 rounded-lg px-4 py-3 dark:bg-gray-700 focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                placeholder="Enter complete delivery address..."
                            >{{ old('delivery_address') }}</textarea>
                            @error('delivery_address')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="delivery_phone" class="block text-sm font-medium mb-2">Phone Number <span class="text-red-500">*</span></label>
                            <input 
                                type="tel" 
                                name="delivery_phone" 
                                id="delivery_phone" 
                                class="w-full border dark:border-gray-600 rounded-lg px-4 py-3 dark:bg-gray-700 focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                placeholder="Enter phone number for delivery contact..."
                                value="{{ old('delivery_phone') }}"
                            >
                            @error('delivery_phone')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full bg-green-600 text-white py-3 sm:py-4 rounded-lg hover:bg-green-700 transition-colors font-semibold text-sm sm:text-base">Place Order - ${{ number_format($total, 2) }}</button>
            </form>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 sm:p-6 h-fit order-1 lg:order-2">
            <h3 class="font-semibold text-lg sm:text-xl mb-4">Order Summary</h3>
            <div class="max-h-60 overflow-y-auto">
                @forelse($cartItems as $item)
                <div class="flex justify-between py-2 text-sm sm:text-base text-gray-600 dark:text-gray-400">
                    <span class="break-words">{{ $item->quantity }}x {{ $item->menuItem->name }}</span>
                    <span class="shrink-0 ml-2">${{ number_format($item->quantity * $item->menuItem->price, 2) }}</span>
                </div>
                @empty
                <p class="text-gray-500 dark:text-gray-400 text-sm">Your cart is empty.</p>
                @endforelse
            </div>
            <hr class="my-3 dark:border-gray-700">
            <div class="flex justify-between font-bold text-lg">
                <span>Total</span>
                <span class="text-green-600 dark:text-green-400">${{ number_format($total, 2) }}</span>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const stripeRadio = document.querySelector('input[name="payment_method"][value="stripe"]');
    const codRadio = document.querySelector('input[name="payment_method"][value="cod"]');
    const saveCardSection = document.getElementById('save-card-section');
    const codDeliverySection = document.getElementById('cod-delivery-section');
    const kbzPaySection = document.getElementById('kbz-pay-section');

    function toggleSections() {
        const selected = document.querySelector('input[name="payment_method"]:checked');
        if (!selected) return;
        
        const isSavedCard = selected.value.startsWith('saved_');
        
        if (selected.value === 'cod') {
            saveCardSection.style.display = 'none';
            codDeliverySection.classList.remove('hidden');
            kbzPaySection.classList.add('hidden');
        } else if (selected.value === 'stripe' || isSavedCard) {
            saveCardSection.style.display = isSavedCard ? 'none' : 'block';
            codDeliverySection.classList.add('hidden');
            kbzPaySection.classList.add('hidden');
        } else if (selected.value === 'kbz_pay') {
            saveCardSection.style.display = 'none';
            codDeliverySection.classList.add('hidden');
            kbzPaySection.classList.remove('hidden');
        } else {
            saveCardSection.style.display = 'none';
            codDeliverySection.classList.add('hidden');
            kbzPaySection.classList.add('hidden');
        }
    }

    document.querySelectorAll('.payment-method-radio').forEach(function(radio) {
        radio.addEventListener('change', toggleSections);
    });

    toggleSections();
});
</script>
@endsection
