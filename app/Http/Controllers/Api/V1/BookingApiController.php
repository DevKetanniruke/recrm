<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingApiController extends Controller
{
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;
        
        $bookings = Booking::where('company_id', $companyId)
            ->with(['project', 'unit', 'customer', 'salesAgent'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return BookingResource::collection($bookings);
    }

    public function show(Booking $booking)
    {
        if ($booking->company_id !== auth()->user()->company_id) {
            return response()->json(['message' => 'Unauthorized access to booking resource.'], 403);
        }

        $booking->load(['project', 'unit', 'customer', 'salesAgent']);
        return new BookingResource($booking);
    }
}
