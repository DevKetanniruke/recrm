<?php

namespace App\Http\Controllers;

use App\Http\Requests\CancelBookingRequest;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Project;
use App\Models\SystemSetting;
use App\Models\Unit;
use App\Services\BookingService;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    use AuthorizesRequests;

    protected BookingService $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $query = Booking::with(['unit.project', 'customer', 'salesAgent', 'project'])
            ->where('company_id', $companyId);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('booking_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($qc) use ($search) {
                      $qc->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%")
                         ->orWhere('mobile', 'like', "%{$search}%");
                  })
                  ->orWhereHas('unit', function ($qu) use ($search) {
                      $qu->where('unit_number', 'like', "%{$search}%");
                  });
            });
        }

        $bookings = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('bookings.index', compact('bookings'));
    }

    public function create(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $availableUnits = Unit::with('project', 'pricing')
            ->where('company_id', $companyId)
            ->whereIn('status', ['Available', 'Hold'])
            ->get();

        $selectedUnit = $request->filled('unit_id')
            ? Unit::with('project', 'pricing')->where('company_id', $companyId)->find($request->unit_id)
            : null;

        $leads = Lead::where('company_id', $companyId)->get();
        $customers = Customer::where('company_id', $companyId)->get();
        $projects = Project::where('company_id', $companyId)->get();

        return view('bookings.create', compact('availableUnits', 'selectedUnit', 'leads', 'customers', 'projects'));
    }

    public function store(StoreBookingRequest $request)
    {
        try {
            $booking = $this->bookingService->createBooking($request->validated(), auth()->user());

            return redirect()->route('bookings.show', $booking->id)
                ->with('success', "Booking #{$booking->booking_number} confirmed successfully! Unit marked as Booked.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(Booking $booking)
    {
        $this->authorize('view', $booking);

        $booking->load([
            'unit.project',
            'unit.pricing',
            'project',
            'customer.coApplicants',
            'customer.documents',
            'salesAgent',
            'cancelledBy',
            'paymentSchedules',
            'payments.receivedBy',
            'documents',
        ]);

        return view('bookings.show', compact('booking'));
    }

    public function cancel(CancelBookingRequest $request, Booking $booking)
    {
        $this->authorize('cancel', $booking);

        try {
            $this->bookingService->cancelBooking(
                $booking,
                auth()->user(),
                $request->cancellation_reason,
                (float) ($request->cancellation_refund_amount ?? 0)
            );

            return back()->with('success', "Booking #{$booking->booking_number} cancelled and unit released back to Available.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function downloadConfirmationPdf(Booking $booking)
    {
        $this->authorize('view', $booking);

        $booking->load(['unit.project', 'unit.pricing', 'project', 'customer.coApplicants', 'salesAgent']);
        $company = auth()->user()->company;
        $settings = SystemSetting::where('company_id', auth()->user()->company_id)->pluck('setting_value', 'setting_key');

        return view('bookings.pdf', compact('booking', 'company', 'settings'));
    }
}
