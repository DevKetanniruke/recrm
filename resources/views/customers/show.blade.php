@extends('layouts.app')

@section('title', $customer->full_name . ' - Customer 360 View')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('customers.index') }}" class="text-decoration-none text-muted small"><i class="bi bi-arrow-left"></i> Customer Registry</a>
            <div class="d-flex align-items-center gap-2 mt-1">
                <h3 class="fw-bold text-dark mb-0">{{ $customer->full_name }}</h3>
                <span class="badge bg-light text-secondary border fs-6 fw-normal">{{ $customer->customer_number }}</span>
                @if($customer->kyc_status == 'Verified')
                    <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1"><i class="bi bi-shield-check me-1"></i> KYC Verified</span>
                @else
                    <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-3 py-1"><i class="bi bi-clock-history me-1"></i> KYC {{ $customer->kyc_status }}</span>
                @endif
            </div>
        </div>
        <div class="d-flex gap-2">
            <button type="button" data-bs-toggle="modal" data-bs-target="#uploadDocModal" class="btn btn-outline-primary rounded-pill px-3">
                <i class="bi bi-upload me-1"></i> Upload Document
            </button>
            <a href="{{ route('bookings.create', ['customer_id' => $customer->id]) }}" class="btn btn-primary rounded-pill px-4">
                <i class="bi bi-journal-plus me-1"></i> New Unit Booking
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Profile Details -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-person-circle text-primary me-2"></i> Primary Info</h5>
                    <div class="mb-3">
                        <label class="text-muted small d-block">Full Name</label>
                        <span class="fw-bold text-dark fs-6">{{ $customer->full_name }}</span>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small d-block">Contact</label>
                        <span class="fw-semibold text-dark"><i class="bi bi-telephone me-1"></i> {{ $customer->primary_mobile }}</span>
                        <div class="small text-muted"><i class="bi bi-envelope me-1"></i> {{ $customer->email }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small d-block">PAN Card</label>
                        <span class="fw-bold text-uppercase">{{ $customer->PAN ?? $customer->pan_number ?? 'Not Provided' }}</span>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small d-block">Occupation / Employer</label>
                        <span class="fw-semibold text-dark">{{ $customer->occupation ?: 'N/A' }}</span>
                        @if($customer->company_or_employer)
                            <div class="small text-muted">at {{ $customer->company_or_employer }}</div>
                        @endif
                    </div>
                    <div class="mb-0">
                        <label class="text-muted small d-block">Address</label>
                        <span class="small text-dark">{{ $customer->address ?: 'N/A' }}</span>
                        @if($customer->city)
                            <div class="small text-muted">{{ implode(', ', array_filter([$customer->city, $customer->state, $customer->pincode, $customer->country])) }}</div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- KYC Status Toggle Card -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-shield-lock text-warning me-2"></i> KYC Verification Status</h6>
                    <form action="{{ route('customers.update-kyc', $customer->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <select name="kyc_status" class="form-select rounded-pill">
                                <option value="Pending" {{ $customer->kyc_status == 'Pending' ? 'selected' : '' }}>Pending</option>
                                <option value="Verified" {{ $customer->kyc_status == 'Verified' ? 'selected' : '' }}>Verified</option>
                                <option value="Rejected" {{ $customer->kyc_status == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-sm btn-dark w-100 rounded-pill">Update KYC Status</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Side: Co-Applicants, Documents Vault & Bookings -->
        <div class="col-lg-8">
            <!-- Co-Applicants Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-dark mb-0"><i class="bi bi-people text-info me-2"></i> Co-Applicants</h5>
                    <button type="button" data-bs-toggle="modal" data-bs-target="#addCoModal" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                        <i class="bi bi-plus-lg me-1"></i> Add Co-Applicant
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="bg-light text-muted small">
                                <tr>
                                    <th class="ps-4">Co-Applicant Name</th>
                                    <th>Relationship</th>
                                    <th>Contact</th>
                                    <th>Ownership %</th>
                                    <th class="text-end pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($customer->coApplicants as $co)
                                    <tr>
                                        <td class="ps-4 fw-bold text-dark">{{ $co->customer_name }}</td>
                                        <td><span class="badge bg-light text-dark border">{{ $co->relationship }}</span></td>
                                        <td class="small">{{ $co->mobile ?: $co->email ?: 'N/A' }}</td>
                                        <td class="fw-semibold text-primary">{{ number_format($co->ownership_percentage, 2) }}%</td>
                                        <td class="text-end pe-4">
                                            <form action="{{ route('co-applicants.destroy', $co->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove co-applicant?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger border-0"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted small">No co-applicants added for this customer.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Secure Customer Document Vault Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-dark mb-0"><i class="bi bi-file-earmark-lock text-danger me-2"></i> Encrypted Document Vault</h5>
                    <button type="button" data-bs-toggle="modal" data-bs-target="#uploadDocModal" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                        <i class="bi bi-upload me-1"></i> Upload File
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="bg-light text-muted small">
                                <tr>
                                    <th class="ps-4">Document Type</th>
                                    <th>File Name</th>
                                    <th>Uploaded By</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($customer->documents as $doc)
                                    <tr>
                                        <td class="ps-4"><span class="badge bg-light text-secondary border">{{ $doc->document_type }}</span></td>
                                        <td class="fw-semibold text-dark small">{{ $doc->file_name }}</td>
                                        <td class="small text-muted">{{ $doc->uploader?->name ?: 'System' }}</td>
                                        <td>
                                            @if($doc->verification_status == 'Verified')
                                                <span class="badge bg-success-subtle text-success">Verified</span>
                                            @else
                                                <span class="badge bg-warning-subtle text-warning-emphasis">Pending</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="{{ route('customer-documents.download', $doc->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                <i class="bi bi-download me-1"></i> Download
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted small">No identity or legal documents uploaded yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Customer Bookings History -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="fw-bold text-dark mb-0"><i class="bi bi-journal-check text-success me-2"></i> Unit Booking History</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="bg-light text-muted small">
                                <tr>
                                    <th class="ps-4">Booking #</th>
                                    <th>Unit & Project</th>
                                    <th>Agreed Price</th>
                                    <th>Booking Date</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($customer->bookings as $bkg)
                                    <tr>
                                        <td class="ps-4 fw-bold text-dark">{{ $bkg->booking_number }}</td>
                                        <td>
                                            <div class="fw-bold text-primary">Unit #{{ $bkg->unit?->unit_number }}</div>
                                            <div class="small text-muted">{{ $bkg->unit?->project?->name }}</div>
                                        </td>
                                        <td class="fw-bold text-dark">₹{{ number_format($bkg->agreed_price, 2) }}</td>
                                        <td class="small text-muted">{{ $bkg->booking_date ? $bkg->booking_date->format('d M Y') : 'N/A' }}</td>
                                        <td>
                                            @if($bkg->status == 'Confirmed')
                                                <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1">Confirmed</span>
                                            @elseif($bkg->status == 'Cancelled')
                                                <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-1">Cancelled</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-1">{{ $bkg->status }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="{{ route('bookings.show', $bkg->id) }}" class="btn btn-sm btn-outline-dark rounded-pill px-3">
                                                Details
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted small">No active bookings recorded for this customer.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upload Document Modal -->
<div class="modal fade" id="uploadDocModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('customer-documents.store', $customer->id) }}" method="POST" enctype="multipart/form-data" class="modal-content rounded-4 border-0">
            @csrf
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-file-earmark-arrow-up text-primary me-2"></i> Upload Customer Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Document Type <span class="text-danger">*</span></label>
                    <select name="document_type" class="form-select rounded-pill" required>
                        <option value="Identity Proof">Identity Proof (Aadhaar / Passport / Voter ID)</option>
                        <option value="PAN Card">PAN Card</option>
                        <option value="Address Proof">Address Proof</option>
                        <option value="Photograph">Passport Size Photograph</option>
                        <option value="Sale Agreement">Signed Sale Agreement</option>
                        <option value="Booking Form">Signed Booking Form</option>
                        <option value="Other">Other Document</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Select File (PDF, JPG, PNG, Max 10MB) <span class="text-danger">*</span></label>
                    <input type="file" name="document_file" class="form-control" required>
                </div>
                <div class="mb-0">
                    <label class="form-label fw-semibold">Notes / Remarks</label>
                    <textarea name="verification_notes" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Upload to Vault</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Co-Applicant Modal -->
<div class="modal fade" id="addCoModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('co-applicants.store', $customer->id) }}" method="POST" class="modal-content rounded-4 border-0">
            @csrf
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-plus text-info me-2"></i> Add Co-Applicant</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Co-Applicant Name <span class="text-danger">*</span></label>
                    <input type="text" name="customer_name" class="form-control rounded-pill" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Relationship <span class="text-danger">*</span></label>
                    <select name="relationship" class="form-select rounded-pill" required>
                        <option value="Spouse">Spouse</option>
                        <option value="Parent">Parent</option>
                        <option value="Child">Child</option>
                        <option value="Sibling">Sibling</option>
                        <option value="Business Partner">Business Partner</option>
                    </select>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">Mobile</label>
                        <input type="text" name="mobile" class="form-control rounded-pill">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Ownership %</label>
                        <input type="number" step="0.01" name="ownership_percentage" class="form-control rounded-pill" value="50.00">
                    </div>
                </div>
                <div class="mb-0">
                    <label class="form-label fw-semibold">PAN Number</label>
                    <input type="text" name="pan_number" class="form-control rounded-pill text-uppercase">
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Add Co-Applicant</button>
            </div>
        </form>
    </div>
</div>
@endsection
