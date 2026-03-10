<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CafeTable;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\StockItem;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'totalOrders' => Order::count(),
            'pendingOrders' => Order::where('status', 'pending')->count(),
            'todayOrders' => Order::whereDate('created_at', today())->count(),
            'todayRevenue' => Order::whereDate('created_at', today())->where('payment_status', 'paid')->sum('total'),
            'totalRevenue' => Order::where('payment_status', 'paid')->sum('total'),
            'totalUsers' => User::count(),
            'totalMenuItems' => MenuItem::count(),
            'totalTables' => CafeTable::count(),
            'pendingReservations' => Reservation::where('status', 'pending')->count(),
            'lowStockItems' => StockItem::whereColumn('current_quantity', '<=', 'min_quantity')->count(),
        ];

        $recentOrders = Order::with('user', 'items')->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentOrders'));
    }
}
