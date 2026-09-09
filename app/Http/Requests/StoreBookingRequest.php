<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'unit_id' => 'required|exists:units,id',
            'project_id' => 'nullable|exists:projects,id',
            'lead_id' => 'nullable|exists:leads,id',
            'customer_id' => 'nullable|exists:customers,id',
            'first_name' => 'required_without:customer_id|nullable|string|max:100',
            'last_name' => 'required_without:customer_id|nullable|string|max:100',
            'mobile' => 'required_without:customer_id|nullable|string|max:20',
            'email' => 'required_without:customer_id|nullable|email|max:150',
            'booking_date' => 'required|date',
            'quoted_price' => 'nullable|numeric|min:0',
            'agreed_price' => 'required|numeric|min:1',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:1',
            'booking_amount_paid' => 'required|numeric|min:0',
            'payment_mode' => 'nullable|string|max:50',
            'payment_reference' => 'nullable|string|max:100',
            'terms_conditions' => 'nullable|string',
            'co_applicants' => 'nullable|array',
        ];
    }
}
