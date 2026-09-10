<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecordCommissionPayoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'commission_id' => ['required', 'exists:commissions,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_date' => ['required', 'date'],
            'payment_mode' => ['required', 'string', 'in:NEFT,RTGS,Cheque,UPI'],
            'payment_reference' => ['nullable', 'string', 'max:100'],
            'bank_details' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
