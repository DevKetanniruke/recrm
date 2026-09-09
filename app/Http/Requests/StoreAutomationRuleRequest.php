<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAutomationRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'trigger_event' => ['required', 'string', 'in:lead.created,site_visit.scheduled,booking.confirmed,payment.due,payment.overdue,followup.due'],
            'communication_template_id' => ['required', 'exists:communication_templates,id'],
            'delay_minutes' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }
}
