<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'booking_id' => 'required|exists:bookings,id',
            'template_id' => 'nullable|exists:payment_plan_templates,id',
            'milestones' => 'nullable|array',
            'milestones.*.milestone_name' => 'required_with:milestones|string|max:150',
            'milestones.*.milestone_code' => 'nullable|string|max:50',
            'milestones.*.percentage' => 'required_with:milestones|numeric|min:0|max:100',
            'milestones.*.due_date' => 'nullable|date',
            'milestones.*.amount_due' => 'nullable|numeric|min:0',
        ];
    }
}
