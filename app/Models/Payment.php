<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory, BelongsToOrganization;

    public const PROVIDER_SANTIMPAY = 'santimpay';
    public const PROVIDER_TELEBIRR = 'telebirr';
    public const PROVIDER_CHAPA = 'chapa';
    public const PROVIDER_PAYPAL = 'paypal';
    public const PROVIDER_CARD = 'card';
    public const PROVIDER_CRYPTO = 'crypto';
    public const PROVIDER_CASH = 'cash';

    public const STATUS_PENDING = 'pending';
    public const STATUS_VERIFIED = 'verified';
    public const STATUS_FAILED = 'failed';
    public const STATUS_REFUNDED = 'refunded';

    protected $fillable = [
        'organization_id',
        'order_id',
        'user_id',
        'provider',
        'amount',
        'currency',
        'transaction_reference',
        'provider_reference',
        'status',
        'request_payload',
        'response_payload',
        'verified_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'request_payload' => 'array',
        'response_payload' => 'array',
        'verified_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isVerified(): bool
    {
        return $this->status === self::STATUS_VERIFIED;
    }
}
