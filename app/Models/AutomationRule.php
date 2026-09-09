<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutomationRule extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id',
        'name',
        'trigger_event',
        'conditions_json',
        'communication_template_id',
        'delay_minutes',
        'is_active',
    ];

    protected $casts = [
        'conditions_json' => 'array',
        'delay_minutes' => 'integer',
        'is_active' => 'boolean',
    ];

    public function template()
    {
        return $this->belongsTo(CommunicationTemplate::class, 'communication_template_id');
    }

    public function executions()
    {
        return $this->hasMany(AutomationExecution::class);
    }
}
