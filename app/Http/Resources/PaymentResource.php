<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'receipt_number' => $this->receipt_number,
            'payment_mode' => $this->payment_mode,
            'amount_paid' => (float) $this->amount_paid,
            'payment_date' => $this->payment_date,
            'transaction_reference' => $this->transaction_reference,
            'status' => $this->status,
            'booking' => new BookingResource($this->whenLoaded('booking')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
