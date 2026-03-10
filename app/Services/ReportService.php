<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function getSalesReport(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->startOfDay();
        $endDate = $endDate ?? now()->endOfDay();

        $orders = Order::whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('payment_status', ['verified', 'paid'])
            ->where('status', '!=', 'cancelled');

        $totalRevenue = (float) $orders->sum('total');
        $orderCount = $orders->count();
        $averageOrderValue = $orderCount > 0 ? $totalRevenue / $orderCount : 0;

        $paymentMethodBreakdown = Order::whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('payment_status', ['verified', 'paid'])
            ->where('status', '!=', 'cancelled')
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as total'))
            ->groupBy('payment_method')
            ->get();

        $dailySales = Order::whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('payment_status', ['verified', 'paid'])
            ->where('status', '!=', 'cancelled')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as orders'), DB::raw('SUM(total) as revenue'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'total_revenue' => $totalRevenue,
            'order_count' => $orderCount,
            'average_order_value' => $averageOrderValue,
            'payment_method_breakdown' => $paymentMethodBreakdown,
            'daily_sales' => $dailySales,
        ];
    }

    public function getPopularItems(?Carbon $startDate = null, ?Carbon $endDate = null, int $limit = 10): Collection
    {
        $startDate = $startDate ?? now()->subDays(30)->startOfDay();
        $endDate = $endDate ?? now()->endOfDay();

        return OrderItem::whereHas('order', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate])
                ->whereIn('payment_status', ['verified', 'paid'])
                ->where('status', '!=', 'cancelled');
        })
        ->select('menu_item_id', DB::raw('SUM(quantity) as total_quantity'), DB::raw('COUNT(DISTINCT order_id) as order_count'))
        ->with('menuItem:id,name,price')
        ->groupBy('menu_item_id')
        ->orderByDesc('total_quantity')
        ->limit($limit)
        ->get()
        ->map(function ($item) {
            $item->revenue = $item->total_quantity * ($item->menuItem->price ?? 0);
            return $item;
        });
    }

    public function getCustomerAnalytics(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->subDays(30)->startOfDay();
        $endDate = $endDate ?? now()->endOfDay();

        $totalCustomers = User::whereHas('orders', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate])
                ->whereIn('payment_status', ['verified', 'paid'])
                ->where('status', '!=', 'cancelled');
        })->count();

        $newCustomers = User::whereBetween('first_order_date', [$startDate, $endDate])->count();

        $returningCustomers = $totalCustomers - $newCustomers;

        $topCustomers = User::whereHas('orders', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate])
                ->whereIn('payment_status', ['verified', 'paid'])
                ->where('status', '!=', 'cancelled');
        })
        ->select('id', 'name', 'email', 'total_orders', 'total_spent')
        ->orderByDesc('total_spent')
        ->limit(10)
        ->get();

        $customerRetention = $this->calculateRetentionRate($startDate, $endDate);

        return [
            'total_customers' => $totalCustomers,
            'new_customers' => $newCustomers,
            'returning_customers' => $returningCustomers,
            'new_customer_percentage' => $totalCustomers > 0 ? ($newCustomers / $totalCustomers) * 100 : 0,
            'returning_customer_percentage' => $totalCustomers > 0 ? ($returningCustomers / $totalCustomers) * 100 : 0,
            'top_customers' => $topCustomers,
            'retention_rate' => $customerRetention,
        ];
    }

    private function calculateRetentionRate(Carbon $startDate, Carbon $endDate): float
    {
        $previousPeriodStart = $startDate->copy()->subDays(30);
        $previousPeriodEnd = $startDate->copy()->subDay();

        $previousCustomers = User::whereHas('orders', function ($query) use ($previousPeriodStart, $previousPeriodEnd) {
            $query->whereBetween('created_at', [$previousPeriodStart, $previousPeriodEnd])
                ->whereIn('payment_status', ['verified', 'paid'])
                ->where('status', '!=', 'cancelled');
        })->pluck('id');

        $returningCustomers = User::whereHas('orders', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate])
                ->whereIn('payment_status', ['verified', 'paid'])
                ->where('status', '!=', 'cancelled');
        })
        ->whereIn('id', $previousCustomers)
        ->count();

        $previousCount = $previousCustomers->count();

        return $previousCount > 0 ? ($returningCustomers / $previousCount) * 100 : 0;
    }

    public function getDateRangeOptions(): array
    {
        return [
            'today' => [
                'label' => 'Today',
                'start' => now()->startOfDay(),
                'end' => now()->endOfDay(),
            ],
            'yesterday' => [
                'label' => 'Yesterday',
                'start' => now()->subDay()->startOfDay(),
                'end' => now()->subDay()->endOfDay(),
            ],
            'last_7_days' => [
                'label' => 'Last 7 Days',
                'start' => now()->subDays(7)->startOfDay(),
                'end' => now()->endOfDay(),
            ],
            'last_30_days' => [
                'label' => 'Last 30 Days',
                'start' => now()->subDays(30)->startOfDay(),
                'end' => now()->endOfDay(),
            ],
            'this_month' => [
                'label' => 'This Month',
                'start' => now()->startOfMonth(),
                'end' => now()->endOfDay(),
            ],
            'last_month' => [
                'label' => 'Last Month',
                'start' => now()->subMonth()->startOfMonth(),
                'end' => now()->subMonth()->endOfMonth(),
            ],
        ];
    }
}
