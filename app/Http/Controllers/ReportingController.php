<?php

namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\ChannelPartner;
use App\Models\Project;
use App\Models\UnitType;
use App\Models\User;
use App\Models\Wing;
use App\Services\AnalyticsService;
use App\Services\ReportExportService;
use Illuminate\Http\Request;

class ReportingController extends Controller
{
    protected AnalyticsService $analyticsService;
    protected ReportExportService $exportService;

    public function __construct(AnalyticsService $analyticsService, ReportExportService $exportService)
    {
        $this->analyticsService = $analyticsService;
        $this->exportService = $exportService;
    }

    /**
     * Display central reporting portal and active filter tab.
     */
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $tab = $request->get('tab', 'leads');
        $filters = $request->all();

        // Common Filter Option Collections
        $projects = Project::where('company_id', $companyId)->get();
        $executives = User::where('company_id', $companyId)->get();
        $unitTypes = UnitType::where('company_id', $companyId)->get();
        $channelPartners = ChannelPartner::where('company_id', $companyId)->get();
        $buildings = Building::whereHas('project', fn($q) => $q->where('company_id', $companyId))->get();
        $wings = Wing::whereHas('building.project', fn($q) => $q->where('company_id', $companyId))->get();

        $reportData = [];

        switch ($tab) {
            case 'site_visits':
                $reportData = $this->analyticsService->getSiteVisitReportData($companyId, $filters);
                break;
            case 'sales':
                $reportData = $this->analyticsService->getSalesReportData($companyId, $filters);
                break;
            case 'inventory':
                $reportData = $this->analyticsService->getInventoryReportData($companyId, $filters);
                break;
            case 'finance':
                $reportData = $this->analyticsService->getFinanceReportData($companyId, $filters);
                break;
            case 'channel_partners':
                $reportData = $this->analyticsService->getChannelPartnerReportData($companyId, $filters);
                break;
            case 'executives':
                $reportData = $this->analyticsService->getExecutivePerformanceData($companyId, $filters);
                break;
            case 'leads':
            default:
                $tab = 'leads';
                $reportData = $this->analyticsService->getLeadReportData($companyId, $filters);
                break;
        }

