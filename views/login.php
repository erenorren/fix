<?php
// views/login.php - HARUS TANPA SPASI SEBELUM <?php

// Cek jika sudah login, redirect ke dashboard
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    header('Location: index.php?page=dashboard');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Penitipan Hewan</title>
    
    <!-- Gunakan CDN untuk reliability -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #0265FE 0%, #1e40af 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }
        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
        }
        .login-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        .form-control {
            padding: 12px;
            font-size: 16px;
        }
        .btn-login {
            padding: 12px;
            font-size: 16px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="text-center mb-4">
                <h2 class="fw-bold text-primary">
                    <i class="bi bi-heart-pulse me-2"></i>Sistem Penitipan Hewan
                </h2>
                <p class="text-muted">Silakan login untuk melanjutkan</p>
            </div>
            
            <div id="alertMessage" class="alert alert-danger d-none" role="alert"></div>
            
            <form id="loginForm" method="POST" action="javascript:void(0);">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                        <input type="text" class="form-control" id="username" name="username" 
                               placeholder="Masukkan username" required autofocus>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Masukkan password" required>
                    </div>
                </div>
                
                <div class="d-grid gap-2 mb-3">
                    <button type="submit" class="btn btn-primary btn-login" id="btnLogin">
                        <span id="btnText">Login</span>
                        <span id="btnSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
                    </button>
                </div>
                
                <div class="text-center">
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        Gunakan: <strong>admin</strong> / <strong>password123</strong>
                    </small>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            const btnLogin = document.getElementById('btnLogin');
            const btnText = document.getElementById('btnText');
            const btnSpinner = document.getElementById('btnSpinner');
            const alertMessage = document.getElementById('alertMessage');
            
            // Reset alert
            alertMessage.classList.add('d-none');
            
            // Validasi sederhana
            if (!username || !password) {
                showAlert('Username dan password harus diisi', 'danger');
                return;
            }
            
            // Tampilkan loading
            btnLogin.disabled = true;
            btnText.textContent = 'Memproses...';
            btnSpinner.classList.remove('d-none');
            
            try {
                // Kirim data login
                const formData = new FormData();
                formData.append('username', username);
                formData.append('password', password);
                
                const response = await fetch('index.php?action=login', {
                    method: 'POST',
                    body: formData,
                    credentials: 'include' // Penting untuk session cookie
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Login berhasil
                    showAlert('Login berhasil! Mengalihkan...', 'success');
                    
                    // Tunggu 1 detik lalu redirect
                    setTimeout(() => {
                        window.location.href = 'index.php?page=dashboard';
                    }, 1000);
                    
                } else {
                    // Login gagal
                    showAlert(data.message || 'Username atau password salah', 'danger');
                    btnLogin.disabled = false;
                    btnText.textContent = 'Login';
                    btnSpinner.classList.add('d-none');
                }
                
            } catch (error) {
                console.error('Error:', error);
                showAlert('Terjadi kesalahan koneksi', 'danger');
                btnLogin.disabled = false;
                btnText.textContent = 'Login';
                btnSpinner.classList.add('d-none');
            }
        });
        
        function showAlert(message, type) {
            const alertMessage = document.getElementById('alertMessage');
            alertMessage.textContent = message;
            alertMessage.className = `alert alert-${type}`;
            alertMessage.classList.remove('d-none');
        }
    </script>
</body>
</html>