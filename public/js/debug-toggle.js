// public/js/debug-toggle.js - Simple production debug toggle
(function() {
    // Only enable if URL has debug parameter or localStorage flag
    const urlParams = new URLSearchParams(window.location.search);
    const debugEnabled = urlParams.has('debug') || 
                        localStorage.getItem('app_debug') === 'true' ||
                        window.location.hostname.includes('vercel');
    
    if (!debugEnabled) return;
    
    console.log('=== APP DEBUG MODE ===');
    
    // Simple cookie check
    console.log('Cookies:', document.cookie);
    console.log('Has PHPSESSID:', document.cookie.includes('PHPSESSID'));
    
    // Add debug hotkey (Ctrl+Shift+D)
    document.addEventListener('keydown', (e) => {
        if (e.ctrlKey && e.shiftKey && e.key === 'D') {
            localStorage.setItem('app_debug', 
                localStorage.getItem('app_debug') === 'true' ? 'false' : 'true');
            window.location.reload();
        }
    });
    
    console.log('Debug hotkey: Ctrl+Shift+D');
})();