<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isProjectManager();
    }

    public function rules(): array
    {
        return [
            'floor_id' => 'required|exists:floors,id',
            'unit_number' => 'required|string|max:50',
            'unit_type' => 'required|string|max:50',
            'facing' => 'required|string|max:50',
            'carpet_area_sqft' => 'required|numeric|min:1',
            'super_builtup_area_sqft' => 'required|numeric|min:1',
            'base_rate_per_sqft' => 'required|numeric|min:0',
            'total_price' => 'required|numeric|min:0',
            'status' => 'required|string|in:Available,On Hold,Booked,Sold,Blocked',
            'features' => 'nullable|array',
            'layout_plan' => 'nullable|file|mimes:pdf,jpg,png,webp|max:5120',
        ];
    }
}
