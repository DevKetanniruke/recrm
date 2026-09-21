<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PropFlow CRM - Executive Pitch Deck & Automation Overview</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            color: #0f172a;
            background: #f8fafc;
            font-size: 14px;
        }
        h1, h2, h3, h4, h5, .brand-font {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
        }
        .pitch-container {
            max-width: 900px;
            margin: 20px auto;
            background: #ffffff;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        }
        .header-banner {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #ffffff;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
        }
        .card-feature {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            background: #ffffff;
        }
        .badge-pill {
            background: #e0e7ff;
            color: #3730a3;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
        }
        @media print {
            body { background: #ffffff; margin: 0; }
            .pitch-container { box-shadow: none; padding: 0; margin: 0; max-width: 100%; }
            .no-print { display: none !important; }
            .page-break { page-break-before: always; }
        }
    </style>
</head>
<body>
    <div class="container text-end my-3 no-print" style="max-width: 900px;">
        <button onclick="window.print()" class="btn btn-primary shadow-sm rounded-pill px-4">
            <i class="bi bi-printer-fill me-2"></i> Print / Save as PDF
        </button>
    </div>

    <div class="pitch-container">
        <!-- Header Banner -->
        <div class="header-banner">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h2 class="brand-font mb-1"><i class="bi bi-building-fill text-primary me-2"></i> PropFlow CRM V1.0</h2>
                    <p class="mb-0 text-white-50 fs-6">Next-Generation Real Estate Developer & Builder Management Platform</p>
                </div>
                <div class="text-end">
                    <span class="badge bg-primary fs-6 px-3 py-2">Client Pitch & Automation Deck</span>
                </div>
            </div>
        </div>

        <!-- Executive Summary -->
        <div class="mb-5">
            <h4 class="brand-font text-primary mb-3"><i class="bi bi-rocket-takeoff me-2"></i> 1. Executive Summary & Pitch Pitch</h4>
            <div class="p-4 bg-light rounded-3 border border-primary border-opacity-25">
                <p class="fs-6 fw-semibold text-dark mb-2">"PropFlow CRM is an end-to-end financial, sales, and communication automation platform built specifically for Real Estate Developers and Builders."</p>
                <p class="text-secondary mb-0">Unlike generic CRMs, PropFlow natively unifies the entire builder lifecycle: from Facebook/Web lead capture and automated WhatsApp engagement, to inventory visual grid locking, milestone payment schedule generation, FIFO revenue collection, automated overdue demand notices, and channel partner commission calculations.</p>
            </div>
        </div>

        <!-- Key Automations Section -->
        <div class="mb-5">
            <h4 class="brand-font text-primary mb-3"><i class="bi bi-cpu-fill me-2"></i> 2. CRM Automation Capabilities</h4>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card-feature h-100">
                        <h6 class="fw-bold text-dark"><i class="bi bi-chat-left-dots-fill text-primary me-2"></i> Automated Multi-Channel Engagement</h6>
                        <ul class="text-secondary small mb-0 ps-3">
                            <li>Instant auto-reply via WhatsApp, Email & SMS on lead capture.</li>
                            <li>Contextual placeholder hydration (`{{lead_name}}`, `{{unit_number}}`, `{{due_amount}}`).</li>
                            <li>Automated pre-site visit reminders sent 24h before visits.</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card-feature h-100">
                        <h6 class="fw-bold text-dark"><i class="bi bi-diagram-3-fill text-success me-2"></i> Lead Pipeline & SLA Rules</h6>
                        <ul class="text-secondary small mb-0 ps-3">
                            <li>Round-robin auto-assignment to sales agents.</li>
                            <li>Instant duplicate phone/email detection and auto-merging.</li>
                            <li>Automated manager alerts if leads are uncontacted after 2 hours.</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card-feature h-100">
                        <h6 class="fw-bold text-dark"><i class="bi bi-cash-stack text-warning me-2"></i> Financial & Payment Automation</h6>
                        <ul class="text-secondary small mb-0 ps-3">
                            <li>Auto-generates construction & time-linked payment plans on booking.</li>
                            <li>Automated FIFO payment allocation against oldest demand notices.</li>
                            <li>Auto-calculates late interest & generates PDF Demand Notices.</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card-feature h-100">
                        <h6 class="fw-bold text-dark"><i class="bi bi-houses-fill text-danger me-2"></i> Inventory & Broker Automations</h6>
                        <ul class="text-secondary small mb-0 ps-3">
                            <li>Automated 48h unit lock expiration (releases unconfirmed holds).</li>
                            <li>Auto-calculation of broker commissions on milestone payments.</li>
                            <li>Automated partner performance scoring and payout approvals.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-break"></div>

        <!-- Developer Benefits Section -->
        <div class="mb-5">
            <h4 class="brand-font text-primary mb-3"><i class="bi bi-graph-up-arrow me-2"></i> 3. Business Benefits for Builders & Developers</h4>
            <table class="table table-bordered align-middle">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 25%;">Impact Area</th>
                        <th style="width: 40%;">CRM Solution / Feature</th>
                        <th style="width: 35%;">Tangible Business ROI</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="fw-bold text-dark"><i class="bi bi-speedometer text-primary me-2"></i> Lead Velocity</td>
                        <td>Instant 5-second multi-channel engagement & Round-Robin assignment</td>
                        <td><span class="badge bg-success bg-opacity-10 text-success">35% Increase in Conversions</span></td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-dark"><i class="bi bi-wallet2 text-success me-2"></i> Cash Collections</td>
                        <td>Automated Payment Demand Notices & FIFO allocation</td>
                        <td><span class="badge bg-success bg-opacity-10 text-success">40% Reduction in DSO (Overdues)</span></td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-dark"><i class="bi bi-grid-fill text-info me-2"></i> Inventory Velocity</td>
                        <td>Auto lock release timers & real-time visual unit matrix</td>
                        <td><span class="badge bg-info bg-opacity-10 text-info">Zero Inventory Hoarding</span></td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-dark"><i class="bi bi-person-badge text-warning me-2"></i> Channel Partners</td>
                        <td>Transparent lead attribution & auto commission rules engine</td>
                        <td><span class="badge bg-warning bg-opacity-10 text-dark">50% More Active Brokers</span></td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-dark"><i class="bi bi-shield-check text-danger me-2"></i> Multi-Tenant Governance</td>
                        <td>Role-based permissions & audit logs across companies</td>
                        <td><span class="badge bg-danger bg-opacity-10 text-danger">100% Data Security & SLA Audit</span></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Client Pitch Presentation Script -->
        <div class="mb-4">
            <h4 class="brand-font text-primary mb-3"><i class="bi bi-easel-fill me-2"></i> 4. Client Pitch Presentation Outline</h4>
            
            <div class="mb-3">
                <h6 class="fw-bold text-dark">Slide 1: The Problem Real Estate Builders Face Today</h6>
                <p class="text-secondary small">"Builders lose up to 30% of sales due to slow lead follow-ups, manual payment tracking on Excel, dead unit inventory, and broker dispute overhead."</p>
            </div>

            <div class="mb-3">
                <h6 class="fw-bold text-dark">Slide 2: The PropFlow CRM Solution</h6>
                <p class="text-secondary small">"PropFlow is purpose-built for real estate. It automates lead engagement, locks unit inventory, generates milestone demand notices, and tracks collections automatically."</p>
            </div>

            <div class="mb-3">
                <h6 class="fw-bold text-dark">Slide 3: ROI & Expected Results</h6>
                <p class="text-secondary small">"Expect 35% higher lead conversions, 40% faster revenue collection cycles, and 100% real-time visibility across all active projects and sales teams."</p>
            </div>
        </div>

        <div class="pt-3 border-top d-flex align-items-center justify-content-between text-muted small">
            <div>PropFlow Real Estate CRM V1.0 &copy; {{ date('Y') }} All Rights Reserved.</div>
            <div>Generated for Real Estate Builders & Developers</div>
        </div>
    </div>
</body>
</html>
