<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LabourEntry extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'project_id',
        'building_id',
        'labour_identifier',
        'work_category',
        'days_worked',
        'daily_wage_rate',
        'total_wages',
        'work_date',
        'supervisor_user_id',
        'payment_status',
        'notes',
    ];

    protected $casts = [
        'days_worked' => 'decimal:2',
        'daily_wage_rate' => 'decimal:2',
        'total_wages' => 'decimal:2',
        'work_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($entry) {
            $entry->total_wages = (float) $entry->days_worked * (float) $entry->daily_wage_rate;
        });
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_user_id');
    }
}
