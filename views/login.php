<?php
// views/login.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Sistem Penitipan Hewan</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0-beta1/dist/css/adminlte.min.css">

    <style>
        /* Custom CSS */
        body.login-page {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #0265FE !important; 
        }
        .login-box {
            width: 360px;
        }
        .login-title {
            font-size: 1.5rem; 
            font-weight: 500; 
            color: #212529; 
            text-decoration: none;
        }
    </style>
</head>
<body class="login-page">

<div class="login-box">
    
    <div class="card card-outline card-primary shadow-lg border-0">
        <div class="card-header text-center py-3">
            <a href="#" class="login-title">
                Sistem Penitipan Hewan
            </a>
        </div>
        
        <div class="card-body p-4">
            <p class="login-box-msg text-muted">Silakan login untuk masuk sistem</p>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-exclamation-circle-fill me-2"></i>
                    <?= $_SESSION['error_message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <div id="alert-container"></div>

            <form id="loginForm" action="index.php?action=login" method="post">
                <div class="input-group mb-3">
                    <input type="text" name="username" class="form-control form-control-lg bg-light" placeholder="Username" required autofocus>
                    <div class="input-group-text bg-light border-start-0 text-muted">
                        <span class="bi bi-person-fill"></span>
                    </div>
                </div>

                <div class="input-group mb-3">
                    <input type="password" name="password" class="form-control form-control-lg bg-light" placeholder="Password" required>
                    <div class="input-group-text bg-light border-start-0 text-muted">
                        <span class="bi bi-lock-fill"></span>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-lg w-100 shadow-sm fw-bold">Sign In</button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0-beta1/dist/js/adminlte.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const alertContainer = document.getElementById('alert-container');
    
    if (loginForm) {
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Show loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Memproses...';
            
            // Clear alerts
            if (alertContainer) alertContainer.innerHTML = '';
            
            // Prepare data
            const formData = {
                username: this.querySelector('[name="username"]').value.trim(),
                password: this.querySelector('[name="password"]').value
            };
            
            try {
                // Send as JSON (better for Vercel)
                const response = await fetch('index.php?action=login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(formData),
                    credentials: 'include' // IMPORTANT for Vercel sessions
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Redirect with delay
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 300);
                } else {
                    showError(data.message);
                }
                
            } catch (error) {
                console.error('Login error:', error);
                
                // Different error messages
                let errorMsg = 'Terjadi kesalahan koneksi. ';
                if (error.message.includes('Failed to fetch')) {
                    errorMsg += 'Tidak dapat terhubung ke server. ';
                }
                errorMsg += 'Silakan coba lagi.';
                
                showError(errorMsg);
                
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
        
        function showError(message) {
            if (!alertContainer) return;
            
            const alertHTML = `
                <div class="alert alert-danger alert-dismissible fade show mb-3">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            alertContainer.innerHTML = alertHTML;
        }
    }
});
</script>
</body>
</html>