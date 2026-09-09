<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentPlanRequest;
use App\Models\Booking;
use App\Models\PaymentPlanTemplate;
use App\Services\PaymentPlanService;
use Illuminate\Http\Request;

class PaymentPlanController extends Controller
{
    protected PaymentPlanService $planService;

    public function __construct(PaymentPlanService $planService)
    {
        $this->planService = $planService;
    }

    public function templates()
    {
        $companyId = auth()->user()->company_id;
        $templates = PaymentPlanTemplate::where('company_id', $companyId)->get();

        return view('payments.plans.templates', compact('templates'));
    }

    public function storeTemplate(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'milestones' => 'required|array|min:1',
            'milestones.*.milestone_name' => 'required|string|max:150',
            'milestones.*.milestone_code' => 'nullable|string|max:50',
            'milestones.*.percentage' => 'required|numeric|min:0|max:100',
            'milestones.*.trigger_days' => 'nullable|integer|min:0',
        ]);

        PaymentPlanTemplate::create([
            'company_id' => auth()->user()->company_id,
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'milestones_json' => $request->milestones,
            'is_active' => true,
        ]);

        return back()->with('success', "Payment plan template '{$request->name}' created successfully!");
    }

    public function generateForBooking(StorePaymentPlanRequest $request)
    {
        $booking = Booking::where('company_id', auth()->user()->company_id)->findOrFail($request->booking_id);

        if ($request->filled('template_id')) {
            $template = PaymentPlanTemplate::where('company_id', auth()->user()->company_id)->findOrFail($request->template_id);
            $this->planService->generateSchedules($booking, $template);
        } else {
            $this->planService->generateSchedules($booking, $request->milestones ?? []);
        }

        return back()->with('success', 'Custom payment schedules generated for booking successfully!');
    }
}
