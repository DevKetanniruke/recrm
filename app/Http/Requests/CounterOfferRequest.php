<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CounterOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'counter_price' => 'required|numeric|min:1',
            'token_amount' => 'required|numeric|min:0',
            'offered_by' => 'required|string|in:Buyer,Builder',
            'comments' => 'nullable|string',
        ];
    }
}
