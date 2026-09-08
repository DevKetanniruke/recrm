<?php

namespace App\Http\Controllers;

use App\Models\UnitType;
use Illuminate\Http\Request;

class UnitTypeController extends Controller
{
    public function index()
    {
        $unitTypes = UnitType::withCount('units')->get();
        return view('unit_types.index', compact('unitTypes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:50',
            'category' => 'required|string|max:50',
            'default_carpet_area' => 'required|numeric|min:1',
        ]);

        $unitType = UnitType::create($data);

        return back()->with('success', "Unit type '{$unitType->name}' created successfully!");
    }
}
