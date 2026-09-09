<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CancelBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'cancellation_reason' => 'required|string|min:5',
            'cancellation_refund_amount' => 'nullable|numeric|min:0',
        ];
    }
}
