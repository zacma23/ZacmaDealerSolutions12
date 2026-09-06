<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Listing extends Model
{
    use HasFactory, SoftDeletes, BelongsToOrganization;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING_REVIEW = 'pending_review';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_RESERVED = 'reserved';
    public const STATUS_SOLD = 'sold';
    public const STATUS_RENTED = 'rented';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_ARCHIVED = 'archived';

    public const PRICE_FIXED = 'fixed';
    public const PRICE_NEGOTIABLE = 'negotiable';
    public const PRICE_CONTACT = 'contact_price';
    public const PRICE_FREE = 'free';
    public const PRICE_AUCTION = 'auction';

    protected $fillable = [
        'organization_id',
        'user_id',
        'category_id',
        'title',
        'slug',
        'description',
        'price',
        'currency',
        'price_type',
        'status',
        'approval_status',
        'featured',
        'featured_until',
        'address',
        'city',
        'state',
        'country',
        'latitude',
        'longitude',
        'contact_phone',
        'contact_email',
        'contact_whatsapp',
        'views_count',
        'inquiries_count',
        'metadata',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'featured' => 'boolean',
        'featured_until' => 'datetime',
        'metadata' => 'array',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function fieldValues(): HasMany
    {
        return $this->hasMany(ListingFieldValue::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(ListingMedia::class)->orderBy('sort_order');
    }

    public function primaryMedia(): HasOne
    {
        return $this->hasOne(ListingMedia::class)->where('is_primary', true);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function getFieldValue(string $fieldKey): mixed
    {
        $fieldVal = $this->fieldValues->first(function ($val) use ($fieldKey) {
            return $val->categoryField && $val->categoryField->name === $fieldKey;
        });

        return $fieldVal ? $fieldVal->value : null;
    }

    public function getPrimaryImageUrl(): string
    {
        $primary = $this->primaryMedia ?? $this->media->first();
        if ($primary && $primary->file_path) {
            if (str_starts_with($primary->file_path, 'http://') || str_starts_with($primary->file_path, 'https://')) {
                return $primary->file_path;
            }
            return asset('storage/' . $primary->file_path);
        }

        $categoryPlaceholders = [
            'vehicles' => 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=800&q=80',
            'property' => 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=800&q=80',
            'electronics' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=800&q=80',
            'machinery' => 'https://images.unsplash.com/photo-1578575437130-527eed3abbec?auto=format&fit=crop&w=800&q=80',
            'services' => 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?auto=format&fit=crop&w=800&q=80',
        ];

        $slug = $this->category?->slug;
        if ($slug && isset($categoryPlaceholders[$slug])) {
            return $categoryPlaceholders[$slug];
        }

        return 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=800&q=80';
    }
}
