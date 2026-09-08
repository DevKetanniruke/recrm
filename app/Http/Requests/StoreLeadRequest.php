<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'mobile' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'alternate_mobile' => 'nullable|string|max:20',
            'alt_phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'city' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:255',
            'source' => 'nullable|string|max:50',
            'source_id' => 'nullable|exists:lead_sources,id',
            'campaign' => 'nullable|string|max:100',
            'project_id' => 'nullable|exists:projects,id',
            'unit_type' => 'nullable|string|max:50',
            'preferred_unit_type' => 'nullable|string|max:50',
            'minimum_budget' => 'nullable|numeric|min:0',
            'budget_min' => 'nullable|numeric|min:0',
            'maximum_budget' => 'nullable|numeric|min:0',
            'budget_max' => 'nullable|numeric|min:0',
            'preferred_floor' => 'nullable|string|max:50',
            'preferred_facing' => 'nullable|string|max:50',
            'purchase_timeline' => 'nullable|string|max:50',
            'priority' => 'nullable|string|in:Low,Medium,High,Hot,Warm,Cold',
            'rating' => 'nullable|string|in:Low,Medium,High,Hot,Warm,Cold',
            'status' => 'nullable|string|max:50',
            'status_id' => 'nullable|exists:lead_statuses,id',
            'assigned_to' => 'nullable|exists:users,id',
            'assigned_team_id' => 'nullable|exists:teams,id',
            'notes' => 'nullable|string',
        ];
    }

    public function prepareForValidation(): void
    {
        // Normalize mobile / phone
        $mobileVal = $this->mobile ?? $this->phone;
        $altVal = $this->alternate_mobile ?? $this->alt_phone;
        $minBud = $this->minimum_budget ?? $this->budget_min;
        $maxBud = $this->maximum_budget ?? $this->budget_max;
        $sourceVal = $this->source ?? $this->lead_source;
        $unitVal = $this->unit_type ?? $this->preferred_unit_type;
        $prioVal = $this->priority ?? $this->rating ?? 'Medium';

        $this->merge([
            'mobile' => $mobileVal,
            'alternate_mobile' => $altVal,
            'minimum_budget' => $minBud,
            'maximum_budget' => $maxBud,
            'source' => $sourceVal,
            'unit_type' => $unitVal,
            'priority' => $prioVal,
        ]);
    }
}
