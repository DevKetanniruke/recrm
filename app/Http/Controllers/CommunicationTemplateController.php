<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommunicationTemplateRequest;
use App\Models\CommunicationTemplate;
use Illuminate\Http\Request;

class CommunicationTemplateController extends Controller
{
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $query = CommunicationTemplate::where('company_id', $companyId);
        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $templates = $query->orderBy('name')->paginate(15);
        return view('communication.templates.index', compact('templates'));
    }

    public function create()
    {
        return view('communication.templates.create');
    }

    public function store(StoreCommunicationTemplateRequest $request)
    {
        $companyId = auth()->user()->company_id;

        $template = CommunicationTemplate::create([
            'company_id' => $companyId,
            'name' => $request->name,
            'channel' => $request->channel,
            'subject' => $request->subject,
            'body' => $request->body,
            'is_transactional' => $request->boolean('is_transactional'),
            'status' => $request->status ?? 'Active',
            'version' => 1,
            'variables_json' => [
                'customer_name', 'project_name', 'unit_number', 'booking_number', 'payment_amount', 'due_date', 'sales_executive'
            ],
        ]);

        return redirect()->route('communication.templates.index')
            ->with('success', "Communication Template '{$template->name}' created successfully!");
    }

    public function edit(CommunicationTemplate $template)
    {
        if ($template->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        return view('communication.templates.create', compact('template'));
    }

    public function update(StoreCommunicationTemplateRequest $request, CommunicationTemplate $template)
    {
        if ($template->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $template->update([
            'name' => $request->name,
            'channel' => $request->channel,
            'subject' => $request->subject,
            'body' => $request->body,
            'is_transactional' => $request->boolean('is_transactional'),
            'status' => $request->status ?? 'Active',
            'version' => $template->version + 1,
        ]);

        return redirect()->route('communication.templates.index')
            ->with('success', "Communication Template '{$template->name}' updated to v{$template->version}!");
    }
}
