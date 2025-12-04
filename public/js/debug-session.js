// public/js/debug-session.js - Lightweight version
(function() {
    console.log('🔍 Session Debug Enabled');
    
    // 1. Basic cookie check
    console.log('Cookies:', document.cookie);
    console.log('Has PHPSESSID:', document.cookie.includes('PHPSESSID'));
    
    // 2. Session test
    fetch(window.location.pathname, {
        method: 'HEAD',
        credentials: 'include'
    })
    .then(res => {
        console.log('Session check - Status:', res.status);
        console.log('Session cookie sent:', res.status !== 401 ? '✅ Yes' : '❌ No');
    })
    .catch(err => console.error('Session check failed:', err));
    
    // 3. Navigation monitoring
    document.addEventListener('click', function(e) {
        const link = e.target.closest('a');
        if (link && link.href) {
            console.log('Navigating to:', link.href);
            console.log('Current cookies:', document.cookie);
        }
    });
    
    // 4. Add debug toggle button (optional)
    if (!document.getElementById('debug-toggle')) {
        const btn = document.createElement('button');
        btn.id = 'debug-toggle';
        btn.innerHTML = '🔍';
        btn.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            z-index: 10000;
            font-size: 20px;
        `;
        btn.title = 'Toggle Debug';
        btn.onclick = function() {
            const debug = localStorage.getItem('debug') === 'true';
            localStorage.setItem('debug', !debug);
            window.location.reload();
        };
        document.body.appendChild(btn);
    }
})();