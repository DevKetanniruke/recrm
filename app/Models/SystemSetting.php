<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id',
        'key',
        'value',
        'group',
    ];

    public static function getByKey(string $key, ?int $companyId = null, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->first();

        return $setting ? $setting->value : $default;
    }

    public static function setVal(string $key, mixed $value, string $group = 'general', ?int $companyId = null): void
    {
        static::updateOrCreate(
            ['company_id' => $companyId, 'key' => $key],
            ['value' => $value, 'group' => $group]
        );
    }
}
