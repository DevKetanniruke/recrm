<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Floor extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'wing_id',
        'floor_number',
        'label',
        'status',
    ];

    public function wing()
    {
        return $this->belongsTo(Wing::class);
    }

    public function units()
    {
        return $this->hasMany(Unit::class);
    }
}
