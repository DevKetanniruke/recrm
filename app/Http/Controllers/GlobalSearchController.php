<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Project;
use App\Models\Unit;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    /**
     * Company-scoped multi-entity global search endpoint.
     */
    public function index(Request $request)
    {
        $query = trim($request->get('q', ''));
        $companyId = auth()->user()->company_id;

        if (strlen($query) < 2) {
            return response()->json([
                'query' => $query,
                'results' => [],
            ]);
        }

        $results = [];

        // 1. Leads Search
        if (auth()->user()->hasPermissionTo('leads.view')) {
            $leads = Lead::where('company_id', $companyId)
                ->whereNull('merged_into_lead_id')
                ->where(function ($q) use ($query) {
                    $q->where('lead_number', 'LIKE', "%{$query}%")
                      ->orWhere('first_name', 'LIKE', "%{$query}%")
                      ->orWhere('last_name', 'LIKE', "%{$query}%")
                      ->orWhere('mobile', 'LIKE', "%{$query}%")
                      ->orWhere('email', 'LIKE', "%{$query}%");
                })
                ->take(5)
                ->get();

            foreach ($leads as $l) {
                $results[] = [
                    'category' => 'Leads',
                    'title' => "{$l->full_name} ({$l->lead_number})",
                    'subtitle' => "Mobile: {$l->mobile} | Status: {$l->status}",
                    'url' => route('leads.show', $l->id),
                    'icon' => 'bi-person-lines-fill',
                    'badge' => 'Lead',
                    'badge_class' => 'bg-primary',
                ];
            }
        }

        // 2. Customers Search
        if (auth()->user()->hasPermissionTo('customers.view')) {
            $customers = Customer::where('company_id', $companyId)
                ->where(function ($q) use ($query) {
                    $q->where('customer_number', 'LIKE', "%{$query}%")
                      ->orWhere('first_name', 'LIKE', "%{$query}%")
                      ->orWhere('last_name', 'LIKE', "%{$query}%")
                      ->orWhere('mobile', 'LIKE', "%{$query}%")
                      ->orWhere('email', 'LIKE', "%{$query}%");
                })
                ->take(5)
                ->get();

            foreach ($customers as $c) {
                $results[] = [
                    'category' => 'Customers',
                    'title' => "{$c->full_name} ({$c->customer_number})",
                    'subtitle' => "Mobile: {$c->mobile} | Email: {$c->email}",
                    'url' => route('customers.show', $c->id),
                    'icon' => 'bi-person-badge-fill',
                    'badge' => 'Customer',
                    'badge_class' => 'bg-success',
                ];
            }
        }

        // 3. Projects Search
        if (auth()->user()->hasPermissionTo('projects.view')) {
            $projects = Project::where('company_id', $companyId)
                ->where(function ($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%")
                      ->orWhere('code', 'LIKE', "%{$query}%");
                })
                ->take(5)
                ->get();

            foreach ($projects as $p) {
                $results[] = [
                    'category' => 'Projects',
                    'title' => "{$p->name} ({$p->code})",
                    'subtitle' => "Status: {$p->status}",
                    'url' => route('projects.show', $p->id),
                    'icon' => 'bi-buildings-fill',
                    'badge' => 'Project',
                    'badge_class' => 'bg-info',
                ];
            }
        }

        // 4. Units Search
        if (auth()->user()->hasPermissionTo('inventory.view')) {
            $units = Unit::where('company_id', $companyId)
                ->where('unit_number', 'LIKE', "%{$query}%")
                ->with('project')
                ->take(5)
                ->get();

            foreach ($units as $u) {
                $results[] = [
                    'category' => 'Property Units',
                    'title' => "Unit #{$u->unit_number}",
                    'subtitle' => "Project: " . ($u->project?->name ?? 'N/A') . " | Status: {$u->status}",
                    'url' => route('inventory.grid') . "?search={$u->unit_number}",
                    'icon' => 'bi-grid-3x3-gap-fill',
                    'badge' => 'Unit',
                    'badge_class' => 'bg-warning text-dark',
                ];
            }
        }

        // 5. Bookings Search
        if (auth()->user()->hasPermissionTo('bookings.view')) {
            $bookings = Booking::where('company_id', $companyId)
                ->where('booking_number', 'LIKE', "%{$query}%")
                ->with(['customer', 'unit'])
                ->take(5)
                ->get();

            foreach ($bookings as $b) {
                $results[] = [
                    'category' => 'Bookings',
                    'title' => "Booking {$b->booking_code}",
                    'subtitle' => "Customer: " . ($b->customer?->full_name ?? 'N/A') . " | Status: {$b->status}",
                    'url' => route('bookings.show', $b->id),
                    'icon' => 'bi-file-earmark-check-fill',
                    'badge' => 'Booking',
                    'badge_class' => 'bg-indigo text-white',
                ];
            }
        }

        // 6. Payments Search
        if (auth()->user()->hasPermissionTo('payments.view')) {
            $payments = Payment::where('company_id', $companyId)
                ->where('receipt_number', 'LIKE', "%{$query}%")
                ->with('booking.customer')
                ->take(5)
                ->get();

            foreach ($payments as $pay) {
                $results[] = [
                    'category' => 'Payments',
                    'title' => "Receipt {$pay->receipt_number}",
                    'subtitle' => "Amount: ₹" . number_format($pay->amount_paid, 2) . " | Mode: {$pay->payment_mode}",
                    'url' => route('payments.show', $pay->id),
                    'icon' => 'bi-receipt',
                    'badge' => 'Payment',
                    'badge_class' => 'bg-teal text-white',
                ];
            }
        }

        return response()->json([
            'query' => $query,
            'count' => count($results),
            'results' => $results,
        ]);
    }
}
