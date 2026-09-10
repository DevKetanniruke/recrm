<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_code' => $this->booking_code,
            'agreement_value' => (float) $this->agreement_value,
            'discount_amount' => (float) $this->discount_amount,
            'status' => $this->status,
            'booking_date' => $this->booking_date?->format('Y-m-d'),
            'project' => new ProjectResource($this->whenLoaded('project')),
            'unit' => new UnitResource($this->whenLoaded('unit')),
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'sales_agent' => new UserResource($this->whenLoaded('salesAgent')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
