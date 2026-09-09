<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'channel' => ['required', 'string', 'in:email,sms,whatsapp,in_app'],
            'communication_template_id' => ['required', 'exists:communication_templates,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'scheduled_at' => ['nullable', 'date'],
            'lead_status' => ['nullable', 'string'],
            'customer_status' => ['nullable', 'string'],
        ];
    }
}
