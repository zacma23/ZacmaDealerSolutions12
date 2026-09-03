<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingFieldValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'listing_id',
        'category_field_id',
        'value',
        'numeric_value',
    ];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function categoryField(): BelongsTo
    {
        return $this->belongsTo(CategoryField::class, 'category_field_id');
    }
}
