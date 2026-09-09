<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Models\CoApplicant;
use App\Models\Customer;
use App\Models\Lead;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $query = Customer::with(['bookings.unit', 'coApplicants', 'documents'])
            ->where('company_id', $companyId);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('customer_number', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('PAN', 'like', "%{$search}%");
            });
        }

        if ($request->filled('kyc_status')) {
            $query->where('kyc_status', $request->kyc_status);
        }

        $customers = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('customers.index', compact('customers'));
    }

    public function create(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $leads = Lead::where('company_id', $companyId)->get();
        $selectedLeadId = $request->get('lead_id');

        return view('customers.create', compact('leads', 'selectedLeadId'));
    }

    public function store(StoreCustomerRequest $request)
    {
        $companyId = auth()->user()->company_id;

        $customer = Customer::create(array_merge($request->validated(), [
            'company_id' => $companyId,
            'kyc_status' => 'Pending',
        ]));

        if ($request->filled('co_applicants')) {
            foreach ($request->co_applicants as $coData) {
                if (!empty($coData['customer_name'])) {
                    CoApplicant::create([
                        'company_id' => $companyId,
                        'customer_id' => $customer->id,
                        'customer_name' => $coData['customer_name'],
                        'relationship' => $coData['relationship'] ?? 'Co-Applicant',
                        'mobile' => $coData['mobile'] ?? null,
                        'email' => $coData['email'] ?? null,
                        'ownership_percentage' => $coData['ownership_percentage'] ?? 0.00,
                        'applicant_type' => $coData['applicant_type'] ?? 'Co-Applicant',
                        'pan_number' => $coData['pan_number'] ?? null,
                        'aadhaar_number' => $coData['aadhaar_number'] ?? null,
                    ]);
                }
            }
        }

        return redirect()->route('customers.show', $customer->id)->with('success', "Customer record #{$customer->customer_number} created successfully!");
    }

    public function show(Customer $customer)
    {
        $this->authorize('view', $customer);

        $customer->load([
            'lead',
            'coApplicants',
            'documents.uploader',
            'bookings.unit.project',
            'bookings.payments',
            'bookings.paymentSchedules',
        ]);

        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        $this->authorize('update', $customer);

        $customer->load('coApplicants');

        return view('customers.edit', compact('customer'));
    }

    public function update(StoreCustomerRequest $request, Customer $customer)
    {
        $this->authorize('update', $customer);

        $customer->update($request->validated());

        return redirect()->route('customers.show', $customer->id)->with('success', 'Customer profile updated successfully!');
    }

    public function updateKyc(Request $request, Customer $customer)
    {
        $this->authorize('update', $customer);

        $request->validate([
            'kyc_status' => 'required|in:Pending,Verified,Rejected',
            'PAN' => 'nullable|string|max:20',
            'tax_id' => 'nullable|string|max:50',
        ]);

        $customer->update($request->only(['kyc_status', 'PAN', 'tax_id']));

        return back()->with('success', 'Customer KYC status updated successfully!');
    }
}
