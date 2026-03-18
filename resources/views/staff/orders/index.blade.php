@extends('layouts.staff')

@php
use Illuminate\Support\Str;
@endphp

@section('title', 'Orders')

@section('content')
<h1 class="text-2xl font-bold mb-6 dark:text-white">Orders</h1>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
    <table class="min-w-full">
        <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-700 dark:text-gray-300 uppercase">Order ID</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-700 dark:text-gray-300 uppercase">Customer</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-700 dark:text-gray-300 uppercase">Items</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-700 dark:text-gray-300 uppercase">Total</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-700 dark:text-gray-300 uppercase">Payment</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-700 dark:text-gray-300 uppercase">Status</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-700 dark:text-gray-300 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            @foreach($orders as $order)
            <tr class="bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="px-4 py-4 whitespace-nowrap">#{{ $order->id }}</td>
                <td class="px-4 py-4 whitespace-nowrap text-gray-900 dark:text-gray-100">{{ $order->user->name }}</td>
                <td class="px-4 py-4 text-sm text-gray-900 dark:text-gray-100">
                    @foreach($order->items as $item)
                    <div>
                        {{ $item->quantity }}x {{ $item->menuItem->name ?? 'N/A' }}
                        @if($item->notes)
                        <span class="text-blue-600 dark:text-blue-400 text-xs">({{ $item->notes }})</span>
                        @endif
                    </div>
                    @endforeach
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-gray-900 dark:text-gray-100">${{ number_format($order->total, 2) }}</td>
                <td class="px-4 py-4">
                    <div class="flex flex-col gap-1">
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ strtoupper($order->payment_method) }}</span>
                        @if($order->payment_method !== 'cod')
                        <span class="px-2 py-0.5 text-xs rounded-full {{ $order->payment_status === 'verified' ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300' : ($order->payment_status === 'failed' ? 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300' : ($order->payment_status === 'awaiting_verification' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300' : 'bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-300')) }}">
                            {{ ucfirst(str_replace('_', ' ', $order->payment_status)) }}
                        </span>
                        @if($order->payment_screenshot)
                        <a href="{{ route('staff.orders.view-screenshot', ['order' => $order->id]) }}" target="_blank" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">View Screenshot</a>
                        @endif
                        @else
                        <span class="px-2 py-0.5 text-xs rounded-full 
                            @if($order->delivery_status === 'delivered') bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300
                            @elseif($order->delivery_status === 'out_for_delivery') bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300
                            @elseif($order->delivery_status === 'failed') bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300
                            @else bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300 @endif">
                            {{ ucfirst(str_replace('_', ' ', $order->delivery_status)) }}
                        </span>
                        @if($order->delivery_address)
                        <span class="text-xs text-gray-500 dark:text-gray-400" title="{{ $order->delivery_address }}">
                            📍 {{ Str::limit($order->delivery_address, 15) }}
                        </span>
                        @endif
                        @if($order->delivery_phone)
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            📞 {{ $order->delivery_phone }}
                        </span>
                        @endif
                        @endif
                    </div>
                </td>
                <td class="px-4 py-4">
                    @if($order->status === 'cancelled')
                        <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                            Cancelled
                        </span>
                        @if($order->rejection)
                        <div class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $order->rejection->reason }}</div>
                        @elseif($order->payment_note)
                        <div class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $order->payment_note }}</div>
                        @endif
                    @elseif($order->status === 'preparing')
                        <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            Preparing
                        </span>
                    @elseif($order->status === 'ready')
                        <span class="px-2 py-1 text-xs rounded bg-cyan-100 text-cyan-800 dark:bg-cyan-900 dark:text-cyan-200">
                            Ready
                        </span>
                    @elseif($order->status === 'completed')
                        <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                            Completed
                        </span>
                    @else
                        <span class="px-2 py-1 text-xs rounded bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                            Pending
                        </span>
                    @endif
                </td>
                <td class="px-4 py-4 text-gray-900 dark:text-gray-100">
                    {{-- Payment Verification (Staff can do this) --}}
                    @if($order->payment_method !== 'cod' && $order->payment_status !== 'verified' && $order->payment_status !== 'paid' && $order->payment_screenshot)
                    <div class="flex flex-col gap-2 mb-2">
                        <form action="{{ route('staff.orders.verify-payment', $order->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="bg-green-600 text-white px-3 py-1 rounded text-xs hover:bg-green-700 w-full">Verify Payment</button>
                        </form>
                        <button type="button" onclick="document.getElementById('rejectPay{{ $order->id }}').showModal()" class="bg-red-600 text-white px-3 py-1 rounded text-xs hover:bg-red-700">Reject</button>
                        
                        <dialog id="rejectPay{{ $order->id }}" class="modal p-6 rounded-lg shadow-xl dark:bg-gray-800">
                            <h3 class="text-lg font-bold mb-4 dark:text-white">Reject Payment - Order #{{ $order->id }}</h3>
                            <form action="{{ route('staff.orders.reject-payment', $order->id) }}" method="POST">
                                @csrf
                                <div class="mb-4">
                                    <label class="block text-sm font-medium mb-2 dark:text-gray-300">Reason for rejection</label>
                                    <textarea name="note" rows="3" class="w-full border rounded px-3 py-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required placeholder="Explain why payment is rejected..."></textarea>
                                </div>
                                <div class="flex gap-2">
                                    <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Reject Payment</button>
                                    <button type="button" onclick="document.getElementById('rejectPay{{ $order->id }}').close()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Cancel</button>
                                </div>
                            </form>
                        </dialog>
                    </div>
                    @else
                    <span class="text-gray-400 dark:text-gray-500 text-sm">View only - Kitchen manages status</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="px-6 py-4 bg-white dark:bg-gray-800">
        {{ $orders->links() }}
    </div>
</div>
@endsection
