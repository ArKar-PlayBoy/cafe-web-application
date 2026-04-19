<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateReport;
use App\Services\CacheService;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

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

        // Use async for large date ranges (> 30 days)
        $daysDiff = $startDate->diffInDays($endDate);
        $cacheKey = CacheService::report('sales', $startDate->toDateString(), $endDate->toDateString());
        $pendingKey = CacheService::reportPending('sales', $startDate->toDateString(), $endDate->toDateString());

        if ($daysDiff > 30) {
            // Check if already cached from previous async job
            $cachedReport = Cache::get($cacheKey);
            if ($cachedReport !== null) {
                return view('admin.reports.sales', [
                    'report' => $cachedReport,
                    'dateRange' => $dateRange,
                    'dateOptions' => $dateOptions,
                ]);
            }

            // Avoid queueing duplicate generation jobs while one is already pending.
            if (Cache::add($pendingKey, true, now()->addMinutes(5))) {
                GenerateReport::dispatch($startDate, $endDate, 'sales');
            }

            return back()->with('info', 'Report is being generated in background. Refresh shortly.');
        }

        $report = CacheService::remember(
            $cacheKey,
            CacheService::TTL_REPORT,
            fn () => $this->reportService->getSalesReport($startDate, $endDate)
        );

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

        // Use async for large date ranges (> 30 days)
        $daysDiff = $startDate->diffInDays($endDate);
        $limit = (int) $request->get('limit', 10);
        $limit = max(1, min($limit, 100));
        $cacheVariant = "limit:{$limit}";
        $cacheKey = CacheService::report('popular', $startDate->toDateString(), $endDate->toDateString(), $cacheVariant);
        $pendingKey = CacheService::reportPending('popular', $startDate->toDateString(), $endDate->toDateString(), $cacheVariant);

        if ($daysDiff > 30) {
            $cachedReport = Cache::get($cacheKey);
            if ($cachedReport !== null) {
                return view('admin.reports.items', [
                    'popularItems' => $cachedReport,
                    'dateRange' => $dateRange,
                    'dateOptions' => $dateOptions,
                    'limit' => $limit,
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                ]);
            }

            if (Cache::add($pendingKey, true, now()->addMinutes(5))) {
                GenerateReport::dispatch($startDate, $endDate, 'popular', $limit);
            }

            return back()->with('info', 'Report is being generated in background. Refresh shortly.');
        }

        $popularItems = CacheService::remember(
            $cacheKey,
            CacheService::TTL_REPORT,
            fn () => $this->reportService->getPopularItems($startDate, $endDate, $limit)
        );

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

        // Use async for large date ranges (> 30 days)
        $daysDiff = $startDate->diffInDays($endDate);
        $cacheKey = CacheService::report('customers', $startDate->toDateString(), $endDate->toDateString());
        $pendingKey = CacheService::reportPending('customers', $startDate->toDateString(), $endDate->toDateString());

        if ($daysDiff > 30) {
            $cachedReport = Cache::get($cacheKey);
            if ($cachedReport !== null) {
                return view('admin.reports.customers', [
                    'analytics' => $cachedReport,
                    'dateRange' => $dateRange,
                    'dateOptions' => $dateOptions,
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                ]);
            }

            if (Cache::add($pendingKey, true, now()->addMinutes(5))) {
                GenerateReport::dispatch($startDate, $endDate, 'customers');
            }

            return back()->with('info', 'Report is being generated in background. Refresh shortly.');
        }

        $analytics = CacheService::remember(
            $cacheKey,
            CacheService::TTL_REPORT,
            fn () => $this->reportService->getCustomerAnalytics($startDate, $endDate)
        );

        return view('admin.reports.customers', compact('analytics', 'dateRange', 'dateOptions', 'startDate', 'endDate'));
    }
}
