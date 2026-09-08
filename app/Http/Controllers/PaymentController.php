<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\PaymentService;
use Exception;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function index(Request $request)
    {
        $query = Payment::with(['booking.customer', 'booking.unit', 'paymentSchedule', 'receivedBy']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('receipt_number', 'like', "%{$search}%")
                    ->orWhere('transaction_reference', 'like', "%{$search}%");
            });
        }

        $payments = $query->orderBy('payment_date', 'desc')->paginate(15);
        $bookings = Booking::with('customer')->where('status', 'Confirmed')->get();

        return view('payments.index', compact('payments', 'bookings'));
    }

    public function store(StorePaymentRequest $request)
    {
        try {
            $payment = $this->paymentService->recordPayment($request->validated(), auth()->id());
            return redirect()->route('payments.receipt', $payment->id)
                ->with('success', "Payment receipt #{$payment->receipt_number} recorded successfully!");
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function receipt(Payment $payment)
    {
        $payment->load(['booking.customer', 'booking.unit.floor.wing.building.project', 'paymentSchedule', 'receivedBy', 'company']);
        return view('payments.receipt', compact('payment'));
    }
}
