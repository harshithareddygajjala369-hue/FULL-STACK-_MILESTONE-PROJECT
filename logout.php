<?php
require_once 'config/database.php';

// Log activity if user was logged in and exists in database
if (isLoggedIn()) {
    try {
        // Verify user still exists in database
        $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $userExists = $stmt->fetch();
        
        if ($userExists) {
            // Log the logout activity
            $stmt = $pdo->prepare("
                INSERT INTO user_activity_logs (
                    user_id, 
                    activity_type, 
                    description, 
                    ip_address,
                    user_agent
                ) VALUES (?, 'logout', ?, ?, ?)
            ");
            $stmt->execute([
                $_SESSION['user_id'],
                'User logged out',
                $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]);
        }
    } catch (Exception $e) {
        // Log error but don't stop logout process
        error_log("Logout activity log error: " . $e->getMessage());
    }
}

// Clear all session data
$_SESSION = array();

// Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to home page with logout message
header('Location: index.php?logout=success');
exit();
?>