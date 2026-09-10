<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommissionStructureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'calculation_type' => ['required', 'string', 'in:percentage,fixed_amount,slab_based,milestone_based'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'unit_type_id' => ['nullable', 'exists:unit_types,id'],
            'rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'fixed_amount' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }
}
