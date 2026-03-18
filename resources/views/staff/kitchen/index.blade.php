@extends('layouts.staff')

@section('title', 'Kitchen Display')

@section('content')
<div class="min-h-screen bg-gray-900 text-white">
    <div class="container mx-auto px-4 py-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Kitchen Display</h1>
            <div class="flex items-center gap-4">
                <button id="refreshBtn" onclick="checkNewOrders()" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 rounded-lg flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>Check New Orders</span>
                </button>
            </div>
        </div>

        <div class="flex gap-2 mb-6">
            <a href="{{ route('staff.kitchen.index') }}" class="px-4 py-2 rounded-lg {{ $filter === 'all' ? 'bg-blue-600' : 'bg-gray-700 hover:bg-gray-600' }}">
                All ({{ \App\Models\KitchenTicket::whereHas('order', fn($q) => $q->whereIn('status', ['pending', 'preparing', 'ready']))->count() }})
            </a>
            <a href="{{ route('staff.kitchen.index', ['filter' => 'new']) }}" class="px-4 py-2 rounded-lg {{ $filter === 'new' ? 'bg-blue-600' : 'bg-gray-700 hover:bg-gray-600' }}">
                New ({{ \App\Models\KitchenTicket::where('status', 'new')->whereHas('order', fn($q) => $q->whereIn('status', ['pending', 'preparing', 'ready']))->count() }})
            </a>
            <a href="{{ route('staff.kitchen.index', ['filter' => 'preparing']) }}" class="px-4 py-2 rounded-lg {{ $filter === 'preparing' ? 'bg-blue-600' : 'bg-gray-700 hover:bg-gray-600' }}">
                Preparing ({{ \App\Models\KitchenTicket::where('status', 'preparing')->whereHas('order', fn($q) => $q->whereIn('status', ['pending', 'preparing', 'ready']))->count() }})
            </a>
            <a href="{{ route('staff.kitchen.index', ['filter' => 'ready']) }}" class="px-4 py-2 rounded-lg {{ $filter === 'ready' ? 'bg-blue-600' : 'bg-gray-700 hover:bg-gray-600' }}">
                Ready ({{ \App\Models\KitchenTicket::where('status', 'ready')->whereHas('order', fn($q) => $q->whereIn('status', ['pending', 'preparing', 'ready']))->count() }})
            </a>
        </div>

        @if($tickets->isEmpty())
            <div class="text-center py-12 text-gray-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <p class="text-xl">No orders in queue</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4" id="kitchenTickets">
                @foreach($tickets as $ticket)
                    <div class="ticket-card bg-gray-800 rounded-lg p-4 border-l-4 {{ $ticket->status === 'new' ? 'border-green-500' : ($ticket->status === 'preparing' ? 'border-yellow-500' : 'border-blue-500') }}" data-ticket-id="{{ $ticket->id }}">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <span class="text-2xl font-bold">#{{ $ticket->order->id }}</span>
                                <span class="ml-2 px-2 py-1 text-xs rounded-full {{ $ticket->status === 'new' ? 'bg-green-600' : ($ticket->status === 'preparing' ? 'bg-yellow-600' : 'bg-blue-600') }}">
                                    {{ ucfirst($ticket->status) }}
                                </span>
                            </div>
                            <div class="text-sm text-gray-400">
                                {{ $ticket->created_at->format('H:i') }}
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="text-sm text-gray-400 mb-1">Customer: {{ $ticket->order->user->name ?? 'Guest' }}</div>
                            @if($ticket->order->reservation && $ticket->order->reservation->table)
                                <div class="text-sm text-gray-400">Table: {{ $ticket->order->reservation->table->table_number }}</div>
                            @endif
                        </div>

                        <div class="border-t border-gray-700 pt-3 mb-3">
                            @foreach($ticket->order->items as $item)
                                <div class="flex justify-between items-center py-1">
                                    <span class="font-semibold">{{ $item->quantity }}x {{ $item->menuItem->name ?? 'Unknown' }}</span>
                                </div>
                            @endforeach
                        </div>

                        @foreach($ticket->order->items as $item)
                            @if($item->notes)
                            <div class="bg-orange-900/50 border border-orange-700 rounded p-2 mb-2 text-sm">
                                <strong>{{ $item->menuItem->name ?? 'Item' }}:</strong> {{ $item->notes }}
                            </div>
                            @endif
                        @endforeach

                        <div class="flex gap-2" x-data="{ open: false, current: '{{ $ticket->status }}' }">
                            <div class="relative flex-1">
                                <button type="button" @click="open = !open" class="w-full px-3 py-2 bg-white text-gray-900 rounded text-sm font-semibold flex items-center justify-between">
                                    <span x-text="current === 'new' ? '🆕 New' : current === 'preparing' ? '🔥 Preparing' : current === 'ready' ? '✅ Ready' : '✓ Completed'"></span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                                <div x-show="open" @click.away="open = false" class="absolute z-10 mt-1 w-full bg-gray-800 rounded-lg shadow-lg py-1" style="display: none;">
                                    <form action="{{ route('staff.kitchen.update', $ticket) }}" method="POST" class="m-0">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" name="status" value="new" @click="current = 'new'; open = false" class="w-full px-3 py-2 text-left text-sm hover:bg-gray-700 text-white {{ $ticket->status === 'new' ? 'bg-gray-700' : '' }}">
                                            🆕 New
                                        </button>
                                        <button type="submit" name="status" value="preparing" @click="current = 'preparing'; open = false" class="w-full px-3 py-2 text-left text-sm hover:bg-gray-700 text-white {{ $ticket->status === 'preparing' ? 'bg-gray-700' : '' }}">
                                            🔥 Preparing
                                        </button>
                                        <button type="submit" name="status" value="ready" @click="current = 'ready'; open = false" class="w-full px-3 py-2 text-left text-sm hover:bg-gray-700 text-white {{ $ticket->status === 'ready' ? 'bg-gray-700' : '' }}">
                                            ✅ Ready
                                        </button>
                                        <button type="submit" name="status" value="completed" @click="current = 'completed'; open = false" class="w-full px-3 py-2 text-left text-sm hover:bg-gray-700 text-white {{ $ticket->status === 'completed' ? 'bg-gray-700' : '' }}">
                                            ✓ Completed
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <button onclick="printTicket({{ $ticket->id }})" class="px-3 py-2 bg-white hover:bg-gray-100 text-gray-900 rounded text-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<div id="printArea" class="hidden"></div>

