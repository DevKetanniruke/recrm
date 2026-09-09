<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunicationTemplate extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id',
        'name',
        'channel',
        'subject',
        'body',
        'variables_json',
        'is_transactional',
        'status',
        'version',
    ];

    protected $casts = [
        'variables_json' => 'array',
        'is_transactional' => 'boolean',
        'version' => 'integer',
    ];

    public function campaigns()
    {
        return $this->hasMany(Campaign::class);
    }

    public function logs()
    {
        return $this->hasMany(CommunicationLog::class);
    }
}
