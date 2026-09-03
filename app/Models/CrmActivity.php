<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmActivity extends Model
{
    use HasFactory, BelongsToOrganization;

    public const TYPE_CALL = 'call';
    public const TYPE_EMAIL = 'email';
    public const TYPE_SMS = 'sms';
    public const TYPE_MEETING = 'meeting';
    public const TYPE_WHATSAPP = 'whatsapp';
    public const TYPE_TELEGRAM = 'telegram';
    public const TYPE_NOTE = 'note';
    public const TYPE_FOLLOW_UP = 'follow_up';
    public const TYPE_APPOINTMENT = 'appointment';
    public const TYPE_QUOTE = 'quote';
    public const TYPE_PAYMENT = 'payment';
    public const TYPE_MESSAGE = 'message';

    protected $fillable = [
        'organization_id',
        'user_id',
        'contact_id',
        'lead_id',
        'deal_id',
        'type',
        'subject',
        'description',
        'metadata',
        'occurred_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }
}
