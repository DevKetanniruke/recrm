<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory, BelongsToCompany, Auditable, SoftDeletes;

    protected $fillable = [
        'company_id',
        'lead_number',
        'first_name',
        'last_name',
        'mobile',
        'alternate_mobile',
        'email',
        'city',
        'location',
        'source',
        'source_id',
        'campaign',
        'project_id',
        'unit_type',
        'minimum_budget',
        'maximum_budget',
        'preferred_floor',
        'preferred_facing',
        'purchase_timeline',
        'priority',
        'status',
        'status_id',
        'assigned_to',
        'assigned_team_id',
        'notes',
        'merged_into_lead_id',
    ];

    protected $casts = [
        'minimum_budget' => 'decimal:2',
        'maximum_budget' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($lead) {
            if (empty($lead->lead_number)) {
                $lead->lead_number = 'LD-' . date('Y') . '-' . str_pad((string) (static::where('company_id', $lead->company_id)->max('id') + 1), 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    // Alias accessors & mutators for V0.1/V0.2 backwards compatibility
    protected function phone(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->mobile,
            set: fn ($value) => ['mobile' => $value],
        );
    }

    protected function altPhone(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->alternate_mobile,
            set: fn ($value) => ['alternate_mobile' => $value],
        );
    }

    protected function budgetMin(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->minimum_budget,
            set: fn ($value) => ['minimum_budget' => $value],
        );
    }

    protected function budgetMax(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->maximum_budget,
            set: fn ($value) => ['maximum_budget' => $value],
        );
    }

    protected function leadSource(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->source,
            set: fn ($value) => ['source' => $value],
        );
    }

    protected function rating(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->priority,
            set: fn ($value) => ['priority' => $value],
        );
    }

    protected function preferredUnitType(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->unit_type,
            set: fn ($value) => ['unit_type' => $value],
        );
    }

    // Relationships
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function sourceObj(): BelongsTo
    {
        return $this->belongsTo(LeadSource::class, 'source_id');
    }

    public function statusObj(): BelongsTo
    {
        return $this->belongsTo(LeadStatus::class, 'status_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignedTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'assigned_team_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(LeadActivity::class)->orderBy('created_at', 'desc');
    }

    public function followups(): HasMany
    {
        return $this->hasMany(LeadFollowup::class)->orderBy('followup_at', 'asc');
    }

    public function siteVisits(): HasMany
    {
        return $this->hasMany(SiteVisit::class)->orderBy('visit_date', 'desc');
    }

    public function assignmentHistories(): HasMany
    {
        return $this->hasMany(LeadAssignmentHistory::class)->orderBy('created_at', 'desc');
    }

    public function customer(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Customer::class);
    }

    public function mergedIntoLead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'merged_into_lead_id');
    }

    public function mergedLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'merged_into_lead_id');
    }
}
