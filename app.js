document.addEventListener('DOMContentLoaded', () => {
    
    // Toggle between login and signup
    const loginView = document.getElementById('login-view');
    const signupView = document.getElementById('signup-view');
    const showSignupBtn = document.getElementById('show-signup');
    const showLoginBtn = document.getElementById('show-login');

    if(showSignupBtn && showLoginBtn) {
        showSignupBtn.addEventListener('click', () => {
            loginView.classList.add('hidden');
            signupView.classList.remove('hidden');
        });
        showLoginBtn.addEventListener('click', () => {
            signupView.classList.add('hidden');
            loginView.classList.remove('hidden');
        });
    }

    // "Database" initialization using localStorage
    // We store an array of user objects: { email, username, password }
    let usersDB = JSON.parse(localStorage.getItem('usersDB')) || [];

    // Signup Logic
    const signupForm = document.getElementById('signup-form');
    if (signupForm) {
        signupForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const email = document.getElementById('signup-email').value;
            const username = document.getElementById('signup-username').value;
            const password = document.getElementById('signup-password').value;
            const confirmPassword = document.getElementById('signup-confirm-password').value;
            
            if(password !== confirmPassword) {
                alert("Passwords do not match!");
                return;
            }

            // Check if user already exists
            const existingUser = usersDB.find(u => u.username === username || u.email === email);
            if(existingUser) {
                alert("User with this email or username already exists!");
                return;
            }

            // Save new user to our local "database"
            usersDB.push({ email, username, password });
            localStorage.setItem('usersDB', JSON.stringify(usersDB));

            alert("Account created successfully! You can now log in.");
            
            // Switch back to login view and clear form
            signupView.classList.add('hidden');
            loginView.classList.remove('hidden');
            signupForm.reset();
        });
    }

    // Login Logic Updates
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const usernameInput = document.getElementById('login-username').value;
            const passwordInput = document.getElementById('login-password').value;
            
            // Check credentials against our "database"
            const user = usersDB.find(u => u.username === usernameInput && u.password === passwordInput);

            if(user) {
                // Successful login
                localStorage.setItem('currentUser', user.username);
                
                const btn = loginForm.querySelector('.btn-primary');
                btn.textContent = 'Authenticating...';
                btn.style.opacity = '0.8';
                btn.style.transform = 'scale(0.98)';
                
                setTimeout(() => {
                    window.location.href = 'home.html';
                }, 800);
            } else {
                // Failed login
                alert("Invalid username or password!");
            }
        });
    }

    // Home Page Logic
    const userDisplay = document.getElementById('user-display');
    const logoutBtn = document.getElementById('logout-btn');

    if (userDisplay) {
        const username = localStorage.getItem('currentUser') || 'Guest';
        userDisplay.textContent = username;
        
        // Security check: Redirect to index if not logged in
        if (!localStorage.getItem('currentUser')) {
            window.location.href = 'index.html';
        }
    }

    if (logoutBtn) {
        logoutBtn.addEventListener('click', () => {
            // Clear current session
            localStorage.removeItem('currentUser');
            
            logoutBtn.textContent = 'Logging out...';
            logoutBtn.style.opacity = '0.7';
            
            setTimeout(() => {
                window.location.href = 'index.html';
            }, 500);
        });
    }
});
