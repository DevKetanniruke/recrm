<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>429 - Too Many Requests | PropFlow CRM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/crm.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100 p-3">
    <div class="card border-0 shadow-lg rounded-4 p-4 text-center" style="max-width: 480px; width: 100%;">
        <div class="stat-icon bg-warning bg-opacity-10 text-warning mx-auto mb-3 p-3 rounded-circle" style="width: 70px; height: 70px; font-size: 2rem;">
            <i class="bi bi-speedometer"></i>
        </div>
        <h2 class="fw-bold brand-font text-dark mb-2">429 - Rate Limit Exceeded</h2>
        <p class="text-secondary small mb-4">You have sent too many requests in a short period of time. Please wait a minute before making additional requests.</p>
        <div>
            <a href="{{ url('/dashboard') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
                <i class="bi bi-house-door-fill me-1"></i> Back to Dashboard
            </a>
        </div>
    </div>
</body>
</html>
