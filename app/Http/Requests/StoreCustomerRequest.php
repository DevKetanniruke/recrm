<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'mobile' => 'required|string|max:20',
            'alternate_mobile' => 'nullable|string|max:20',
            'email' => 'required|email|max:150',
            'date_of_birth' => 'nullable|date',
            'occupation' => 'nullable|string|max:100',
            'company_or_employer' => 'nullable|string|max:150',
            'nationality' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:50',
            'PAN' => 'nullable|string|max:20',
            'reference' => 'nullable|string|max:100',
            'communication_preference' => 'nullable|string|in:Email,Phone,WhatsApp,SMS',
            'co_applicants' => 'nullable|array',
            'co_applicants.*.customer_name' => 'required_with:co_applicants|string|max:150',
            'co_applicants.*.relationship' => 'nullable|string|max:50',
            'co_applicants.*.mobile' => 'nullable|string|max:20',
            'co_applicants.*.email' => 'nullable|email|max:150',
            'co_applicants.*.ownership_percentage' => 'nullable|numeric|min:0|max:100',
            'co_applicants.*.applicant_type' => 'nullable|string|max:30',
            'co_applicants.*.pan_number' => 'nullable|string|max:20',
            'co_applicants.*.aadhaar_number' => 'nullable|string|max:20',
        ];
    }
}
