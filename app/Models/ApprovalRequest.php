<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'requested_by',
        'approved_by',
        'action',
        'resource_type',
        'resource_id',
        'payload',
        'reason',
        'rejection_reason',
        'status',
        'expires_at',
        'approved_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'expires_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired')
            ->orWhere(function ($q) {
                $q->where('status', 'pending')
                    ->where('expires_at', '<', now());
            });
    }

    public function scopeForAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending' && ($this->expires_at === null || $this->expires_at->isFuture());
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function approve(User $approver, ?string $notes = null): void
    {
        $this->update([
            'approved_by' => $approver->id,
            'status' => 'approved',
            'approved_at' => now(),
            'rejection_reason' => $notes,
        ]);
    }

    public function reject(User $rejecter, string $reason): void
    {
        $this->update([
            'approved_by' => $rejecter->id,
            'status' => 'rejected',
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }
}
