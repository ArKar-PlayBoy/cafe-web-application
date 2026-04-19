<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KitchenTicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'order' => $this->whenLoaded('order', fn () => [
                'id' => $this->order->id,
                'status' => $this->order->status,
                'user' => $this->order->relationLoaded('user') ? [
                    'id' => $this->order->user->id,
                    'name' => $this->order->user->name,
                ] : null,
                'items' => $this->order->relationLoaded('items') ? 
                    $this->order->items->map(fn ($item) => [
                        'id' => $item->id,
                        'menu_item' => $item->relationLoaded('menuItem') ? $item->menuItem->name : null,
                        'quantity' => $item->quantity,
                        'notes' => $item->notes,
                    ]) : null,
            ]),
            'printed_at' => $this->printed_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}