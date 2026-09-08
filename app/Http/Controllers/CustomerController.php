<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::with(['bookings.unit']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $customers = $query->orderBy('created_at', 'desc')->paginate(15);
        return view('customers.index', compact('customers'));
    }

    public function show(Customer $customer)
    {
        $customer->load(['lead', 'bookings.unit.floor.wing.building.project', 'bookings.payments', 'bookings.paymentSchedules']);
        return view('customers.show', compact('customer'));
    }

    public function updateKyc(Request $request, Customer $customer)
    {
        $request->validate([
            'kyc_status' => 'required|in:Pending,Verified,Rejected',
            'pan_number' => 'nullable|string|max:50',
            'tax_id' => 'nullable|string|max:50',
        ]);

        $customer->update($request->only(['kyc_status', 'pan_number', 'tax_id']));

        return back()->with('success', 'Customer KYC status updated successfully!');
    }
}
