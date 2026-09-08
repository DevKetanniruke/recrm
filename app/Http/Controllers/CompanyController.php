<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::withCount('users')->paginate(10);
        return view('companies.index', compact('companies'));
    }

    public function show(Company $company)
    {
        $company->load(['users', 'roles']);
        return view('companies.show', compact('company'));
    }

    public function edit(Company $company)
    {
        return view('companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'legal_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'gst_number' => 'nullable|string|max:50',
            'pan_number' => 'nullable|string|max:50',
            'tax_id_rera' => 'nullable|string|max:100',
            'default_timezone' => 'required|string|max:50',
            'currency_code' => 'required|string|max:10',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        $company->update($data);

        return redirect()->route('companies.show', $company->id)->with('success', 'Company profile and tax settings updated successfully!');
    }
}
