<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'subscription_plan_id',
        'name',
        'slug',
        'subdomain',
        'custom_domain',
        'email',
        'phone',
        'address',
        'city',
        'country',
        'currency',
        'logo',
        'favicon',
        'branding_colors',
        'status',
        'subscription_ends_at',
    ];

    protected $casts = [
        'branding_colors' => 'array',
        'subscription_ends_at' => 'datetime',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    public function dealStages(): HasMany
    {
        return $this->hasMany(DealStage::class)->orderBy('sort_order');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function aiRequests(): HasMany
    {
        return $this->hasMany(AiRequest::class);
    }

    public function automationRules(): HasMany
    {
        return $this->hasMany(AutomationRule::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function settings(): HasMany
    {
        return $this->hasMany(Setting::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function canCreateListing(): bool
    {
        if (!$this->plan) {
            return true; // default open
        }
        return $this->listings()->count() < $this->plan->listing_limit;
    }

    public function canCreateContact(): bool
    {
        if (!$this->plan) {
            return true;
        }
        return $this->contacts()->count() < $this->plan->contact_limit;
    }

    public function canAddUser(): bool
    {
        if (!$this->plan) {
            return true;
        }
        return $this->users()->count() < $this->plan->user_limit;
    }

    public function getPlanFeature(string $key, mixed $default = null): mixed
    {
        if (!$this->plan || empty($this->plan->features) || !is_array($this->plan->features)) {
            return $default;
        }
        return $this->plan->features[$key] ?? $default;
    }

    public function dailyPostsAllowed(): int
    {
        return (int) $this->getPlanFeature('posts_per_day', 5);
    }

    public function postsTodayCount(): int
    {
        return $this->listings()->whereDate('created_at', today())->count();
    }

    public function canPostItemToday(): bool
    {
        $allowed = $this->dailyPostsAllowed();
        if ($allowed < 0) {
            return true; // unlimited on Advance tier
        }
        return $this->postsTodayCount() < $allowed;
    }

    public function canAccessCustomerPhone(): bool
    {
        return (bool) $this->getPlanFeature('customer_phone_access', true);
    }

    public function hasAiCustomerAssistant(): bool
    {
        return (bool) $this->getPlanFeature('ai_customer_assistant', true);
    }

    public function hasUsernameBrandedAi(): bool
    {
        return (bool) $this->getPlanFeature('ai_username_branding', false);
    }

    public function aiAssistantBrandedName(): string
    {
        if ($this->hasUsernameBrandedAi()) {
            $handle = $this->subdomain ?: $this->slug;
            return "@{$handle} AI Customer Assistant";
        }
        return "Zacma Dealership Copilot";
    }
}
