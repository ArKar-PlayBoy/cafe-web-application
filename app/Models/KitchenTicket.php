<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KitchenTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'status',
        'printed_at',
        'completed_at',
    ];

    protected $casts = [
        'printed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function markAsPreparing(): void
    {
        $this->update(['status' => 'preparing']);
    }

    public function markAsReady(): void
    {
        $this->update([
            'status' => 'ready',
            'completed_at' => now(),
        ]);
    }

    public function markAsCompleted(): void
    {
        $this->update(['status' => 'completed']);
    }

    public function markPrinted(): void
    {
        $this->update(['printed_at' => now()]);
    }

    public function isNew(): bool
    {
        return $this->status === 'new';
    }

    public function isPreparing(): bool
    {
        return $this->status === 'preparing';
    }

    public function isReady(): bool
    {
        return $this->status === 'ready';
    }
}
