<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommissionStructureRequest;
use App\Models\CommissionStructure;
use App\Models\Project;
use App\Models\UnitType;
use Illuminate\Http\Request;

class CommissionStructureController extends Controller
{
    public function index()
    {
        $companyId = auth()->user()->company_id;

        $structures = CommissionStructure::with(['project', 'unitType'])
            ->where('company_id', $companyId)
            ->orderBy('created_at', 'desc')
            ->get();

        $projects = Project::where('company_id', $companyId)->get();
        $unitTypes = UnitType::where('company_id', $companyId)->get();

        return view('brokers.rules.index', compact('structures', 'projects', 'unitTypes'));
    }

    public function store(StoreCommissionStructureRequest $request)
    {
        $companyId = auth()->user()->company_id;

        $structure = CommissionStructure::create([
            'company_id' => $companyId,
            'name' => $request->name,
            'calculation_type' => $request->calculation_type,
            'project_id' => $request->project_id,
            'unit_type_id' => $request->unit_type_id,
            'rate' => $request->rate ?? 2.00,
            'fixed_amount' => $request->fixed_amount ?? 0.00,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', "Commission Scheme '{$structure->name}' created successfully!");
    }
}
