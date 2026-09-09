<?php

namespace App\Http\Controllers;

use App\Http\Requests\CounterOfferRequest;
use App\Http\Requests\StoreOfferRequest;
use App\Models\Lead;
use App\Models\Offer;
use App\Models\Project;
use App\Models\Unit;
use App\Models\User;
use App\Services\OfferService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    use AuthorizesRequests;

    protected OfferService $offerService;

    public function __construct(OfferService $offerService)
    {
        $this->offerService = $offerService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $companyId = $user->company_id;

        $query = Offer::with(['lead', 'unit.project', 'creator', 'approver'])
            ->where('company_id', $companyId);

        // Role Scoping
        if (!$user->isAdmin()) {
            if ($user->isManager()) {
                $allowedUserIds = $user->getTeamMemberIds();
                $query->whereIn('created_by', $allowedUserIds);
            } else {
                $query->where('created_by', $user->id);
            }
        }

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('offer_number', 'like', "%{$search}%")
                  ->orWhereHas('lead', function ($ql) use ($search) {
                      $ql->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        $offers = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('offers.index', compact('offers'));
    }

    public function create(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $leads = Lead::where('company_id', $companyId)->get();
        $projects = Project::where('company_id', $companyId)->get();
        $availableUnits = Unit::with('pricing')->where('company_id', $companyId)->where('status', 'Available')->get();

        $selectedLeadId = $request->get('lead_id');
        $selectedUnitId = $request->get('unit_id');

        return view('offers.create', compact('leads', 'projects', 'availableUnits', 'selectedLeadId', 'selectedUnitId'));
    }

    public function store(StoreOfferRequest $request)
    {
        $lead = Lead::findOrFail($request->lead_id);
        $unit = Unit::findOrFail($request->unit_id);

        $offer = $this->offerService->createOffer(
            lead: $lead,
            unit: $unit,
            offeredPrice: $request->offered_price,
            tokenAmount: $request->token_amount_offered,
            paymentPlanType: $request->payment_plan_type,
            validityDays: $request->validity_days ?? 7,
            creator: auth()->user(),
            terms: $request->terms_conditions
        );

        return redirect()->route('offers.show', $offer->id)->with('success', "Offer #{$offer->offer_number} created successfully! Status: {$offer->status}.");
    }

    public function show(Offer $offer)
    {
        $this->authorize('view', $offer);

        $offer->load(['lead', 'unit.project', 'unit.pricing', 'creator', 'approver', 'negotiationRounds.proposedByUser']);

        return view('offers.show', compact('offer'));
    }

    public function submitCounterOffer(CounterOfferRequest $request, Offer $offer)
    {
        $this->authorize('update', $offer);

        $this->offerService->submitCounterOffer(
            offer: $offer,
            counterPrice: $request->counter_price,
            tokenAmount: $request->token_amount,
            offeredBy: $request->offered_by,
            user: auth()->user(),
            comments: $request->comments
        );

        return back()->with('success', 'Counter-offer round recorded!');
    }

    public function approve(Request $request, Offer $offer)
    {
        $this->authorize('approve', $offer);

        $request->validate([
            'approval_notes' => 'nullable|string',
        ]);

        $this->offerService->approveOffer($offer, auth()->user(), $request->approval_notes);

        return back()->with('success', "Offer #{$offer->offer_number} approved successfully!");
    }

    public function reject(Request $request, Offer $offer)
    {
        $this->authorize('reject', $offer);

        $request->validate([
            'rejection_reason' => 'required|string',
        ]);

        $this->offerService->rejectOffer($offer, auth()->user(), $request->rejection_reason);

        return back()->with('success', "Offer #{$offer->offer_number} rejected. Unit lock released.");
    }

    public function convertToBooking(Offer $offer)
    {
        $this->authorize('convertBooking', $offer);

        $booking = $this->offerService->convertToBooking($offer, auth()->user());

        return redirect()->route('bookings.show', $booking->id)->with('success', "Offer successfully converted into Booking #{$booking->booking_number}!");
    }

    public function downloadPdf(Offer $offer)
    {
        $this->authorize('view', $offer);

        $offer->load(['lead', 'unit.project', 'creator', 'approver', 'negotiationRounds']);

        return view('offers.pdf', compact('offer'));
    }
}
