<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->isSalesAgent() || auth()->user()->isProjectManager());
    }

    public function rules(): array
    {
        return [
            'unit_id' => 'required|exists:units,id',
            'lead_id' => 'nullable|exists:leads,id',
            'customer_first_name' => 'required|string|max:100',
            'customer_last_name' => 'nullable|string|max:100',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_pan_number' => 'nullable|string|max:50',
            'customer_address' => 'nullable|string',
            'booking_date' => 'required|date',
            'agreed_price' => 'required|numeric|min:1',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'booking_amount_paid' => 'required|numeric|min:0',
            'terms_conditions' => 'nullable|string',
            'milestones' => 'nullable|array',
            'milestones.*.milestone_name' => 'required_with:milestones|string',
            'milestones.*.due_date' => 'required_with:milestones|date',
            'milestones.*.amount_due' => 'required_with:milestones|numeric|min:0',
        ];
    }
}
