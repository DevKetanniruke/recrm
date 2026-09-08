<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteVisit extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id',
        'lead_id',
        'project_id',
        'assigned_to',
        'visit_date',
        'status',
        'feedback',
        'rating',
    ];

    protected $casts = [
        'visit_date' => 'datetime',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
