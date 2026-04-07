<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CsvSanitizer;
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
                CsvSanitizer::sanitizeCell((string) $order->id),
                CsvSanitizer::sanitizeCell($order->created_at->toDateTimeString()),
                CsvSanitizer::sanitizeCell($order->user->name ?? 'N/A'),
                CsvSanitizer::sanitizeCell($items),
                CsvSanitizer::sanitizeCell((string) $order->total),
                CsvSanitizer::sanitizeCell($order->status),
                CsvSanitizer::sanitizeCell($order->payment_status),
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

        $request->validate([
            'status' => 'nullable|in:pending,preparing,ready,completed,cancelled',
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
        ]);

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
                    CsvSanitizer::sanitizeCell((string) $order->id),
                    CsvSanitizer::sanitizeCell($order->created_at->toDateTimeString()),
                    CsvSanitizer::sanitizeCell($order->user->name ?? 'N/A'),
                    CsvSanitizer::sanitizeCell($items),
                    CsvSanitizer::sanitizeCell((string) $order->total),
                    CsvSanitizer::sanitizeCell($order->status),
                    CsvSanitizer::sanitizeCell($order->payment_status),
                ]);
            }

            fclose($handle);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
