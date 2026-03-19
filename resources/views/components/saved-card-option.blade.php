{{-- Saved Card Option Component --}}
@props([
    'card',
    'index' => 0
])

<label class="flex items-center p-4 cursor-pointer payment-option-label relative group transition-all duration-200 border-2 rounded-xl border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:border-gray-300 dark:hover:border-gray-600">
    <div class="relative flex items-center mr-4">
        <input 
            type="radio" 
            name="payment_method" 
            value="saved_{{$card['id']}}" 
            class="w-4 h-4 text-emerald-600 dark:text-emerald-400 border-gray-300 dark:border-gray-600 focus:ring-emerald-500 dark:focus:ring-emerald-400 focus:ring-offset-0 transition-all"
        >
    </div>
    
    <div class="font-medium flex items-center justify-between w-full text-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 flex items-center justify-center shrink-0">
                @if(strtolower($card['brand'] ?? '') === 'visa')
                    <span class="font-bold text-blue-600 text-xs">VISA</span>
                @elseif(strtolower($card['brand'] ?? '') === 'mastercard')
                    <span class="font-bold text-red-500 text-xs">MC</span>
                @else
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="5" width="20" height="14" rx="2" ry="2"/>
                        <line x1="2" y1="10" x2="22" y2="10"/>
                    </svg>
                @endif
            </div>
            
            <div class="flex flex-col">
                <span class="font-semibold text-gray-900 dark:text-gray-100">
                    {{ ucfirst($card['brand'] ?? 'Card') }} ending in {{ $card['last4'] ?? '****' }}
                </span>
                <span class="text-[11px] text-gray-500 dark:text-gray-400 uppercase tracking-wider mt-0.5">
                    Expires {{ str_pad($card['exp_month'] ?? '00', 2, '0', STR_PAD_LEFT) }}/{{ substr($card['exp_year'] ?? '0000', -2) }}
                </span>
            </div>
        </div>
        
        <span class="text-gray-400 dark:text-gray-500 group-hover:text-emerald-500 dark:group-hover:text-emerald-400 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </span>
    </div>
</label>
