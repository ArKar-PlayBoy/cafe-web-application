@extends('layouts.app')

@section('title', 'My Orders')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-serif font-bold mb-6">My Orders</h1>

    @if($orders->isEmpty())
    <div class="text-center py-12">
        <svg class="w-24 h-24 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        <p class="text-gray-500 dark:text-gray-400 text-xl mb-4">You haven't placed any orders yet</p>
        <a href="{{ route('menu') }}" class="inline-block bg-green-600 text-white px-6 py-3 rounded-full hover:bg-green-700 transition-colors">Browse Menu</a>
    </div>
    @else
    <div class="space-y-4">
        @foreach($orders as $order)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="font-semibold text-lg">Order #{{ $order->id }}</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">{{ $order->created_at->format('M d, Y H:i') }}</p>
                </div>
                <div class="text-right">
                    <span class="px-3 py-1 text-sm rounded-full {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400' : ($order->status === 'completed' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : ($order->status === 'cancelled' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400')) }}">
                        {{ ucfirst($order->status) }}
                    </span>
                    @if($order->status === 'cancelled')
                        @if($order->rejection)
                        <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $order->rejection->reason }}</p>
                        @elseif($order->payment_note)
                        <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $order->payment_note }}</p>
                        @endif
                    @endif
                </div>
            </div>
            <div class="flex justify-between items-center">
                <div>
                    @foreach($order->items as $item)
                    <p class="text-gray-600 dark:text-gray-400">
                        {{ $item->quantity }}x {{ $item->menuItem->name }}
                        @if($item->notes)
                        <span class="text-blue-600 dark:text-blue-400 text-xs ml-1">({{ $item->notes }})</span>
                        @endif
                    </p>
                    @endforeach
                </div>
                <div class="text-right">
                    <p class="font-bold text-xl text-green-600 dark:text-green-400">${{ number_format($order->total, 2) }}</p>
                    <a href="{{ route('orders.show', $order->id) }}" class="text-green-600 dark:text-green-400 hover:underline">View Details</a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    <div class="mt-6">
        {{ $orders->links() }}
    </div>
    @endif
</div>
@endsection
