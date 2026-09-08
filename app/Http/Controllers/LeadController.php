<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\UpdateLeadRequest;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use App\Services\LeadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class LeadController extends Controller
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

        $query = Lead::with(['project', 'sourceObj', 'statusObj', 'assignedTo', 'assignedTeam', 'activities'])
            ->where('company_id', $companyId)
            ->whereNull('merged_into_lead_id');

        // Scope access based on Role / Policy
        if (!$user->isAdmin()) {
            if ($user->isManager()) {
                $allowedUserIds = $user->getTeamMemberIds();
                $userTeamIds = $user->teams()->pluck('teams.id')->toArray();

                $query->where(function ($q) use ($allowedUserIds, $userTeamIds) {
                    $q->whereIn('assigned_to', $allowedUserIds)
                      ->orWhereIn('assigned_team_id', $userTeamIds);
                });
            } else {
                $userTeamIds = $user->teams()->pluck('teams.id')->toArray();
                $query->where(function ($q) use ($user, $userTeamIds) {
                    $q->where('assigned_to', $user->id)
                      ->orWhereIn('assigned_team_id', $userTeamIds);
                });
            }
        }

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('lead_number', 'like', "%{$search}%");
            });
        }

        $viewMode = $request->get('view', 'list'); // 'list' or 'kanban'

        $sources = LeadSource::where('company_id', $companyId)->where('is_active', true)->get();
        $statuses = LeadStatus::where('company_id', $companyId)->where('is_active', true)->orderBy('sort_order')->get();
        $projects = Project::where('company_id', $companyId)->get();
        $agents = User::where('company_id', $companyId)->get();
        $teams = Team::where('company_id', $companyId)->get();

        if ($viewMode === 'kanban') {
            $leadsByStatus = [];
            foreach ($statuses as $statusObj) {
                $statusQuery = (clone $query)->where(function ($q) use ($statusObj) {
                    $q->where('status_id', $statusObj->id)
                      ->orWhere('status', $statusObj->name);
                });
                $leadsByStatus[$statusObj->name] = $statusQuery->orderBy('created_at', 'desc')->get();
            }
            return view('leads.index', compact('leadsByStatus', 'sources', 'statuses', 'projects', 'agents', 'teams', 'viewMode'));
        }

        $leads = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('leads.index', compact('leads', 'sources', 'statuses', 'projects', 'agents', 'teams', 'viewMode'));
    }

    public function create()
    {
        $companyId = auth()->user()->company_id;
        $sources = LeadSource::where('company_id', $companyId)->where('is_active', true)->get();
        $statuses = LeadStatus::where('company_id', $companyId)->where('is_active', true)->orderBy('sort_order')->get();
        $projects = Project::where('company_id', $companyId)->get();
        $agents = User::where('company_id', $companyId)->get();
        $teams = Team::where('company_id', $companyId)->get();

        return view('leads.create', compact('sources', 'statuses', 'projects', 'agents', 'teams'));
    }

    public function store(StoreLeadRequest $request)
    {
        $companyId = auth()->user()->company_id;
        $data = $request->validated();

        // Duplicate Check
        if (!$request->boolean('ignore_duplicates') && !empty($data['mobile'])) {
            $duplicates = $this->leadService->checkDuplicates($companyId, $data['mobile'], $data['email'] ?? null);
            if ($duplicates->isNotEmpty()) {
                return back()->withInput()->with('duplicate_warning', $duplicates);
            }
        }

        $data['company_id'] = $companyId;
        if (empty($data['assigned_to'])) {
            $data['assigned_to'] = auth()->id();
        }

        $lead = Lead::create($data);

        // Initial Activity
        $this->leadService->logActivity(
            lead: $lead,
            type: 'Note',
            subject: 'Lead Created',
            summary: 'Lead registered into CRM system.',
            user: auth()->user()
        );

        // Record Initial Assignment
        if (!empty($lead->assigned_to) || !empty($lead->assigned_team_id)) {
            $this->leadService->assignLead($lead, $lead->assigned_to, $lead->assigned_team_id, auth()->user(), 'Initial lead creation assignment');
        }

        return redirect()->route('leads.show', $lead->id)->with('success', 'Lead created successfully!');
    }

    public function show(Lead $lead)
    {
        $this->authorize('view', $lead);

        $lead->load([
            'project',
            'sourceObj',
            'statusObj',
            'assignedTo',
            'assignedTeam',
            'activities.user',
            'followups.user',
            'siteVisits.assignedTo',
            'assignmentHistories.assigner',
            'assignmentHistories.assignedUser',
            'assignmentHistories.assignedTeam',
            'customer',
        ]);

        // Aggregate Chronological Timeline Items
        $timeline = collect();

        foreach ($lead->activities as $act) {
            $timeline->push([
                'type' => 'activity',
                'title' => $act->subject ?? ($act->activity_type . ' Activity'),
                'description' => $act->summary,
                'user' => $act->user?->name ?? 'System',
                'timestamp' => $act->created_at,
                'badge' => $act->activity_type,
                'outcome' => $act->outcome,
                'next_action' => $act->next_action,
            ]);
        }

        foreach ($lead->followups as $fol) {
            $timeline->push([
                'type' => 'followup',
                'title' => 'Follow-up Scheduled: ' . $fol->type,
                'description' => $fol->notes,
                'user' => $fol->user?->name ?? 'System',
                'timestamp' => $fol->followup_at,
                'badge' => 'Follow-up',
                'status' => $fol->status,
            ]);
        }

        foreach ($lead->assignmentHistories as $hist) {
            $assignedToName = $hist->assignedUser?->name ?? ($hist->assignedTeam?->name ? 'Team: ' . $hist->assignedTeam->name : 'Unassigned');
            $timeline->push([
                'type' => 'assignment',
                'title' => 'Lead Assigned to ' . $assignedToName,
                'description' => $hist->notes ?? 'Reassignment event',
                'user' => $hist->assigner?->name ?? 'System',
                'timestamp' => $hist->created_at,
                'badge' => 'Assignment',
            ]);
        }

        foreach ($lead->siteVisits as $visit) {
            $timeline->push([
                'type' => 'site_visit',
                'title' => 'Site Visit: ' . ($visit->project?->name ?? 'Project'),
                'description' => $visit->notes,
                'user' => $visit->assignedTo?->name ?? 'Agent',
                'timestamp' => $visit->visit_date,
                'badge' => 'Site Visit',
                'status' => $visit->status,
            ]);
        }

        $timeline = $timeline->sortByDesc('timestamp')->values();

        $companyId = auth()->user()->company_id;
        $sources = LeadSource::where('company_id', $companyId)->where('is_active', true)->get();
        $statuses = LeadStatus::where('company_id', $companyId)->where('is_active', true)->orderBy('sort_order')->get();
        $projects = Project::where('company_id', $companyId)->get();
        $agents = User::where('company_id', $companyId)->get();
        $teams = Team::where('company_id', $companyId)->get();

        return view('leads.show', compact('lead', 'timeline', 'sources', 'statuses', 'projects', 'agents', 'teams'));
    }

    public function edit(Lead $lead)
    {
        $this->authorize('update', $lead);

        $companyId = auth()->user()->company_id;
        $sources = LeadSource::where('company_id', $companyId)->where('is_active', true)->get();
        $statuses = LeadStatus::where('company_id', $companyId)->where('is_active', true)->orderBy('sort_order')->get();
        $projects = Project::where('company_id', $companyId)->get();
        $agents = User::where('company_id', $companyId)->get();
        $teams = Team::where('company_id', $companyId)->get();

        return view('leads.edit', compact('lead', 'sources', 'statuses', 'projects', 'agents', 'teams'));
    }

    public function update(UpdateLeadRequest $request, Lead $lead)
    {
        $this->authorize('update', $lead);

        $data = $request->validated();
        $oldAssignedTo = $lead->assigned_to;
        $oldAssignedTeam = $lead->assigned_team_id;
        $oldStatus = $lead->status;

        $lead->update($data);

        // Track Reassignment if changed
        if (($request->has('assigned_to') && $request->assigned_to != $oldAssignedTo) ||
            ($request->has('assigned_team_id') && $request->assigned_team_id != $oldAssignedTeam)) {
            $this->leadService->assignLead($lead, $data['assigned_to'] ?? null, $data['assigned_team_id'] ?? null, auth()->user(), 'Updated via Lead Edit');
        }

        // Track Status Change Activity
        if ($request->has('status') && $request->status !== $oldStatus) {
            $this->leadService->logActivity(
                lead: $lead,
                type: 'Status Change',
                subject: "Status changed from {$oldStatus} to {$lead->status}",
                user: auth()->user()
            );
        }

        return redirect()->route('leads.show', $lead->id)->with('success', 'Lead updated successfully!');
    }

    public function updateStatus(Request $request, Lead $lead)
    {
        $this->authorize('update', $lead);

        $request->validate([
            'status' => 'required|string',
            'lost_reason' => 'nullable|string',
        ]);

        $oldStatus = $lead->status;
        $statusObj = LeadStatus::where('company_id', auth()->user()->company_id)->where('name', $request->status)->first();

        $lead->update([
            'status' => $request->status,
            'status_id' => $statusObj?->id,
            'notes' => $request->filled('lost_reason') ? trim($lead->notes . "\n[Lost Reason]: " . $request->lost_reason) : $lead->notes,
        ]);

        $this->leadService->logActivity(
            lead: $lead,
            type: 'Status Change',
            subject: "Status updated to {$lead->status}",
            summary: $request->lost_reason ? "Lost Reason: {$request->lost_reason}" : null,
            user: auth()->user()
        );

        return back()->with('success', "Lead status updated to {$lead->status}!");
    }

    public function addActivity(Request $request, Lead $lead)
    {
        $this->authorize('update', $lead);

        $request->validate([
            'activity_type' => 'required|string',
            'subject' => 'nullable|string|max:255',
            'summary' => 'required|string',
            'outcome' => 'nullable|string|max:255',
            'next_action' => 'nullable|string|max:255',
            'next_followup_at' => 'nullable|date',
        ]);

        $this->leadService->logActivity(
            lead: $lead,
            type: $request->activity_type,
            subject: $request->subject ?? ($request->activity_type . ' Logged'),
            summary: $request->summary,
            outcome: $request->outcome,
            nextAction: $request->next_action,
            user: auth()->user()
        );

        if ($request->filled('next_followup_at')) {
            \App\Models\LeadFollowup::create([
                'company_id' => $lead->company_id,
                'lead_id' => $lead->id,
                'user_id' => auth()->id(),
                'type' => $request->activity_type,
                'followup_at' => $request->next_followup_at,
                'notes' => $request->next_action ?? 'Scheduled follow-up from activity log',
                'status' => 'Pending',
            ]);
        }

        return back()->with('success', 'Activity logged successfully!');
    }

    public function destroy(Lead $lead)
    {
        $this->authorize('delete', $lead);

        $lead->delete();

        return redirect()->route('leads.index')->with('success', 'Lead deleted successfully!');
    }

    // Duplicate Check & Merge Interface
    public function checkDuplicatesApi(Request $request)
    {
        $mobile = $request->get('mobile');
        $email = $request->get('email');
        $excludeId = $request->get('exclude_id');

        if (empty($mobile) && empty($email)) {
            return response()->json(['duplicates' => []]);
        }

        $duplicates = $this->leadService->checkDuplicates(auth()->user()->company_id, $mobile ?? '', $email, $excludeId);

        return response()->json([
            'has_duplicates' => $duplicates->isNotEmpty(),
            'duplicates' => $duplicates->map(fn ($l) => [
                'id' => $l->id,
                'lead_number' => $l->lead_number,
                'full_name' => $l->full_name,
                'mobile' => $l->mobile,
                'email' => $l->email,
                'created_at' => $l->created_at->format('Y-m-d H:i'),
            ]),
        ]);
    }

    public function duplicatesView()
    {
        $companyId = auth()->user()->company_id;
        
        // Find potential duplicates grouped by mobile
        $duplicateMobiles = Lead::where('company_id', $companyId)
            ->whereNull('merged_into_lead_id')
            ->select('mobile')
            ->groupBy('mobile')
            ->havingRaw('COUNT(id) > 1')
            ->pluck('mobile');

        $duplicateGroups = [];
        foreach ($duplicateMobiles as $mob) {
            $duplicateGroups[] = Lead::where('company_id', $companyId)
                ->whereNull('merged_into_lead_id')
                ->where('mobile', $mob)
                ->get();
        }

        return view('leads.duplicates', compact('duplicateGroups'));
    }

    public function mergeLeads(Request $request)
    {
        $request->validate([
            'primary_lead_id' => 'required|exists:leads,id',
            'secondary_lead_ids' => 'required|array|min:1',
            'secondary_lead_ids.*' => 'exists:leads,id',
        ]);

        $primaryLead = Lead::findOrFail($request->primary_lead_id);
        $this->authorize('update', $primaryLead);

        $this->leadService->mergeLeads($primaryLead, $request->secondary_lead_ids, auth()->user(), $request->except(['_token', 'primary_lead_id', 'secondary_lead_ids']));

        return redirect()->route('leads.show', $primaryLead->id)->with('success', 'Leads merged successfully!');
    }

    // CSV Import / Export
    public function importForm()
    {
        return view('leads.import');
    }

    public function processImport(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $path = $request->file('csv_file')->getRealPath();
        $fileData = array_map('str_getcsv', file($path));

        if (empty($fileData) || count($fileData) < 2) {
            return back()->with('error', 'CSV file is empty or missing data rows.');
        }

        $header = array_map(fn ($h) => strtolower(trim(str_replace(' ', '_', $h))), $fileData[0]);
        $rows = [];

        for ($i = 1; $i < count($fileData); $i++) {
            if (count($fileData[$i]) === count($header)) {
                $rows[] = array_combine($header, $fileData[$i]);
            }
        }

        $result = $this->leadService->importLeads(auth()->user()->company, $rows, auth()->user());

        if (!empty($result['failed_rows'])) {
            session(['import_failed_rows' => $result['failed_rows']]);
        }

        return redirect()->route('leads.import.form')->with([
            'import_summary' => $result,
            'success' => "Import complete! Successfully imported {$result['success_count']} leads. Failed: {$result['failed_count']}",
        ]);
    }

    public function downloadFailedImportRows()
    {
        $failedRows = session('import_failed_rows', []);
        if (empty($failedRows)) {
            return back()->with('error', 'No failed rows to download.');
        }

        $filename = 'failed_leads_import_' . date('Y_m_d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($failedRows) {
            $output = fopen('php://output', 'w');
            if (!empty($failedRows[0])) {
                fputcsv($output, array_keys($failedRows[0]));
            }
            foreach ($failedRows as $row) {
                fputcsv($output, $row);
            }
            fclose($output);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function export(Request $request)
    {
        $user = auth()->user();
        $companyId = $user->company_id;

        $query = Lead::with(['project', 'sourceObj', 'statusObj', 'assignedTo'])
            ->where('company_id', $companyId)
            ->whereNull('merged_into_lead_id');

        if (!$user->isAdmin()) {
            $allowedUserIds = $user->getTeamMemberIds();
            $query->whereIn('assigned_to', $allowedUserIds);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $leads = $query->get();

        $filename = 'leads_export_' . date('Y_m_d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($leads) {
            $output = fopen('php://output', 'w');
            fputcsv($output, [
                'Lead Number', 'First Name', 'Last Name', 'Mobile', 'Alternate Mobile', 'Email',
                'City', 'Location', 'Source', 'Campaign', 'Project', 'Unit Type',
                'Min Budget', 'Max Budget', 'Priority', 'Status', 'Assigned Agent', 'Created At'
            ]);

            foreach ($leads as $l) {
                fputcsv($output, [
                    $l->lead_number,
                    $l->first_name,
                    $l->last_name,
                    $l->mobile,
                    $l->alternate_mobile,
                    $l->email,
                    $l->city,
                    $l->location,
                    $l->source,
                    $l->campaign,
                    $l->project?->name,
                    $l->unit_type,
                    $l->minimum_budget,
                    $l->maximum_budget,
                    $l->priority,
                    $l->status,
                    $l->assignedTo?->name,
                    $l->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($output);
        };

        return Response::stream($callback, 200, $headers);
    }
}
