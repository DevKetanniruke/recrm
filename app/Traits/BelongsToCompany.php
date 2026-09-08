<?php

namespace App\Traits;

use App\Models\Company;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToCompany
{
    /**
     * Boot the trait to automatically scope queries and set company_id on creation.
     */
    protected static function bootBelongsToCompany(): void
    {
        static::creating(function ($model) {
            if (auth()->check() && ! $model->company_id && auth()->user()->company_id) {
                $model->company_id = auth()->user()->company_id;
            }
        });

        static::addGlobalScope('company', function (Builder $builder) {
            if (auth()->check()) {
                $user = auth()->user();
                if ($user->role !== 'super_admin' && $user->company_id) {
                    $builder->where($builder->getModel()->getTable() . '.company_id', $user->company_id);
                }
            }
        });
    }

    /**
     * Relationship to the Company model.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
