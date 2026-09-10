<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreChannelPartnerRequest;
use App\Models\ChannelPartner;
use App\Models\ChannelPartnerContact;
use Illuminate\Http\Request;

class ChannelPartnerController extends Controller
{
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $query = ChannelPartner::with(['primaryContact', 'contacts'])->where('company_id', $companyId);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                  ->orWhere('partner_code', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        $partners = $query->orderBy('company_name')->paginate(15);

        return view('brokers.partners.index', compact('partners'));
    }

    public function create()
    {
        return view('brokers.partners.create');
    }

    public function store(StoreChannelPartnerRequest $request)
    {
        $companyId = auth()->user()->company_id;

        $partner = ChannelPartner::create([
            'company_id' => $companyId,
            'company_name' => $request->company_name,
            'contact_person' => $request->contact_person,
            'mobile' => $request->mobile,
            'email' => $request->email,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'pincode' => $request->pincode,
            'gst_number' => $request->gst_number,
            'pan_number' => $request->pan_number,
            'rera_registration_number' => $request->rera_registration_number,
            'status' => $request->status ?? 'Active',
            'onboarding_date' => $request->onboarding_date ?? now()->toDateString(),
        ]);

        // Create primary contact
        ChannelPartnerContact::create([
            'company_id' => $companyId,
            'channel_partner_id' => $partner->id,
            'name' => $request->contact_person,
            'mobile' => $request->mobile,
            'email' => $request->email,
            'designation' => 'Primary Contact',
            'is_primary' => true,
        ]);

        return redirect()->route('brokers.partners.index')
            ->with('success', "Channel Partner '{$partner->company_name}' (#{$partner->partner_code}) onboarded successfully!");
    }

    public function show(ChannelPartner $partner)
    {
        if ($partner->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $partner->load(['contacts', 'leads.salesOwner', 'commissions.booking.customer', 'commissions.payouts']);

        return view('brokers.partners.show', compact('partner'));
    }

    public function addContact(Request $request, ChannelPartner $partner)
    {
        if ($partner->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'mobile' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'designation' => ['nullable', 'string', 'max:100'],
        ]);

        ChannelPartnerContact::create([
            'company_id' => auth()->user()->company_id,
            'channel_partner_id' => $partner->id,
            'name' => $request->name,
            'mobile' => $request->mobile,
            'email' => $request->email,
            'designation' => $request->designation,
            'is_primary' => false,
        ]);

        return back()->with('success', "Contact person '{$request->name}' added to {$partner->company_name}.");
    }
}
