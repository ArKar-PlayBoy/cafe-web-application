@extends('layouts.admin')

@section('title', 'Payment Screenshot - Order #' . $order->id)

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('admin.orders') }}" class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600">
            <svg class="w-5 h-5 dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-2xl font-bold dark:text-white">Payment Screenshot - Order #{{ $order->id }}</h1>
    </div>

    @if($order->payment_method === 'cod')
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
        <div class="bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4 mb-6">
            <div class="flex items-center gap-3">
                <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <h3 class="font-semibold text-yellow-800 dark:text-yellow-200">Cash on Delivery Order</h3>
                    <p class="text-sm text-yellow-700 dark:text-yellow-300">No payment screenshot required. Payment collected upon delivery.</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Order Total</p>
                <p class="text-2xl font-bold text-green-600">${{ number_format($order->total, 2) }}</p>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Delivery Status</p>
                <span class="inline-block px-3 py-1 rounded-full text-sm font-medium 
                    @if($order->delivery_status === 'delivered') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                    @elseif($order->delivery_status === 'out_for_delivery') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                    @elseif($order->delivery_status === 'failed') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                    @else bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 @endif">
                    {{ ucfirst(str_replace('_', ' ', $order->delivery_status)) }}
                </span>
            </div>
            @if($order->delivery_phone)
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Contact Phone</p>
                <p class="font-semibold dark:text-white">{{ $order->delivery_phone }}</p>
            </div>
            @endif
        </div>

        @if($order->delivery_address)
        <div class="mt-4 bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Delivery Address</p>
            <p class="font-semibold dark:text-white">{{ $order->delivery_address }}</p>
        </div>
        @endif
    </div>
    @else
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Screenshot Section -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-blue-800 px-6 py-4">
                    <h2 class="text-xl font-bold text-white">Payment Screenshot</h2>
                    <p class="text-blue-200 text-sm">Click image to enlarge</p>
                </div>
                
                <div class="p-4">
                    <div class="relative group cursor-zoom-in" onclick="toggleZoom(this)">
                        <img src="{{ route('admin.orders.view-screenshot.raw', $order->id) }}" 
                             alt="Payment Screenshot" 
                             class="w-full h-auto rounded-lg border-2 border-gray-200 dark:border-gray-600 transition-transform duration-300"
                             id="screenshotImg">
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors duration-300 rounded-lg flex items-center justify-center">
                            <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 bg-black/50 text-white px-3 py-1 rounded-full text-sm">
                                Click to zoom
                            </span>
                        </div>
                    </div>
                    
                    <div class="flex gap-3 mt-4">
                        <a href="{{ route('admin.orders.view-screenshot.raw', $order->id) }}" 
                           target="_blank"
                           class="flex items-center gap-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-white px-4 py-2 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Open Full Size
                        </a>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
