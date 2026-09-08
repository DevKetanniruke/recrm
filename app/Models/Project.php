<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, BelongsToCompany, SoftDeletes, Auditable;

    protected $fillable = [
        'company_id',
        'project_name',
        'name',
        'project_code',
        'code',
        'description',
        'project_type',
        'address',
        'city',
        'state',
        'pincode',
        'latitude',
        'longitude',
        'RERA_number',
        'RERA_registration_date',
        'start_date',
        'expected_completion',
        'actual_completion',
        'project_status',
        'status',
        'project_manager_id',
        'total_land_area',
        'project_images',
        'project_documents',
    ];

    protected $casts = [
        'RERA_registration_date' => 'date',
        'start_date' => 'date',
        'expected_completion' => 'date',
        'actual_completion' => 'date',
        'total_land_area' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'project_images' => 'array',
        'project_documents' => 'array',
    ];

    public static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if (empty($model->project_name) && ! empty($model->name)) {
                $model->project_name = $model->name;
            }
            if (empty($model->name) && ! empty($model->project_name)) {
                $model->name = $model->project_name;
            }
            if (empty($model->project_status) && ! empty($model->status)) {
                $model->project_status = $model->status;
            }
            if (empty($model->status) && ! empty($model->project_status)) {
                $model->status = $model->project_status;
            }
        });
    }

    public function projectManager()
    {
        return $this->belongsTo(User::class, 'project_manager_id');
    }

    public function buildings()
    {
        return $this->hasMany(Building::class);
    }

    public function units()
    {
        return $this->hasMany(Unit::class);
    }
}
