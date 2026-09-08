<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $companyId = auth()->user()->company_id;
        $settings = SystemSetting::where('company_id', $companyId)
            ->get()
            ->pluck('value', 'key');

        return view('settings.index', compact('settings'));
    }

    public function store(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $data = $request->except('_token');

        foreach ($data as $key => $value) {
            SystemSetting::setVal($key, $value, 'general', $companyId);
        }

        return back()->with('success', 'System preferences updated successfully!');
    }
}
