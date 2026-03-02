<?php
require_once 'config/database.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['login_time'] = time();
            
            // Update last login
            $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $stmt->execute([$user['id']]);
            
            // Log activity with error handling
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO user_activity_logs (
                        user_id, 
                        activity_type, 
                        description, 
                        ip_address,
                        user_agent
                    ) VALUES (?, 'login', ?, ?, ?)
                ");
                $stmt->execute([
                    $user['id'],
                    'User logged in successfully',
                    $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                    $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
                ]);
            } catch (Exception $e) {
                error_log("Login activity log error: " . $e->getMessage());
            }
            
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Invalid email or password';
        }
    }
}

// Include header only once
require_once 'includes/header.php';
?>

<div class="auth-container">
    <h2>Login to Your Account</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if (isset($_GET['registered'])): ?>
        <div class="alert alert-success">Registration successful! Please login.</div>
    <?php endif; ?>
    
    <form method="POST" action="" id="loginForm">
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required autofocus>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <div class="form-group" style="display: flex; align-items: center;">
            <input type="checkbox" id="remember" name="remember" style="width: auto; margin-right: 10px;">
            <label for="remember" style="margin-bottom: 0;">Remember me</label>
        </div>
        
        <button type="submit" class="btn">Login</button>
    </form>
    
    <p style="text-align: center; margin-top: 20px;">
        Don't have an account? <a href="signup.php">Sign up here</a>
    </p>
    <p style="text-align: center; margin-top: 10px;">
        <a href="forgot-password.php">Forgot Password?</a>
    </p>
</div>

<?php 
// Include footer only at the end
require_once 'includes/footer.php'; 
?>