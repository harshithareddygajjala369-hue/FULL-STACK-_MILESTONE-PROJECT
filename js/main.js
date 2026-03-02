// Form validation
document.addEventListener('DOMContentLoaded', function() {
    // Login form validation
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            if (!email || !password) {
                e.preventDefault();
                showAlert('Please fill in all fields', 'danger');
            }
        });
    }
    
    // Signup form validation
    const signupForm = document.getElementById('signupForm');
    if (signupForm) {
        signupForm.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                showAlert('Passwords do not match', 'danger');
            }
            
            if (password.length < 6) {
                e.preventDefault();
                showAlert('Password must be at least 6 characters long', 'danger');
            }
        });
    }
    
    // Membership selection
    const membershipCards = document.querySelectorAll('.membership-card .btn');
    membershipCards.forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!isLoggedIn()) {
                e.preventDefault();
                showAlert('Please login to subscribe', 'warning');
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 2000);
            }
        });
    });
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000);
    });
});

// Show alert message
function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    
    const container = document.querySelector('.container');
    if (container) {
        container.insertBefore(alertDiv, container.firstChild);
        
        setTimeout(() => {
            alertDiv.style.transition = 'opacity 0.5s';
            alertDiv.style.opacity = '0';
            setTimeout(() => {
                alertDiv.remove();
            }, 500);
        }, 5000);
    }
}

// Check if user is logged in
function isLoggedIn() {
    return document.body.classList.contains('logged-in');
}

// Payment demo simulation
function simulatePayment(amount) {
    return new Promise((resolve) => {
        showAlert('Processing payment...', 'warning');
        
        setTimeout(() => {
            const success = Math.random() > 0.1; // 90% success rate
            
            if (success) {
                showAlert(`Payment of $${amount} completed successfully!`, 'success');
                resolve({ success: true, transactionId: 'TXN' + Date.now() });
            } else {
                showAlert('Payment failed. Please try again.', 'danger');
                resolve({ success: false });
            }
        }, 2000);
    });
}

// Countdown timer for subscription expiry
function updateExpiryTimer() {
    const expiryElements = document.querySelectorAll('.expiry-timer');
    
    expiryElements.forEach(element => {
        const expiryDate = new Date(element.dataset.expiry);
        const now = new Date();
        const diff = expiryDate - now;
        
        if (diff <= 0) {
            element.textContent = 'Expired';
            element.classList.add('text-danger');
        } else {
            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            
            element.textContent = `${days}d ${hours}h remaining`;
        }
    });
}

// Update timers every minute
setInterval(updateExpiryTimer, 60000);

// Interactive features
document.addEventListener('click', function(e) {
    // Tooltip for features
    if (e.target.classList.contains('feature-icon')) {
        const tooltip = e.target.nextElementSibling;
        if (tooltip && tooltip.classList.contains('tooltip')) {
            tooltip.style.display = tooltip.style.display === 'block' ? 'none' : 'block';
        }
    }
});

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});