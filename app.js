document.addEventListener('DOMContentLoaded', () => {
    
    // Login Logic
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const username = document.getElementById('username').value;
            
            if(username) {
                // Save the username for a mock login state
                localStorage.setItem('currentUser', username);
                
                // Add a cool transition effect on the button
                const btn = loginForm.querySelector('.btn-primary');
                btn.textContent = 'Authenticating...';
                btn.style.opacity = '0.8';
                btn.style.transform = 'scale(0.98)';
                
                setTimeout(() => {
                    window.location.href = 'home.html';
                }, 800);
            }
        });
    }

    // Home Page Logic
    const userDisplay = document.getElementById('user-display');
    const logoutBtn = document.getElementById('logout-btn');

    if (userDisplay) {
        // Retrieve username or default to Guest
        const username = localStorage.getItem('currentUser') || 'Guest';
        userDisplay.textContent = username;
        
        // If not logged in, you could redirect back to index.html here for security
        if (!localStorage.getItem('currentUser')) {
            window.location.href = 'index.html';
        }
    }

    if (logoutBtn) {
        logoutBtn.addEventListener('click', () => {
            // Clear mock session
            localStorage.removeItem('currentUser');
            
            // Visual feedback
            logoutBtn.textContent = 'Logging out...';
            logoutBtn.style.opacity = '0.7';
            
            setTimeout(() => {
                window.location.href = 'index.html';
            }, 500);
        });
    }
});
