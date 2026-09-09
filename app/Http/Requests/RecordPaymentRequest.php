<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecordPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'booking_id' => 'required|exists:bookings,id',
            'customer_id' => 'nullable|exists:customers,id',
            'payment_schedule_id' => 'nullable|exists:payment_schedules,id',
            'amount_paid' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'payment_mode' => 'required|string|in:Cash,Cheque,Bank Transfer,UPI,Card,Online Gateway,Other',
            'transaction_reference' => 'nullable|string|max:100',
            'bank_cheque_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'allocations' => 'nullable|array',
            'allocations.*.payment_schedule_id' => 'required_with:allocations|exists:payment_schedules,id',
            'allocations.*.amount' => 'required_with:allocations|numeric|min:0',
        ];
    }
}
