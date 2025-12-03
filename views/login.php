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

<!-- Di bagian JS login.php, GANTI script dengan ini: -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const alertContainer = document.getElementById('alert-container');
    
    if (loginForm) {
        console.log('Login form loaded');
        
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            console.log('Form submitted');
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Show loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Memproses...';
            
            // Clear alerts
            if (alertContainer) alertContainer.innerHTML = '';
            
            // Prepare data - menggunakan FormData sederhana
            const formData = new FormData(this);
            
            console.log('Sending login request...');
            
            try {
                // ✅ FIX 1: Gunakan URL absolute untuk Vercel
                const currentUrl = window.location.href;
                const baseUrl = currentUrl.split('?')[0]; // Hapus query string
                const loginUrl = baseUrl + '?action=login';
                
                console.log('Login URL:', loginUrl);
                
                // ✅ FIX 2: Gunakan fetch dengan headers yang sederhana
                const response = await fetch(loginUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                    },
                    body: formData, // FormData auto sets Content-Type
                    credentials: 'include' // Untuk cookies/session
                });
                
                console.log('Response status:', response.status);
                
                // Cek jika response tidak OK
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const result = await response.json();
                console.log('Response data:', result);
                
                if (result.success) {
                    // ✅ Show success message
                    if (alertContainer) {
                        alertContainer.innerHTML = `
                            <div class="alert alert-success alert-dismissible fade show mb-3">
                                <i class="bi bi-check-circle me-2"></i>
                                ${result.message}
                            </div>
                        `;
                    }
                    
                    // ✅ Redirect setelah 1 detik
                    setTimeout(() => {
                        console.log('Redirecting to:', result.redirect);
                        window.location.href = result.redirect;
                    }, 1000);
                    
                } else {
                    // ✅ Show error message
                    if (alertContainer) {
                        alertContainer.innerHTML = `
                            <div class="alert alert-danger alert-dismissible fade show mb-3">
                                <i class="bi bi-exclamation-circle me-2"></i>
                                ${result.message}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        `;
                    }
                }
                
            } catch (error) {
                console.error('Login error details:', error);
                
                // ✅ Show detailed error for debugging
                let errorMsg = 'Gagal terhubung ke server. ';
                errorMsg += 'Error: ' + error.message;
                
                if (alertContainer) {
                    alertContainer.innerHTML = `
                        <div class="alert alert-danger alert-dismissible fade show mb-3">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            ${errorMsg}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `;
                }
                
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
        
        // ✅ Test connection on load
        console.log('Testing connection...');
        fetch(window.location.href)
            .then(res => console.log('Connection test:', res.status))
            .catch(err => console.warn('Connection test failed:', err));
    }
});
</script>
</body>
</html>