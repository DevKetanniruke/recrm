<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Unit;
use App\Services\BookingService;
use Exception;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    protected BookingService $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    public function index(Request $request)
    {
        $query = Booking::with(['unit.floor.wing.building.project', 'customer', 'salesAgent']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->orderBy('created_at', 'desc')->paginate(15);
        return view('bookings.index', compact('bookings'));
    }

    public function create(Request $request)
    {
        $availableUnits = Unit::with('floor.wing.building.project')
            ->where('status', 'Available')
            ->get();

        $selectedUnit = $request->filled('unit_id')
            ? Unit::with('floor.wing.building.project')->find($request->unit_id)
            : null;

        $leads = Lead::whereIn('status', ['New', 'Contacted', 'Interested', 'Site Visit Scheduled', 'Negotiation'])->get();

        return view('bookings.create', compact('availableUnits', 'selectedUnit', 'leads'));
    }

    public function store(StoreBookingRequest $request)
    {
        try {
            $booking = $this->bookingService->createBooking($request->validated(), auth()->id());
            return redirect()->route('bookings.show', $booking->id)
                ->with('success', "Booking #{$booking->booking_number} created successfully!");
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(Booking $booking)
    {
        $booking->load([
            'unit.floor.wing.building.project',
            'customer',
            'salesAgent',
            'paymentSchedules',
            'payments.receivedBy',
        ]);

        return view('bookings.show', compact('booking'));
    }

    public function cancel(Request $request, Booking $booking)
    {
        $request->validate([
            'cancellation_reason' => 'required|string',
            'cancellation_refund_amount' => 'required|numeric|min:0',
        ]);

        $this->bookingService->cancelBooking(
            $booking,
            $request->cancellation_reason,
            (float) $request->cancellation_refund_amount,
            auth()->id()
        );

        return back()->with('success', "Booking #{$booking->booking_number} cancelled and unit released.");
    }
}
