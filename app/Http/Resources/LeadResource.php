<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'lead_number' => $this->lead_number,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'mobile' => $this->mobile,
            'email' => $this->email,
            'source' => $this->source,
            'status' => $this->status,
            'priority' => $this->priority,
            'project' => new ProjectResource($this->whenLoaded('project')),
            'assigned_to' => new UserResource($this->whenLoaded('assignedTo')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
