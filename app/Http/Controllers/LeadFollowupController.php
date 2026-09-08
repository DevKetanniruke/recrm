<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadFollowup;
use Illuminate\Http\Request;

class LeadFollowupController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = LeadFollowup::with(['lead', 'user'])->where('company_id', $user->company_id);

        if (!$user->isAdmin()) {
            if ($user->isManager()) {
                $allowedUserIds = $user->getTeamMemberIds();
                $query->whereIn('user_id', $allowedUserIds);
            } else {
                $query->where('user_id', $user->id);
            }
        }

        $filter = $request->get('filter', 'today'); // today, overdue, upcoming, all

        if ($filter === 'today') {
            $query->whereDate('followup_at', today())->where('status', 'Pending');
        } elseif ($filter === 'overdue') {
            $query->where('followup_at', '<', now())->where('status', 'Pending');
        } elseif ($filter === 'upcoming') {
            $query->where('followup_at', '>', now())->where('status', 'Pending');
        }

        $followups = $query->orderBy('followup_at', 'asc')->paginate(20);

        return view('leads.followups', compact('followups', 'filter'));
    }

    public function store(Request $request, Lead $lead)
    {
        $request->validate([
            'type' => 'required|string|in:Call,WhatsApp,Email,SMS,Meeting,Follow-up,Site Visit,Other',
            'followup_at' => 'required|date',
            'notes' => 'nullable|string',
            'user_id' => 'nullable|exists:users,id',
        ]);

        LeadFollowup::create([
            'company_id' => $lead->company_id,
            'lead_id' => $lead->id,
            'user_id' => $request->user_id ?? auth()->id(),
            'type' => $request->type,
            'followup_at' => $request->followup_at,
            'notes' => $request->notes,
            'status' => 'Pending',
        ]);

        return back()->with('success', 'Follow-up scheduled successfully!');
    }

    public function updateStatus(Request $request, LeadFollowup $followup)
    {
        $request->validate([
            'status' => 'required|in:Pending,Completed,Missed,Cancelled',
            'outcome' => 'nullable|string',
            'next_action' => 'nullable|string',
        ]);

        $followup->update([
            'status' => $request->status,
            'outcome' => $request->outcome,
            'next_action' => $request->next_action,
            'completed_at' => $request->status === 'Completed' ? now() : null,
        ]);

        return back()->with('success', 'Follow-up status updated!');
    }
}
