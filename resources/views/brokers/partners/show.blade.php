@extends('layouts.app')

@section('title', 'Channel Partner Profile - ' . $partner->company_name)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h3 class="fw-bold mb-0">{{ $partner->company_name }}</h3>
                <span class="badge bg-primary font-monospace fs-6">{{ $partner->partner_code }}</span>
                @if($partner->status === 'Active')
                    <span class="badge bg-success">Active</span>
                @elseif($partner->status === 'Suspended')
                    <span class="badge bg-warning text-dark">Suspended</span>
                @else
                    <span class="badge bg-danger">{{ $partner->status }}</span>
                @endif
            </div>
            <p class="text-muted small mb-0">Onboarded {{ $partner->onboarding_date ? $partner->onboarding_date->format('M d, Y') : '-' }}</p>
        </div>
        <a href="{{ route('brokers.partners.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Registry
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Agency Info -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3 border-bottom pb-2">Agency Details</h5>
                    <div class="mb-2"><strong>Primary Contact:</strong> {{ $partner->contact_person }}</div>
                    <div class="mb-2"><strong>Mobile:</strong> {{ $partner->mobile }}</div>
                    <div class="mb-2"><strong>Email:</strong> {{ $partner->email ?: 'N/A' }}</div>
                    <div class="mb-2"><strong>RERA Reg:</strong> {{ $partner->rera_registration_number ?: 'N/A' }}</div>
                    <div class="mb-2"><strong>GST:</strong> {{ $partner->gst_number ?: 'N/A' }}</div>
                    <div class="mb-2"><strong>PAN:</strong> {{ $partner->pan_number ?: 'N/A' }}</div>
                    <div class="mb-0"><strong>Address:</strong> {{ $partner->address }}, {{ $partner->city }}</div>
                </div>
            </div>

            <!-- Additional Contacts -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">Agency Contacts ({{ $partner->contacts->count() }})</h6>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addContactModal">
                        <i class="bi bi-plus"></i> Add
                    </button>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($partner->contacts as $contact)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold">{{ $contact->name }} @if($contact->is_primary) <span class="badge bg-info text-dark ms-1">Primary</span> @endif</div>
                                    <small class="text-muted">{{ $contact->designation ?: 'Representative' }} | {{ $contact->mobile }}</small>
                                </div>
                            </li>
                        @empty
                            <li class="list-group-item text-center text-muted py-3">No secondary contacts added.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <!-- Attributed Leads & Commissions -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-light">
                    <h6 class="fw-bold mb-0">Attributed Leads ({{ $partner->leads->count() }})</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="bg-light">
                                <tr>
                                    <th>Lead Name</th>
                                    <th>Contact</th>
                                    <th>Assigned Executive</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($partner->leads as $lead)
                                    <tr>
                                        <td class="fw-bold">{{ $lead->first_name }} {{ $lead->last_name }}</td>
                                        <td>{{ $lead->mobile }}</td>
                                        <td>{{ $lead->salesOwner?->name ?? 'Unassigned' }}</td>
                                        <td><span class="badge bg-secondary">{{ $lead->status }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-3">No leads attributed to this channel partner.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-light">
                    <h6 class="fw-bold mb-0">Commission Ledger ({{ $partner->commissions->count() }})</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="bg-light">
                                <tr>
                                    <th>Booking Ref</th>
                                    <th>Agreed Value</th>
                                    <th>Rate</th>
                                    <th>Approved Comm.</th>
                                    <th>Paid</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($partner->commissions as $comm)
                                    <tr>
                                        <td class="fw-bold">{{ $comm->booking?->booking_number }}</td>
                                        <td>₹{{ number_format($comm->agreement_value, 2) }}</td>
                                        <td>{{ $comm->commission_percentage }}%</td>
                                        <td class="fw-bold text-success">₹{{ number_format($comm->approved_commission_amount, 2) }}</td>
                                        <td>₹{{ number_format($comm->paid_amount, 2) }}</td>
                                        <td>
                                            @if($comm->status === 'Paid')
                                                <span class="badge bg-success">Paid</span>
                                            @elseif($comm->status === 'Approved')
                                                <span class="badge bg-primary">Approved</span>
                                            @else
                                                <span class="badge bg-warning text-dark">{{ $comm->status }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-3">No commissions generated for this partner yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add Contact -->
<div class="modal fade" id="addContactModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Add Agency Contact</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('brokers.partners.add-contact', $partner) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Contact Person Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Mobile</label>
                        <input type="text" name="mobile" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Designation</label>
                        <input type="text" name="designation" class="form-control" placeholder="e.g. Sales Manager / Partner">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Contact</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
