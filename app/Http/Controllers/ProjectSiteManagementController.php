<?php

namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\LabourEntry;
use App\Models\MaterialCategory;
use App\Models\MaterialEntry;
use App\Models\MaterialInward;
use App\Models\MaterialStock;
use App\Models\Project;
use App\Models\Vendor;
use App\Models\VendorCategory;
use App\Models\VendorPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectSiteManagementController extends Controller
{
    /**
     * Site Management Dashboard for a specific Project.
     */
    public function index(Request $request, Project $project)
    {
        $companyId = auth()->user()->company_id;

        // Ensure Project belongs to current tenant company
        if ($project->company_id !== $companyId) {
            abort(403, 'Unauthorized access to project.');
        }

        $activeTab = $request->get('tab', 'summary');

        // Preset Date Filters (Today, Weekly, Monthly)
        $preset = $request->get('preset');
        if ($preset === 'today') {
            $startDate = now()->format('Y-m-d');
            $endDate = now()->format('Y-m-d');
        } elseif ($preset === 'this_week') {
            $startDate = now()->startOfWeek()->format('Y-m-d');
            $endDate = now()->format('Y-m-d');
        } elseif ($preset === 'this_month') {
            $startDate = now()->startOfMonth()->format('Y-m-d');
            $endDate = now()->format('Y-m-d');
        } elseif ($preset === 'last_month') {
            $startDate = now()->subMonth()->startOfMonth()->format('Y-m-d');
            $endDate = now()->subMonth()->endOfMonth()->format('Y-m-d');
        } else {
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');
        }

        $selectedCategoryId = $request->get('category_id');
        $selectedVendorId = $request->get('vendor_id');
        $selectedPaymentMode = $request->get('payment_mode');
        $selectedBuildingId = $request->get('building_id');

        // Common Collections for Filter Dropdowns & Modals
        $materialCategories = MaterialCategory::where('company_id', $companyId)->orderBy('name')->get();
        $vendorCategories = VendorCategory::where('company_id', $companyId)->orderBy('name')->get();
        $buildings = Building::where('project_id', $project->id)->orderBy('name')->get();
        $vendors = Vendor::with('category')->where('company_id', $companyId)->orderBy('vendor_name')->get();

        // Populate default categories if empty
        if ($materialCategories->isEmpty()) {
            $defaultCatNames = ['Bandhkam (Cement & Crusher)', 'Plywoods & Shuttering', 'Steel & Rebar', 'Bricks & Blocks', 'Plumbing & Sanitation', 'Electrical & Wiring'];
            foreach ($defaultCatNames as $catName) {
                MaterialCategory::create([
                    'company_id' => $companyId,
                    'name' => $catName,
                ]);
            }
            $materialCategories = MaterialCategory::where('company_id', $companyId)->get();
        }

        if ($vendorCategories->isEmpty()) {
            $defaultVendorCatNames = ['Plumbers', 'Painters', 'Plywood Suppliers', 'Electrical Contractors', 'Civil Contractors'];
            foreach ($defaultVendorCatNames as $vCatName) {
                VendorCategory::create([
                    'company_id' => $companyId,
                    'name' => $vCatName,
                ]);
            }
            $vendorCategories = VendorCategory::where('company_id', $companyId)->get();
        }

        // Material Query
        $materialQuery = MaterialEntry::with(['category', 'building', 'supplierVendor'])
            ->where('company_id', $companyId)
            ->where('project_id', $project->id);

        if ($startDate && $endDate) {
            $materialQuery->whereBetween('entry_date', [$startDate, $endDate]);
        }
        if ($selectedCategoryId) {
            $materialQuery->where('category_id', $selectedCategoryId);
        }
        if ($selectedBuildingId) {
            $materialQuery->where('building_id', $selectedBuildingId);
        }
        $materialEntries = (clone $materialQuery)->orderBy('entry_date', 'desc')->paginate(15, ['*'], 'mat_page');

        // Labour Query
        $labourQuery = LabourEntry::with(['building', 'supervisor'])
            ->where('company_id', $companyId)
            ->where('project_id', $project->id);

        if ($startDate && $endDate) {
            $labourQuery->whereBetween('work_date', [$startDate, $endDate]);
        }
        if ($selectedBuildingId) {
            $labourQuery->where('building_id', $selectedBuildingId);
        }
        $labourEntries = (clone $labourQuery)->orderBy('work_date', 'desc')->paginate(15, ['*'], 'lab_page');

        // Vendor Payment Query
        $vendorPaymentQuery = VendorPayment::with(['vendor.category', 'creator'])
            ->where('company_id', $companyId)
            ->where('project_id', $project->id);

        if ($startDate && $endDate) {
            $vendorPaymentQuery->whereBetween('payment_date', [$startDate, $endDate]);
        }
        if ($selectedVendorId) {
            $vendorPaymentQuery->where('vendor_id', $selectedVendorId);
        }
        if ($selectedPaymentMode) {
            $vendorPaymentQuery->where('payment_mode', $selectedPaymentMode);
        }
        $vendorPayments = (clone $vendorPaymentQuery)->orderBy('payment_date', 'desc')->paginate(15, ['*'], 'pay_page');

        // KPI Summary Aggregations
        $totalMaterialSpend = (float) (clone $materialQuery)->sum('total_cost');
        $totalLabourSpend = (float) (clone $labourQuery)->sum('total_wages');
        
        $vendorPaymentsBase = VendorPayment::where('company_id', $companyId)->where('project_id', $project->id);
        if ($startDate && $endDate) {
            $vendorPaymentsBase->whereBetween('payment_date', [$startDate, $endDate]);
        }

        $totalCashPaid = (float) (clone $vendorPaymentsBase)->where('payment_mode', 'Cash')->sum('amount');
        $totalChequePaid = (float) (clone $vendorPaymentsBase)->where('payment_mode', 'Cheque')->sum('amount');
        $totalVendorPaid = (float) (clone $vendorPaymentsBase)->sum('amount');
        $totalVendorBilled = (float) (clone $vendorPaymentsBase)->sum('invoice_bill_amount');
        $totalRemainingDue = max(0, $totalVendorBilled - $totalVendorPaid);

        // Category-wise Vendor Payment Summary
        $categoryVendorSummary = DB::table('vendor_payments')
            ->join('vendors', 'vendor_payments.vendor_id', '=', 'vendors.id')
            ->join('vendor_categories', 'vendors.category_id', '=', 'vendor_categories.id')
            ->where('vendor_payments.company_id', $companyId)
            ->where('vendor_payments.project_id', $project->id)
            ->whereNull('vendor_payments.deleted_at')
            ->select(
                'vendor_categories.name as category_name',
                DB::raw("SUM(CASE WHEN vendor_payments.payment_mode = 'Cash' THEN vendor_payments.amount ELSE 0 END) as cash_amount"),
                DB::raw("SUM(CASE WHEN vendor_payments.payment_mode = 'Cheque' THEN vendor_payments.amount ELSE 0 END) as cheque_amount"),
                DB::raw("SUM(vendor_payments.amount) as total_paid"),
                DB::raw("SUM(vendor_payments.invoice_bill_amount) as total_billed")
            )
            ->groupBy('vendor_categories.name')
            ->get();

        // Live Material Stock Inventory Query
        $materialStocks = MaterialStock::with('category')
            ->where('company_id', $companyId)
            ->where('project_id', $project->id)
            ->orderBy('material_name')
            ->get();

        // Inward Stock Replenishments Query
        $materialInwardsQuery = MaterialInward::with(['category', 'supplierVendor', 'receiver'])
            ->where('company_id', $companyId)
            ->where('project_id', $project->id);
        
        if ($startDate && $endDate) {
            $materialInwardsQuery->whereBetween('received_date', [$startDate, $endDate]);
        }
        $materialInwards = (clone $materialInwardsQuery)->orderBy('received_date', 'desc')->paginate(15, ['*'], 'inw_page');

        // Sweet Alerts Stock Notification Payload
        $lowStockItems = $materialStocks->filter(fn($st) => $st->isLowStock() || $st->isOutOfStock());

        return view('projects.site_management.index', compact(
            'project',
            'activeTab',
            'materialCategories',
            'vendorCategories',
            'buildings',
            'vendors',
            'materialEntries',
            'labourEntries',
            'vendorPayments',
            'materialStocks',
            'materialInwards',
            'lowStockItems',
            'totalMaterialSpend',
            'totalLabourSpend',
            'totalCashPaid',
            'totalChequePaid',
            'totalVendorPaid',
            'totalVendorBilled',
            'totalRemainingDue',
            'categoryVendorSummary',
            'startDate',
            'endDate',
            'preset',
            'selectedCategoryId',
            'selectedVendorId',
            'selectedPaymentMode',
            'selectedBuildingId'
        ));
    }

    /**
     * Store Category-wise Material Outward / Usage Entry & Auto-Deduct Stock.
     */
    public function storeMaterial(Request $request, Project $project)
    {
        $companyId = auth()->user()->company_id;

        $validated = $request->validate([
            'category_id' => 'required|exists:material_categories,id',
            'material_name' => 'required|string|max:150',
            'quantity' => 'required|numeric|min:0.001',
            'unit_of_measure' => 'required|string|max:30',
            'unit_cost' => 'nullable|numeric|min:0',
            'entry_date' => 'required|date|before_or_equal:today',
            'building_id' => 'nullable|exists:buildings,id',
            'supplier_vendor_id' => 'nullable|exists:vendors,id',
            'invoice_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $validated['company_id'] = $companyId;
        $validated['project_id'] = $project->id;
        $validated['created_by'] = auth()->id();
        $validated['unit_cost'] = (float) ($validated['unit_cost'] ?? 0);
        $validated['total_cost'] = (float) $validated['quantity'] * $validated['unit_cost'];

        MaterialEntry::create($validated);

        // Find or create matching stock balance item & auto-deduct usage
        $stock = MaterialStock::firstOrCreate(
            [
                'company_id' => $companyId,
                'project_id' => $project->id,
                'category_id' => $validated['category_id'],
                'material_name' => $validated['material_name'],
            ],
            [
                'unit_of_measure' => $validated['unit_of_measure'],
                'current_stock_qty' => 0,
                'min_threshold_qty' => 10.000,
                'unit_cost' => $validated['unit_cost'],
            ]
        );

        $newQty = max(0, (float) $stock->current_stock_qty - (float) $validated['quantity']);
        $stock->update(['current_stock_qty' => $newQty]);

        $alertMsg = 'Material usage entry recorded successfully.';
        if ($stock->isOutOfStock()) {
            $alertMsg .= " ⚠️ Alert: {$stock->material_name} is now OUT OF STOCK!";
        } elseif ($stock->isLowStock()) {
            $alertMsg .= " ⚠️ Warning: {$stock->material_name} is below safety threshold ({$stock->current_stock_qty} {$stock->unit_of_measure} remaining).";
        }

        return redirect()->route('projects.site-management', [$project->id, 'tab' => 'materials'])
            ->with('success', $alertMsg);
    }

    /**
     * Store Daily Inward Material Replenishment & Auto-Increment Stock.
     */
    public function storeInward(Request $request, Project $project)
    {
        $companyId = auth()->user()->company_id;

        $validated = $request->validate([
            'category_id' => 'required|exists:material_categories,id',
            'material_name' => 'required|string|max:150',
            'qty_received' => 'required|numeric|min:0.001',
            'unit_of_measure' => 'required|string|max:30',
            'unit_cost' => 'nullable|numeric|min:0',
            'vendor_id' => 'nullable|exists:vendors,id',
            'invoice_number' => 'nullable|string|max:100',
            'gate_pass_number' => 'nullable|string|max:100',
            'received_date' => 'required|date|before_or_equal:today',
            'min_threshold_qty' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $validated['company_id'] = $companyId;
        $validated['project_id'] = $project->id;
        $validated['created_by'] = auth()->id();
        $validated['unit_cost'] = (float) ($validated['unit_cost'] ?? 0);
        $validated['total_cost'] = (float) $validated['qty_received'] * $validated['unit_cost'];

        // Find or create matching MaterialStock
        $stock = MaterialStock::firstOrCreate(
            [
                'company_id' => $companyId,
                'project_id' => $project->id,
                'category_id' => $validated['category_id'],
                'material_name' => $validated['material_name'],
            ],
            [
                'unit_of_measure' => $validated['unit_of_measure'],
                'current_stock_qty' => 0,
                'min_threshold_qty' => (float) ($validated['min_threshold_qty'] ?? 10.000),
                'unit_cost' => $validated['unit_cost'],
            ]
        );

        $validated['stock_id'] = $stock->id;

        // Create Inward Record
        MaterialInward::create($validated);

        // Auto-increment Stock Balance
        $newQty = (float) $stock->current_stock_qty + (float) $validated['qty_received'];
        $stock->update([
            'current_stock_qty' => $newQty,
            'unit_cost' => $validated['unit_cost'] > 0 ? $validated['unit_cost'] : $stock->unit_cost,
            'last_replenished_at' => now(),
            'min_threshold_qty' => isset($validated['min_threshold_qty']) && $validated['min_threshold_qty'] > 0 ? (float) $validated['min_threshold_qty'] : $stock->min_threshold_qty,
        ]);

        return redirect()->route('projects.site-management', [$project->id, 'tab' => 'stocks'])
            ->with('success', "Inward stock of {$validated['qty_received']} {$validated['unit_of_measure']} {$validated['material_name']} received and added to live inventory!");
    }

    /**
     * Store new Material Category.
     */
    public function storeMaterialCategory(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        $validated['company_id'] = auth()->user()->company_id;

        MaterialCategory::create($validated);

        return redirect()->back()->with('success', 'Material category added successfully.');
    }

    /**
     * Store Daily Labour Wage Entry.
     */
    public function storeLabour(Request $request, Project $project)
    {
        $companyId = auth()->user()->company_id;

        $validated = $request->validate([
            'labour_identifier' => 'required|string|max:150',
            'work_category' => 'required|string|max:100',
            'days_worked' => 'required|numeric|min:0.1|max:31',
            'daily_wage_rate' => 'nullable|numeric|min:0',
            'work_date' => 'required|date|before_or_equal:today',
            'building_id' => 'nullable|exists:buildings,id',
            'payment_status' => 'required|in:Pending,Paid,Partial',
            'notes' => 'nullable|string',
        ]);

        $validated['company_id'] = $companyId;
        $validated['project_id'] = $project->id;
        $validated['supervisor_user_id'] = auth()->id();
        $validated['daily_wage_rate'] = (float) ($validated['daily_wage_rate'] ?? 0);
        $validated['total_wages'] = (float) $validated['days_worked'] * $validated['daily_wage_rate'];

        LabourEntry::create($validated);

        return redirect()->route('projects.site-management', [$project->id, 'tab' => 'labour'])
            ->with('success', 'Labour wage entry recorded successfully.');
    }

    /**
     * Store Vendor Master.
     */
    public function storeVendor(Request $request, Project $project)
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
        ]);

        $validated['company_id'] = $companyId;

        Vendor::create($validated);

        return redirect()->route('projects.site-management', [$project->id, 'tab' => 'vendors'])
            ->with('success', 'Vendor profile created successfully.');
    }

    /**
     * Store Vendor Payment (Cash / Cheque / Online).
     */
    public function storeVendorPayment(Request $request, Project $project)
    {
        $companyId = auth()->user()->company_id;

        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'payment_mode' => 'required|in:Cash,Cheque,NEFT/RTGS,UPI',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date|before_or_equal:today',
            'cheque_number' => 'nullable|required_if:payment_mode,Cheque|string|max:50',
            'bank_name' => 'nullable|string|max:100',
            'transaction_reference' => 'nullable|string|max:100',
            'invoice_bill_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $validated['company_id'] = $companyId;
        $validated['project_id'] = $project->id;
        $validated['created_by'] = auth()->id();
        $validated['invoice_bill_amount'] = $validated['invoice_bill_amount'] ?? 0;

        VendorPayment::create($validated);

        return redirect()->route('projects.site-management', [$project->id, 'tab' => 'vendors'])
            ->with('success', 'Vendor payment transaction recorded successfully.');
    }

    /**
     * Delete Material Entry.
     */
    public function destroyMaterial(Project $project, MaterialEntry $material)
    {
        if ($material->project_id !== $project->id || $material->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $material->delete();

        return redirect()->back()->with('success', 'Material entry removed.');
    }

    /**
     * Delete Labour Entry.
     */
    public function destroyLabour(Project $project, LabourEntry $labour)
    {
        if ($labour->project_id !== $project->id || $labour->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $labour->delete();

        return redirect()->back()->with('success', 'Labour entry removed.');
    }

    /**
     * Delete Vendor Payment.
     */
    public function destroyVendorPayment(Project $project, VendorPayment $payment)
    {
        if ($payment->project_id !== $project->id || $payment->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $payment->delete();

        return redirect()->back()->with('success', 'Vendor payment transaction removed.');
    }

    /**
     * CSV Export of Material & Vendor Ledgers.
     */
    public function exportExcel(Request $request, Project $project)
    {
        $companyId = auth()->user()->company_id;
        $type = $request->get('export_type', 'materials');
        $fileName = "{$project->project_name}_{$type}_report_" . date('Y_m_d') . ".csv";

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename={$fileName}",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        return response()->stream(function () use ($companyId, $project, $type) {
            $file = fopen('php://output', 'w');

            if ($type === 'materials') {
                fputcsv($file, ['ID', 'Date', 'Material Name', 'Category', 'Quantity', 'Unit', 'Unit Cost (INR)', 'Total Cost (INR)', 'Invoice Ref']);
                $entries = MaterialEntry::with('category')->where('company_id', $companyId)->where('project_id', $project->id)->get();
                foreach ($entries as $e) {
                    fputcsv($file, [
                        $e->id,
                        $e->entry_date->format('Y-m-d'),
                        $e->material_name,
                        $e->category->name ?? 'Uncategorized',
                        $e->quantity,
                        $e->unit_of_measure,
                        $e->unit_cost,
                        $e->total_cost,
                        $e->invoice_number ?? 'N/A'
                    ]);
                }
            } elseif ($type === 'vendors') {
                fputcsv($file, ['ID', 'Date', 'Vendor Name', 'Category', 'Payment Mode', 'Amount Paid (INR)', 'Cheque No / Ref', 'Billed Amount (INR)']);
                $payments = VendorPayment::with(['vendor.category'])->where('company_id', $companyId)->where('project_id', $project->id)->get();
                foreach ($payments as $p) {
                    fputcsv($file, [
                        $p->id,
                        $p->payment_date->format('Y-m-d'),
                        $p->vendor->vendor_name ?? 'N/A',
                        $p->vendor->category->name ?? 'N/A',
                        $p->payment_mode,
                        $p->amount,
                        $p->cheque_number ?? ($p->transaction_reference ?? 'N/A'),
                        $p->invoice_bill_amount
                    ]);
                }
            } else {
                fputcsv($file, ['ID', 'Date', 'Labour Name / ID', 'Category', 'Days Worked', 'Daily Rate (INR)', 'Total Wages (INR)', 'Status']);
                $labours = LabourEntry::where('company_id', $companyId)->where('project_id', $project->id)->get();
                foreach ($labours as $l) {
                    fputcsv($file, [
                        $l->id,
                        $l->work_date->format('Y-m-d'),
                        $l->labour_identifier,
                        $l->work_category,
                        $l->days_worked,
                        $l->daily_wage_rate,
                        $l->total_wages,
                        $l->payment_status
                    ]);
                }
            }

            fclose($file);
        }, 200, $headers);
    }

    /**
     * Printable PDF Statement for Vendor Ledger.
     */
    public function exportPdf(Request $request, Project $project)
    {
        $companyId = auth()->user()->company_id;
        $vendorId = $request->get('vendor_id');

        $vendor = null;
        if ($vendorId) {
            $vendor = Vendor::with('category')->where('company_id', $companyId)->find($vendorId);
        }

        $paymentsQuery = VendorPayment::with(['vendor.category', 'creator'])
            ->where('company_id', $companyId)
            ->where('project_id', $project->id);

        if ($vendorId) {
            $paymentsQuery->where('vendor_id', $vendorId);
        }

        $payments = $paymentsQuery->orderBy('payment_date', 'asc')->get();

        $totalCash = $payments->where('payment_mode', 'Cash')->sum('amount');
        $totalCheque = $payments->where('payment_mode', 'Cheque')->sum('amount');
        $totalOnline = $payments->whereIn('payment_mode', ['NEFT/RTGS', 'UPI'])->sum('amount');
        $totalPaid = $payments->sum('amount');
        $totalBilled = $payments->sum('invoice_bill_amount');
        $remainingDue = max(0, $totalBilled - $totalPaid);

        return view('projects.site_management.vendor_ledger_pdf', compact(
            'project',
            'vendor',
            'payments',
            'totalCash',
            'totalCheque',
            'totalOnline',
            'totalPaid',
            'totalBilled',
            'remainingDue'
        ));
    }
}
