<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaterialCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'code',
        'description',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function materialEntries()
    {
        return $this->hasMany(MaterialEntry::class, 'category_id');
    }
}
