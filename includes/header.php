<?php
// Session validation
if (isset($pdo)) {
    validateUserSession($pdo);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Membership Engine</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="<?php echo isLoggedIn() ? 'logged-in' : 'logged-out'; ?>">
    <nav class="navbar">
        <div class="logo">
            <i class="fas fa-crown"></i> Membership Pro
        </div>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="index.php#features">Features</a>
            <a href="index.php#pricing">Pricing</a>
            <?php if (isLoggedIn()): 
                $currentUser = getCurrentUser($pdo);
                if ($currentUser): 
            ?>
                <a href="dashboard.php">Dashboard</a>
                <div class="user-info">
                    <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($currentUser['username'] ?? 'User'); ?></span>
                    <a href="logout.php" class="btn btn-secondary" style="width: auto; padding: 8px 15px;">Logout</a>
                </div>
            <?php else: 
                    // Invalid session, force logout
                    header('Location: logout.php');
                    exit();
                endif;
            else: ?>
                <a href="login.php">Login</a>
                <a href="signup.php" class="btn" style="width: auto; padding: 8px 20px;">Sign Up</a>
            <?php endif; ?>
        </div>
    </nav>
    <div class="container">