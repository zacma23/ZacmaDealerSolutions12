<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory, SoftDeletes, BelongsToOrganization;

    public const STATUS_NEW = 'new';
    public const STATUS_CONTACTED = 'contacted';
    public const STATUS_QUALIFIED = 'qualified';
    public const STATUS_PROPOSAL = 'proposal';
    public const STATUS_NEGOTIATION = 'negotiation';
    public const STATUS_WON = 'won';
    public const STATUS_LOST = 'lost';
    public const STATUS_CLOSED = 'closed';

    public const PRIORITY_LOW = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_URGENT = 'urgent';

    public const SCORE_HOT = 'hot';
    public const SCORE_WARM = 'warm';
    public const SCORE_COLD = 'cold';

    protected $fillable = [
        'organization_id',
        'contact_id',
        'listing_id',
        'category_id',
        'assigned_user_id',
        'title',
        'source',
        'status',
        'priority',
        'estimated_value',
        'currency',
        'score',
        'score_category',
        'score_explanation',
        'last_contact_at',
        'next_follow_up_at',
        'notes',
    ];

    protected $casts = [
        'estimated_value' => 'decimal:2',
        'score' => 'integer',
        'last_contact_at' => 'datetime',
        'next_follow_up_at' => 'datetime',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
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

    public function isHot(): bool
    {
        return $this->score_category === self::SCORE_HOT;
    }

    public function isFollowUpDue(): bool
    {
        return $this->next_follow_up_at && $this->next_follow_up_at->isPast();
    }
}
