<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoryField extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'label',
        'field_type',
        'placeholder',
        'default_value',
        'unit',
        'is_required',
        'is_filterable',
        'show_in_card',
        'sort_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_filterable' => 'boolean',
        'show_in_card' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(CategoryFieldOption::class)->orderBy('sort_order');
    }

    public function values(): HasMany
    {
        return $this->hasMany(ListingFieldValue::class);
    }
}
