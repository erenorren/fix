<?php
// views/login.php - SIMPLE VERSION
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Sistem Penitipan Hewan</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.min.css">
    
    <style>
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
    </style>
</head>
<body class="login-page">

<div class="login-box">
    <div class="card card-outline card-primary shadow-lg border-0">
        <div class="card-header text-center py-3">
            <h4 class="login-title">Sistem Penitipan Hewan</h4>
        </div>
        
        <div class="card-body p-4">
            <p class="login-box-msg text-muted">Silakan login untuk masuk sistem</p>

            <div id="alert-container"></div>

            <form id="loginForm">
                <div class="input-group mb-3">
                    <input type="text" name="username" class="form-control" placeholder="Username" required autofocus>
                    <div class="input-group-text">
                        <i class="bi bi-person-fill"></i>
                    </div>
                </div>

                <div class="input-group mb-3">
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                    <div class="input-group-text">
                        <i class="bi bi-lock-fill"></i>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-lg w-100 shadow-sm">Sign In</button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    
    loginForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        // Show loading
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Loading...';
        
        // Get form data
        const formData = new FormData(this);
        
        try {
            // ✅ SIMPLE FETCH - NO COMPLEX URL
            const response = await fetch('index.php?action=login', {
                method: 'POST',
                body: formData
            });
            
            // Cek jika response OK
            if (!response.ok) {
                throw new Error('HTTP error: ' + response.status);
            }
            
            // Parse JSON
            const result = await response.json();
            console.log('Login result:', result);
            
            if (result.success) {
                // Redirect ke dashboard
                window.location.href = result.redirect;
            } else {
                alert('Login gagal: ' + result.message);
            }
            
        } catch (error) {
            console.error('Login error:', error);
            alert('Error: ' + error.message + '\nCoba refresh halaman atau gunakan:\nUsername: admin\nPassword: admin123');
            
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
});
</script>
</body>
</html>