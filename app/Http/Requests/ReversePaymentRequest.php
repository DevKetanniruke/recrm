<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReversePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'reversal_reason' => 'required|string|min:5',
            'refund_amount' => 'nullable|numeric|min:0',
        ];
    }
}
