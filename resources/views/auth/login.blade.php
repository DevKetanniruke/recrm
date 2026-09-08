<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Real Estate CRM System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/crm.css">
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 1.25rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 440px;
            padding: 2.5rem;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="text-center mb-4">
            <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width:60px; height:60px;">
                <i class="bi bi-building-fill fs-2"></i>
            </div>
            <h3 class="brand-font fw-bold text-dark mb-1">PropFlow CRM</h3>
            <p class="text-secondary small">Real Estate Developer Portal</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger small py-2 mb-3">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ url('login') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label text-secondary small fw-semibold">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-secondary"></i></span>
                    <input type="email" name="email" id="emailInput" class="form-control bg-light border-start-0" value="admin@recrm.com" required autofocus>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label text-secondary small fw-semibold">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-secondary"></i></span>
                    <input type="password" name="password" id="passwordInput" class="form-control bg-light border-start-0" value="password" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold mb-3">
                Sign In to Dashboard <i class="bi bi-arrow-right ms-1"></i>
            </button>
        </form>

        <div class="border-top pt-3 text-center">
            <p class="text-secondary small mb-2">Quick Demo Quick-Select:</p>
            <div class="d-flex justify-content-center gap-2">
                <button type="button" class="btn btn-xs btn-outline-secondary btn-sm" onclick="setRole('admin@recrm.com')">Admin</button>
                <button type="button" class="btn btn-xs btn-outline-secondary btn-sm" onclick="setRole('agent@recrm.com')">Sales Agent</button>
                <button type="button" class="btn btn-xs btn-outline-secondary btn-sm" onclick="setRole('accountant@recrm.com')">Accountant</button>
            </div>
        </div>
    </div>

    <script>
        function setRole(email) {
            document.getElementById('emailInput').value = email;
            document.getElementById('passwordInput').value = 'password';
        }
    </script>
</body>
</html>
