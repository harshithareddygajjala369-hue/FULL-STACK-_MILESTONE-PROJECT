<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get current user data
function getCurrentUser($pdo) {
    if (isLoggedIn()) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    }
    return null;
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Redirect if logged in
function requireGuest() {
    if (isLoggedIn()) {
        header('Location: dashboard.php');
        exit();
    }
}

// Sanitize input
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Display error/success messages
function displayMessage($message, $type = 'success') {
    return '<div class="alert alert-' . $type . '">' . $message . '</div>';
}

// Format date
function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

// Calculate days remaining
function daysRemaining($end_date) {
    $end = strtotime($end_date);
    $now = time();
    $diff = $end - $now;
    return floor($diff / (60 * 60 * 24));
}

// Generate unique subscription number
function generateSubscriptionNumber($pdo) {
    $prefix = 'SUB';
    $date = date('Ymd');
    $attempts = 0;
    $maxAttempts = 10;
    
    while ($attempts < $maxAttempts) {
        $random = str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
        $subscription_number = $prefix . $date . $random;
        
        // Check if number exists
        $stmt = $pdo->prepare("SELECT id FROM user_subscriptions WHERE subscription_number = ?");
        $stmt->execute([$subscription_number]);
        
        if (!$stmt->fetch()) {
            return $subscription_number;
        }
        
        $attempts++;
    }
    
    // If all attempts failed, use timestamp
    return $prefix . $date . time();
}

// Generate unique transaction number
function generateTransactionNumber($pdo) {
    $prefix = 'TXN';
    $date = date('Ymd');
    $attempts = 0;
    $maxAttempts = 10;
    
    while ($attempts < $maxAttempts) {
        $random = str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
        $transaction_number = $prefix . $date . $random;
        
        // Check if number exists
        $stmt = $pdo->prepare("SELECT id FROM payment_transactions WHERE transaction_number = ?");
        $stmt->execute([$transaction_number]);
        
        if (!$stmt->fetch()) {
            return $transaction_number;
        }
        
        $attempts++;
    }
    
    // If all attempts failed, use timestamp
    return $prefix . $date . time();
}
?>
<?php
// Add this new function to validate user session

// Validate if the logged-in user still exists in database
function validateUserSession($pdo) {
    if (isLoggedIn()) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND status = 'active'");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if (!$user) {
            // User doesn't exist or is inactive, clear session
            $_SESSION = array();
            session_destroy();
            return false;
        }
        return true;
    }
    return false;
}

// Add this to existing functions
?>