<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'current_quantity' => $this->current_quantity,
            'min_quantity' => $this->min_quantity,
            'unit' => $this->unit,
            'unit_cost' => $this->when($this->unit_cost, $this->unit_cost),
            'category' => $this->when($this->category, $this->category),
            'bin_location' => $this->when($this->bin_location, $this->bin_location),
            'barcode' => $this->when($this->barcode, $this->barcode),
            'is_low_stock' => $this->current_quantity < $this->min_quantity,
            'expiry_tracking' => $this->whenLoaded('batches', fn () =>
                $this->batches->where('expiry_date', '!=', null)->isNotEmpty()
            ),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}