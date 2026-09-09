<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateDemandNoticeRequest extends FormRequest
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
            'demand_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:demand_date',
            'demand_amount' => 'required|numeric|min:1',
            'penalty_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ];
    }
}
