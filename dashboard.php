<?php
require_once 'config/database.php';

// Require login
requireLogin();

$user = getCurrentUser($pdo);

// Get user's current subscription
$stmt = $pdo->prepare("
    SELECT us.*, mt.tier_name, mt.price, mt.max_projects, mt.storage_gb, mt.support_level, mt.features 
    FROM user_subscriptions us 
    JOIN membership_tiers mt ON us.tier_id = mt.id 
    WHERE us.user_id = ? AND us.status = 'active' 
    ORDER BY us.id DESC LIMIT 1
");
$stmt->execute([$_SESSION['user_id']]);
$currentSubscription = $stmt->fetch();

// Get subscription history
$stmt = $pdo->prepare("
    SELECT us.*, mt.tier_name 
    FROM user_subscriptions us 
    JOIN membership_tiers mt ON us.tier_id = mt.id 
    WHERE us.user_id = ? 
    ORDER BY us.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$subscriptionHistory = $stmt->fetchAll();

// Include header only once
require_once 'includes/header.php';
?>

<div class="dashboard-container">
    <div class="welcome-section">
        <h1>Welcome back, <?php echo htmlspecialchars($user['full_name'] ?: $user['username']); ?>!</h1>
        <p>Manage your membership and account settings</p>
    </div>
    
    <?php if ($currentSubscription): ?>
        <div class="current-plan">
            <h2>Current Plan: <?php echo htmlspecialchars($currentSubscription['tier_name']); ?></h2>
            <div class="plan-details">
                <div class="plan-item">
                    <i class="fas fa-calendar-alt"></i>
                    <h4>Valid Until</h4>
                    <p><?php echo date('M d, Y', strtotime($currentSubscription['end_date'])); ?></p>
                </div>
                <div class="plan-item">
                    <i class="fas fa-clock"></i>
                    <h4>Time Remaining</h4>
                    <p class="expiry-timer" data-expiry="<?php echo $currentSubscription['end_date']; ?>">
                        <?php 
                        $days = daysRemaining($currentSubscription['end_date']);
                        echo $days >= 0 ? $days . ' days remaining' : 'Expired';
                        ?>
                    </p>
                </div>
                <div class="plan-item">
                    <i class="fas fa-dollar-sign"></i>
                    <h4>Monthly Price</h4>
                    <p>$<?php echo number_format($currentSubscription['price'], 2); ?></p>
                </div>
            </div>
            
            <div style="margin-top: 20px; display: flex; gap: 15px; justify-content: center;">
                <a href="subscribe.php?upgrade=1" class="btn" style="width: auto;">Upgrade Plan</a>
                <a href="unsubscribe.php" class="btn btn-danger" style="width: auto;">Cancel Subscription</a>
            </div>
        </div>
        
        <div style="margin-top: 30px;">
            <h3>Plan Features</h3>
            <ul class="features" style="margin-top: 15px;">
                <li><i class="fas fa-check"></i> Max Projects: <?php echo $currentSubscription['max_projects'] == 999999 ? 'Unlimited' : $currentSubscription['max_projects']; ?></li>
                <li><i class="fas fa-check"></i> Storage: <?php echo $currentSubscription['storage_gb']; ?>GB</li>
                <li><i class="fas fa-check"></i> Support Level: <?php echo ucfirst($currentSubscription['support_level']); ?></li>
                <?php 
                $features = explode(',', $currentSubscription['features']);
                foreach ($features as $feature): 
                ?>
                    <li><i class="fas fa-check"></i> <?php echo trim($feature); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">
            <h3>You don't have an active subscription</h3>
            <p>Choose a plan that suits your needs and get started today!</p>
            <a href="index.php#pricing" class="btn" style="width: auto; margin-top: 15px;">View Plans</a>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($subscriptionHistory)): ?>
        <div style="margin-top: 40px;">
            <h3>Subscription History</h3>
            <div style="overflow-x: auto;">
                <table style="width: 100%; margin-top: 20px; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8f9fa;">
                            <th style="padding: 12px; text-align: left;">Plan</th>
                            <th style="padding: 12px; text-align: left;">Start Date</th>
                            <th style="padding: 12px; text-align: left;">End Date</th>
                            <th style="padding: 12px; text-align: left;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subscriptionHistory as $sub): ?>
                            <tr style="border-bottom: 1px solid #dee2e6;">
                                <td style="padding: 12px;"><?php echo htmlspecialchars($sub['tier_name']); ?></td>
                                <td style="padding: 12px;"><?php echo date('M d, Y', strtotime($sub['start_date'])); ?></td>
                                <td style="padding: 12px;"><?php echo date('M d, Y', strtotime($sub['end_date'])); ?></td>
                                <td style="padding: 12px;">
                                    <span style="background: <?php 
                                        echo $sub['status'] == 'active' ? '#28a745' : 
                                            ($sub['status'] == 'expired' ? '#dc3545' : '#ffc107'); 
                                    ?>; color: white; padding: 5px 10px; border-radius: 3px; display: inline-block;">
                                        <?php echo ucfirst($sub['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php 
// Include footer only at the end
require_once 'includes/footer.php'; 
?>