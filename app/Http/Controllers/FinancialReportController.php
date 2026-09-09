<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\PaymentSchedule;
use App\Models\Project;
use App\Services\FinancialService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinancialReportController extends Controller
{
    protected FinancialService $financialService;

    public function __construct(FinancialService $financialService)
    {
        $this->financialService = $financialService;
    }

    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;

        // 1. KPI Counters
        $todayCollections = (float) Payment::where('company_id', $companyId)
            ->where('is_reversed', false)
            ->whereDate('payment_date', now()->toDateString())
            ->sum('amount_paid');

        $monthlyCollections = (float) Payment::where('company_id', $companyId)
            ->where('is_reversed', false)
            ->whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('amount_paid');

        $totalCollections = (float) Payment::where('company_id', $companyId)
            ->where('is_reversed', false)
            ->sum('amount_paid');

        $overallOutstanding = (float) PaymentSchedule::where('company_id', $companyId)
            ->sum('outstanding_amount');

        $overdueOutstanding = (float) PaymentSchedule::where('company_id', $companyId)
            ->where('due_date', '<', now())
            ->whereIn('status', ['Overdue', 'Pending', 'Partially Paid'])
            ->sum('outstanding_amount');

        // 2. Project-Wise Collection & Outstanding Breakdown
        $projectsReport = Project::where('company_id', $companyId)
            ->with(['units.bookings.paymentSchedules', 'units.bookings.payments'])
            ->get()
            ->map(function ($project) {
                $totalAgreed = 0.00;
                $totalCollected = 0.00;
                $totalOutstanding = 0.00;

                foreach ($project->units as $unit) {
                    foreach ($unit->bookings as $booking) {
                        if ($booking->status !== 'Cancelled') {
                            $totalAgreed += $booking->total_amount;
                            $totalCollected += $booking->totalPaid();
                            $totalOutstanding += $booking->balanceDue();
                        }
                    }
                }

                return [
                    'project_name' => $project->name,
                    'project_code' => $project->code,
                    'total_units' => $project->units->count(),
                    'total_agreed' => $totalAgreed,
                    'total_collected' => $totalCollected,
                    'total_outstanding' => $totalOutstanding,
                ];
            });

        // 3. Payment Mode Distribution
        $modeDistribution = Payment::where('company_id', $companyId)
            ->where('is_reversed', false)
            ->select('payment_mode', DB::raw('SUM(amount_paid) as total_amount'), DB::raw('COUNT(*) as total_count'))
            ->groupBy('payment_mode')
            ->get();

        // 4. Booking-Wise Outstanding Top 10
        $bookingLedger = Booking::with(['customer', 'unit.project'])
            ->where('company_id', $companyId)
            ->where('status', '!=', 'Cancelled')
            ->get()
            ->map(function ($booking) {
                return [
                    'booking_number' => $booking->booking_number,
                    'customer_name' => $booking->customer?->full_name,
                    'unit_number' => $booking->unit?->unit_number,
                    'project_name' => $booking->unit?->project?->name,
                    'total_amount' => $booking->total_amount,
                    'paid_amount' => $booking->totalPaid(),
                    'balance_due' => $booking->balanceDue(),
                ];
            })
            ->sortByDesc('balance_due')
            ->take(10);

        return view('payments.reports.index', compact(
            'todayCollections',
            'monthlyCollections',
            'totalCollections',
            'overallOutstanding',
            'overdueOutstanding',
            'projectsReport',
            'modeDistribution',
            'bookingLedger'
        ));
    }
}
