{{-- COD Delivery Section Component --}}
<div id="cod-section" x-data="{ address: '{{ old('delivery_address', '') }}', phone: '{{ old('delivery_phone', '') }}' }" class="bg-gray-50 dark:bg-gray-800/40 rounded-lg border border-gray-200 dark:border-gray-700 p-5 mb-6 hidden transition-all">
    <div class="flex items-center gap-2 mb-4">
        <div class="w-8 h-8 rounded bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        </div>
        <div>
            <h2 class="text-sm font-bold text-gray-900 dark:text-gray-100">Delivery Details</h2>
            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mt-0.5">Cash on Delivery</p>
        </div>
    </div>
    
    <div class="space-y-4">
        <div>
            <label for="delivery_address" class="flex items-center justify-between mb-1.5">
                <span class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Delivery Address <span class="text-red-500">*</span></span>
                <svg x-show="address.trim().length >= 10" x-cloak class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </label>
            <textarea 
                name="delivery_address" 
                id="delivery_address" 
                x-model="address"
                rows="3"
                class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-gray-900 dark:text-gray-100 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm placeholder:text-gray-400"
                placeholder="E.g., 123 Cafe Street, Floor 2, Yangon"
            >{{ old('delivery_address') }}</textarea>
            @error('delivery_address')
                <p class="text-red-500 text-xs font-semibold mt-1">{{ $message }}</p>
            @enderror
        </div>
        
        <div>
            <label for="delivery_phone" class="flex items-center justify-between mb-1.5">
                <span class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Phone Number <span class="text-red-500">*</span></span>
                <svg x-show="phone.trim().length >= 6" x-cloak class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </label>
            <input 
                type="tel" 
                name="delivery_phone" 
                id="delivery_phone" 
                x-model="phone"
                class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-gray-900 dark:text-gray-100 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm placeholder:text-gray-400"
                placeholder="E.g., 09xxxxxxxxx"
                value="{{ old('delivery_phone') }}"
            >
            @error('delivery_phone')
                <p class="text-red-500 text-xs font-semibold mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
