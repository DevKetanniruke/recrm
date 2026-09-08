<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadAssignmentHistory extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id',
        'lead_id',
        'assigned_by',
        'assigned_to_user_id',
        'assigned_to_team_id',
        'previous_user_id',
        'previous_team_id',
        'notes',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function assignedTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'assigned_to_team_id');
    }

    public function previousUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'previous_user_id');
    }

    public function previousTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'previous_team_id');
    }
}
