<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCampaignRequest;
use App\Jobs\ExecuteCampaignJob;
use App\Models\Campaign;
use App\Models\CommunicationTemplate;
use App\Models\Project;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $query = Campaign::with(['template', 'project'])->where('company_id', $companyId);
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $campaigns = $query->orderBy('created_at', 'desc')->paginate(15);
        return view('communication.campaigns.index', compact('campaigns'));
    }

    public function create()
    {
        $companyId = auth()->user()->company_id;
        $templates = CommunicationTemplate::where('company_id', $companyId)->where('status', 'Active')->get();
        $projects = Project::where('company_id', $companyId)->get();

        return view('communication.campaigns.create', compact('templates', 'projects'));
    }

    public function store(StoreCampaignRequest $request)
    {
        $companyId = auth()->user()->company_id;

        $audienceFilter = [];
        if ($request->filled('lead_status')) {
            $audienceFilter['lead_status'] = $request->lead_status;
        }
        if ($request->filled('customer_status')) {
            $audienceFilter['customer_status'] = $request->customer_status;
        }

        $scheduledAt = $request->filled('scheduled_at') ? $request->scheduled_at : null;
        $status = $scheduledAt ? 'Scheduled' : 'Draft';

        $campaign = Campaign::create([
            'company_id' => $companyId,
            'project_id' => $request->project_id,
            'name' => $request->name,
            'channel' => $request->channel,
            'communication_template_id' => $request->communication_template_id,
            'audience_filter_json' => $audienceFilter,
            'scheduled_at' => $scheduledAt,
            'status' => $status,
        ]);

        return redirect()->route('communication.campaigns.index')
            ->with('success', "Campaign '{$campaign->name}' created successfully!");
    }

    public function launch(Campaign $campaign)
    {
        if ($campaign->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        ExecuteCampaignJob::dispatch($campaign);

        return back()->with('success', "Campaign '{$campaign->name}' execution queued!");
    }

    public function cancel(Campaign $campaign)
    {
        if ($campaign->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $campaign->update(['status' => 'Cancelled']);

        return back()->with('success', "Campaign '{$campaign->name}' has been cancelled.");
    }
}
