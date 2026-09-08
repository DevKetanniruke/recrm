<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->isAccountant() || auth()->user()->isAdmin());
    }

    public function rules(): array
    {
        return [
            'booking_id' => 'required|exists:bookings,id',
            'payment_schedule_id' => 'nullable|exists:payment_schedules,id',
            'amount_paid' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string|in:Bank Transfer,Cheque,UPI,Cash,Credit Card',
            'transaction_reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ];
    }
}
