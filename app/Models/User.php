<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_SUPER_ADMIN = 'SUPER_ADMIN';
    public const ROLE_ORGANIZATION_ADMIN = 'ORGANIZATION_ADMIN';
    public const ROLE_MANAGER = 'MANAGER';
    public const ROLE_SALES_AGENT = 'SALES_AGENT';
    public const ROLE_STAFF = 'STAFF';
    public const ROLE_SELLER = 'SELLER';
    public const ROLE_CUSTOMER = 'CUSTOMER';

    protected $fillable = [
        'organization_id',
        'name',
        'email',
        'phone',
        'password',
        'role',
        'avatar',
        'is_active',
        'email_verified_at',
        'phone_verified_at',
        'otp_code',
        'otp_expires_at',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp_code',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'otp_expires_at' => 'datetime',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function assignedLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'assigned_user_id');
    }

    public function assignedDeals(): HasMany
    {
        return $this->hasMany(Deal::class, 'assigned_user_id');
    }

    public function assignedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_user_id');
    }

    public function assignedAppointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'assigned_user_id');
    }

    public function isSuperAdmin(): bool
    {
        if (in_array(strtoupper(str_replace([' ', '-', '_'], '', (string)$this->role)), ['SUPERADMIN', 'ADMIN'], true)) {
            return true;
        }

        if (strtolower($this->email) === 'admin@zacma.com') {
            return true;
        }

        if (session()->has('impersonator_id')) {
            $impersonator = static::find(session('impersonator_id'));
            if ($impersonator && in_array(strtoupper(str_replace([' ', '-', '_'], '', (string)$impersonator->role)), ['SUPERADMIN', 'ADMIN'], true)) {
                return true;
            }
        }

        return false;
    }

    public function isOrgAdmin(): bool
    {
        return $this->role === self::ROLE_ORGANIZATION_ADMIN;
    }

    public function isManager(): bool
    {
        return $this->role === self::ROLE_MANAGER;
    }

    public function isSalesAgent(): bool
    {
        return $this->role === self::ROLE_SALES_AGENT;
    }

    public function isStaff(): bool
    {
        return in_array($this->role, [
            self::ROLE_ORGANIZATION_ADMIN,
            self::ROLE_MANAGER,
            self::ROLE_SALES_AGENT,
            self::ROLE_STAFF
        ]);
    }

    public function isSeller(): bool
    {
        return $this->role === self::ROLE_SELLER;
    }

    public function isCustomer(): bool
    {
        return $this->role === self::ROLE_CUSTOMER;
    }

    public function hasRole(string|array $roles): bool
    {
        if (is_array($roles)) {
            return in_array($this->role, $roles);
        }
        return $this->role === $roles;
    }

    public function canAccessTenant(?int $orgId): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        return (int) $this->organization_id === (int) $orgId;
    }

    public function getDashboardUrl(): string
    {
        if ($this->isSuperAdmin()) {
            return route('super-admin.dashboard');
        }
        if ($this->isStaff()) {
            return route('dealer.dashboard');
        }
        if ($this->isSeller()) {
            return route('seller.dashboard');
        }
        return route('customer.dashboard');
    }

    public function getAvatarUrl(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        $name = urlencode($this->name);
        return "https://ui-avatars.com/api/?name={$name}&color=FFFFFF&background=2563EB&bold=true";
    }
}
