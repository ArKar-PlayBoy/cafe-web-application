{{-- Payment Option Component --}}
@props([
    'value',
    'label',
    'icon' => 'credit-card',
    'checked' => false,
    'showIcon' => true
])

<label class="flex items-center p-4 cursor-pointer payment-option-label relative group transition-all duration-200
    {{ $checked 
        ? 'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-500 dark:border-emerald-400 ring-1 ring-emerald-500 dark:ring-emerald-400' 
        : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:border-gray-300 dark:hover:border-gray-600' 
    }}
    {{ $errors->has('payment_method') ? 'border-red-400 dark:border-red-500 bg-red-50/50 dark:bg-red-900/10' : '' }}
    border-2 rounded-xl
">
    <div class="relative flex items-center mr-4">
        <input 
            type="radio" 
            name="payment_method" 
            value="{{ $value }}" 
            class="w-4 h-4 text-emerald-600 dark:text-emerald-400 border-gray-300 dark:border-gray-600 focus:ring-emerald-500 dark:focus:ring-emerald-400 focus:ring-offset-0 transition-all"
            {{ $checked ? 'checked' : '' }}
        >
    </div>
    
    <div class="font-medium flex items-center gap-3 flex-1">
        @if($showIcon)
            <div class="{{ $checked ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400 dark:text-gray-500' }} transition-colors">
                @switch($icon)
                    @case('credit-card')
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="5" width="20" height="14" rx="2" ry="2"/>
                            <line x1="2" y1="10" x2="22" y2="10"/>
                        </svg>
                        @break
                    @case('cash')
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        @break
                    @case('mobile')
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 1a3 3 0 00-3 3v8a3 3 0 006 0V4a3 3 0 00-3-3z"/>
                            <path d="M19 10H5a2 2 0 00-2 2v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 00-2-2z"/>
                            <line x1="12" y1="19" x2="12" y2="23"/>
                        </svg>
                        @break
                    @default
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                @endswitch
            </div>
        @endif
        <span class="text-sm sm:text-base {{ $checked ? 'text-emerald-700 dark:text-emerald-300 font-semibold' : 'text-gray-700 dark:text-gray-200' }} transition-colors">{{ $label }}</span>
        
        @if($checked)
        <span class="ml-auto text-emerald-600 dark:text-emerald-400">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </span>
        @endif
    </div>
</label>
