<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSiteVisitRequest;
use App\Models\Lead;
use App\Models\Project;
use App\Models\SiteVisit;
use App\Models\User;
use Illuminate\Http\Request;

class SiteVisitController extends Controller
{
    public function index()
    {
        $siteVisits = SiteVisit::with(['lead', 'project', 'assignedTo'])
            ->orderBy('visit_date', 'desc')
            ->paginate(15);

        $leads = Lead::all();
        $projects = Project::all();
        $agents = User::where('company_id', auth()->user()->company_id)->get();

        return view('site_visits.index', compact('siteVisits', 'leads', 'projects', 'agents'));
    }

    public function store(StoreSiteVisitRequest $request)
    {
        $data = $request->validated();
        $siteVisit = SiteVisit::create($data);

        // Update Lead status to Site Visit Scheduled
        $lead = Lead::find($request->lead_id);
        if ($lead && in_array($lead->status, ['New', 'Contacted', 'Interested'])) {
            $lead->update(['status' => 'Site Visit Scheduled']);
        }

        return back()->with('success', 'Site visit scheduled successfully!');
    }

    public function updateStatus(Request $request, SiteVisit $siteVisit)
    {
        $request->validate([
            'status' => 'required|in:Scheduled,Completed,Cancelled,No-Show',
            'feedback' => 'nullable|string',
            'rating' => 'nullable|string',
        ]);

        $siteVisit->update($request->only(['status', 'feedback', 'rating']));

        return back()->with('success', 'Site visit updated!');
    }
}
