<?php
require_once 'config/database.php';

// Fetch membership tiers
$stmt = $pdo->query("SELECT * FROM membership_tiers ORDER BY price");
$tiers = $stmt->fetchAll();

// Include header only once at the beginning
require_once 'includes/header.php';
?>

<div class="hero" style="text-align: center; padding: 60px 0;">
    <h1 style="font-size: 48px; color: white; margin-bottom: 20px;">Choose Your Perfect Plan</h1>
    <p style="font-size: 18px; color: white; opacity: 0.9;">Unlock premium features with our flexible membership options</p>
</div>

<div class="membership-container" id="pricing">
    <?php foreach ($tiers as $index => $tier): ?>
        <div class="membership-card <?php echo $index == 1 ? 'popular' : ''; ?>">
            <div class="card-header">
                <h3><?php echo htmlspecialchars($tier['tier_name']); ?></h3>
                <div class="price">
                    $<?php echo number_format($tier['price'], 2); ?><span>/month</span>
                </div>
            </div>
            <ul class="features">
                <li><i class="fas fa-check"></i> Max Projects: <?php echo $tier['max_projects'] == 999999 ? 'Unlimited' : $tier['max_projects']; ?></li>
                <li><i class="fas fa-check"></i> Storage: <?php echo $tier['storage_gb']; ?>GB</li>
                <li><i class="fas fa-check"></i> Support: <?php echo ucfirst($tier['support_level']); ?></li>
                <li><i class="fas fa-check"></i> Duration: <?php echo $tier['duration_days']; ?> days</li>
                <?php 
                $features = explode(',', $tier['features']);
                foreach ($features as $feature): 
                ?>
                    <li><i class="fas fa-check"></i> <?php echo trim($feature); ?></li>
                <?php endforeach; ?>
            </ul>
            <a href="subscribe.php?tier=<?php echo $tier['id']; ?>" class="btn">Get Started</a>
        </div>
    <?php endforeach; ?>
</div>

<div class="features-section" id="features" style="background: white; padding: 60px; border-radius: 10px; margin-top: 40px;">
    <h2 style="text-align: center; margin-bottom: 40px;">Why Choose Us?</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px;">
        <div class="feature-item" style="text-align: center;">
            <i class="fas fa-shield-alt" style="font-size: 48px; color: #667eea; margin-bottom: 20px;"></i>
            <h3>Secure Payments</h3>
            <p style="color: #666;">Your payments are protected with industry-standard encryption</p>
        </div>
        <div class="feature-item" style="text-align: center;">
            <i class="fas fa-clock" style="font-size: 48px; color: #667eea; margin-bottom: 20px;"></i>
            <h3>24/7 Support</h3>
            <p style="color: #666;">Get help anytime with our dedicated support team</p>
        </div>
        <div class="feature-item" style="text-align: center;">
            <i class="fas fa-sync" style="font-size: 48px; color: #667eea; margin-bottom: 20px;"></i>
            <h3>Easy Cancellation</h3>
            <p style="color: #666;">Cancel or change your plan anytime with no questions asked</p>
        </div>
    </div>
</div>

<?php 
// Include footer only at the end
require_once 'includes/footer.php'; 
?>