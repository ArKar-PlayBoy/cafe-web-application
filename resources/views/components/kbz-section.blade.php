{{-- KBZ Pay Section Component --}}
<div id="kbz-section" class="bg-gray-50 dark:bg-gray-800/40 rounded-lg border border-gray-200 dark:border-gray-700 p-5 mb-6 hidden transition-all">
    <div class="bg-white dark:bg-gray-900 border border-blue-100 dark:border-blue-900/50 rounded-lg p-5 shadow-sm">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded bg-[#006CBB] flex items-center justify-center text-white shrink-0">
                <span class="font-extrabold text-sm tracking-tighter">KBZ</span>
            </div>
            <div>
                <h3 class="font-bold text-base text-gray-900 dark:text-gray-100 leading-tight">KBZ Pay Transfer</h3>
                <p class="text-[10px] font-bold uppercase tracking-widest text-[#006CBB] dark:text-blue-400 mt-0.5">Manual Verification</p>
            </div>
        </div>
        
        {{-- QR Code Section --}}
        <div class="flex flex-col sm:flex-row gap-6 items-center sm:items-start mb-4">
            {{-- QR Code Image Placeholder --}}
            <div class="flex-shrink-0">
                @if(file_exists(public_path('images/kbz-qr.png')))
                    <img src="{{ asset('images/kbz-qr.png') }}" alt="KBZ Pay QR Code" class="w-48 h-48 sm:w-56 sm:h-56 rounded-lg border-2 border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow cursor-pointer" id="kbz-qr-image" onclick="openQRModal()">
                @else
                    <div class="w-48 h-48 sm:w-56 sm:h-56 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 flex flex-col items-center justify-center bg-gray-50 dark:bg-gray-800/50">
                        <svg class="w-12 h-12 text-gray-400 dark:text-gray-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                        </svg>
                        <p class="text-xs text-gray-500 dark:text-gray-400 text-center px-4">QR Code Not Found</p>
                        <p class="text-[10px] text-gray-400 dark:text-gray-500 text-center px-4 mt-1">Upload to public/images/kbz-qr.png</p>
                    </div>
                @endif
            </div>
            
            {{-- Payment Details --}}
            <div class="flex-1 text-center sm:text-left">
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 mb-4">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Amount to Pay</p>
                    <p class="text-3xl font-bold text-[#006CBB] dark:text-blue-400">${{ number_format($total ?? 0, 2) }}</p>
                </div>
                
                <div class="text-sm text-gray-600 dark:text-gray-300 space-y-1">
                    <p><span class="font-medium">Scan the QR code</span> with your KBZ Pay app</p>
                    <p class="text-gray-500 dark:text-gray-400">Enter the exact amount shown above</p>
                </div>
                
                {{-- Steps to Pay --}}
                <div class="mt-4 flex flex-wrap gap-2 justify-center sm:justify-start">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 dark:bg-gray-700 rounded-full text-xs font-medium text-gray-600 dark:text-gray-300">
                        <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        Open KBZ Pay
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 dark:bg-gray-700 rounded-full text-xs font-medium text-gray-600 dark:text-gray-300">
                        <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        Scan QR Code
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 dark:bg-gray-700 rounded-full text-xs font-medium text-gray-600 dark:text-gray-300">
                        <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Pay Amount
                    </span>
                </div>
            </div>
        </div>
        
        <div class="flex items-start gap-3 p-4 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-100 dark:border-amber-800/50">
            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div class="space-y-1">
                <p class="text-xs font-semibold text-amber-800 dark:text-amber-300">Important:</p>
                <ol class="text-xs font-medium text-gray-600 dark:text-gray-400 space-y-0.5 list-decimal list-inside">
                    <li>Complete your order below</li>
                    <li>Transfer the exact amount using the QR code</li>
                    <li>Upload payment screenshot on the confirmation page</li>
                    <li>Staff will verify and confirm your order</li>
                </ol>
            </div>
        </div>
    </div>
</div>

{{-- QR Code Modal (for full-screen viewing) --}}
@if(file_exists(public_path('images/kbz-qr.png')))
<div id="qr-modal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-[100] hidden items-center justify-center p-4" onclick="closeQRModal()">
    <div class="bg-white dark:bg-gray-900 rounded-2xl p-6 max-w-sm w-full relative" onclick="event.stopPropagation()">
        <button onclick="closeQRModal()" class="absolute top-3 right-3 w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <div class="text-center">
            <p class="font-bold text-lg text-gray-900 dark:text-white mb-2">Scan with KBZ Pay</p>
            <img src="{{ asset('images/kbz-qr.png') }}" alt="KBZ Pay QR Code" class="w-full max-w-xs mx-auto rounded-lg border-2 border-gray-200 dark:border-gray-700">
            <p class="text-2xl font-bold text-[#006CBB] dark:text-blue-400 mt-4">${{ number_format($total ?? 0, 2) }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Scan and pay this exact amount</p>
        </div>
    </div>
</div>

<script>
function openQRModal() {
    const modal = document.getElementById('qr-modal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }
}

function closeQRModal() {
    const modal = document.getElementById('qr-modal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeQRModal();
});
</script>
@endif
