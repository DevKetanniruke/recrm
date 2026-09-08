<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSiteVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'lead_id' => 'required|exists:leads,id',
            'project_id' => 'required|exists:projects,id',
            'assigned_to' => 'nullable|exists:users,id',
            'visit_date' => 'required|date|after_or_equal:now',
            'feedback' => 'nullable|string',
            'rating' => 'nullable|string|max:50',
        ];
    }
}
