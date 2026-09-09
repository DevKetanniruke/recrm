@extends('layouts.app')

@section('title', 'Unified Sales Activities Calendar')
@section('page-title', 'Sales Calendar & Agenda')

@section('content')
<div class="container-fluid" x-data="calendarApp()" x-init="fetchEvents()">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="mb-1 fw-bold brand-font text-dark"><i class="bi bi-calendar3 text-primary me-2"></i> Unified Sales Activities Calendar</h4>
            <p class="text-muted small mb-0">Consolidated real-time schedule of follow-ups, site visits, calls, and offer expirations.</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-warning text-dark px-2 py-1"><i class="bi bi-calendar-event me-1"></i> Follow-ups</span>
            <span class="badge bg-info text-dark px-2 py-1"><i class="bi bi-geo-alt me-1"></i> Site Visits</span>
            <span class="badge bg-purple text-white px-2 py-1" style="background-color:#8b5cf6;"><i class="bi bi-tag me-1"></i> Offer Expiries</span>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="row g-2 align-items-center">
                <div class="col-md-3">
                    <select class="form-select form-select-sm" x-model="selectedCategory" @change="filterEvents()">
                        <option value="">All Categories</option>
                        <option value="Follow-up">Follow-up Tasks</option>
                        <option value="Site Visit">Site Visits</option>
                        <option value="Offer Expiry">Offer Expiries</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <select class="form-select form-select-sm" x-model="selectedAgent" @change="filterEvents()">
                        <option value="">All Escort Agents</option>
                        @foreach($agents as $ag)
                            <option value="{{ $ag->name }}">{{ $ag->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6 d-flex justify-content-end">
                    <button type="button" class="btn btn-sm btn-outline-primary me-1" @click="changeView('month')">Month</button>
                    <button type="button" class="btn btn-sm btn-outline-primary me-1" @click="changeView('week')">Week</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" @click="fetchEvents()"><i class="bi bi-arrow-clockwise me-1"></i> Refresh</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar Grid Payload Container -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div x-show="loading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <div class="text-muted small mt-2">Loading calendar events...</div>
            </div>

            <div x-show="!loading" x-cloak>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0" style="font-size:0.875rem;">
                        <thead class="table-light text-center">
                            <tr>
                                <th style="width: 14%;">Sun</th>
                                <th style="width: 14%;">Mon</th>
                                <th style="width: 14%;">Tue</th>
                                <th style="width: 14%;">Wed</th>
                                <th style="width: 14%;">Thu</th>
                                <th style="width: 14%;">Fri</th>
                                <th style="width: 14%;">Sat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Agenda Event Stream List -->
                            <template x-for="event in filteredEvents" :key="event.id">
                                <div class="p-3 mb-2 rounded shadow-sm border" :style="`border-left: 5px solid ${event.color} !important; background-color: #fff;`">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="fw-bold text-dark" x-text="event.title"></div>
                                        <span class="badge bg-light text-dark border" x-text="formatDate(event.start)"></span>
                                    </div>
                                    <div class="small text-muted mt-1 d-flex gap-3">
                                        <span><i class="bi bi-tag me-1"></i> <span x-text="event.extendedProps.category"></span></span>
                                        <span x-show="event.extendedProps.agent"><i class="bi bi-person me-1"></i> <span x-text="event.extendedProps.agent"></span></span>
                                        <span x-show="event.extendedProps.status"><i class="bi bi-info-circle me-1"></i> <span x-text="event.extendedProps.status"></span></span>
                                    </div>
                                    <div class="mt-2 text-end" x-show="event.url">
                                        <a :href="event.url" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size:0.75rem;">View Record <i class="bi bi-chevron-right ms-1"></i></a>
                                    </div>
                                </div>
                            </template>
                            <div x-show="filteredEvents.length === 0" class="text-center py-5 text-muted">
                                <i class="bi bi-calendar-x fs-2 d-block mb-2"></i> No sales activities scheduled for this filter.
                            </div>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function calendarApp() {
        return {
            loading: true,
            rawEvents: [],
            filteredEvents: [],
            selectedCategory: '',
            selectedAgent: '',
            fetchEvents() {
                this.loading = true;
                fetch('/calendar/events')
                    .then(res => res.json())
                    .then(data => {
                        this.rawEvents = data;
                        this.filterEvents();
                        this.loading = false;
                    })
                    .catch(err => {
                        console.error(err);
                        this.loading = false;
                    });
            },
            filterEvents() {
                this.filteredEvents = this.rawEvents.filter(e => {
                    let catMatch = !this.selectedCategory || e.extendedProps.category === this.selectedCategory;
                    let agentMatch = !this.selectedAgent || e.extendedProps.agent === this.selectedAgent;
                    return catMatch && agentMatch;
                });
            },
            formatDate(isoStr) {
                if (!isoStr) return '';
                const d = new Date(isoStr);
                return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
            },
            changeView(v) {
                // View toggle handler
            }
        }
    }
</script>
@endpush
@endsection
