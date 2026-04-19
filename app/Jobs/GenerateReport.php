<?php

namespace App\Jobs;

use App\Services\CacheService;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GenerateReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ?Carbon $startDate = null,
        public ?Carbon $endDate = null,
        public string $type = 'sales',
        public int $limit = 10
    ) {}

    public function handle(ReportService $reportService): void
    {
        $variant = $this->type === 'popular' ? "limit:{$this->limit}" : null;
        $startDate = $this->startDate?->toDateString() ?? '';
        $endDate = $this->endDate?->toDateString() ?? '';
        $cacheKey = CacheService::report($this->type, $startDate, $endDate, $variant);
        $pendingKey = CacheService::reportPending($this->type, $startDate, $endDate, $variant);

        try {
            $result = match ($this->type) {
                'sales' => $reportService->getSalesReport($this->startDate, $this->endDate),
                'customers' => $reportService->getCustomerAnalytics($this->startDate, $this->endDate),
                'popular' => $reportService->getPopularItems($this->startDate, $this->endDate, $this->limit),
                default => null,
            };

            // Cache async results for the same TTL used by synchronous report reads.
            Cache::put($cacheKey, $result, now()->addSeconds(CacheService::TTL_REPORT));

            Log::info('GenerateReport job completed', [
                'type' => $this->type,
                'start' => $this->startDate?->toDateString(),
                'end' => $this->endDate?->toDateString(),
                'cache_key' => $cacheKey,
            ]);
        } catch (\Exception $e) {
            Log::error('GenerateReport job failed', [
                'error' => $e->getMessage(),
                'type' => $this->type,
            ]);

            throw $e;
        } finally {
            Cache::forget($pendingKey);
        }
    }
}
