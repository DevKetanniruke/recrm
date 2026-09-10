<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Building;
use App\Models\ChannelPartner;
use App\Models\Commission;
use App\Models\CommissionPayout;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadFollowup;
use App\Models\Offer;
use App\Models\Payment;
use App\Models\PaymentSchedule;
use App\Models\Project;
use App\Models\SiteVisit;
use App\Models\Unit;
use App\Models\UnitType;
use App\Models\User;
use App\Models\Wing;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Compute executive CRM KPIs and chart payloads.
     */
    public function getExecutiveMetrics(int $companyId): array
    {
        $baseLeads = Lead::where('company_id', $companyId)->whereNull('merged_into_lead_id');

        // Executive KPI Card Metrics
        $totalProjects = Project::where('company_id', $companyId)->count();
        $totalUnits = Unit::where('company_id', $companyId)->count();
        $availableUnits = Unit::where('company_id', $companyId)->where('status', 'Available')->count();
        $bookedUnits = Unit::where('company_id', $companyId)->where('status', 'Booked')->count();
        $soldUnits = Unit::where('company_id', $companyId)->where('status', 'Sold')->count();

        $totalLeads = (clone $baseLeads)->count();
        $qualifiedLeads = (clone $baseLeads)->whereIn('status', ['Qualified', 'Site Visit Planned', 'Site Visit Completed', 'Negotiation', 'Offer Made', 'Booking', 'Won'])->count();

        $siteVisits = SiteVisit::where('company_id', $companyId)->count();
        
        $activeBookingsQuery = Booking::where('company_id', $companyId)->whereIn('status', ['Confirmed', 'Agreement Signed', 'Completed']);
        $bookings = (clone $activeBookingsQuery)->count();
        $bookingValue = (float) (clone $activeBookingsQuery)->sum('agreed_price');

        // Revenue Collections & Outstandings
        $currentPeriodCollection = (float) Payment::where('company_id', $companyId)->where('status', 'Verified')->sum('amount_paid');
        
        $schedulesQuery = PaymentSchedule::where('company_id', $companyId)->where('status', '!=', 'Paid');
        $outstandingPayments = (float) (clone $schedulesQuery)->sum('amount_due');
        $overduePayments = (float) (clone $schedulesQuery)->where('due_date', '<', now()->toDateString())->sum('amount_due');

        // 8-Stage Sales Funnel Progression & Conversion Calculations
        $funnelStages = [
            'Leads' => fn($q) => $q,
            'Contacted' => fn($q) => $q->whereIn('status', ['Contacted', 'Qualified', 'Site Visit Planned', 'Site Visit Completed', 'Negotiation', 'Offer Made', 'Booking', 'Won']),
            'Qualified' => fn($q) => $q->whereIn('status', ['Qualified', 'Site Visit Planned', 'Site Visit Completed', 'Negotiation', 'Offer Made', 'Booking', 'Won']),
            'Site Visit' => fn($q) => $q->where(function ($sub) {
                $sub->whereIn('status', ['Site Visit Planned', 'Site Visit Completed', 'Negotiation', 'Offer Made', 'Booking', 'Won'])
                    ->orWhereHas('siteVisits');
            }),
            'Negotiation' => fn($q) => $q->whereIn('status', ['Negotiation', 'Offer Made', 'Booking', 'Won']),
            'Offer' => fn($q) => $q->where(function ($sub) {
                $sub->whereIn('status', ['Offer Made', 'Booking', 'Won'])
                    ->orWhereHas('offers');
            }),
            'Booking' => fn($q) => $q->where(function ($sub) {
                $sub->whereIn('status', ['Booking', 'Won'])
                    ->orWhereHas('bookings');
            }),
            'Won' => fn($q) => $q->where('status', 'Won'),
        ];

        $salesFunnel = [];
        $previousCount = $totalLeads;

        foreach ($funnelStages as $stageName => $callback) {
            $stageQuery = $callback(clone $baseLeads);
            $stageCount = $stageQuery->count();

            $stageConversion = $previousCount > 0 ? round(($stageCount / $previousCount) * 100, 1) : 0;
            $overallConversion = $totalLeads > 0 ? round(($stageCount / $totalLeads) * 100, 1) : 0;

            $salesFunnel[$stageName] = [
                'stage' => $stageName,
                'count' => $stageCount,
                'stage_conversion' => $stageConversion,
                'overall_conversion' => $overallConversion,
            ];

            $previousCount = $stageCount > 0 ? $stageCount : $previousCount;
        }

        // Unit Inventory Status Breakdown for Chart
        $unitStats = [
            'Available' => $availableUnits,
            'Hold' => Unit::where('company_id', $companyId)->whereIn('status', ['Hold', 'On Hold'])->count(),
            'Booked' => $bookedUnits,
            'Sold' => $soldUnits,
            'Blocked' => Unit::where('company_id', $companyId)->where('status', 'Blocked')->count(),
        ];

        // Leads by Source for Pie/Doughnut Chart
        $leadsBySource = (clone $baseLeads)
            ->selectRaw("COALESCE(source, 'Direct / Walk-in') as source_name, count(*) as count")
            ->groupBy('source_name')
            ->pluck('count', 'source_name')
            ->toArray();

        // Monthly Collection Trends (Last 6 Months)
        $collectionTrends = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = now()->subMonths($i)->startOfMonth();
            $monthEnd = now()->subMonths($i)->endOfMonth();
            $monthLabel = $monthStart->format('M Y');

            $monthCollected = Payment::where('company_id', $companyId)
                ->where('status', 'Verified')
                ->whereBetween('payment_date', [$monthStart, $monthEnd])
                ->sum('amount_paid');

            $collectionTrends[$monthLabel] = (float) $monthCollected;
        }

        // Monthly Booking Trends (Last 6 Months)
        $salesTrends = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = now()->subMonths($i)->startOfMonth();
            $monthEnd = now()->subMonths($i)->endOfMonth();
            $monthLabel = $monthStart->format('M Y');

            $monthBookings = Booking::where('company_id', $companyId)
                ->whereIn('status', ['Confirmed', 'Agreement Signed', 'Completed'])
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->sum('agreed_price');

            $salesTrends[$monthLabel] = (float) $monthBookings;
        }

        return [
            'totalProjects' => $totalProjects,
            'totalUnits' => $totalUnits,
            'availableUnits' => $availableUnits,
            'bookedUnits' => $bookedUnits,
            'soldUnits' => $soldUnits,
            'totalLeads' => $totalLeads,
            'qualifiedLeads' => $qualifiedLeads,
            'siteVisits' => $siteVisits,
            'bookings' => $bookings,
            'bookingValue' => $bookingValue,
            'currentPeriodCollection' => $currentPeriodCollection,
            'outstandingPayments' => $outstandingPayments,
            'overduePayments' => $overduePayments,
            'salesFunnel' => $salesFunnel,
            'unitStats' => $unitStats,
            'leadsBySource' => $leadsBySource,
            'collectionTrends' => $collectionTrends,
            'salesTrends' => $salesTrends,
        ];
    }

    /**
     * A. Lead Reports Generator
     */
    public function getLeadReportData(int $companyId, array $filters): array
    {
        $query = Lead::where('company_id', $companyId)
            ->whereNull('merged_into_lead_id')
            ->with(['project', 'assignedTo']);

        if (!empty($filters['source'])) {
            $query->where('source', $filters['source']);
        }
        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }
        if (!empty($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['lost_reason'])) {
            $query->where('lost_reason', $filters['lost_reason']);
        }
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $totalFiltered = (clone $query)->count();
        $wonCount = (clone $query)->where('status', 'Won')->count();
        $lostCount = (clone $query)->where('status', 'Lost')->count();
        $conversionRate = $totalFiltered > 0 ? round(($wonCount / $totalFiltered) * 100, 1) : 0;

        $byStatus = (clone $query)
            ->selectRaw("COALESCE(status, 'New') as status_name, count(*) as count")
            ->groupBy('status_name')
            ->pluck('count', 'status_name')
            ->toArray();

        $bySource = (clone $query)
            ->selectRaw("COALESCE(source, 'Direct') as source_name, count(*) as count")
            ->groupBy('source_name')
            ->pluck('count', 'source_name')
            ->toArray();

        $leads = isset($filters['export']) && $filters['export']
            ? $query->orderBy('created_at', 'desc')->get()
            : $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return [
            'leads' => $leads,
            'totalCount' => $totalFiltered,
            'wonCount' => $wonCount,
            'lostCount' => $lostCount,
            'conversionRate' => $conversionRate,
            'byStatus' => $byStatus,
            'bySource' => $bySource,
        ];
    }

    /**
     * B. Site Visit Reports Generator
     */
    public function getSiteVisitReportData(int $companyId, array $filters): array
    {
        $query = SiteVisit::where('company_id', $companyId)
            ->with(['lead', 'project', 'assignedTo']);

        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }
        if (!empty($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['date_from'])) {
            $query->whereDate('visit_date', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('visit_date', '<=', $filters['date_to']);
        }

        $totalVisits = (clone $query)->count();
        $completedVisits = (clone $query)->where('status', 'Completed')->count();
        $scheduledVisits = (clone $query)->where('status', 'Scheduled')->count();
        $cancelledVisits = (clone $query)->where('status', 'Cancelled')->count();
        $noShowVisits = (clone $query)->where('status', 'No Show')->count();

        // Visit to Booking Conversion Rate
        $convertedBookings = (clone $query)->where('status', 'Completed')
            ->whereHas('lead', fn($q) => $q->whereIn('status', ['Booking', 'Won']))
            ->count();
        $visitConversionRate = $completedVisits > 0 ? round(($convertedBookings / $completedVisits) * 100, 1) : 0;

        $visits = isset($filters['export']) && $filters['export']
            ? $query->orderBy('visit_date', 'desc')->get()
            : $query->orderBy('visit_date', 'desc')->paginate(15)->withQueryString();

        return [
            'visits' => $visits,
            'totalVisits' => $totalVisits,
            'completedVisits' => $completedVisits,
            'scheduledVisits' => $scheduledVisits,
            'cancelledVisits' => $cancelledVisits,
            'noShowVisits' => $noShowVisits,
            'visitConversionRate' => $visitConversionRate,
        ];
    }

    /**
     * C. Sales & Booking Reports Generator
     */
    public function getSalesReportData(int $companyId, array $filters): array
    {
        $query = Booking::where('company_id', $companyId)
            ->with(['project', 'unit.building', 'unit.wing', 'unit.floor', 'unit.unitType', 'customer', 'salesAgent']);

        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }
        if (!empty($filters['assigned_to'])) {
            $query->where('sales_agent_id', $filters['assigned_to']);
        }
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
        if (!empty($filters['unit_type_id'])) {
            $query->whereHas('unit', fn($q) => $q->where('unit_type_id', $filters['unit_type_id']));
        }

        $totalBookings = (clone $query)->count();
        $totalSalesValue = (float) (clone $query)->sum('agreed_price');
        $totalDiscounts = (float) (clone $query)->sum('discount_amount');

        $bookings = isset($filters['export']) && $filters['export']
            ? $query->orderBy('created_at', 'desc')->get()
            : $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return [
            'bookings' => $bookings,
            'totalBookings' => $totalBookings,
            'totalSalesValue' => $totalSalesValue,
            'totalDiscounts' => $totalDiscounts,
        ];
    }

    /**
     * D. Inventory Reports Generator
     */
    public function getInventoryReportData(int $companyId, array $filters): array
    {
        $query = Unit::where('company_id', $companyId)
            ->with(['project', 'building', 'wing', 'floor', 'unitType']);

        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }
        if (!empty($filters['building_id'])) {
            $query->where('building_id', $filters['building_id']);
        }
        if (!empty($filters['wing_id'])) {
            $query->where('wing_id', $filters['wing_id']);
        }
        if (!empty($filters['unit_type_id'])) {
            $query->where('unit_type_id', $filters['unit_type_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $totalUnits = (clone $query)->count();
        $available = (clone $query)->where('status', 'Available')->count();
        $hold = (clone $query)->whereIn('status', ['Hold', 'On Hold'])->count();
        $booked = (clone $query)->where('status', 'Booked')->count();
        $sold = (clone $query)->where('status', 'Sold')->count();
        $blocked = (clone $query)->where('status', 'Blocked')->count();

        // Project-wise matrix aggregation
        $projectMatrix = Unit::where('company_id', $companyId)
            ->select('project_id', 'status', DB::raw('count(*) as total'))
            ->groupBy('project_id', 'status')
            ->get()
            ->groupBy('project_id');

        $units = isset($filters['export']) && $filters['export']
            ? $query->orderBy('unit_number', 'asc')->get()
            : $query->orderBy('unit_number', 'asc')->paginate(15)->withQueryString();

        return [
            'units' => $units,
            'totalUnits' => $totalUnits,
            'available' => $available,
            'hold' => $hold,
            'booked' => $booked,
            'sold' => $sold,
            'blocked' => $blocked,
            'projectMatrix' => $projectMatrix,
        ];
    }

    /**
     * E. Finance Reports Generator
     */
    public function getFinanceReportData(int $companyId, array $filters): array
    {
        $paymentsQuery = Payment::where('company_id', $companyId)
            ->with(['booking.customer', 'booking.project', 'receivedBy']);

        if (!empty($filters['project_id'])) {
            $paymentsQuery->whereHas('booking', fn($q) => $q->where('project_id', $filters['project_id']));
        }
        if (!empty($filters['payment_mode'])) {
            $paymentsQuery->where('payment_mode', $filters['payment_mode']);
        }
        if (!empty($filters['date_from'])) {
            $paymentsQuery->whereDate('payment_date', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $paymentsQuery->whereDate('payment_date', '<=', $filters['date_to']);
        }

        $totalAgreementValue = (float) Booking::where('company_id', $companyId)
            ->whereIn('status', ['Confirmed', 'Agreement Signed', 'Completed'])
            ->sum('agreed_price');

        $totalBilled = (float) PaymentSchedule::where('company_id', $companyId)->sum('amount_due');
        $totalCollected = (float) Payment::where('company_id', $companyId)->where('status', 'Verified')->sum('amount_paid');
        $outstanding = max(0, $totalBilled - $totalCollected);
        $overdue = (float) PaymentSchedule::where('company_id', $companyId)
            ->where('status', '!=', 'Paid')
            ->where('due_date', '<', now()->toDateString())
            ->sum('amount_due');

        $paymentModeAnalysis = Payment::where('company_id', $companyId)
            ->where('status', 'Verified')
            ->selectRaw('payment_mode, sum(amount_paid) as total_amount, count(*) as count')
            ->groupBy('payment_mode')
            ->get();

        $payments = isset($filters['export']) && $filters['export']
            ? $paymentsQuery->orderBy('payment_date', 'desc')->get()
            : $paymentsQuery->orderBy('payment_date', 'desc')->paginate(15)->withQueryString();

        return [
            'payments' => $payments,
            'totalAgreementValue' => $totalAgreementValue,
            'totalBilled' => $totalBilled,
            'totalCollected' => $totalCollected,
            'outstanding' => $outstanding,
            'overdue' => $overdue,
            'paymentModeAnalysis' => $paymentModeAnalysis,
        ];
    }

    /**
     * F. Channel Partner Reports Generator
     */
    public function getChannelPartnerReportData(int $companyId, array $filters): array
    {
        $query = ChannelPartner::where('company_id', $companyId);

        if (!empty($filters['partner_id'])) {
            $query->where('id', $filters['partner_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $partnersList = $query->get()->map(function ($partner) use ($filters) {
            $leadsQuery = Lead::where('company_id', $partner->company_id)->where('channel_partner_id', $partner->id);
            $visitsQuery = SiteVisit::where('company_id', $partner->company_id)->whereHas('lead', fn($q) => $q->where('channel_partner_id', $partner->id));
            $bookingsQuery = Booking::where('company_id', $partner->company_id)->whereHas('lead', fn($q) => $q->where('channel_partner_id', $partner->id));

            if (!empty($filters['date_from'])) {
                $leadsQuery->whereDate('created_at', '>=', $filters['date_from']);
                $visitsQuery->whereDate('visit_date', '>=', $filters['date_from']);
                $bookingsQuery->whereDate('created_at', '>=', $filters['date_from']);
            }
            if (!empty($filters['date_to'])) {
                $leadsQuery->whereDate('created_at', '<=', $filters['date_to']);
                $visitsQuery->whereDate('visit_date', '<=', $filters['date_to']);
                $bookingsQuery->whereDate('created_at', '<=', $filters['date_to']);
            }

            $leadsCount = $leadsQuery->count();
            $visitsCount = $visitsQuery->count();
            $bookingsCount = $bookingsQuery->count();
            $salesValue = (float) $bookingsQuery->sum('agreed_price');

            $totalCommissions = (float) Commission::where('company_id', $partner->company_id)->where('channel_partner_id', $partner->id)->sum('commission_amount');
            $paidCommissions = (float) CommissionPayout::where('company_id', $partner->company_id)->where('channel_partner_id', $partner->id)->where('status', 'Completed')->sum('amount_paid');
            $outstandingCommissions = max(0, $totalCommissions - $paidCommissions);

            return [
                'partner' => $partner,
                'leadsCount' => $leadsCount,
                'visitsCount' => $visitsCount,
                'bookingsCount' => $bookingsCount,
                'salesValue' => $salesValue,
                'totalCommissions' => $totalCommissions,
                'paidCommissions' => $paidCommissions,
                'outstandingCommissions' => $outstandingCommissions,
            ];
        });

        return [
            'partners' => $partnersList,
        ];
    }

    /**
     * G. Executive Performance Reports Generator
     */
    public function getExecutivePerformanceData(int $companyId, array $filters): array
    {
        $query = User::where('company_id', $companyId);

        if (!empty($filters['assigned_to'])) {
            $query->where('id', $filters['assigned_to']);
        }

        $executives = $query->get()->map(function ($exec) use ($companyId, $filters) {
            $leadsQuery = Lead::where('company_id', $companyId)->where('assigned_to', $exec->id);
            $visitsQuery = SiteVisit::where('company_id', $companyId)->where('assigned_to', $exec->id);
            $offersQuery = Offer::where('company_id', $companyId)->where('sales_agent_id', $exec->id);
            $bookingsQuery = Booking::where('company_id', $companyId)->where('sales_agent_id', $exec->id);

            if (!empty($filters['date_from'])) {
                $leadsQuery->whereDate('created_at', '>=', $filters['date_from']);
                $visitsQuery->whereDate('visit_date', '>=', $filters['date_from']);
                $offersQuery->whereDate('created_at', '>=', $filters['date_from']);
                $bookingsQuery->whereDate('created_at', '>=', $filters['date_from']);
            }
            if (!empty($filters['date_to'])) {
                $leadsQuery->whereDate('created_at', '<=', $filters['date_to']);
                $visitsQuery->whereDate('visit_date', '<=', $filters['date_to']);
                $offersQuery->whereDate('created_at', '<=', $filters['date_to']);
                $bookingsQuery->whereDate('created_at', '<=', $filters['date_to']);
            }

            $leadsAssigned = $leadsQuery->count();
            $activitiesCount = LeadActivity::where('company_id', $companyId)->where('user_id', $exec->id)->count();
            $followupsCount = LeadFollowup::where('company_id', $companyId)->where('assigned_to', $exec->id)->count();
            $visitsCount = $visitsQuery->count();
            $offersCount = $offersQuery->count();
            $bookingsCount = $bookingsQuery->count();
            $salesValue = (float) $bookingsQuery->sum('agreed_price');
            $conversionRate = $leadsAssigned > 0 ? round(($bookingsCount / $leadsAssigned) * 100, 1) : 0;

            return [
                'user' => $exec,
                'leadsAssigned' => $leadsAssigned,
                'activitiesCount' => $activitiesCount,
                'followupsCount' => $followupsCount,
                'visitsCount' => $visitsCount,
                'offersCount' => $offersCount,
                'bookingsCount' => $bookingsCount,
                'salesValue' => $salesValue,
                'conversionRate' => $conversionRate,
            ];
        });

        return [
            'executives' => $executives,
        ];
    }
}
