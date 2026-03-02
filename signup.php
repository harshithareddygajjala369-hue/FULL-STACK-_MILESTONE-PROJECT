<?php
require_once 'config/database.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all required fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        // Check if user exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);
        
        if ($stmt->fetch()) {
            $error = 'Email or username already exists';
        } else {
            try {
                // Start transaction
                $pdo->beginTransaction();
                
                // Create user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, full_name, password, email_verified) VALUES (?, ?, ?, ?, 1)");
                
                if ($stmt->execute([$username, $email, $full_name, $hashed_password])) {
                    $user_id = $pdo->lastInsertId();
                    
                    // Create welcome notification
                    $stmt = $pdo->prepare("
                        INSERT INTO notifications (
                            user_id, 
                            notification_type, 
                            title, 
                            message
                        ) VALUES (?, 'welcome', ?, ?)
                    ");
                    $stmt->execute([
                        $user_id,
                        'Welcome to Membership Pro!',
                        'Thank you for joining. Explore our membership plans to get started.'
                    ]);
                    
                    // Commit transaction
                    $pdo->commit();
                    
                    // Redirect to login with success message
                    header('Location: login.php?registered=1');
                    exit();
                } else {
                    $pdo->rollBack();
                    $error = 'Failed to create account. Please try again.';
                }
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = 'Registration failed: ' . $e->getMessage();
            }
        }
    }
}

// Include header only once
require_once 'includes/header.php';
?>

<div class="auth-container">
    <h2>Create Your Account</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="POST" action="" id="signupForm">
        <div class="form-group">
            <label for="username">Username *</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email Address *</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="full_name">Full Name</label>
            <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label for="password">Password *</label>
            <input type="password" id="password" name="password" required>
            <small style="color: #666;">Minimum 6 characters</small>
        </div>
        
        <div class="form-group">
            <label for="confirm_password">Confirm Password *</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        
        <div class="form-group">
            <label>
                <input type="checkbox" name="terms" required style="width: auto; margin-right: 10px;">
                I agree to the <a href="#" target="_blank">Terms of Service</a> and <a href="#" target="_blank">Privacy Policy</a>
            </label>
        </div>
        
        <button type="submit" class="btn">Sign Up</button>
    </form>
    
    <p style="text-align: center; margin-top: 20px;">
        Already have an account? <a href="login.php">Login here</a>
    </p>
</div>

<?php 
// Include footer only at the end
require_once 'includes/footer.php'; 
?>