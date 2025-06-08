document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('cardSearch');
    const cards = document.querySelectorAll('.nav-card');
    const searchStatus = document.getElementById('searchStatus');
    let timeoutId;

    // Show all cards initially
    cards.forEach(card => {
        card.style.display = 'flex';
        card.style.opacity = '1';
    });

    searchInput.addEventListener('input', function(e) {
        clearTimeout(timeoutId);
        
        timeoutId = setTimeout(() => {
            const searchTerm = e.target.value.toLowerCase().trim();
            let matchCount = 0;

            cards.forEach(card => {
                const title = card.querySelector('h3').textContent.toLowerCase();
                const description = card.querySelector('p').textContent.toLowerCase();
                const matches = title.includes(searchTerm) || description.includes(searchTerm);
                
                if (matches) {
                    card.style.display = 'flex';
                    card.style.opacity = '1';
                    matchCount++;
                } else {
                    card.style.opacity = '0';
                    setTimeout(() => {
                        card.style.display = 'none';
                    }, 300);
                }
            });

            // Update search status
            if (searchTerm !== '') {
                searchStatus.textContent = `Found ${matchCount} ${matchCount === 1 ? 'result' : 'results'}`;
                searchStatus.style.opacity = '1';
            } else {
                searchStatus.style.opacity = '0';
                // Show all cards when search is empty
                cards.forEach(card => {
                    card.style.display = 'flex';
                    card.style.opacity = '1';
                });
            }
        }, 200); // Debounce delay
    });

    document.addEventListener('DOMContentLoaded', function() {
        // Function to handle page exit animation
        function handlePageExit(event) {
            // Prevent default navigation
            event.preventDefault();
            
            // Get the target href
            const targetHref = event.currentTarget.href;
            
            // Add fade-out class to body
            document.body.classList.add('fade-out');
            
            // Redirect after animation completes
            setTimeout(() => {
                window.location.href = targetHref;
            }, 500);
        }
    
        // Add exit animation to all navigation links
        const navLinks = document.querySelectorAll('.nav-card');
        navLinks.forEach(link => {
            link.addEventListener('click', handlePageExit);
        });
    
        // Optional: Add search functionality
        const searchInput = document.getElementById('cardSearch');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const navCards = document.querySelectorAll('.nav-card');
                
                navCards.forEach(card => {
                    const title = card.querySelector('.nav-card-content h3').textContent.toLowerCase();
                    const description = card.querySelector('.nav-card-content p').textContent.toLowerCase();
                    
                    if (title.includes(searchTerm) || description.includes(searchTerm)) {
                        card.style.display = 'flex';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        }
    
        // Hamburger menu toggle functionality
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const body = document.body;
            
            if (sidebar) {
                sidebar.classList.toggle('show');
                body.classList.toggle('sidebar-open');
            }
        }
    
        // Attach toggle functionality if hamburger button exists
        const hamburgerBtn = document.querySelector('.hamburger-btn');
        if (hamburgerBtn) {
            hamburgerBtn.addEventListener('click', toggleSidebar);
        }
    });

    // Clear search on ESC key
    searchInput.addEventListener('keyup', function(e) {
        if (e.key === 'Escape') {
            this.value = '';
            searchStatus.style.opacity = '0';
            cards.forEach(card => {
                card.style.display = 'flex';
                card.style.opacity = '1';
            });
        }
    });
});

function back() {
    window.location.href = "../";
}

// Add these CSS rules first
`.input-error {
    border-bottom-color: #EF4444 !important;
}

.error-message {
    color: #EF4444;
    font-size: 0.75rem;
    margin-top: 0.25rem;
    opacity: 0;
    transform: translateY(-10px);
    transition: all 0.3s ease;
}

.error-message.visible {
    opacity: 1;
    transform: translateY(0);
}`

// Password toggle functionality
document.querySelector('.password-toggle').addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const eyeIcon = this.querySelector('.eye-icon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />`;
    } else {
        passwordInput.type = 'password';
        eyeIcon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />`;
    }
});

// JavaScript for validation and animation
document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    clearErrors();
    const email = document.getElementById('email');
    const password = document.getElementById('password');
    let isValid = true;
    if (!email.value) {
        showError(email, 'Email or SATS Number is required');
        isValid = false;
    }
    if (!password.value) {
        showError(password, 'Password is required');
        isValid = false;
    } else if (password.value.length < 6) {
        showError(password, 'Password must be at least 6 characters');
        isValid = false;
    }
    if (isValid) {
        const loginBtn = document.querySelector('.login-btn');
        loginBtn.disabled = true;
        loginBtn.innerHTML = '<span class="loading-spinner"></span> Logging in...';
        const formData = new URLSearchParams();
        formData.append('email', email.value);
        formData.append('password', password.value);
        fetch('index.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: formData.toString()
        })
        .then(response => response.text())
        .then(html => {
            if (html.includes('dashboard/index.php') || html.includes('Welcome') || html.includes('Dashboard')) {
                window.location.href = 'dashboard/index.php';
            } else if (html.includes('Invalid credentials') || html.includes('Please enter both email and password')) {
                showError(email, 'Invalid credentials or inactive account.');
                showError(password, ' ');
                loginBtn.disabled = false;
                loginBtn.innerHTML = 'Log in';
            } else {
                // fallback: reload page
                window.location.reload();
            }
        })
        .catch(() => {
            showError(email, 'Server error');
            showError(password, ' ');
            loginBtn.disabled = false;
            loginBtn.innerHTML = 'Log in';
        });
    }
    return false;
});

function reloadPage() {
    window.location.reload();
}

function showError(input, message) {
    input.classList.add('input-error');
    
    // Create error message element if it doesn't exist
    let errorDiv = input.parentElement.querySelector('.error-message');
    if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        input.parentElement.appendChild(errorDiv);
    }
    
    errorDiv.textContent = message;
    errorDiv.classList.add('visible');
    
    // Shake animation for the input
    input.style.animation = 'shake 0.5s ease';
    setTimeout(() => {
        input.style.animation = '';
    }, 500);
}

function clearErrors() {
    const inputs = document.querySelectorAll('input');
    inputs.forEach(input => {
        input.classList.remove('input-error');
        const errorDiv = input.parentElement.querySelector('.error-message');
        if (errorDiv) {
            errorDiv.classList.remove('visible');
        }
    });
}

document.querySelector('.google-btn').addEventListener('click', function() {
    // Create alert element
    const alert = document.createElement('div');
    alert.className = 'alert';
    alert.textContent = 'Coming Soon!';
    document.body.appendChild(alert);

    // Show alert
    setTimeout(() => alert.classList.add('show'), 100);

    // Remove alert after 3 seconds
    setTimeout(() => {
        alert.classList.remove('show');
        setTimeout(() => alert.remove(), 300); // Remove from DOM after animation
    }, 3000);
});
