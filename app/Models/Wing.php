<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wing extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'building_id',
        'name',
        'code',
        'number_of_floors',
        'status',
    ];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function floors()
    {
        return $this->hasMany(Floor::class);
    }
}
