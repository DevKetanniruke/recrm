<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectV2Request extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasPermissionTo('projects.create');
    }

    public function rules(): array
    {
        return [
            'project_name' => 'required|string|max:255',
            'project_code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'project_type' => 'required|string|in:Residential,Commercial,Mixed',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'RERA_number' => 'nullable|string|max:100',
            'RERA_registration_date' => 'nullable|date',
            'start_date' => 'nullable|date',
            'expected_completion' => 'nullable|date',
            'actual_completion' => 'nullable|date',
            'project_status' => 'required|string|in:Planning,Under Construction,Ready to Possess,Completed,On Hold,Cancelled',
            'project_manager_id' => 'nullable|exists:users,id',
            'total_land_area' => 'nullable|numeric|min:0',
        ];
    }
}
