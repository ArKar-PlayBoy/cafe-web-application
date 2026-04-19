<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'payment_reference' => $this->when($this->payment_reference, $this->payment_reference),
            'payment_screenshot' => $this->when(
                $this->relationLoaded('paymentVerifier') || $request->user()?->isStaff(),
                fn () => $this->payment_screenshot
            ),
            'payment_note' => $this->when($this->payment_note, $this->payment_note),
            'payment_verified_at' => $this->when(
                $this->relationLoaded('paymentVerifier') || $request->user()?->isStaff(),
                fn () => $this->payment_verified_at?->toIso8601String()
            ),
            'payment_verified_by' => $this->whenLoaded('paymentVerifier', fn () => [
                'id' => $this->paymentVerifier->id,
                'name' => $this->paymentVerifier->name,
            ]),
            'total' => (float) $this->total,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}