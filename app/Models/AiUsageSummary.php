<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiUsageSummary extends Model
{
    use HasFactory, BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'year_month',
        'total_requests',
        'total_tokens',
        'total_cost',
    ];

    protected $casts = [
        'total_requests' => 'integer',
        'total_tokens' => 'integer',
        'total_cost' => 'decimal:4',
    ];
}
