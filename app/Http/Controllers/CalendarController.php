<?php

namespace App\Http\Controllers;

use App\Models\LeadActivity;
use App\Models\LeadFollowup;
use App\Models\Offer;
use App\Models\Project;
use App\Models\SiteVisit;
use App\Models\User;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $companyId = $user->company_id;

        $projects = Project::where('company_id', $companyId)->get();
        $agents = User::where('company_id', $companyId)->get();

        return view('calendar.index', compact('projects', 'agents'));
    }

    public function eventsApi(Request $request)
    {
        $user = auth()->user();
        $companyId = $user->company_id;

        $events = [];

        // 1. Follow-ups
        $followupsQuery = LeadFollowup::with(['lead', 'user'])->where('company_id', $companyId);
        if (!$user->isAdmin()) {
            if ($user->isManager()) {
                $followupsQuery->whereIn('user_id', $user->getTeamMemberIds());
            } else {
                $followupsQuery->where('user_id', $user->id);
            }
        }
        foreach ($followupsQuery->get() as $f) {
            $events[] = [
                'id' => 'fol_' . $f->id,
                'title' => 'Follow-up: ' . ($f->lead?->full_name ?? 'Lead') . ' (' . $f->type . ')',
                'start' => $f->followup_at->toIso8601String(),
                'color' => $f->status === 'Completed' ? '#10b981' : ($f->followup_at->isPast() ? '#ef4444' : '#f59e0b'),
                'textColor' => '#ffffff',
                'url' => route('leads.show', $f->lead_id),
                'extendedProps' => [
                    'category' => 'Follow-up',
                    'status' => $f->status,
                    'notes' => $f->notes,
                    'agent' => $f->user?->name,
                ],
            ];
        }

        // 2. Site Visits
        $visitsQuery = SiteVisit::with(['lead', 'project', 'assignedTo'])->where('company_id', $companyId);
        if (!$user->isAdmin()) {
            if ($user->isManager()) {
                $visitsQuery->whereIn('assigned_to', $user->getTeamMemberIds());
            } else {
                $visitsQuery->where('assigned_to', $user->id);
            }
        }
        foreach ($visitsQuery->get() as $v) {
            $events[] = [
                'id' => 'sv_' . $v->id,
                'title' => 'Site Visit: ' . ($v->lead?->full_name ?? 'Lead') . ' @ ' . ($v->project?->name ?? 'Project'),
                'start' => $v->visit_date->toIso8601String(),
                'color' => '#06b6d4',
                'textColor' => '#ffffff',
                'url' => route('site-visits.show', $v->id),
                'extendedProps' => [
                    'category' => 'Site Visit',
                    'status' => $v->status,
                    'driver' => $v->driver_name,
                    'agent' => $v->assignedTo?->name,
                ],
            ];
        }

        // 3. Offers Validity Deadlines
        $offersQuery = Offer::with(['lead', 'unit'])->where('company_id', $companyId);
        if (!$user->isAdmin()) {
            $offersQuery->whereIn('created_by', $user->getTeamMemberIds());
        }
        foreach ($offersQuery->get() as $o) {
            $events[] = [
                'id' => 'off_' . $o->id,
                'title' => 'Offer Expiry: #' . $o->offer_number . ' (' . ($o->lead?->full_name ?? 'Buyer') . ')',
                'start' => $o->valid_until->toIso8601String(),
                'color' => '#8b5cf6',
                'textColor' => '#ffffff',
                'url' => route('offers.show', $o->id),
                'extendedProps' => [
                    'category' => 'Offer Expiry',
                    'status' => $o->status,
                    'unit' => $o->unit?->unit_number,
                ],
            ];
        }

        return response()->json($events);
    }
}
