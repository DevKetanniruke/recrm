<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\ChannelPartner;
use App\Models\Commission;
use App\Models\Lead;
use App\Models\SiteVisit;
use Illuminate\Http\Request;

class BrokerReportController extends Controller
{
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $partners = ChannelPartner::where('company_id', $companyId)->get();

        $selectedPartnerId = $request->channel_partner_id;

        $reportData = [];
        foreach ($partners as $partner) {
            if ($selectedPartnerId && $partner->id != $selectedPartnerId) {
                continue;
            }

            // Leads count
            $leadsCount = Lead::where('company_id', $companyId)
                ->where('channel_partner_id', $partner->id)
                ->count();

            // Site Visits count
            $visitsCount = SiteVisit::where('company_id', $companyId)
                ->whereHas('lead', function ($q) use ($partner) {
                    $q->where('channel_partner_id', $partner->id);
                })
                ->count();

            // Bookings count & value
            $bookingsQuery = Booking::where('company_id', $companyId)
                ->where(function ($q) use ($partner) {
                    $q->where('channel_partner_id', $partner->id)
                      ->orWhereHas('customer.lead', function ($lq) use ($partner) {
                          $lq->where('channel_partner_id', $partner->id);
                      });
                })
                ->where('status', 'Confirmed');

            $bookingsCount = $bookingsQuery->count();
            $bookingValue = (float) $bookingsQuery->sum('agreed_price');

            // Commissions summary
            $commissionsQuery = Commission::where('company_id', $companyId)
                ->where('channel_partner_id', $partner->id);

            $totalCommissionEarned = (float) $commissionsQuery->sum('approved_commission_amount');
            $totalCommissionPaid = (float) $commissionsQuery->sum('paid_amount');
            $totalCommissionOutstanding = max(0, $totalCommissionEarned - $totalCommissionPaid);

            $reportData[] = [
                'partner' => $partner,
                'leads_count' => $leadsCount,
                'visits_count' => $visitsCount,
                'bookings_count' => $bookingsCount,
                'booking_value' => $bookingValue,
                'commission_earned' => $totalCommissionEarned,
                'commission_paid' => $totalCommissionPaid,
                'commission_outstanding' => $totalCommissionOutstanding,
            ];
        }

        return view('brokers.reports.index', compact('partners', 'reportData'));
    }
}
