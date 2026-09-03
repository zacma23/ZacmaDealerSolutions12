<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutomationRule extends Model
{
    use HasFactory, BelongsToOrganization;

    public const TRIGGER_LEAD_CREATED = 'lead.created';
    public const TRIGGER_LEAD_INACTIVE_3DAYS = 'lead.inactive_3days';
    public const TRIGGER_DEAL_STAGE_CHANGED = 'deal.stage_changed';
    public const TRIGGER_PAYMENT_COMPLETED = 'payment.completed';
    public const TRIGGER_APPOINTMENT_CREATED = 'appointment.created';

    protected $fillable = [
        'organization_id',
        'name',
        'trigger_event',
        'conditions',
        'actions',
        'is_active',
        'execution_count',
        'last_triggered_at',
    ];

    protected $casts = [
        'conditions' => 'array',
        'actions' => 'array',
        'is_active' => 'boolean',
        'last_triggered_at' => 'datetime',
    ];
}
