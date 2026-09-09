<?php

namespace App\Http\Controllers;

use App\Models\CustomerCommunicationPreference;
use Illuminate\Http\Request;

class OptOutController extends Controller
{
    public function index()
    {
        $companyId = auth()->user()->company_id;

        $preferences = CustomerCommunicationPreference::with(['customer', 'lead'])
            ->where('company_id', $companyId)
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        return view('communication.optouts.index', compact('preferences'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'recipient' => ['required', 'string'],
            'channel' => ['required', 'string', 'in:email,sms,whatsapp,all'],
            'opt_in_marketing' => ['required', 'boolean'],
            'reason' => ['nullable', 'string'],
        ]);

        $companyId = auth()->user()->company_id;

        CustomerCommunicationPreference::updateOrCreate(
            [
                'company_id' => $companyId,
                'recipient' => $request->recipient,
                'channel' => $request->channel,
            ],
            [
                'opt_in_marketing' => $request->boolean('opt_in_marketing'),
                'opt_out_reason' => $request->reason,
                'opted_out_at' => $request->boolean('opt_in_marketing') ? null : now(),
            ]
        );

        return back()->with('success', "Communication preference for '{$request->recipient}' saved.");
    }
}
