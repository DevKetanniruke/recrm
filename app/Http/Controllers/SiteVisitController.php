<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckInSiteVisitRequest;
use App\Http\Requests\StoreSiteVisitRequest;
use App\Models\Lead;
use App\Models\Project;
use App\Models\SiteVisit;
use App\Models\Unit;
use App\Models\User;
use App\Services\LeadService;
use Illuminate\Http\Request;

class SiteVisitController extends Controller
{
    protected LeadService $leadService;

    public function __construct(LeadService $leadService)
    {
        $this->leadService = $leadService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $companyId = $user->company_id;

        $query = SiteVisit::with(['lead', 'project', 'assignedTo'])
            ->where('company_id', $companyId);

        // Role Scoping
        if (!$user->isAdmin()) {
            if ($user->isManager()) {
                $allowedUserIds = $user->getTeamMemberIds();
                $query->whereIn('assigned_to', $allowedUserIds);
            } else {
                $query->where('assigned_to', $user->id);
            }
        }

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('lead', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        $siteVisits = $query->orderBy('visit_date', 'desc')->paginate(15);

        $leads = Lead::where('company_id', $companyId)->get();
        $projects = Project::where('company_id', $companyId)->get();
        $agents = User::where('company_id', $companyId)->get();
        $availableUnits = Unit::where('company_id', $companyId)->where('status', 'Available')->get();

        return view('site_visits.index', compact('siteVisits', 'leads', 'projects', 'agents', 'availableUnits'));
    }

    public function store(StoreSiteVisitRequest $request)
    {
        $data = $request->validated();
        $data['company_id'] = auth()->user()->company_id;

        if ($request->filled('transportation_type')) {
            $data['transportation_type'] = $request->transportation_type;
            $data['driver_name'] = $request->driver_name;
            $data['driver_phone'] = $request->driver_phone;
            $data['pickup_location'] = $request->pickup_location;
            $data['pickup_time'] = $request->pickup_time;
        }

        $siteVisit = SiteVisit::create($data);

        // Update Lead status to Site Visit Planned if currently New/Contacted/Interested
        $lead = Lead::find($request->lead_id);
        if ($lead && in_array($lead->status, ['New', 'Contacted', 'Interested'])) {
            $lead->update(['status' => 'Site Visit Planned']);
        }

        $this->leadService->logActivity(
            lead: $siteVisit->lead,
            type: 'Site Visit',
            subject: "Site Visit Scheduled for {$siteVisit->project?->name}",
            summary: "Scheduled for " . $siteVisit->visit_date->format('M d, Y h:i A') . ($request->driver_name ? " (Cab Driver: {$request->driver_name})" : ""),
            user: auth()->user()
        );

        return back()->with('success', 'Site visit scheduled successfully!');
    }

    public function show(SiteVisit $siteVisit)
    {
        $siteVisit->load(['lead', 'project', 'assignedTo']);
        $unitsViewed = !empty($siteVisit->units_viewed) ? Unit::whereIn('id', $siteVisit->units_viewed)->get() : collect();
        $allUnits = Unit::where('project_id', $siteVisit->project_id)->get();

        return view('site_visits.show', compact('siteVisit', 'unitsViewed', 'allUnits'));
    }

    public function dispatchCab(Request $request, SiteVisit $siteVisit)
    {
        $request->validate([
            'driver_name' => 'required|string|max:100',
            'driver_phone' => 'required|string|max:20',
            'pickup_location' => 'nullable|string|max:255',
            'pickup_time' => 'nullable|date',
        ]);

        $siteVisit->update([
            'transportation_type' => 'Company Cab',
            'driver_name' => $request->driver_name,
            'driver_phone' => $request->driver_phone,
            'pickup_location' => $request->pickup_location,
            'pickup_time' => $request->pickup_time,
            'status' => 'Driver Assigned',
        ]);

        return back()->with('success', 'Transport cab dispatched and driver assigned!');
    }

    public function checkIn(CheckInSiteVisitRequest $request, SiteVisit $siteVisit)
    {
        $siteVisit->update([
            'check_in_at' => now(),
            'check_in_lat' => $request->latitude,
            'check_in_lng' => $request->longitude,
            'status' => 'Checked In',
        ]);

        $this->leadService->logActivity(
            lead: $siteVisit->lead,
            type: 'Site Visit',
            subject: 'Site Visit GPS Check-In',
            summary: "Checked in at site at " . now()->format('h:i A') . ($request->latitude ? " (GPS: {$request->latitude}, {$request->longitude})" : ""),
            user: auth()->user()
        );

        return back()->with('success', 'GPS Check-in recorded at site!');
    }

    public function checkOut(Request $request, SiteVisit $siteVisit)
    {
        $request->validate([
            'rating' => 'required|string|in:Hot,Warm,Cold,Not Interested',
            'feedback' => 'required|string',
            'units_viewed' => 'nullable|array',
            'units_viewed.*' => 'exists:units,id',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $siteVisit->update([
            'check_out_at' => now(),
            'check_out_lat' => $request->latitude,
            'check_out_lng' => $request->longitude,
            'rating' => $request->rating,
            'feedback' => $request->feedback,
            'units_viewed' => $request->units_viewed ?? [],
            'status' => 'Completed',
        ]);

        // Sync rating & status to Lead
        $lead = $siteVisit->lead;
        if ($lead) {
            $priority = match($request->rating) {
                'Hot' => 'Hot',
                'Warm' => 'High',
                'Cold' => 'Medium',
                default => 'Low',
            };

            $lead->update([
                'priority' => $priority,
                'status' => 'Site Visit Completed',
                'notes' => trim($lead->notes . "\n[Site Visit Feedback]: " . $request->feedback),
            ]);
        }

        $this->leadService->logActivity(
            lead: $siteVisit->lead,
            type: 'Site Visit',
            subject: 'Site Visit Completed',
            summary: "Visit finished. Overall Rating: {$request->rating}. Feedback: {$request->feedback}",
            user: auth()->user()
        );

        return back()->with('success', 'Site visit marked as Completed!');
    }

    public function updateStatus(Request $request, SiteVisit $siteVisit)
    {
        $request->validate([
            'status' => 'required|in:Scheduled,Driver Assigned,In Transit,Checked In,Completed,Rescheduled,Cancelled,No Show',
            'feedback' => 'nullable|string',
            'rating' => 'nullable|string',
        ]);

        $siteVisit->update($request->only(['status', 'feedback', 'rating']));

        return back()->with('success', 'Site visit status updated!');
    }
}
