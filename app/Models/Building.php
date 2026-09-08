<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Building extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'project_id',
        'name',
        'code',
        'number_of_floors',
        'status',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function wings()
    {
        return $this->hasMany(Wing::class);
    }
}
