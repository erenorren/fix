<?php
// views/login.php
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
    <title>Login</title>
    
    <!-- Bootstrap 5 dari CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-box {
            width: 100%;
            max-width: 400px;
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2 class="text-center mb-4">Login Sistem</h2>
        
        <div id="alert" class="alert alert-danger d-none"></div>
        
        <form id="loginForm">
            <div class="mb-3">
                <label>Username</label>
                <input type="text" name="username" class="form-control" value="admin" required>
            </div>
            
            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" value="password123" required>
            </div>
            
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
        
        <div class="mt-3 text-center">
            <small class="text-muted">Gunakan: admin / password123</small>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const btn = this.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            const alertDiv = document.getElementById('alert');
            
            // Reset alert
            alertDiv.classList.add('d-none');
            
            // Show loading
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Loading...';
            
            try {
                const response = await fetch('index.php?action=login', {
                    method: 'POST',
                    body: formData,
                    credentials: 'include' // INI PENTING!
                });
                
                console.log('Response status:', response.status);
                
                const data = await response.json();
                console.log('Response data:', data);
                
                if (data.success) {
                    // Success - redirect
                    window.location.href = data.redirect || 'index.php?page=dashboard';
                } else {
                    // Show error
                    alertDiv.textContent = data.message || 'Login gagal';
                    alertDiv.classList.remove('d-none');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
                
            } catch (error) {
                console.error('Fetch error:', error);
                alertDiv.textContent = 'Terjadi kesalahan koneksi. Coba refresh halaman.';
                alertDiv.classList.remove('d-none');
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        });
        
        // Cek cookies saat load
        console.log('Cookies:', document.cookie);
        console.log('Has PHPSESSID:', document.cookie.includes('PHPSESSID'));
    </script>
</body>
</html>