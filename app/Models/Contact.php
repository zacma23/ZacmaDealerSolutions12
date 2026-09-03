<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use HasFactory, SoftDeletes, BelongsToOrganization;

    public const TYPE_LEAD = 'lead';
    public const TYPE_PROSPECT = 'prospect';
    public const TYPE_CUSTOMER = 'customer';
    public const TYPE_SELLER = 'seller';
    public const TYPE_PARTNER = 'partner';
    public const TYPE_VENDOR = 'vendor';
    public const TYPE_COMPANY = 'company';
    public const TYPE_OTHER = 'other';

    public const STATUS_LEAD = 'lead';
    public const STATUS_PROSPECT = 'prospect';
    public const STATUS_CUSTOMER = 'customer';
    public const STATUS_VIP = 'vip';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_LOST = 'lost';

    protected $fillable = [
        'organization_id',
        'user_id',
        'assigned_user_id',
        'first_name',
        'last_name',
        'company',
        'email',
        'phone',
        'alternative_phone',
        'contact_type',
        'status',
        'source',
        'address',
        'city',
        'country',
        'notes',
        'ai_summary',
        'last_contact_at',
        'last_activity_at',
        'custom_fields',
    ];

    protected $casts = [
        'last_contact_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'custom_fields' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(CrmActivity::class)->orderBy('occurred_at', 'desc');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}
