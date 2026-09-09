<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAutomationRuleRequest;
use App\Models\AutomationRule;
use App\Models\CommunicationTemplate;
use Illuminate\Http\Request;

class AutomationController extends Controller
{
    public function index()
    {
        $companyId = auth()->user()->company_id;

        $rules = AutomationRule::with('template', 'executions')
            ->where('company_id', $companyId)
            ->orderBy('created_at', 'desc')
            ->get();

        $templates = CommunicationTemplate::where('company_id', $companyId)->where('status', 'Active')->get();

        return view('communication.automations.index', compact('rules', 'templates'));
    }

    public function store(StoreAutomationRuleRequest $request)
    {
        $companyId = auth()->user()->company_id;

        $rule = AutomationRule::create([
            'company_id' => $companyId,
            'name' => $request->name,
            'trigger_event' => $request->trigger_event,
            'communication_template_id' => $request->communication_template_id,
            'delay_minutes' => $request->delay_minutes ?? 0,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', "Automation Rule '{$rule->name}' created successfully!");
    }

    public function toggle(AutomationRule $rule)
    {
        if ($rule->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $rule->update(['is_active' => !$rule->is_active]);

        return back()->with('success', "Automation Rule status updated!");
    }
}