@if($order->canReviewPayment())
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg mt-6 p-6">
                <h3 class="text-lg font-semibold mb-4 dark:text-white">Verify Payment</h3>
                <div class="flex flex-wrap gap-3">
                    <form action="{{ route('admin.orders.verify-payment', $order->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Verify Payment
                        </button>
                    </form>
                    <button type="button" onclick="document.getElementById('rejectModal').showModal()" class="flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Reject Payment
                    </button>
                </div>
            </div>
            @elseif(in_array($order->payment_status, [\App\Models\Order::PAYMENT_STATUS_VERIFIED, \App\Models\Order::PAYMENT_STATUS_PAID], true))
            <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 rounded-xl p-6 mt-6">
                <div class="flex items-center gap-3">
                    <div class="bg-green-100 dark:bg-green-900 p-2 rounded-full">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-green-800 dark:text-green-200">Payment Verified</h3>
                        <p class="text-sm text-green-700 dark:text-green-300">This payment has been verified and confirmed.</p>
                    </div>
                </div>
            </div>
            @elseif($order->payment_status === \App\Models\Order::PAYMENT_STATUS_FAILED)
            <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 rounded-xl p-6 mt-6">
                <div class="flex items-center gap-3">
                    <div class="bg-red-100 dark:bg-red-900 p-2 rounded-full">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-red-800 dark:text-red-200">Payment Rejected</h3>
                        <p class="text-sm text-red-700 dark:text-red-300">This payment was rejected. {{ $order->payment_note ? 'Reason: ' . $order->payment_note : '' }}</p>
                    </div>
                </div>
            </div>
            @else
            <div class="bg-gray-50 dark:bg-gray-900/30 border border-gray-200 dark:border-gray-700 rounded-xl p-6 mt-6">
                <div class="flex items-center gap-3">
                    <div class="bg-gray-100 dark:bg-gray-900 p-2 rounded-full">
                        <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800 dark:text-gray-200">No Action Available</h3>
                        <p class="text-sm text-gray-700 dark:text-gray-300">This payment cannot be reviewed.</p>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Order Details Sidebar -->
        <div class="space-y-6">
            <!-- Order Info -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold mb-4 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Order Details
                </h3>
                
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Order ID</p>
                        <p class="font-semibold dark:text-white">#{{ $order->id }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Order Total</p>
                        <p class="text-2xl font-bold text-green-600">${{ number_format($order->total, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Payment Method</p>
                        <p class="font-semibold dark:text-white">{{ strtoupper($order->payment_method) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Payment Status</p>
                        <span class="inline-block px-3 py-1 rounded-full text-sm font-medium 
                            @if($order->payment_status === 'verified') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                            @elseif($order->payment_status === 'failed') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                            @else bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 @endif">
                            {{ ucfirst(str_replace('_', ' ', $order->payment_status)) }}
                        </span>
                    </div>
                    @if($order->payment_reference)
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Payment Reference</p>
                        <p class="font-mono font-semibold text-sm dark:text-white bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">{{ $order->payment_reference }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Customer Info -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold mb-4 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Customer Info
                </h3>
                
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Name</p>
                        <p class="font-semibold dark:text-white">{{ $order->user->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Email</p>
                        <p class="font-semibold dark:text-white text-sm">{{ $order->user->email }}</p>
                    </div>
                    @if($order->delivery_phone)
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Delivery Phone</p>
                        <p class="font-semibold dark:text-white">{{ $order->delivery_phone }}</p>
                    </div>
                    @endif
                    @if($order->delivery_address)
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Delivery Address</p>
                        <p class="font-semibold dark:text-white text-sm">{{ $order->delivery_address }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Order Items -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold mb-4 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Order Items
                </h3>
                
                <div class="space-y-2">
                    @foreach($order->items as $item)
                    <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700 last:border-0">
                        <div>
                            <p class="font-medium dark:text-white">{{ $item->quantity }}x {{ $item->menuItem->name ?? 'N/A' }}</p>
                            @if($item->notes)
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $item->notes }}</p>
                            @endif
                        </div>
                        <p class="font-semibold dark:text-white">${{ number_format($item->price * $item->quantity, 2) }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Reject Modal -->
@if($order->canReviewPayment())
<dialog id="rejectModal" class="modal p-6 rounded-2xl shadow-2xl bg-white dark:bg-gray-800 border dark:border-gray-700 w-full max-w-md">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-bold dark:text-white">Reject Payment - Order #{{ $order->id }}</h3>
        <button onclick="document.getElementById('rejectModal').close()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
    <form action="{{ route('admin.orders.reject-payment', $order->id) }}" method="POST">
        @csrf
        <div class="mb-4">
            <label class="block text-sm font-medium mb-2 dark:text-gray-300">Reason for rejection</label>
            <textarea name="note" rows="4" class="w-full border rounded-lg px-4 py-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required placeholder="Explain why payment is rejected..."></textarea>
        </div>
        <div class="flex gap-3">
            <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white px-4 py-3 rounded-lg font-semibold transition-colors">
                Reject Payment
            </button>
            <button type="button" onclick="document.getElementById('rejectModal').close()" class="bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-white px-4 py-3 rounded-lg font-semibold transition-colors">
                Cancel
            </button>
        </div>
    </form>
</dialog>
@endif

<style>
    .cursor-zoom-in {
        cursor: zoom-in;
    }
    .cursor-zoom-out {
        cursor: zoom-out;
    }
</style>

<script>
    function toggleZoom(element) {
        const img = element.querySelector('img');
        if (img.classList.contains('scale-100')) {
            img.classList.remove('scale-100');
            img.classList.add('scale-150');
            element.classList.remove('cursor-zoom-in');
            element.classList.add('cursor-zoom-out');
        } else {
            img.classList.remove('scale-150');
            img.classList.add('scale-100');
            element.classList.remove('cursor-zoom-out');
            element.classList.add('cursor-zoom-in');
        }
    }
</script>
@endsection
