<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateDemandNoticeRequest;
use App\Models\Booking;
use App\Models\PaymentDemandNotice;
use App\Models\PaymentSchedule;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class PaymentDemandController extends Controller
{
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $query = PaymentDemandNotice::with(['booking.unit.project', 'customer', 'paymentSchedule'])
            ->where('company_id', $companyId);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $demands = $query->orderBy('created_at', 'desc')->paginate(15);
        $bookings = Booking::with('customer', 'paymentSchedules')->where('company_id', $companyId)->get();

        return view('payments.demands.index', compact('demands', 'bookings'));
    }

    public function store(CreateDemandNoticeRequest $request)
    {
        $companyId = auth()->user()->company_id;
        $booking = Booking::where('company_id', $companyId)->findOrFail($request->booking_id);

        $demand = PaymentDemandNotice::create([
            'company_id' => $companyId,
            'booking_id' => $booking->id,
            'customer_id' => $request->customer_id ?? $booking->customer_id,
            'payment_schedule_id' => $request->payment_schedule_id,
            'demand_date' => $request->demand_date,
            'due_date' => $request->due_date,
            'demand_amount' => $request->demand_amount,
            'penalty_amount' => $request->penalty_amount ?? 0.00,
            'status' => 'Sent',
            'notes' => $request->notes,
        ]);

        return back()->with('success', "Payment Demand Notice #{$demand->demand_number} created and sent successfully!");
    }

    public function downloadPdf(PaymentDemandNotice $demand)
    {
        if ($demand->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $demand->load(['booking.customer', 'booking.unit.project', 'paymentSchedule', 'customer']);
        $company = auth()->user()->company;
        $settings = SystemSetting::where('company_id', auth()->user()->company_id)->pluck('setting_value', 'setting_key');

        return view('payments.demands.pdf', compact('demand', 'company', 'settings'));
    }
}
