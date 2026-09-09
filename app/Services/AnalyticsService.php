<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Models\Offer;
use App\Models\Payment;
use App\Models\Project;
use App\Models\SiteVisit;
use App\Models\Unit;

class AnalyticsService
{
    /**
     * Compute executive CRM KPIs and chart payloads.
     */
    public function getExecutiveMetrics(int $companyId): array
    {
        $baseLeads = Lead::where('company_id', $companyId)->whereNull('merged_into_lead_id');

        // Lead Counter KPIs
        $totalLeads = (clone $baseLeads)->count();
        $newLeads = (clone $baseLeads)->whereIn('status', ['New', 'Contacted'])->count();
        $hotLeads = (clone $baseLeads)->whereIn('priority', ['Hot', 'High'])->count();
        $createdTodayLeads = (clone $baseLeads)->whereDate('created_at', today())->count();
        $qualifiedLeads = (clone $baseLeads)->whereIn('status', ['Qualified', 'Site Visit Planned', 'Site Visit Completed', 'Negotiation'])->count();

        // Follow-up KPIs
        $followupsToday = LeadFollowup::where('company_id', $companyId)->whereDate('followup_at', today())->where('status', 'Pending')->count();
        $overdueFollowups = LeadFollowup::where('company_id', $companyId)->where('followup_at', '<', now())->where('status', 'Pending')->count();

        // Site Visit KPIs
        $siteVisitsScheduled = SiteVisit::where('company_id', $companyId)->where('status', 'Scheduled')->count();
        $siteVisitsCompleted = SiteVisit::where('company_id', $companyId)->where('status', 'Completed')->count();

        // V0.4 Offer & Negotiation KPIs
        $activeOffersCount = Offer::where('company_id', $companyId)->whereIn('status', ['Draft', 'Pending Manager Approval', 'Pending Admin Approval', 'Approved', 'Countered'])->count();
        $pendingOffersCount = Offer::where('company_id', $companyId)->whereIn('status', ['Pending Manager Approval', 'Pending Admin Approval'])->count();
        $approvedOffersCount = Offer::where('company_id', $companyId)->where('status', 'Approved')->count();

        // Won & Lost Deals
        $wonDeals = (clone $baseLeads)->where('status', 'Won')->count();
        $lostDeals = (clone $baseLeads)->where('status', 'Lost')->count();

        // Inventory & Revenue KPIs
        $totalProjects = Project::where('company_id', $companyId)->count();
        $totalBookings = Booking::where('company_id', $companyId)->whereIn('status', ['Confirmed', 'Agreement Signed', 'Completed'])->count();
        $totalRevenueCollected = Payment::where('company_id', $companyId)->where('status', 'Verified')->sum('amount_paid');
        $totalInventoryValue = Unit::where('company_id', $companyId)->sum('total_price');

        // Unit Inventory Breakdown
        $unitStats = [
            'Available' => Unit::where('company_id', $companyId)->where('status', 'Available')->count(),
            'On Hold' => Unit::where('company_id', $companyId)->whereIn('status', ['Hold', 'On Hold'])->count(),
            'Booked' => Unit::where('company_id', $companyId)->where('status', 'Booked')->count(),
            'Sold' => Unit::where('company_id', $companyId)->where('status', 'Sold')->count(),
            'Blocked' => Unit::where('company_id', $companyId)->where('status', 'Blocked')->count(),
        ];

        // Leads by Source
        $leadsBySource = (clone $baseLeads)
            ->selectRaw("COALESCE(source, 'Website') as source_name, count(*) as count")
            ->groupBy('source_name')
            ->pluck('count', 'source_name')
            ->toArray();

        // Leads by Status
        $leadsByStatus = (clone $baseLeads)
            ->selectRaw("COALESCE(status, 'New') as status_name, count(*) as count")
            ->groupBy('status_name')
            ->pluck('count', 'status_name')
            ->toArray();

        // Leads by Project
        $leadsByProjectRaw = (clone $baseLeads)
            ->with('project')
            ->selectRaw("project_id, count(*) as count")
            ->groupBy('project_id')
            ->get();

        $leadsByProject = [];
        foreach ($leadsByProjectRaw as $item) {
            $pName = $item->project?->name ?? 'Unassigned';
            $leadsByProject[$pName] = $item->count;
        }

        // Conversion Funnel
        $funnelStages = ['New', 'Contacted', 'Qualified', 'Site Visit Planned', 'Site Visit Completed', 'Negotiation', 'Won', 'Lost'];
        $leadFunnel = [];
        foreach ($funnelStages as $stage) {
            $leadFunnel[$stage] = (clone $baseLeads)->where('status', $stage)->count();
        }

        return [
            'totalProjects' => $totalProjects,
            'totalLeads' => $totalLeads,
            'newLeads' => $newLeads,
            'hotLeads' => $hotLeads,
            'createdTodayLeads' => $createdTodayLeads,
            'qualifiedLeads' => $qualifiedLeads,
            'followupsToday' => $followupsToday,
            'overdueFollowups' => $overdueFollowups,
            'siteVisitsScheduled' => $siteVisitsScheduled,
            'siteVisitsCompleted' => $siteVisitsCompleted,
            'activeOffersCount' => $activeOffersCount,
            'pendingOffersCount' => $pendingOffersCount,
            'approvedOffersCount' => $approvedOffersCount,
            'wonDeals' => $wonDeals,
            'lostDeals' => $lostDeals,
            'totalBookings' => $totalBookings,
            'totalRevenueCollected' => (float) $totalRevenueCollected,
            'totalInventoryValue' => (float) $totalInventoryValue,
            'unitStats' => $unitStats,
            'leadsBySource' => $leadsBySource,
            'leadsByStatus' => $leadsByStatus,
            'leadsByProject' => $leadsByProject,
            'leadFunnel' => $leadFunnel,
        ];
    }
}
