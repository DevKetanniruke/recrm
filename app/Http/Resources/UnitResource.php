<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'unit_number' => $this->unit_number,
            'status' => $this->status,
            'base_price' => (float) $this->base_price,
            'total_price' => (float) $this->total_price,
            'project' => new ProjectResource($this->whenLoaded('project')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
