<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadAssignmentHistory;
use App\Models\LeadFollowup;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LeadService
{
    /**
     * Check for duplicate leads in the same company by mobile or email.
     */
    public function checkDuplicates(int $companyId, string $mobile, ?string $email = null, ?int $excludeLeadId = null): Collection
    {
        $cleanMobile = preg_replace('/[^0-9]/', '', $mobile);

        return Lead::where('company_id', $companyId)
            ->whereNull('merged_into_lead_id')
            ->when($excludeLeadId, fn ($q) => $q->where('id', '!=', $excludeLeadId))
            ->where(function ($query) use ($mobile, $cleanMobile, $email) {
                if (!empty($cleanMobile)) {
                    $query->where('mobile', $mobile)
                        ->orWhereRaw("REPLACE(REPLACE(REPLACE(mobile, ' ', ''), '-', ''), '+', '') LIKE ?", ["%{$cleanMobile}%"]);
                }

                if (!empty($email)) {
                    $query->orWhere('email', strtolower(trim($email)));
                }
            })
            ->get();
    }

    /**
     * Merge secondary leads into a primary lead.
     */
    public function mergeLeads(Lead $primaryLead, array $secondaryLeadIds, User $actor, array $fieldOverrides = []): Lead
    {
        return DB::transaction(function () use ($primaryLead, $secondaryLeadIds, $actor, $fieldOverrides) {
            $secondaryLeads = Lead::where('company_id', $primaryLead->company_id)
                ->whereIn('id', $secondaryLeadIds)
                ->where('id', '!=', $primaryLead->id)
                ->get();

            // Apply selected field overrides
            if (!empty($fieldOverrides)) {
                $primaryLead->update($fieldOverrides);
            }

            foreach ($secondaryLeads as $secondary) {
                // Re-associate activities, followups, site visits
                LeadActivity::where('lead_id', $secondary->id)->update(['lead_id' => $primaryLead->id]);
                LeadFollowup::where('lead_id', $secondary->id)->update(['lead_id' => $primaryLead->id]);
                DB::table('site_visits')->where('lead_id', $secondary->id)->update(['lead_id' => $primaryLead->id]);
                LeadAssignmentHistory::where('lead_id', $secondary->id)->update(['lead_id' => $primaryLead->id]);

                // Append notes if any
                if (!empty($secondary->notes)) {
                    $primaryLead->notes = trim($primaryLead->notes . "\n[Merged Notes from #" . $secondary->lead_number . "]: " . $secondary->notes);
                }

                // Mark secondary lead as merged and soft delete
                $secondary->merged_into_lead_id = $primaryLead->id;
                $secondary->save();
                $secondary->delete();
            }

            $primaryLead->save();

            // Log activity on primary lead
            $this->logActivity(
                lead: $primaryLead,
                type: 'Note',
                subject: 'Leads Merged',
                summary: "Merged with secondary lead(s): " . implode(', ', $secondaryLeads->pluck('lead_number')->toArray()),
                user: $actor
            );

            return $primaryLead;
        });
    }

    /**
     * Assign or reassign lead to user or team with history log.
     */
    public function assignLead(Lead $lead, ?int $userId, ?int $teamId, User $actor, ?string $notes = null): Lead
    {
        return DB::transaction(function () use ($lead, $userId, $teamId, $actor, $notes) {
            $prevUser = $lead->assigned_to;
            $prevTeam = $lead->assigned_team_id;
            $hasHistory = LeadAssignmentHistory::where('lead_id', $lead->id)->exists();

            if (!$hasHistory || $prevUser !== $userId || $prevTeam !== $teamId) {
                LeadAssignmentHistory::create([
                    'company_id' => $lead->company_id,
                    'lead_id' => $lead->id,
                    'assigned_by' => $actor->id,
                    'assigned_to_user_id' => $userId,
                    'assigned_to_team_id' => $teamId,
                    'previous_user_id' => $hasHistory ? $prevUser : null,
                    'previous_team_id' => $hasHistory ? $prevTeam : null,
                    'notes' => $notes,
                ]);

                $lead->update([
                    'assigned_to' => $userId,
                    'assigned_team_id' => $teamId,
                ]);

                $assignedUserName = $userId ? User::find($userId)?->name : 'Unassigned';
                $this->logActivity(
                    lead: $lead,
                    type: 'Assignment',
                    subject: 'Lead Assigned',
                    summary: "Assigned to {$assignedUserName}" . ($notes ? " ({$notes})" : ""),
                    user: $actor
                );
            }

            return $lead;
        });
    }

    /**
     * Log activity on lead.
     */
    public function logActivity(
        Lead $lead,
        string $type,
        string $subject,
        ?string $summary = null,
        ?string $outcome = null,
        ?string $nextAction = null,
        ?User $user = null
    ): LeadActivity {
        return LeadActivity::create([
            'company_id' => $lead->company_id,
            'lead_id' => $lead->id,
            'user_id' => $user?->id ?? auth()->id(),
            'activity_type' => $type,
            'subject' => $subject,
            'summary' => $summary,
            'outcome' => $outcome,
            'next_action' => $nextAction,
            'completed_at' => now(),
            'status' => 'Completed',
        ]);
    }

    /**
     * Import leads array with validation and error tracking.
     */
    public function importLeads(Company $company, array $rows, User $actor): array
    {
        $importedCount = 0;
        $failedRows = [];

        // Preload sources and statuses for lookup
        $sourcesMap = LeadSource::where('company_id', $company->id)->pluck('id', 'name')->toArray();
        $statusesMap = LeadStatus::where('company_id', $company->id)->pluck('id', 'name')->toArray();

        foreach ($rows as $index => $row) {
            $rowNum = $index + 1;
            
            $rules = [
                'first_name' => 'required|string|max:100',
                'mobile' => 'required|string|max:20',
                'email' => 'nullable|email|max:150',
                'priority' => 'nullable|in:Low,Medium,High,Hot',
            ];

            $validator = Validator::make($row, $rules);

            if ($validator->fails()) {
                $row['_error'] = implode('; ', $validator->errors()->all());
                $failedRows[] = $row;
                continue;
            }

            // Source lookup or default
            $sourceName = $row['source'] ?? 'Website';
            $sourceId = $sourcesMap[$sourceName] ?? null;

            // Status lookup or default
            $statusName = $row['status'] ?? 'New';
            $statusId = $statusesMap[$statusName] ?? null;

            DB::transaction(function () use ($company, $row, $sourceName, $sourceId, $statusName, $statusId, $actor, &$importedCount) {
                $lead = Lead::create([
                    'company_id' => $company->id,
                    'first_name' => $row['first_name'],
                    'last_name' => $row['last_name'] ?? null,
                    'mobile' => $row['mobile'],
                    'alternate_mobile' => $row['alternate_mobile'] ?? null,
                    'email' => $row['email'] ?? null,
                    'city' => $row['city'] ?? null,
                    'location' => $row['location'] ?? null,
                    'source' => $sourceName,
                    'source_id' => $sourceId,
                    'campaign' => $row['campaign'] ?? null,
                    'unit_type' => $row['unit_type'] ?? null,
                    'minimum_budget' => isset($row['minimum_budget']) && is_numeric($row['minimum_budget']) ? $row['minimum_budget'] : null,
                    'maximum_budget' => isset($row['maximum_budget']) && is_numeric($row['maximum_budget']) ? $row['maximum_budget'] : null,
                    'priority' => $row['priority'] ?? 'Medium',
                    'status' => $statusName,
                    'status_id' => $statusId,
                    'notes' => $row['notes'] ?? 'Imported via CSV',
                    'assigned_to' => $actor->id,
                ]);

                $this->logActivity(
                    lead: $lead,
                    type: 'Note',
                    subject: 'Lead Created via Import',
                    summary: 'Bulk CSV import',
                    user: $actor
                );

                $importedCount++;
            });
        }

        return [
            'success_count' => $importedCount,
            'failed_count' => count($failedRows),
            'failed_rows' => $failedRows,
        ];
    }
}
