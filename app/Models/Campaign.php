<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id',
        'project_id',
        'name',
        'channel',
        'communication_template_id',
        'audience_filter_json',
        'scheduled_at',
        'started_at',
        'completed_at',
        'status',
        'total_recipients',
        'sent_count',
        'delivered_count',
        'failed_count',
    ];

    protected $casts = [
        'audience_filter_json' => 'array',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'total_recipients' => 'integer',
        'sent_count' => 'integer',
        'delivered_count' => 'integer',
        'failed_count' => 'integer',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function template()
    {
        return $this->belongsTo(CommunicationTemplate::class, 'communication_template_id');
    }

    public function logs()
    {
        return $this->hasMany(CommunicationLog::class);
    }
}
