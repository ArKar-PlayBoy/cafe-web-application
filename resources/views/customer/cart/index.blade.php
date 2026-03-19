@extends('layouts.app')

@section('title', 'Your Cart')

@section('content')
<div class="min-h-screen bg-white dark:bg-gray-900 py-12 font-sans text-gray-900 dark:text-gray-100">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold">Your Cart</h1>
            <a href="{{ route('menu') }}" class="text-sm font-semibold text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 underline decoration-2 underline-offset-4 transition-colors">Continue Shopping</a>
        </div>

        @if($cartItems->isEmpty())
        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-12 sm:p-20 text-center border border-gray-200 dark:border-gray-700 flex flex-col items-center justify-center min-h-[50vh]">
            <div class="w-24 h-24 bg-gray-200 dark:bg-gray-700 rounded-full flex items-center justify-center mb-6">
                <!-- Adorable Coffee Cup Icon -->
                <svg class="w-12 h-12 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 8h-2.5m-15 0h2.5m15 0c1.05 0 1.9.8 2 1.83.22 2.2-1.37 5.17-6 5.17M17.5 8v6c0 1.66-1.34 3-3 3h-5c-1.66 0-3-1.34-3-3V8m11 0H6.5" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 2v3m4-3v3" />
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Your cart feels a bit light</h2>
            <p class="text-gray-500 dark:text-gray-400 mb-8 max-w-sm mx-auto">Looks like you haven't added any delicious treats to your cart yet. Let's fix that!</p>
            <div class="flex flex-col sm:flex-row gap-4 items-center justify-center">
                <a href="{{ route('menu') }}" class="inline-block bg-emerald-600 text-white px-8 py-4 rounded-lg font-bold hover:bg-emerald-700 transition-all duration-300 w-full sm:w-auto">Browse Menu</a>
                <a href="{{ route('menu') }}#specials" class="inline-block bg-white dark:bg-gray-800 text-emerald-600 dark:text-emerald-400 border border-gray-200 dark:border-gray-600 px-8 py-4 rounded-lg font-bold hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-300 w-full sm:w-auto">Today's Specials</a>
            </div>
        </div>
        @else
        <div class="flex flex-col lg:flex-row gap-8 lg:gap-12">
            <div class="lg:w-2/3 space-y-4 text-sm">
                @php($cartTotal = 0)
                @foreach($cartItems as $item)
                @php($itemTotal = (int) $item->quantity * $item->menuItem->price)
                @php($cartTotal += $itemTotal)
                <div class="bg-white dark:bg-gray-800/80 rounded-xl border border-gray-200 dark:border-gray-700 p-4 sm:p-5 flex flex-col sm:flex-row gap-5 items-center group relative overflow-hidden transition-all">
                    <img src="{{ $item->menuItem->featured_image }}" alt="{{ $item->menuItem->name }}" class="w-full sm:w-24 h-40 sm:h-24 object-cover rounded-lg bg-gray-100 dark:bg-gray-700 shrink-0 border border-gray-100 dark:border-gray-600">
                     
                     <div class="flex-1 flex flex-col w-full h-full justify-center">
                         <div class="flex justify-between items-start w-full">
                             <div>
                                <h3 class="font-bold text-lg text-gray-900 dark:text-gray-100 leading-tight mb-0.5">{{ $item->menuItem->name }}</h3>
                                <p class="text-gray-600 dark:text-gray-300 font-semibold">${{ number_format($item->menuItem->price, 2) }} <span class="text-xs font-normal text-gray-500 dark:text-gray-400 ml-0.5">each</span></p>
                             </div>
                             <form action="{{ route('cart.remove', $item->id) }}" method="POST" class="shrink-0 ml-4">
                                 @csrf @method('DELETE')
                                 <button type="submit" class="p-2 text-gray-400 dark:text-gray-500 hover:text-red-500 dark:hover:text-red-400 transition-colors" title="Remove Item">
                                     <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                 </button>
                             </form>
                         </div>
                         
                         @if($item->notes)
                         <p class="text-sm font-medium text-gray-500 dark:text-gray-400 italic mt-2 bg-gray-50 dark:bg-gray-800 p-2.5 rounded-lg border border-gray-100 dark:border-gray-600">"{{ $item->notes }}"</p>
                         @endif

                         <div class="flex items-center justify-between mt-4 md:mt-auto pt-3">
                             <form action="{{ route('cart.update', $item->id) }}" method="POST" class="flex items-center border border-gray-200 dark:border-gray-600 rounded-lg cart-update-form bg-white dark:bg-gray-800">
                                 @csrf @method('PUT')
                                 <button type="button" class="w-8 h-8 flex items-center justify-center text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors qty-btn minus" data-target="qty-{{ $item->id }}">−</button>
                                 <input type="number" id="qty-{{ $item->id }}" name="quantity" value="{{ $item->quantity }}" min="1" max="99" class="w-10 text-center bg-transparent border-x border-gray-200 dark:border-gray-600 p-0 font-semibold text-gray-900 dark:text-white focus:ring-0 quantity-input h-8" data-price="{{ $item->menuItem->price }}" data-item-id="{{ $item->id }}">
                                 <button type="button" class="w-8 h-8 flex items-center justify-center text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors qty-btn plus" data-target="qty-{{ $item->id }}">+</button>
                             </form>
                             <div class="text-right">
                                 <p class="font-bold text-lg text-gray-900 dark:text-gray-100 item-total" data-item-total="{{ $itemTotal }}">${{ number_format($itemTotal, 2) }}</p>
                             </div>
                         </div>
                     </div>
                 </div>
                 @endforeach
             </div>

             <div class="lg:w-1/3">
                 <div class="bg-gray-50 dark:bg-gray-800/40 rounded-xl border border-gray-200 dark:border-gray-700 p-6 sm:p-8 sticky top-8">
                     <h3 class="font-bold text-lg text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                         <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                         Order Summary
                     </h3>
                     
                     <div class="space-y-4 mb-6 pb-6 border-b border-gray-200 dark:border-gray-700 text-sm">
                         <div class="flex justify-between font-medium text-gray-600 dark:text-gray-400">
                             <span>Subtotal</span>
                             <span class="font-semibold text-gray-900 dark:text-white cart-subtotal">${{ number_format($cartTotal, 2) }}</span>
                         </div>
                         <div class="flex justify-between font-medium text-gray-600 dark:text-gray-400">
                             <span>Estimated Tax</span>
                             <span class="text-gray-400 dark:text-gray-500">Calculated at checkout</span>
                         </div>
                     </div>
                     
                     <div class="flex justify-between mb-8">
                         <span class="font-bold text-gray-900 dark:text-white">Total</span>
                         <span class="font-bold text-2xl text-gray-900 dark:text-white cart-total">${{ number_format($cartTotal, 2) }}</span>
                     </div>
                     
                     <a href="{{ route('checkout') }}" class="block w-full bg-emerald-600 text-white text-center py-4 rounded-lg font-bold hover:bg-emerald-700 transition-all duration-300">
                         Proceed to Checkout
                     </a>
                     
                     <div class="mt-6 flex items-center justify-center gap-2 text-gray-400 dark:text-gray-500 text-xs font-semibold uppercase tracking-widest">
                         <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                         Secure Checkout
                     </div>
                 </div>
             </div>
        </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const quantityInputs = document.querySelectorAll('.quantity-input');
    const cartTotalEl = document.querySelector('.cart-total');
    const cartSubtotalEl = document.querySelector('.cart-subtotal');
    
    // Custom + / - buttons logic
    document.querySelectorAll('.qty-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.dataset.target;
            const input = document.getElementById(targetId);
            const form = input.closest('form');
            
            let val = parseInt(input.value) || 1;
            if(this.classList.contains('plus') && val < 99) {
                input.value = val + 1;
                updateCartTotal();
                if(form) form.submit();
            } else if(this.classList.contains('minus') && val > 1) {
                input.value = val - 1;
                updateCartTotal();
                if(form) form.submit();
            }
        });
    });

    function updateCartTotal() {
        let total = 0;
        document.querySelectorAll('.quantity-input').forEach(input => {
            const price = parseFloat(input.dataset.price);
            const qty = parseInt(input.value) || 1;
            total += price * qty;
            
            const itemTotalEl = input.closest('.flex-1').querySelector('.item-total');
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
