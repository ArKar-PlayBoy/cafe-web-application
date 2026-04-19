<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer_name' => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            'reservation_date' => $this->reservation_date?->toDateString(),
            'reservation_time' => $this->reservation_time?->format('H:i'),
            'party_size' => $this->party_size,
            'status' => $this->status,
            'notes' => $this->when($this->notes, $this->notes),
            'table' => $this->whenLoaded('table', fn () => [
                'id' => $this->table->id,
                'table_number' => $this->table->table_number,
                'capacity' => $this->table->capacity,
            ]),
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ]),
            'confirmed_by' => $this->whenLoaded('confirmedBy', fn () => [
                'id' => $this->confirmedBy->id,
                'name' => $this->confirmedBy->name,
            ]),
            'confirmed_at' => $this->confirmed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}