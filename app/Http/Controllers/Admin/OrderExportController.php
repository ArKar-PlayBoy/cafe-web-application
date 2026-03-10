<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderExportController extends Controller
{
    public function exportCsv(Order $order)
    {
        $this->authorize('orders.view');

        $order->load('user', 'items.menuItem');

        $filename = 'order_'.$order->id.'.csv';

        $callback = function () use ($order) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Order ID', 'Date', 'Customer', 'Items', 'Total', 'Status', 'Payment Status']);

            $items = $order->items->map(function ($item) {
                return $item->quantity.'x '.($item->menuItem->name ?? 'N/A');
            })->implode('; ');

            fputcsv($handle, [
                $order->id,
                $order->created_at->toDateTimeString(),
                $order->user->name ?? 'N/A',
                $items,
                $order->total,
                $order->status,
                $order->payment_status,
            ]);

            fclose($handle);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function exportAllCsv(Request $request)
    {
        $this->authorize('orders.view');

        $query = Order::with('user', 'items.menuItem')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->get('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->get('to'));
        }

        $orders = $query->lazy();

        $filename = 'orders_'.now()->format('Ymd_His').'.csv';

        $callback = function () use ($orders) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Order ID', 'Date', 'Customer', 'Items', 'Total', 'Status', 'Payment Status']);

            foreach ($orders as $order) {
                $items = $order->items->map(function ($item) {
                    return $item->quantity.'x '.($item->menuItem->name ?? 'N/A');
                })->implode('; ');

                fputcsv($handle, [
                    $order->id,
                    $order->created_at->toDateTimeString(),
                    $order->user->name ?? 'N/A',
                    $items,
                    $order->total,
                    $order->status,
                    $order->payment_status,
                ]);
            }

            fclose($handle);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
