<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function sales(Request $request)
    {
        $this->authorize('orders.view');

        $dateRange = $request->get('date_range', 'last_30_days');
        $dateOptions = $this->reportService->getDateRangeOptions();
        
        if ($dateRange === 'custom') {
            $startDate = Carbon::parse($request->get('start_date', now()->subDays(30)->format('Y-m-d')))->startOfDay();
            $endDate = Carbon::parse($request->get('end_date', now()->format('Y-m-d')))->endOfDay();
        } elseif (isset($dateOptions[$dateRange])) {
            $startDate = $dateOptions[$dateRange]['start'];
            $endDate = $dateOptions[$dateRange]['end'];
        } else {
            $startDate = now()->subDays(30)->startOfDay();
            $endDate = now()->endOfDay();
            $dateRange = 'last_30_days';
        }

        $report = $this->reportService->getSalesReport($startDate, $endDate);

        return view('admin.reports.sales', compact('report', 'dateRange', 'dateOptions'));
    }

    public function items(Request $request)
    {
        $this->authorize('orders.view');

        $dateRange = $request->get('date_range', 'last_30_days');
        $dateOptions = $this->reportService->getDateRangeOptions();
        
        if ($dateRange === 'custom') {
            $startDate = Carbon::parse($request->get('start_date', now()->subDays(30)->format('Y-m-d')))->startOfDay();
            $endDate = Carbon::parse($request->get('end_date', now()->format('Y-m-d')))->endOfDay();
        } elseif (isset($dateOptions[$dateRange])) {
            $startDate = $dateOptions[$dateRange]['start'];
            $endDate = $dateOptions[$dateRange]['end'];
        } else {
            $startDate = now()->subDays(30)->startOfDay();
            $endDate = now()->endOfDay();
            $dateRange = 'last_30_days';
        }

        $limit = (int) $request->get('limit', 10);
        $limit = max(1, min($limit, 100));
        $popularItems = $this->reportService->getPopularItems($startDate, $endDate, $limit);

        return view('admin.reports.items', compact('popularItems', 'dateRange', 'dateOptions', 'limit', 'startDate', 'endDate'));
    }

    public function customers(Request $request)
    {
        $this->authorize('orders.view');

        $dateRange = $request->get('date_range', 'last_30_days');
        $dateOptions = $this->reportService->getDateRangeOptions();
        
        if ($dateRange === 'custom') {
            $startDate = Carbon::parse($request->get('start_date', now()->subDays(30)->format('Y-m-d')))->startOfDay();
            $endDate = Carbon::parse($request->get('end_date', now()->format('Y-m-d')))->endOfDay();
        } elseif (isset($dateOptions[$dateRange])) {
            $startDate = $dateOptions[$dateRange]['start'];
            $endDate = $dateOptions[$dateRange]['end'];
        } else {
            $startDate = now()->subDays(30)->startOfDay();
            $endDate = now()->endOfDay();
            $dateRange = 'last_30_days';
        }

        $analytics = $this->reportService->getCustomerAnalytics($startDate, $endDate);

        return view('admin.reports.customers', compact('analytics', 'dateRange', 'dateOptions', 'startDate', 'endDate'));
    }
}
