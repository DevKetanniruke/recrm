<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'lead_id' => 'required|exists:leads,id',
            'unit_id' => 'required|exists:units,id',
            'offered_price' => 'required|numeric|min:1',
            'token_amount_offered' => 'required|numeric|min:0',
            'payment_plan_type' => 'required|string|in:Downpayment,Construction Linked,Time Linked',
            'validity_days' => 'nullable|integer|min:1|max:30',
            'terms_conditions' => 'nullable|string',
        ];
    }
}