        return view('reports.index', compact(
            'tab',
            'filters',
            'reportData',
            'projects',
            'executives',
            'unitTypes',
            'channelPartners',
            'buildings',
            'wings'
        ));
    }

    /**
     * Handle CSV exports for report datasets.
     */
    public function export(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $type = $request->get('type', 'leads');
        $filters = array_merge($request->all(), ['export' => true]);

        switch ($type) {
            case 'site_visits':
                $data = $this->analyticsService->getSiteVisitReportData($companyId, $filters);
                $headers = ['Visit ID', 'Lead Name', 'Lead Mobile', 'Project', 'Scheduled Date', 'Executive', 'Status', 'Logistics Requested'];
                $rows = [];
                foreach ($data['visits'] as $v) {
                    $rows[] = [
                        $v->id,
                        $v->lead?->name ?? 'N/A',
                        $v->lead?->mobile ?? 'N/A',
                        $v->project?->name ?? 'N/A',
                        $v->visit_date,
                        $v->assignedTo?->name ?? 'N/A',
                        $v->status,
                        $v->cab_required ? 'Yes' : 'No',
                    ];
                }
                return $this->exportService->exportCsv('site_visit_report_' . date('Ymd_His') . '.csv', $headers, $rows);

            case 'sales':
                $data = $this->analyticsService->getSalesReportData($companyId, $filters);
                $headers = ['Booking Code', 'Customer', 'Project', 'Unit', 'Agreement Value (₹)', 'Discount (₹)', 'Booking Date', 'Sales Executive', 'Status'];
                $rows = [];
                foreach ($data['bookings'] as $b) {
                    $rows[] = [
                        $b->booking_code,
                        $b->customer?->full_name ?? 'N/A',
                        $b->project?->name ?? 'N/A',
                        $b->unit?->unit_number ?? 'N/A',
                        number_format($b->agreement_value, 2, '.', ''),
                        number_format($b->discount_amount, 2, '.', ''),
                        $b->created_at->format('Y-m-d'),
                        $b->salesAgent?->name ?? 'N/A',
                        $b->status,
                    ];
                }
                return $this->exportService->exportCsv('sales_report_' . date('Ymd_His') . '.csv', $headers, $rows);

            case 'inventory':
                $data = $this->analyticsService->getInventoryReportData($companyId, $filters);
                $headers = ['Unit Number', 'Project', 'Building', 'Wing', 'Floor', 'Unit Type', 'Base Price (₹)', 'Total Price (₹)', 'Status'];
                $rows = [];
                foreach ($data['units'] as $u) {
                    $rows[] = [
                        $u->unit_number,
                        $u->project?->name ?? 'N/A',
                        $u->building?->name ?? 'N/A',
                        $u->wing?->name ?? 'N/A',
                        $u->floor?->floor_number ?? 'N/A',
                        $u->unitType?->name ?? 'N/A',
                        number_format($u->base_price, 2, '.', ''),
                        number_format($u->total_price, 2, '.', ''),
                        $u->status,
                    ];
                }
                return $this->exportService->exportCsv('inventory_report_' . date('Ymd_His') . '.csv', $headers, $rows);

            case 'finance':
                $data = $this->analyticsService->getFinanceReportData($companyId, $filters);
                $headers = ['Receipt No', 'Booking Code', 'Customer', 'Payment Mode', 'Amount Paid (₹)', 'Payment Date', 'Transaction Ref', 'Status'];
                $rows = [];
                foreach ($data['payments'] as $p) {
                    $rows[] = [
                        $p->receipt_number,
                        $p->booking?->booking_code ?? 'N/A',
                        $p->booking?->customer?->full_name ?? 'N/A',
                        $p->payment_mode,
                        number_format($p->amount_paid, 2, '.', ''),
                        $p->payment_date,
                        $p->transaction_reference ?? 'N/A',
                        $p->status,
                    ];
                }
                return $this->exportService->exportCsv('finance_report_' . date('Ymd_His') . '.csv', $headers, $rows);

            case 'channel_partners':
                $data = $this->analyticsService->getChannelPartnerReportData($companyId, $filters);
                $headers = ['Partner Code', 'Company Name', 'Contact Person', 'Mobile', 'Leads Attributed', 'Site Visits', 'Bookings', 'Sales Value (₹)', 'Total Commissions (₹)', 'Paid (₹)', 'Outstanding (₹)'];
                $rows = [];
                foreach ($data['partners'] as $item) {
                    $p = $item['partner'];
                    $rows[] = [
                        $p->partner_code,
                        $p->company_name,
                        $p->contact_person,
                        $p->mobile,
                        $item['leadsCount'],
                        $item['visitsCount'],
                        $item['bookingsCount'],
                        number_format($item['salesValue'], 2, '.', ''),
                        number_format($item['totalCommissions'], 2, '.', ''),
                        number_format($item['paidCommissions'], 2, '.', ''),
                        number_format($item['outstandingCommissions'], 2, '.', ''),
                    ];
                }
                return $this->exportService->exportCsv('channel_partners_report_' . date('Ymd_His') . '.csv', $headers, $rows);

            case 'executives':
                $data = $this->analyticsService->getExecutivePerformanceData($companyId, $filters);
                $headers = ['Executive Name', 'Email', 'Role', 'Leads Assigned', 'Activities', 'Follow-ups', 'Site Visits', 'Offers Made', 'Bookings', 'Sales Value (₹)', 'Conversion Rate (%)'];
                $rows = [];
                foreach ($data['executives'] as $item) {
                    $u = $item['user'];
                    $rows[] = [
                        $u->name,
                        $u->email,
                        $u->role,
                        $item['leadsAssigned'],
                        $item['activitiesCount'],
                        $item['followupsCount'],
                        $item['visitsCount'],
                        $item['offersCount'],
                        $item['bookingsCount'],
                        number_format($item['salesValue'], 2, '.', ''),
                        $item['conversionRate'] . '%',
                    ];
                }
                return $this->exportService->exportCsv('executive_performance_report_' . date('Ymd_His') . '.csv', $headers, $rows);

            case 'leads':
            default:
                $data = $this->analyticsService->getLeadReportData($companyId, $filters);
                $headers = ['Lead Number', 'Customer Name', 'Mobile', 'Email', 'Source', 'Project', 'Assigned Executive', 'Status', 'Priority', 'Lost Reason', 'Created Date'];
                $rows = [];
                foreach ($data['leads'] as $l) {
                    $rows[] = [
                        $l->lead_number,
                        $l->name,
                        $l->mobile,
                        $l->email,
                        $l->source,
                        $l->project?->name ?? 'N/A',
                        $l->assignedTo?->name ?? 'N/A',
                        $l->status,
                        $l->priority,
                        $l->lost_reason ?? 'N/A',
                        $l->created_at->format('Y-m-d'),
                    ];
                }
                return $this->exportService->exportCsv('lead_report_' . date('Ymd_His') . '.csv', $headers, $rows);
        }
    }
}
