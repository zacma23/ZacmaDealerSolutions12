<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DealStage extends Model
{
    use HasFactory, BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'name',
        'slug',
        'win_probability',
        'color',
        'sort_order',
        'is_closed_won',
        'is_closed_lost',
    ];

    protected $casts = [
        'win_probability' => 'integer',
        'is_closed_won' => 'boolean',
        'is_closed_lost' => 'boolean',
    ];

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }
}
