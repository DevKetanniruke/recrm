<?php

namespace App\Http\Controllers;

use App\Models\CoApplicant;
use App\Models\Customer;
use Illuminate\Http\Request;

class CoApplicantController extends Controller
{
    public function store(Request $request, Customer $customer)
    {
        $request->validate([
            'customer_name' => 'required|string|max:150',
            'relationship' => 'required|string|max:50',
            'mobile' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:150',
            'ownership_percentage' => 'nullable|numeric|min:0|max:100',
            'applicant_type' => 'nullable|string|max:30',
            'pan_number' => 'nullable|string|max:20',
            'aadhaar_number' => 'nullable|string|max:20',
        ]);

        CoApplicant::create([
            'company_id' => auth()->user()->company_id,
            'customer_id' => $customer->id,
            'customer_name' => $request->customer_name,
            'relationship' => $request->relationship,
            'mobile' => $request->mobile,
            'email' => $request->email,
            'ownership_percentage' => $request->ownership_percentage ?? 0.00,
            'applicant_type' => $request->applicant_type ?? 'Co-Applicant',
            'pan_number' => $request->pan_number,
            'aadhaar_number' => $request->aadhaar_number,
        ]);

        return back()->with('success', 'Co-applicant added successfully!');
    }

    public function destroy(CoApplicant $coApplicant)
    {
        if ($coApplicant->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $coApplicant->delete();

        return back()->with('success', 'Co-applicant removed successfully!');
    }
}
