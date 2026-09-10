<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\LeadResource;
use App\Models\Lead;
use Illuminate\Http\Request;

class LeadApiController extends Controller
{
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;
        
        $leads = Lead::where('company_id', $companyId)
            ->whereNull('merged_into_lead_id')
            ->with(['project', 'assignedTo'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return LeadResource::collection($leads);
    }

    public function show(Lead $lead)
    {
        if ($lead->company_id !== auth()->user()->company_id) {
            return response()->json(['message' => 'Unauthorized access to lead resource.'], 403);
        }

        $lead->load(['project', 'assignedTo']);
        return new LeadResource($lead);
    }
}
