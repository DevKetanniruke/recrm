<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\VendorCategory;
use App\Models\VendorPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendorController extends Controller
{
    /**
     * Display a listing of all vendors with Category & Search Filters.
     */
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;

        // Auto-populate default vendor categories if empty for this company
        $vendorCategories = VendorCategory::withCount(['vendors' => function($q) use ($companyId) {
            $q->where('company_id', $companyId);
        }])
        ->where('company_id', $companyId)
        ->orderBy('name')
        ->get();

        if ($vendorCategories->isEmpty()) {
            $defaultVendorCatNames = ['Plumbers', 'Painters', 'Plywood Suppliers', 'Electrical Contractors', 'Civil Contractors', 'Steel Suppliers', 'Building Material Suppliers'];
            foreach ($defaultVendorCatNames as $vCatName) {
                VendorCategory::create([
                    'company_id' => $companyId,
                    'name' => $vCatName,
                ]);
            }
            $vendorCategories = VendorCategory::withCount(['vendors' => function($q) use ($companyId) {
                $q->where('company_id', $companyId);
            }])
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get();
        }

        $selectedCategory = $request->get('category_id');
        $selectedStatus = $request->get('status');
        $search = $request->get('search');

        // Vendor Base Query
        $vendorQuery = Vendor::with(['category', 'payments'])
            ->where('company_id', $companyId);

        if ($selectedCategory) {
            $vendorQuery->where('category_id', $selectedCategory);
        }

        if ($selectedStatus) {
            $vendorQuery->where('status', $selectedStatus);
        }

        if ($search) {
            $vendorQuery->where(function ($q) use ($search) {
                $q->where('vendor_name', 'like', '%' . $search . '%')
                  ->orWhere('contact_person', 'like', '%' . $search . '%')
                  ->orWhere('mobile', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('gst_number', 'like', '%' . $search . '%')
                  ->orWhere('pan_number', 'like', '%' . $search . '%');
            });
        }

        $vendors = (clone $vendorQuery)->orderBy('vendor_name')->paginate(15)->appends($request->all());

        // Overview KPI Cards Data
        $totalVendorsCount = Vendor::where('company_id', $companyId)->count();
        $activeVendorsCount = Vendor::where('company_id', $companyId)->where('status', 'Active')->count();
        $totalCategoriesCount = $vendorCategories->count();

        // Calculate overall vendor billed and paid totals across the company
        $overallPayments = VendorPayment::where('company_id', $companyId)->get();
        $totalBilled = $overallPayments->sum('invoice_bill_amount');
        $totalPaid = $overallPayments->sum('amount');
        $totalOutstandingDue = max(0, $totalBilled - $totalPaid);

        return view('vendors.index', compact(
            'vendors',
            'vendorCategories',
            'selectedCategory',
            'selectedStatus',
            'search',
            'totalVendorsCount',
            'activeVendorsCount',
            'totalCategoriesCount',
            'totalBilled',
            'totalPaid',
            'totalOutstandingDue'
        ));
    }

    /**
     * Store a newly created vendor.
     */
    public function store(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $validated = $request->validate([
            'category_id' => 'required|exists:vendor_categories,id',
            'vendor_name' => 'required|string|max:200',
            'contact_person' => 'nullable|string|max:150',
            'mobile' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
            'address' => 'nullable|string',
            'gst_number' => 'nullable|string|max:50',
            'pan_number' => 'nullable|string|max:50',
            'status' => 'required|in:Active,Inactive',
        ]);

        $validated['company_id'] = $companyId;

        Vendor::create($validated);

        return redirect()->route('vendors.index')->with('success', 'Vendor added successfully!');
    }

    /**
     * Display the specified vendor profile and ledger details.
     */
    public function show(Vendor $vendor)
    {
        $companyId = auth()->user()->company_id;

        if ($vendor->company_id !== $companyId) {
            abort(403, 'Unauthorized access to vendor.');
        }

        $vendor->load(['category', 'payments.project', 'payments.creator']);

        $totalBilled = $vendor->payments->sum('invoice_bill_amount');
        $totalPaid = $vendor->payments->sum('amount');
        $cashPaid = $vendor->payments->where('payment_mode', 'Cash')->sum('amount');
        $chequePaid = $vendor->payments->where('payment_mode', 'Cheque')->sum('amount');
        $neftPaid = $vendor->payments->where('payment_mode', 'NEFT/RTGS')->sum('amount');
        $upiPaid = $vendor->payments->where('payment_mode', 'UPI')->sum('amount');
        $remainingDue = max(0, $totalBilled - $totalPaid);

        return view('vendors.show', compact(
            'vendor',
            'totalBilled',
            'totalPaid',
            'cashPaid',
            'chequePaid',
            'neftPaid',
            'upiPaid',
            'remainingDue'
        ));
    }

    /**
     * Update the specified vendor.
     */
    public function update(Request $request, Vendor $vendor)
    {
        $companyId = auth()->user()->company_id;

        if ($vendor->company_id !== $companyId) {
            abort(403, 'Unauthorized access to vendor.');
        }

        $validated = $request->validate([
            'category_id' => 'required|exists:vendor_categories,id',
            'vendor_name' => 'required|string|max:200',
            'contact_person' => 'nullable|string|max:150',
            'mobile' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
            'address' => 'nullable|string',
            'gst_number' => 'nullable|string|max:50',
            'pan_number' => 'nullable|string|max:50',
            'status' => 'required|in:Active,Inactive',
        ]);

        $vendor->update($validated);

        return redirect()->back()->with('success', 'Vendor details updated successfully!');
    }

    /**
     * Remove the specified vendor.
     */
    public function destroy(Vendor $vendor)
    {
        $companyId = auth()->user()->company_id;

        if ($vendor->company_id !== $companyId) {
            abort(403, 'Unauthorized access to vendor.');
        }

        $vendor->delete();

        return redirect()->route('vendors.index')->with('success', 'Vendor deleted successfully.');
    }

    /**
     * Store a new vendor category.
     */
    public function storeCategory(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        VendorCategory::create([
            'company_id' => $companyId,
            'name' => $validated['name'],
        ]);

        return redirect()->back()->with('success', 'Vendor Category added successfully!');
    }

    /**
     * Remove a vendor category.
     */
    public function destroyCategory(VendorCategory $category)
    {
        $companyId = auth()->user()->company_id;

        if ($category->company_id !== $companyId) {
            abort(403, 'Unauthorized access to category.');
        }

        if ($category->vendors()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete category with associated vendors.');
        }

        $category->delete();

        return redirect()->back()->with('success', 'Vendor category deleted successfully!');
    }
}
