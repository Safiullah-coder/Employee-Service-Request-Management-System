<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | Service Desk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: radial-gradient(circle at 15% 15%, #355987, #172b4d 62%); min-height: 100vh; display:flex; align-items:center; }
        .login-card { border: 0; border-radius: 18px; box-shadow: 0 24px 70px rgba(0,0,0,.25); }
        .login-logo { width:64px; height:64px; background:#172b4d; border-radius:18px; display:flex; align-items:center; justify-content:center; margin:0 auto 15px; }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card login-card p-4">
                <div class="text-center mb-3">
                    <div class="login-logo">
                        <i class="bi bi-headset text-white fs-2"></i>
                    </div>
                    <h4 class="fw-bold">Service Desk</h4>
                    <p class="text-muted small">Employee Service Request Portal</p>
                </div>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-circle me-1"></i><?= session()->getFlashdata('error') ?>
                    </div>
                <?php endif; ?>
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle me-1"></i><?= session()->getFlashdata('success') ?>
                    </div>
                <?php endif; ?>

                <form action="<?= site_url('login') ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" class="form-control" placeholder="email@company.com" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" class="form-control"  required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Sign in
                    </button>
                </form>
                <hr>
                <!-- <p class="text-muted small text-center mb-0">
                    Demo accounts — Password: <code>password123</code><br>
                    Admin: admin@company.com | Agent: it@company.com<br>
                    Employee: ali@company.com
                </p> -->
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
