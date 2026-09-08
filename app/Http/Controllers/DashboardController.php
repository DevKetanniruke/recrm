<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Project;
use App\Models\SiteVisit;
use App\Services\AnalyticsService;

class DashboardController extends Controller
{
    protected AnalyticsService $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function index()
    {
        $companyId = auth()->user()->company_id;
        $metrics = $this->analyticsService->getExecutiveMetrics($companyId);

        $recentLeads = Lead::with(['project', 'assignedTo'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $upcomingSiteVisits = SiteVisit::with(['lead', 'project', 'assignedTo'])
            ->where('visit_date', '>=', now())
            ->where('status', 'Scheduled')
            ->orderBy('visit_date', 'asc')
            ->take(5)
            ->get();

        $recentBookings = Booking::with(['unit', 'customer', 'salesAgent'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $recentPayments = Payment::with(['booking', 'receivedBy'])
            ->orderBy('payment_date', 'desc')
            ->take(5)
            ->get();

        return view('dashboard', compact('metrics', 'recentLeads', 'upcomingSiteVisits', 'recentBookings', 'recentPayments'));
    }
}
