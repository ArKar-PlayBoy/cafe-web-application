<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\KitchenTicket;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KitchenController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all');

        $query = KitchenTicket::with(['order.user', 'order.items.menuItem'])
            ->whereHas('order', function ($q) {
                $q->whereIn('status', ['pending', 'preparing', 'ready']);
            })
            ->orderBy('created_at', 'asc');

        switch ($filter) {
            case 'new':
                $query->where('status', 'new');
                break;
            case 'preparing':
                $query->where('status', 'preparing');
                break;
            case 'ready':
                $query->where('status', 'ready');
                break;
        }

        $tickets = $query->get();

        return view('staff.kitchen.index', compact('tickets', 'filter'));
    }

    public function updateStatus(Request $request, KitchenTicket $ticket)
    {
        $request->validate([
            'status' => 'required|in:preparing,ready,completed',
        ]);

        $ticket->update(['status' => $request->status]);

        if ($request->status === 'preparing') {
            $ticket->markAsPreparing();
            $ticket->order->update(['status' => 'preparing']);
        } elseif ($request->status === 'ready') {
            $ticket->markAsReady();
            $ticket->order->update(['status' => 'ready']);
        } elseif ($request->status === 'completed') {
            $ticket->markAsCompleted();
            $ticket->order->update(['status' => 'completed']);
        }

        return back()->with('success', 'Ticket updated.');
    }

    public function markPrinted(KitchenTicket $ticket)
    {
        $ticket->markPrinted();
        return back()->with('success', 'Marked as printed.');
    }

    public function getNewTickets(Request $request)
    {
        $lastId = $request->get('last_id', 0);
        
        $tickets = KitchenTicket::with(['order.user', 'order.items.menuItem'])
            ->where('id', '>', $lastId)
            ->where('status', 'new')
            ->whereHas('order', function ($q) {
                $q->whereIn('status', ['pending', 'preparing', 'ready']);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'tickets' => $tickets,
            'count' => $tickets->count(),
        ]);
    }

    public function getActiveCount()
    {
        $count = KitchenTicket::whereHas('order', function ($q) {
            $q->whereIn('status', ['pending', 'preparing', 'ready']);
        })->count();

        return response()->json(['count' => $count]);
    }
}
