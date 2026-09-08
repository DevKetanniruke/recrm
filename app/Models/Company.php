<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'name',
        'legal_name',
        'slug',
        'email',
        'phone',
        'website',
        'logo_path',
        'address',
        'city',
        'state',
        'pincode',
        'country',
        'gst_number',
        'pan_number',
        'tax_id_rera',
        'default_timezone',
        'currency_code',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function roles()
    {
        return $this->hasMany(Role::class);
    }

    public function settings()
    {
        return $this->hasMany(SystemSetting::class);
    }
}
