@extends('layouts.app')

@section('title', 'Cart')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-serif font-bold mb-6">Shopping Cart</h1>

    @if($cartItems->isEmpty())
    <div class="text-center py-12">
        <svg class="w-24 h-24 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
        <p class="text-gray-500 dark:text-gray-400 text-xl mb-4">Your cart is empty</p>
        <a href="{{ route('menu') }}" class="inline-block bg-green-600 text-white px-6 py-3 rounded-full hover:bg-green-700 transition-colors">Browse Menu</a>
    </div>
    @else
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-4">
            @php($cartTotal = 0)
            @foreach($cartItems as $item)
            @php($itemTotal = (int) $item->quantity * $item->menuItem->price)
            @php($cartTotal += $itemTotal)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 flex gap-4">
                <img src="{{ $item->menuItem->featured_image }}" alt="{{ $item->menuItem->name }}" class="w-24 h-24 object-cover rounded-lg">
                 <div class="flex-1">
                     <h3 class="font-semibold text-lg">{{ $item->menuItem->name }}</h3>
                     <p class="text-green-600 dark:text-green-400 font-bold">${{ number_format($item->menuItem->price, 2) }}</p>
                     <form action="{{ route('cart.update', $item->id) }}" method="POST" class="flex items-center gap-2 mt-2 cart-update-form">
                         @csrf @method('PUT')
                         <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="99" class="w-16 border dark:border-gray-600 dark:bg-gray-700 rounded px-2 py-1 quantity-input" data-price="{{ $item->menuItem->price }}" data-item-id="{{ $item->id }}">
                         <button type="submit" class="text-green-600 dark:text-green-400 hover:underline">Update</button>
                     </form>
                     @if($item->notes)
                     <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Note: {{ $item->notes }}</p>
                     @endif
                 </div>
                <div class="text-right">
                    <p class="font-bold text-lg item-total" data-item-total="{{ $itemTotal }}">${{ number_format($itemTotal, 2) }}</p>
                    <form action="{{ route('cart.remove', $item->id) }}" method="POST" class="mt-2">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline">Remove</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 h-fit">
            <h3 class="font-semibold text-xl mb-4">Order Summary</h3>
            <div class="flex justify-between mb-2 text-gray-600 dark:text-gray-400">
                <span>Subtotal</span>
                <span class="cart-subtotal">${{ number_format($cartTotal, 2) }}</span>
            </div>
            <div class="flex justify-between mb-4 pt-2 border-t dark:border-gray-700">
                <span>Total</span>
                <span class="font-bold text-xl text-green-600 dark:text-green-400 cart-total">${{ number_format($cartTotal, 2) }}</span>
            </div>
            <a href="{{ route('checkout') }}" class="block w-full bg-green-600 text-white text-center py-3 rounded-lg hover:bg-green-700 transition-colors">Proceed to Checkout</a>
        </div>
    </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const quantityInputs = document.querySelectorAll('.quantity-input');
    const cartTotalEl = document.querySelector('.cart-total');
    const cartSubtotalEl = document.querySelector('.cart-subtotal');

    function updateCartTotal() {
        let total = 0;
        document.querySelectorAll('.quantity-input').forEach(input => {
            const price = parseFloat(input.dataset.price);
            const qty = parseInt(input.value) || 1;
            total += price * qty;
            
            const itemTotalEl = input.closest('.bg-white').querySelector('.item-total');
            if (itemTotalEl) {
                itemTotalEl.textContent = '$' + (price * qty).toFixed(2);
            }
        });
        
        if (cartTotalEl) cartTotalEl.textContent = '$' + total.toFixed(2);
        if (cartSubtotalEl) cartSubtotalEl.textContent = '$' + total.toFixed(2);
    }

    quantityInputs.forEach(input => {
        const form = input.closest('form');
        input.addEventListener('input', updateCartTotal);
        input.addEventListener('change', () => {
            if (form) {
                form.submit();
            }
        });
    });
});
</script>
@endsection
