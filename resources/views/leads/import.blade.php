@extends('layouts.app')

@section('title', 'Bulk Lead Import & Export')
@section('page-title', 'CSV Import Engine')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold brand-font text-dark"><i class="bi bi-file-earmark-arrow-up-fill text-primary me-2"></i> Bulk Lead CSV Import & Export</h4>
            <p class="text-muted small mb-0">Import hundreds of real estate leads from Facebook Ads, MagicBricks, 99acres, or Excel sheets.</p>
        </div>
        <a href="{{ route('leads.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Pipeline
        </a>
    </div>

    @if(session('import_summary'))
        @php $summary = session('import_summary'); @endphp
        <div class="card border-0 shadow-sm mb-4 bg-light">
            <div class="card-body p-4">
                <h6 class="fw-bold text-dark mb-3"><i class="bi bi-pie-chart-fill text-primary me-2"></i> Last Import Execution Summary</h6>
                <div class="row g-3 text-center mb-3">
                    <div class="col-md-4">
                        <div class="p-3 bg-white rounded shadow-sm border border-success border-opacity-25">
                            <h3 class="fw-bold text-success mb-0">{{ $summary['success_count'] }}</h3>
                            <small class="text-muted text-uppercase fw-semibold" style="font-size:0.7rem;">Leads Imported Successfully</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-white rounded shadow-sm border border-danger border-opacity-25">
                            <h3 class="fw-bold text-danger mb-0">{{ $summary['failed_count'] }}</h3>
                            <small class="text-muted text-uppercase fw-semibold" style="font-size:0.7rem;">Failed / Invalid Rows</small>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-center justify-content-center">
                        @if(!empty($summary['failed_rows']))
                            <a href="{{ route('leads.import.failed-download') }}" class="btn btn-outline-danger btn-sm shadow-sm px-3">
                                <i class="bi bi-download me-1"></i> Download Failed Rows CSV
                            </a>
                        @else
                            <span class="badge bg-success py-2 px-3"><i class="bi bi-check-all me-1"></i> 100% Clean Import</span>
                        @endif
                    </div>
                </div>

                @if(!empty($summary['failed_rows']))
                    <div class="mt-3">
                        <h6 class="fw-bold text-danger small mb-2">Inline Error Report (Failed Rows Preview):</h6>
                        <div class="table-responsive bg-white rounded shadow-sm">
                            <table class="table table-sm table-bordered align-middle mb-0" style="font-size:0.8rem;">
                                <thead class="table-danger">
                                    <tr>
                                        <th>Row Data</th>
                                        <th>Validation Failure Reason</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(array_slice($summary['failed_rows'], 0, 10) as $fRow)
                                        <tr>
                                            <td>
                                                <code>{{ json_encode(array_diff_key($fRow, ['_error' => 1])) }}</code>
                                            </td>
                                            <td class="text-danger fw-semibold">{{ $fRow['_error'] ?? 'Validation failure' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if(count($summary['failed_rows']) > 10)
                            <small class="text-muted mt-1 d-block">Showing first 10 failed rows. Download the full failed CSV for complete details.</small>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @endif

    <div class="row g-4">
        <!-- Import Form -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-upload text-primary me-2"></i> Upload Lead CSV File</h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('leads.import.process') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label small fw-bold">Select CSV File <span class="text-danger">*</span></label>
                            <input type="file" name="csv_file" class="form-control" accept=".csv" required>
                            <small class="text-muted d-block mt-1">Maximum file size: 5MB. Must be standard CSV format.</small>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <a href="{{ route('leads.export') }}" class="btn btn-outline-success btn-sm">
                                <i class="bi bi-download me-1"></i> Export Existing Leads
                            </a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                <i class="bi bi-cloud-upload me-1"></i> Process & Import CSV
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- CSV Format Instructions -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-file-earmark-text text-primary me-2"></i> Required CSV Column Headers</h6>
                </div>
                <div class="card-body p-4 pt-0">
                    <p class="small text-muted mb-3">Ensure the first line of your CSV contains these exact header names:</p>

                    <div class="bg-dark text-light p-3 rounded font-monospace small mb-3 overflow-auto" style="font-size:0.75rem;">
                        first_name,last_name,mobile,alternate_mobile,email,city,location,source,campaign,unit_type,minimum_budget,maximum_budget,priority,status,notes
                    </div>

                    <h6 class="fw-bold text-dark small mb-2">Column Validation Rules:</h6>
                    <ul class="small text-muted ps-3 mb-0">
                        <li><code>first_name</code>: Required text.</li>
                        <li><code>mobile</code>: Required 10-digit number.</li>
                        <li><code>email</code>: Valid email address format (optional).</li>
                        <li><code>priority</code>: Must be Low, Medium, High, or Hot.</li>
                        <li><code>source</code>: Auto-matched with active Lead Sources or defaulted to Website.</li>
                        <li><code>status</code>: Auto-matched with active Lead Statuses or defaulted to New.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
