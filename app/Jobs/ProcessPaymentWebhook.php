<?php

namespace App\Jobs;

use App\Services\PaymentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPaymentWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $payload,
        public ?string $signature = null
    ) {}

    public function handle(PaymentService $paymentService): void
    {
        try {
            $result = $paymentService->handleWebhook($this->payload, $this->signature);

            Log::info('ProcessPaymentWebhook job completed', [
                'status' => $result['status'] ?? 'unknown',
            ]);
        } catch (\Exception $e) {
            Log::error('ProcessPaymentWebhook job failed', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}