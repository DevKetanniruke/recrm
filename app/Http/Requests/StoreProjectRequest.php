<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isProjectManager();
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'project_type' => 'required|string|in:Residential,Commercial,Mixed',
            'status' => 'required|string|in:Upcoming,Ongoing,Completed',
            'location' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'rera_number' => 'nullable|string|max:100',
            'total_area_sqft' => 'nullable|numeric|min:0',
            'amenities' => 'nullable|array',
            'description' => 'nullable|string',
            'brochure' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ];
    }
}
