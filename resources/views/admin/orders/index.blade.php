@extends('layouts.admin')

@section('title', 'Orders')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shadow-lg shadow-indigo-500/30 shrink-0">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        </div>
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white">All Orders</h1>
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mt-0.5">Manage, track, and process customer orders</p>
        </div>
    </div>
    @can('orders.view')
    <div class="flex items-center">
        <a href="{{ route('admin.orders.export-all', request()->only('status', 'from', 'to')) }}" class="px-5 py-2.5 rounded-2xl bg-indigo-600 text-white text-sm font-bold shadow-lg shadow-indigo-600/30 hover:scale-105 active:scale-95 transition-all flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Export CSV
        </a>
    </div>
    @endcan
</div>

<div class="glass-card rounded-[2rem] border border-slate-200/50 dark:border-slate-700/50 bg-white/40 dark:bg-slate-900/40 shadow-sm flex flex-col min-h-[500px]">
    <div class="flex-1 overflow-x-auto overflow-y-hidden rounded-[2rem]">
        <table class="w-full text-left whitespace-nowrap">
            <thead>
                <tr class="bg-slate-50/80 dark:bg-slate-800/80 border-b border-slate-200/50 dark:border-slate-700/50 backdrop-blur-md">
                    <th class="px-6 py-4 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest sticky top-0 z-10">Order ID</th>
                    <th class="px-6 py-4 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest sticky top-0 z-10">Customer</th>
                    <th class="px-6 py-4 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest sticky top-0 z-10">Items</th>
                    <th class="px-6 py-4 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest sticky top-0 z-10">Total</th>
                    <th class="px-6 py-4 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest sticky top-0 z-10">Payment</th>
                    <th class="px-6 py-4 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest sticky top-0 z-10">Status</th>
                    <th class="px-6 py-4 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest sticky top-0 z-10">Date</th>
                    <th class="px-6 py-4 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest sticky top-0 z-10 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100/50 dark:divide-slate-800/50">
                @forelse($orders as $order)
                <tr class="hover:bg-white/80 dark:hover:bg-slate-800/50 transition-colors duration-200 group">
                    <td class="px-6 py-5">
                        <span class="text-sm font-bold text-indigo-600 dark:text-indigo-400 group-hover:underline cursor-pointer">#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
                    </td>
                    <td class="px-6 py-5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-[1rem] bg-gradient-to-br from-slate-200 to-slate-300 dark:from-slate-700 dark:to-slate-800 flex items-center justify-center text-sm font-black text-slate-600 dark:text-slate-300 shadow-sm shrink-0">
                                {{ substr($order->user->name ?? 'G', 0, 1) }}
                            </div>
                            <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $order->user->name }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-5">
                        <div class="flex flex-col gap-1">
                            @foreach($order->items as $item)
                            <div class="text-[11px] font-medium text-slate-600 dark:text-slate-400 bg-slate-100/50 dark:bg-slate-800/50 px-2 py-1 rounded inline-block w-max">
                                <span class="text-slate-900 dark:text-slate-200 font-bold mr-1">{{ $item->quantity }}x</span>{{ $item->menuItem->name ?? 'N/A' }}
                                {{-- @if($item->notes)
                                <span class="text-blue-600 dark:text-blue-400 ml-1">({{ $item->notes }})</span>
                                @endif --}}
                            </div>
                            @endforeach
                        </div>
                    </td>
                    <td class="px-6 py-5">
                        <span class="text-sm font-black text-slate-800 dark:text-slate-100">${{ number_format($order->total, 2) }}</span>
                    </td>
                    <td class="px-6 py-5">
                        <div class="flex flex-col gap-1.5 items-start">
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">{{ $order->payment_method }}</span>
                            @if($order->payment_method !== 'cod')
                                @php
                                    $paymentConfig = [
                                        'verified' => ['bg' => 'bg-emerald-100 dark:bg-emerald-500/20', 'text' => 'text-emerald-700 dark:text-emerald-400', 'dot' => 'bg-emerald-500'],
                                        'failed' => ['bg' => 'bg-rose-100 dark:bg-rose-500/20', 'text' => 'text-rose-700 dark:text-rose-400', 'dot' => 'bg-rose-500'],
                                        'awaiting_verification' => ['bg' => 'bg-amber-100 dark:bg-amber-500/20', 'text' => 'text-amber-700 dark:text-amber-400', 'dot' => 'bg-amber-500'],
                                    ];
                                    $pConf = $paymentConfig[$order->payment_status] ?? ['bg' => 'bg-slate-100 dark:bg-slate-500/20', 'text' => 'text-slate-700 dark:text-slate-400', 'dot' => 'bg-slate-500'];
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-bold rounded-xl {{ $pConf['bg'] }} {{ $pConf['text'] }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $pConf['dot'] }}"></span>
                                    {{ ucfirst(str_replace('_', ' ', $order->payment_status)) }}
                                </span>
                                @if($order->payment_screenshot)
                                <a href="{{ route('admin.orders.view-screenshot', ['order' => $order->id]) }}" target="_blank" class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1 mt-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    View Receipt
                                </a>
                                @endif
                            @else
                                @php
                                    $deliveryConfig = [
                                        'delivered' => ['bg' => 'bg-emerald-100 dark:bg-emerald-500/20', 'text' => 'text-emerald-700 dark:text-emerald-400', 'dot' => 'bg-emerald-500'],
                                        'out_for_delivery' => ['bg' => 'bg-blue-100 dark:bg-blue-500/20', 'text' => 'text-blue-700 dark:text-blue-400', 'dot' => 'bg-blue-500'],
                                        'failed' => ['bg' => 'bg-rose-100 dark:bg-rose-500/20', 'text' => 'text-rose-700 dark:text-rose-400', 'dot' => 'bg-rose-500'],
                                    ];
                                    $dConf = $deliveryConfig[$order->delivery_status] ?? ['bg' => 'bg-amber-100 dark:bg-amber-500/20', 'text' => 'text-amber-700 dark:text-amber-400', 'dot' => 'bg-amber-500'];
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-bold rounded-xl {{ $dConf['bg'] }} {{ $dConf['text'] }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $dConf['dot'] }}"></span>
                                    {{ ucfirst(str_replace('_', ' ', $order->delivery_status)) }}
                                </span>
                                @if($order->delivery_phone)
                                <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400 flex items-center gap-1 mt-0.5">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    {{ $order->delivery_phone }}
                                </span>
                                @endif
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-5">
                        @php
                            $statusConfig = [
                                'pending' => ['bg' => 'bg-amber-100 dark:bg-amber-500/20', 'text' => 'text-amber-700 dark:text-amber-400', 'dot' => 'bg-amber-500'],
                                'confirmed' => ['bg' => 'bg-blue-100 dark:bg-blue-500/20', 'text' => 'text-blue-700 dark:text-blue-400', 'dot' => 'bg-blue-500'],
                                'preparing' => ['bg' => 'bg-violet-100 dark:bg-violet-500/20', 'text' => 'text-violet-700 dark:text-violet-400', 'dot' => 'bg-violet-500'],
                                'ready' => ['bg' => 'bg-cyan-100 dark:bg-cyan-500/20', 'text' => 'text-cyan-700 dark:text-cyan-400', 'dot' => 'bg-cyan-500'],
                                'completed' => ['bg' => 'bg-emerald-100 dark:bg-emerald-500/20', 'text' => 'text-emerald-700 dark:text-emerald-400', 'dot' => 'bg-emerald-500'],
                                'cancelled' => ['bg' => 'bg-rose-100 dark:bg-rose-500/20', 'text' => 'text-rose-700 dark:text-rose-400', 'dot' => 'bg-rose-500'],
                            ];
                            $config = $statusConfig[$order->status] ?? ['bg' => 'bg-slate-100 dark:bg-slate-500/20', 'text' => 'text-slate-700 dark:text-slate-400', 'dot' => 'bg-slate-500'];
                        @endphp
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-xl {{ $config['bg'] }} {{ $config['text'] }} capitalize">
                            <span class="w-1.5 h-1.5 rounded-full {{ $config['dot'] }}"></span>
                            {{ $order->status }}
                        </span>
                        @if($order->status === 'cancelled')
                            @if($order->rejection)
                            <div class="text-[10px] text-rose-600 dark:text-rose-400 mt-1">{{ $order->rejection->reason }}</div>
                            @elseif($order->payment_note)
                            <div class="text-[10px] text-rose-600 dark:text-rose-400 mt-1">{{ $order->payment_note }}</div>
                            @endif
                        @endif
                    </td>
                    <td class="px-6 py-5">
                        <div class="flex flex-col">
                            <span class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ $order->created_at->format('M d, Y') }}</span>
                            <span class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ $order->created_at->format('h:i A') }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-5 text-right flex flex-col items-end gap-2">
                        {{-- COD Delivery Actions --}}
                        @if($order->payment_method === 'cod' && $order->delivery_status !== 'delivered' && $order->delivery_status !== 'failed')
                            @can('orders.update')
                                @if($order->delivery_status === 'pending' && $order->status === 'ready')
                                <form action="{{ route('admin.orders.out-for-delivery', $order->id) }}" method="POST" class="w-full sm:w-auto">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 w-full sm:w-auto text-[11px] font-bold rounded-lg bg-blue-600 text-white hover:bg-blue-700 hover:scale-105 active:scale-95 transition-all shadow-sm">
                                        Out for Delivery
                                    </button>
                                </form>
                                @elseif($order->delivery_status === 'out_for_delivery')
                                <div class="flex gap-2 w-full sm:w-auto">
                                    <form action="{{ route('admin.orders.mark-delivered', $order->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 w-full text-[11px] font-bold rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 hover:scale-105 active:scale-95 transition-all shadow-sm">
                                            Collect Cash
                                        </button>
                                    </form>
                                    <button type="button" onclick="document.getElementById('codFailed{{ $order->id }}').showModal()" class="px-3 py-1.5 w-full text-[11px] font-bold rounded-lg bg-rose-600 text-white hover:bg-rose-700 hover:scale-105 active:scale-95 transition-all shadow-sm">
                                        Failed
                                    </button>
                                </div>
                                @endif
                                
                                {{-- Modular Dialog --}}
                                @if($order->delivery_status === 'out_for_delivery')
                                <dialog id="codFailed{{ $order->id }}" class="modal m-auto p-0 rounded-[2rem] shadow-2xl bg-transparent backdrop:bg-slate-900/50 backdrop:backdrop-blur-sm border-0 w-full max-w-sm">
                                    <div class="glass-card p-6 border dark:border-slate-700 text-left bg-white/95 dark:bg-slate-900/95">
                                        <div class="w-12 h-12 rounded-2xl bg-rose-100 dark:bg-rose-500/20 text-rose-600 dark:text-rose-400 flex items-center justify-center mb-4">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        </div>
                                        <h3 class="text-xl font-black text-slate-900 dark:text-white mb-1">Delivery Failed</h3>
                                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-6">Order #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</p>
                                        
                                        <form action="{{ route('admin.orders.mark-delivery-failed', $order->id) }}" method="POST">
                                            @csrf
                                            <div class="mb-6">
                                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-widest mb-2">Reason</label>
                                                <textarea name="reason" rows="3" class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-rose-500 outline-none" required placeholder="Explain why delivery failed..."></textarea>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl bg-rose-600 text-white text-sm font-bold shadow-sm hover:bg-rose-700 transition-colors">Mark Failed</button>
                                                <button type="button" onclick="document.getElementById('codFailed{{ $order->id }}').close()" class="flex-1 px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm font-bold shadow-sm hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">Cancel</button>
                                            </div>
                                        </form>
                                    </div>
                                </dialog>
                                @endif
                            @endcan
                        @elseif($order->canReviewPayment())
                            @can('orders.verify_payment')
                            <div class="flex gap-2 w-full sm:w-auto">
                                <form action="{{ route('admin.orders.verify-payment', $order->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 w-full text-[11px] font-bold rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 hover:scale-105 active:scale-95 transition-all shadow-sm">
                                        Verify
                                    </button>
                                </form>
                                <button type="button" onclick="document.getElementById('rejectPayment{{ $order->id }}').showModal()" class="px-3 py-1.5 w-full text-[11px] font-bold rounded-lg bg-rose-600 text-white hover:bg-rose-700 hover:scale-105 active:scale-95 transition-all shadow-sm">
                                    Reject
                                </button>
                                
                                <dialog id="rejectPayment{{ $order->id }}" class="modal m-auto p-0 rounded-[2rem] shadow-2xl bg-transparent backdrop:bg-slate-900/50 backdrop:backdrop-blur-sm border-0 w-full max-w-sm">
                                    <div class="glass-card p-6 border dark:border-slate-700 text-left bg-white/95 dark:bg-slate-900/95">
                                        <div class="w-12 h-12 rounded-2xl bg-rose-100 dark:bg-rose-500/20 text-rose-600 dark:text-rose-400 flex items-center justify-center mb-4">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        </div>
                                        <h3 class="text-xl font-black text-slate-900 dark:text-white mb-1">Reject Payment</h3>
                                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-6">Order #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</p>

                                        <form action="{{ route('admin.orders.reject-payment', $order->id) }}" method="POST">
                                            @csrf
                                            <div class="mb-6">
                                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-widest mb-2">Reason</label>
                                                <textarea name="note" rows="3" class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-rose-500 outline-none" required placeholder="Explain why payment is rejected..."></textarea>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl bg-rose-600 text-white text-sm font-bold shadow-sm hover:bg-rose-700 transition-colors">Reject Payment</button>
                                                <button type="button" onclick="document.getElementById('rejectPayment{{ $order->id }}').close()" class="flex-1 px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm font-bold shadow-sm hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">Cancel</button>
                                            </div>
                                        </form>
                                    </div>
                                </dialog>
                            </div>
                            @endcan
                        @endif

                        <a href="{{ route('admin.orders.export', $order->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-[11px] font-bold hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors w-full sm:w-auto mt-auto justify-center">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Export
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-16 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-4 text-slate-400 dark:text-slate-500">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                            </div>
                            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200">No orders found</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm">When customers place orders, they will appear here for you to manage.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($orders->hasPages())
    <div class="px-6 py-4 border-t border-slate-200/50 dark:border-slate-700/50 bg-slate-50/50 dark:bg-slate-800/30 rounded-b-[2rem]">
        {{ $orders->links() }}
    </div>
    @endif
</div>
@endsection
