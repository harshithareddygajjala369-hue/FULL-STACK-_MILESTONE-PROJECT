<?php
require_once 'config/database.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Get active subscription
$stmt = $pdo->prepare("
    SELECT us.*, mt.tier_name 
    FROM user_subscriptions us 
    JOIN membership_tiers mt ON us.tier_id = mt.id 
    WHERE us.user_id = ? AND us.status = 'active'
");
$stmt->execute([$_SESSION['user_id']]);
$subscription = $stmt->fetch();

if (!$subscription) {
    header('Location: dashboard.php');
    exit();
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['confirm_unsubscribe'])) {
        // Cancel subscription
        $stmt = $pdo->prepare("UPDATE user_subscriptions SET status = 'cancelled', auto_renew = 0 WHERE id = ?");
        
        if ($stmt->execute([$subscription['id']])) {
            $message = 'Your subscription has been cancelled successfully.';
            $messageType = 'success';
            
            // Refresh subscription data
            $subscription['status'] = 'cancelled';
        } else {
            $message = 'Failed to cancel subscription. Please try again.';
            $messageType = 'danger';
        }
    }
}

require_once 'includes/header.php';
?>

<div class="auth-container">
    <h2>Cancel Subscription</h2>
    
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>"><?php echo $message; ?></div>
        <?php if ($messageType == 'success'): ?>
            <div style="text-align: center; margin-top: 20px;">
                <a href="dashboard.php" class="btn">Return to Dashboard</a>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    
    <?php if (!$message): ?>
        <div class="alert alert-warning">
            <h3>Are you sure you want to cancel your subscription?</h3>
            <p>You are currently subscribed to: <strong><?php echo htmlspecialchars($subscription['tier_name']); ?></strong></p>
            <p>Your subscription will remain active until: <strong><?php echo date('M d, Y', strtotime($subscription['end_date'])); ?></strong></p>
            <p style="margin-top: 15px; color: #856404;">
                <i class="fas fa-exclamation-triangle"></i> 
                After cancellation, you will lose access to premium features when your current period ends.
            </p>
        </div>
        
        <form method="POST" action="">
            <button type="submit" name="confirm_unsubscribe" class="btn btn-danger">
                Yes, Cancel My Subscription
            </button>
            <a href="dashboard.php" class="btn btn-secondary" style="margin-top: 10px;">No, Keep My Subscription</a>
        </form>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>