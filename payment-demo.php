<?php
require_once 'config/database.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

if (!isset($_SESSION['pending_subscription'])) {
    header('Location: index.php');
    exit();
}

$subscriptionData = $_SESSION['pending_subscription'];
$tier_id = $subscriptionData['tier_id'];
$amount = $subscriptionData['amount'];

// Get tier details
$stmt = $pdo->prepare("SELECT * FROM membership_tiers WHERE id = ?");
$stmt->execute([$tier_id]);
$tier = $stmt->fetch();

if (!$tier) {
    unset($_SESSION['pending_subscription']);
    header('Location: index.php');
    exit();
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['simulate_payment'])) {
        // Simulate payment processing
        $payment_success = true; // Demo: always successful
        
        if ($payment_success) {
            try {
                // Start transaction
                $pdo->beginTransaction();
                
                // Cancel any existing active subscriptions
                $stmt = $pdo->prepare("UPDATE user_subscriptions SET status = 'cancelled' WHERE user_id = ? AND status = 'active'");
                $stmt->execute([$_SESSION['user_id']]);
                
                // Create new subscription
                $start_date = date('Y-m-d');
                $end_date = date('Y-m-d', strtotime("+{$tier['duration_days']} days"));
                $next_billing_date = date('Y-m-d', strtotime("+{$tier['duration_days']} days"));
                
                // Generate unique subscription number
                $subscription_number = generateSubscriptionNumber($pdo);
                
                $stmt = $pdo->prepare("
                    INSERT INTO user_subscriptions (
                        user_id, 
                        tier_id, 
                        subscription_number,
                        start_date, 
                        end_date, 
                        next_billing_date,
                        status, 
                        auto_renew, 
                        payment_method,
                        total_amount
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, 'active', ?, ?, ?
                    )
                ");
                
                $auto_renew = 1; // true
                $payment_method = 'demo_credit_card';
                
                $stmt->execute([
                    $_SESSION['user_id'], 
                    $tier_id, 
                    $subscription_number,
                    $start_date, 
                    $end_date,
                    $next_billing_date,
                    $auto_renew, 
                    $payment_method,
                    $amount
                ]);
                
                $subscription_id = $pdo->lastInsertId();
                
                // Generate unique transaction number
                $transaction_number = generateTransactionNumber($pdo);
                
                // Get user details for billing info
                $user = getCurrentUser($pdo);
                
                // Record transaction
                $stmt = $pdo->prepare("
                    INSERT INTO payment_transactions (
                        user_id, 
                        subscription_id, 
                        transaction_number,
                        amount, 
                        payment_method, 
                        payment_type,
                        payment_status, 
                        gateway_name,
                        billing_name,
                        billing_email
                    ) VALUES (
                        ?, ?, ?, ?, ?, 'subscription', 'completed', 'demo_gateway', ?, ?
                    )
                ");
                
                $stmt->execute([
                    $_SESSION['user_id'], 
                    $subscription_id,
                    $transaction_number,
                    $amount, 
                    $payment_method,
                    $user['full_name'] ?? $user['username'],
                    $user['email']
                ]);
                
                // Log activity
                $stmt = $pdo->prepare("
                    INSERT INTO user_activity_logs (
                        user_id, 
                        activity_type, 
                        description, 
                        ip_address
                    ) VALUES (?, 'subscription_created', ?, ?)
                ");
                $stmt->execute([
                    $_SESSION['user_id'],
                    "Subscribed to {$tier['tier_name']} plan",
                    $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                ]);
                
                // Create notification
                $stmt = $pdo->prepare("
                    INSERT INTO notifications (
                        user_id, 
                        notification_type, 
                        title, 
                        message
                    ) VALUES (?, 'payment_success', ?, ?)
                ");
                $stmt->execute([
                    $_SESSION['user_id'],
                    'Payment Successful!',
                    "Your subscription to {$tier['tier_name']} plan has been activated successfully."
                ]);
                
                // Commit transaction
                $pdo->commit();
                
                unset($_SESSION['pending_subscription']);
                
                $message = 'Payment successful! Your subscription is now active.';
                $messageType = 'success';
                
            } catch (Exception $e) {
                // Rollback transaction on error
                $pdo->rollBack();
                $message = 'Payment failed: ' . $e->getMessage();
                $messageType = 'danger';
            }
        } else {
            $message = 'Payment failed. Please try again.';
            $messageType = 'danger';
        }
    }
}

require_once 'includes/header.php';
?>

<div class="payment-demo-container">
    <h2>Payment Demo</h2>
    
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php if ($messageType == 'success'): ?>
            <div style="text-align: center; margin-top: 20px;">
                <a href="dashboard.php" class="btn">Go to Dashboard</a>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    
    <?php if (!$message): ?>
        <div class="payment-details">
            <h3>Payment Summary</h3>
            <div class="payment-row">
                <span>Plan:</span>
                <span><?php echo htmlspecialchars($tier['tier_name']); ?></span>
            </div>
            <div class="payment-row">
                <span>Duration:</span>
                <span><?php echo $tier['duration_days']; ?> days</span>
            </div>
            <div class="payment-row">
                <span>Amount:</span>
                <span>$<?php echo number_format($amount, 2); ?></span>
            </div>
        </div>
        
        <div class="demo-card">
            <h4><i class="fas fa-credit-card"></i> Demo Card Details</h4>
            <p><strong>Card Number:</strong> 4242 4242 4242 4242</p>
            <p><strong>Expiry:</strong> 12/25</p>
            <p><strong>CVV:</strong> 123</p>
            <p style="margin-top: 10px; color: #666; font-size: 14px;">
                <i class="fas fa-info-circle"></i> 
                This is a demo payment. No actual charges will be made.
            </p>
        </div>
        
        <form method="POST" action="" onsubmit="return confirm('This is a demo payment. Proceed?');">
            <button type="submit" name="simulate_payment" class="btn">
                <i class="fas fa-play"></i> Simulate Payment ($<?php echo number_format($amount, 2); ?>)
            </button>
            <a href="subscribe.php?tier=<?php echo $tier_id; ?>" class="btn btn-secondary" style="margin-top: 10px;">Back</a>
        </form>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>