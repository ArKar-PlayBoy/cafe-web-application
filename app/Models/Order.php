<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'total',
        'payment_method',
        'payment_status',
        'payment_reference',
        'payment_screenshot',
        'payment_note',
        'delivery_address',
        'delivery_phone',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'payment_verified_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public const DELIVERY_STATUS_PENDING = 'pending';

    public const DELIVERY_STATUS_OUT_FOR_DELIVERY = 'out_for_delivery';

    public const DELIVERY_STATUS_DELIVERED = 'delivered';

    public const DELIVERY_STATUS_FAILED = 'failed';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function rejection(): HasOne
    {
        return $this->hasOne(OrderRejection::class);
    }

    public function paymentVerifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payment_verified_by');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function isCOD(): bool
    {
        return $this->payment_method === 'cod';
    }

    public function needsDelivery(): bool
    {
        return $this->isCOD();
    }

    public function canStartDelivery(): bool
    {
        return $this->isCOD()
            && $this->status === 'ready'
            && $this->delivery_status === self::DELIVERY_STATUS_PENDING;
    }

    public function canCollectCash(): bool
    {
        return $this->isCOD()
            && $this->delivery_status === self::DELIVERY_STATUS_OUT_FOR_DELIVERY
            && ! in_array($this->payment_status, ['verified', 'paid']);
    }

    public function markAsOutForDelivery(): bool
    {
        return $this->update([
            'delivery_status' => self::DELIVERY_STATUS_OUT_FOR_DELIVERY,
        ]);
    }

    public function markAsDelivered(): bool
    {
        return $this->update([
            'delivery_status' => self::DELIVERY_STATUS_DELIVERED,
            'payment_status' => 'verified',
            'payment_reference' => 'COD-'.$this->id.'-COLLECTED-'.time(),
            'payment_verified_at' => now(),
            'delivered_at' => now(),
        ]);
    }

    public function markAsFailed(string $reason): bool
    {
        return $this->update([
            'delivery_status' => self::DELIVERY_STATUS_FAILED,
            'delivery_failed_reason' => $reason,
        ]);
    }
}