@push('scripts')
<script>
let lastTicketId = {{ $tickets->max('id') ?? 0 }};

function checkNewOrders() {
    const btn = document.getElementById('refreshBtn');
    btn.disabled = true;
    btn.querySelector('span').textContent = 'Checking...';
    
    fetch(`/staff/kitchen/new-tickets?last_id=${lastTicketId}`)
        .then(res => res.json())
        .then(data => {
            if (data.count > 0) {
                lastTicketId = Math.max(...data.tickets.map(t => t.id));
                
                if ('Notification' in window && Notification.permission === 'granted') {
                    new Notification('New Order!', { 
                        body: `${data.count} new order(s) received`,
                        icon: '/favicon.ico'
                    });
                } else if ('Notification' in window && Notification.permission === 'default') {
                    Notification.requestPermission().then(permission => {
                        if (permission === 'granted') {
                            new Notification('New Order!', { 
                                body: `${data.count} new order(s) received`,
                                icon: '/favicon.ico'
                            });
                        }
                    });
                }
                
                location.reload();
            } else {
                if ('Notification' in window && Notification.permission === 'granted') {
                    new Notification('No New Orders', { 
                        body: 'No new orders since last check',
                        icon: '/favicon.ico'
                    });
                }
            }
        })
        .catch(err => console.error('Error checking new orders:', err))
        .finally(() => {
            btn.disabled = false;
            btn.querySelector('span').textContent = 'Check New Orders';
        });
}

function printTicket(ticketId) {
    const ticket = document.querySelector(`[data-ticket-id="${ticketId}"]`);
    if (!ticket) return;
    
    const orderId = ticket.querySelector('.text-2xl').textContent.replace('#', '');
    const time = ticket.querySelector('.text-sm').textContent;
    const customerName = ticket.querySelector('.text-gray-400').textContent.replace('Customer: ', '').replace('Table: ', '').split('\n')[0];
    const itemsHtml = ticket.querySelector('.border-t').innerHTML;
    const noteEl = ticket.querySelector('.bg-orange-900');
    const noteHtml = noteEl ? '<div class="note"><div class="note-label">Note:</div>' + noteEl.textContent.replace('Note:', '').trim() + '</div>' : '';
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Order #${orderId}</title>
            <link rel="stylesheet" href="${window.location.origin}/css/print.css">
        </head>
        <body>
            <div class="ticket">
                <div class="header">
                    <div class="restaurant-name">Cafe Order</div>
                    <div class="order-id">Order #${orderId}</div>
                    <div class="order-time">${new Date().toLocaleString()}</div>
                </div>
                <div class="info">
                    <div class="info-row">
                        <span class="label">Customer:</span> ${customerName}
                    </div>
                    <div class="info-row">
                        <span class="label">Time:</span> ${time}
                    </div>
                </div>
                <div class="items">
                    <div class="items-header">Items</div>
                    ${itemsHtml}
                </div>
                ${noteHtml}
                <div class="footer">
                    Thank you for your order!
                </div>
            </div>
        </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}

// Request notification permission on page load
if ('Notification' in window && Notification.permission === 'default') {
    Notification.requestPermission();
}
</script>
@endpush
@endsection
