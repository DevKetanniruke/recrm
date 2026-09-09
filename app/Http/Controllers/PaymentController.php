<?php

namespace App\Http\Controllers;

use App\Http\Requests\RecordPaymentRequest;
use App\Http\Requests\ReversePaymentRequest;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\SystemSetting;
use App\Services\FinancialService;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    use AuthorizesRequests;

    protected FinancialService $financialService;

    public function __construct(FinancialService $financialService)
    {
        $this->financialService = $financialService;
    }

    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $query = Payment::with(['booking.customer', 'booking.unit.project', 'customer', 'allocations.paymentSchedule', 'receivedBy'])
            ->where('company_id', $companyId);

        if ($request->filled('payment_mode')) {
            $query->where('payment_mode', $request->payment_mode);
        }

        if ($request->filled('is_reversed')) {
            $query->where('is_reversed', $request->is_reversed == '1');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('payment_number', 'like', "%{$search}%")
                  ->orWhere('receipt_number', 'like', "%{$search}%")
                  ->orWhere('transaction_reference', 'like', "%{$search}%")
                  ->orWhere('bank_cheque_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($qc) use ($search) {
                      $qc->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        $payments = $query->orderBy('created_at', 'desc')->paginate(15);
        $bookings = Booking::with('customer', 'unit')->where('company_id', $companyId)->get();

        return view('payments.index', compact('payments', 'bookings'));
    }

    public function create(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $bookings = Booking::with(['customer', 'unit.project', 'paymentSchedules'])
            ->where('company_id', $companyId)
            ->where('status', '!=', 'Cancelled')
            ->get();

        $selectedBooking = $request->filled('booking_id')
            ? Booking::with(['customer', 'unit.project', 'paymentSchedules'])->where('company_id', $companyId)->find($request->booking_id)
            : null;

        return view('payments.create', compact('bookings', 'selectedBooking'));
    }

    public function store(RecordPaymentRequest $request)
    {
        try {
            $payment = $this->financialService->recordPayment($request->validated(), auth()->user());

            return redirect()->route('payments.show', $payment->id)
                ->with('success', "Payment #{$payment->payment_number} recorded and allocated successfully!");
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(Payment $payment)
    {
        $this->authorize('view', $payment);

        $payment->load([
            'booking.unit.project',
            'customer',
            'allocations.paymentSchedule',
            'refunds.processedBy',
            'receivedBy',
            'reversedBy',
        ]);

        return view('payments.show', compact('payment'));
    }

    public function reverse(ReversePaymentRequest $request, Payment $payment)
    {
        $this->authorize('reverse', $payment);

        try {
            $refund = $this->financialService->reverseOrRefundPayment(
                $payment,
                auth()->user(),
                $request->reversal_reason,
                (float) ($request->refund_amount ?? $payment->amount_paid)
            );

            return back()->with('success', "Payment #{$payment->payment_number} reversed. Refund record #{$refund->refund_number} created.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function receipt(Payment $payment)
    {
        $this->authorize('view', $payment);

        $payment->load([
            'booking.customer',
            'booking.unit.project',
            'customer',
            'allocations.paymentSchedule',
            'receivedBy',
            'company',
        ]);

        $company = auth()->user()->company;
        $settings = SystemSetting::where('company_id', auth()->user()->company_id)->pluck('setting_value', 'setting_key');
        $financialSummary = $this->financialService->calculateSummary($payment->booking);

        return view('payments.receipt', compact('payment', 'company', 'settings', 'financialSummary'));
    }
}
