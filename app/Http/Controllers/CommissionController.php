<?php

namespace App\Http\Controllers;

use App\Http\Requests\RecordCommissionPayoutRequest;
use App\Models\Commission;
use App\Services\Broker\CommissionEngineService;
use Illuminate\Http\Request;

class CommissionController extends Controller
{
    public function __construct(
        protected CommissionEngineService $commissionEngine
    ) {}

    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $query = Commission::with(['channelPartner', 'booking.customer', 'booking.unit.project', 'approvedBy', 'payouts'])
            ->where('company_id', $companyId);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('channel_partner_id')) {
            $query->where('channel_partner_id', $request->channel_partner_id);
        }

        $commissions = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('brokers.commissions.index', compact('commissions'));
    }

    public function approve(Request $request, Commission $commission)
    {
        if ($commission->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $request->validate([
            'approved_amount' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $this->commissionEngine->approveCommission(
            $commission,
            (float) $request->approved_amount,
            auth()->user(),
            $request->notes
        );

        return back()->with('success', "Commission #{$commission->id} approved for amount ₹" . number_format($request->approved_amount, 2));
    }

    public function payout(RecordCommissionPayoutRequest $request)
    {
        $companyId = auth()->user()->company_id;
        $commission = Commission::where('company_id', $companyId)->findOrFail($request->commission_id);

        $payout = $this->commissionEngine->recordPayout($commission, $request->validated(), auth()->user());

        return back()->with('success', "Payout #{$payout->payout_number} of ₹" . number_format($payout->amount, 2) . " recorded successfully!");
    }
}
