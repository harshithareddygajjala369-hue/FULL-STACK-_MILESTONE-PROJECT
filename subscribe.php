<?php
require_once 'config/database.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$tier_id = $_GET['tier'] ?? null;
$isUpgrade = isset($_GET['upgrade']);

if (!$tier_id) {
    header('Location: index.php');
    exit();
}

// Get tier details
$stmt = $pdo->prepare("SELECT * FROM membership_tiers WHERE id = ?");
$stmt->execute([$tier_id]);
$tier = $stmt->fetch();

if (!$tier) {
    header('Location: index.php');
    exit();
}

// Check if user already has active subscription
$stmt = $pdo->prepare("SELECT * FROM user_subscriptions WHERE user_id = ? AND status = 'active'");
$stmt->execute([$_SESSION['user_id']]);
$activeSubscription = $stmt->fetch();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Redirect to payment demo
    $_SESSION['pending_subscription'] = [
        'tier_id' => $tier_id,
        'amount' => $tier['price']
    ];
    header('Location: payment-demo.php');
    exit();
}

require_once 'includes/header.php';
?>

<div class="payment-demo-container">
    <h2>Subscribe to <?php echo htmlspecialchars($tier['tier_name']); ?> Plan</h2>
    
    <?php if ($activeSubscription && !$isUpgrade): ?>
        <div class="alert alert-warning">
            You already have an active subscription. <a href="subscribe.php?tier=<?php echo $tier_id; ?>&upgrade=1">Click here to upgrade</a>
        </div>
    <?php else: ?>
        <div class="payment-details">
            <h3>Order Summary</h3>
            <div class="payment-row">
                <span>Plan:</span>
                <span><?php echo htmlspecialchars($tier['tier_name']); ?></span>
            </div>
            <div class="payment-row">
                <span>Duration:</span>
                <span><?php echo $tier['duration_days']; ?> days</span>
            </div>
            <div class="payment-row">
                <span>Features:</span>
                <span><?php echo htmlspecialchars($tier['features']); ?></span>
            </div>
            <div class="payment-row total">
                <span>Total Amount:</span>
                <span>$<?php echo number_format($tier['price'], 2); ?></span>
            </div>
        </div>
        
        <form method="POST" action="">
            <div class="demo-card">
                <p><i class="fas fa-info-circle"></i> This is a demo payment gateway</p>
                <p>In production, this would redirect to:</p>
                <ul style="margin-top: 10px; margin-left: 20px;">
                    <li>Stripe</li>
                    <li>PayPal</li>
                    <li>Razorpay</li>
                </ul>
            </div>
            
            <button type="submit" class="btn">Proceed to Payment Demo</button>
            <a href="index.php" class="btn btn-secondary" style="margin-top: 10px;">Cancel</a>
        </form>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>