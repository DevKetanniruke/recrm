<?php

namespace App\Http\Controllers;

use App\Models\LeadSource;
use App\Models\LeadStatus;
use Illuminate\Http\Request;

class LeadConfigController extends Controller
{
    public function index()
    {
        $companyId = auth()->user()->company_id;
        $sources = LeadSource::where('company_id', $companyId)->orderBy('name')->get();
        $statuses = LeadStatus::where('company_id', $companyId)->orderBy('sort_order')->get();

        return view('leads.config', compact('sources', 'statuses'));
    }

    public function storeSource(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
        ]);

        LeadSource::create([
            'company_id' => auth()->user()->company_id,
            'name' => trim($request->name),
            'is_active' => true,
        ]);

        return back()->with('success', 'Lead Source added successfully!');
    }

    public function updateSource(Request $request, LeadSource $source)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'is_active' => 'boolean',
        ]);

        $source->update([
            'name' => trim($request->name),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Lead Source updated successfully!');
    }

    public function destroySource(LeadSource $source)
    {
        $source->delete();
        return back()->with('success', 'Lead Source deleted successfully!');
    }

    public function storeStatus(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'color_code' => 'required|string|max:10',
            'sort_order' => 'nullable|integer',
            'is_won' => 'boolean',
            'is_lost' => 'boolean',
        ]);

        LeadStatus::create([
            'company_id' => auth()->user()->company_id,
            'name' => trim($request->name),
            'color_code' => $request->color_code,
            'sort_order' => $request->sort_order ?? 0,
            'is_won' => $request->boolean('is_won'),
            'is_lost' => $request->boolean('is_lost'),
            'is_active' => true,
        ]);

        return back()->with('success', 'Lead Status added successfully!');
    }

    public function updateStatus(Request $request, LeadStatus $status)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'color_code' => 'required|string|max:10',
            'sort_order' => 'nullable|integer',
            'is_won' => 'boolean',
            'is_lost' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $status->update([
            'name' => trim($request->name),
            'color_code' => $request->color_code,
            'sort_order' => $request->sort_order ?? 0,
            'is_won' => $request->boolean('is_won'),
            'is_lost' => $request->boolean('is_lost'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Lead Status updated successfully!');
    }

    public function destroyStatus(LeadStatus $status)
    {
        $status->delete();
        return back()->with('success', 'Lead Status deleted successfully!');
    }
}
