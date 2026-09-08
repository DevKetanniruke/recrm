<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitStatusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_id',
        'previous_status',
        'new_status',
        'reason',
        'user_id',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
