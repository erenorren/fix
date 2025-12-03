<?php
// views/login.php - TIDAK DIUBAH TAMPILAN, hanya backend
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

            <form id="loginForm">
                <div class="input-group mb-3">
                    <input type="text" name="username" class="form-control form-control-lg bg-light" 
                           placeholder="Username" required autofocus>
                    <div class="input-group-text bg-light border-start-0 text-muted">
                        <span class="bi bi-person-fill"></span>
                    </div>
                </div>

                <div class="input-group mb-3">
                    <input type="password" name="password" class="form-control form-control-lg bg-light" 
                           placeholder="Password" required>
                    <div class="input-group-text bg-light border-start-0 text-muted">
                        <span class="bi bi-lock-fill"></span>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-lg w-100 shadow-sm fw-bold" id="loginBtn">
                            <span id="btnText">Sign In</span>
                            <span id="btnSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
                        </button>
                    </div>
                </div>
            </form>

            <!-- Info untuk testing -->
            <div class="text-center mt-3">
                <small class="text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    Gunakan: <strong>admin</strong> / <strong>password123</strong>
                </small>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0-beta1/dist/js/adminlte.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const loginBtn = document.getElementById('loginBtn');
    const btnText = document.getElementById('btnText');
    const btnSpinner = document.getElementById('btnSpinner');
    const alertContainer = document.getElementById('alert-container');
    
    if (loginForm) {
        console.log('Login form ready');
        
        // Auto-fill for testing (bisa dihapus di production)
        document.querySelector('input[name="username"]').value = 'admin';
        document.querySelector('input[name="password"]').value = 'password123';
        
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            console.log('Login form submitted');
            
            // Clear previous alerts
            if (alertContainer) alertContainer.innerHTML = '';
            
            // Get form data
            const formData = new FormData(this);
            const username = formData.get('username').trim();
            const password = formData.get('password');
            
            // Validation
            if (!username || !password) {
                showAlert('Username dan password harus diisi', 'danger');
                return;
            }
            
            // Show loading
            loginBtn.disabled = true;
            btnText.textContent = 'Memproses...';
            btnSpinner.classList.remove('d-none');
            
            try {
                console.log('Sending login request for:', username);
                
                // Use FormData untuk compatibility
                const response = await fetch('index.php?action=login', {
                    method: 'POST',
                    body: formData,
                    credentials: 'include' // SANGAT PENTING untuk session di Vercel
                });
                
                console.log('Response status:', response.status);
                
                // Cek content type
                const contentType = response.headers.get('content-type');
                console.log('Content-Type:', contentType);
                
                let data;
                if (contentType && contentType.includes('application/json')) {
                    data = await response.json();
                } else {
                    const text = await response.text();
                    console.log('Non-JSON response:', text);
                    throw new Error('Server returned non-JSON response');
                }
                
                console.log('Response data:', data);
                
                if (data.success) {
                    console.log('Login successful, redirecting to:', data.redirect);
                    
                    // Show success message
                    showAlert('Login berhasil! Mengalihkan...', 'success');
                    
                    // Wait a moment then redirect
                    setTimeout(() => {
                        window.location.href = data.redirect || 'index.php?page=dashboard';
                    }, 1000);
                    
                } else {
                    console.log('Login failed:', data.message);
                    showAlert(data.message || 'Username atau password salah', 'danger');
                    loginBtn.disabled = false;
                    btnText.textContent = 'Sign In';
                    btnSpinner.classList.add('d-none');
                }
                
            } catch (error) {
                console.error('Login error:', error);
                showAlert('Terjadi kesalahan koneksi. Coba refresh halaman.', 'danger');
                loginBtn.disabled = false;
                btnText.textContent = 'Sign In';
                btnSpinner.classList.add('d-none');
            }
        });
        
        function showAlert(message, type = 'danger') {
            if (!alertContainer) return;
            
            const alertId = 'alert-' + Date.now();
            const icon = type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill';
            
            const alertHTML = `
                <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi ${icon} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            
            alertContainer.innerHTML = alertHTML;
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                const alertEl = document.getElementById(alertId);
                if (alertEl) {
                    alertEl.remove();
                }
            }, 5000);
        }
        
        // Test connection on load
        console.log('Testing connection...');
        fetch('.', { credentials: 'include' })
            .then(res => console.log('Connection OK:', res.status))
            .catch(err => console.warn('Connection test:', err));
    }
});
</script>
</body>
</html>