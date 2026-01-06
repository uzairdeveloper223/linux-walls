<?php
$pageTitle = 'Register';
require_once 'includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $discord_id = trim($_POST['discord_id'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($discord_id) || empty($password)) {
        $error = 'Discord ID and password are required.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        // Check if user exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE discord_id = ?");
        $stmt->execute([$discord_id]);
        
        if ($stmt->fetch()) {
            $error = 'This Discord ID is already registered.';
        } else {
            // Create user
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (discord_id, password_hash) VALUES (?, ?)");
            
            if ($stmt->execute([$discord_id, $password_hash])) {
                $success = 'Account created! You can now login.';
            } else {
                $error = 'Something went wrong. Please try again.';
            }
        }
    }
}
?>

<div class="container">
    <div class="form-container">
        <h1 class="form-title">Join the Community</h1>
        <p class="form-subtitle">Create an account to upload and share wallpapers</p>
        
        <?php if ($error): ?>
            <div class="message message-error">
                <span>⚠️</span> <?php echo sanitize($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="message message-success">
                <span>✅</span> <?php echo sanitize($success); ?>
                <a href="login.php" style="margin-left: 10px;">Login now →</a>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="discord_id">Discord ID</label>
                <input type="text" id="discord_id" name="discord_id" 
                       placeholder="e.g., username_123" 
                       value="<?php echo sanitize($_POST['discord_id'] ?? ''); ?>" required>
                <p class="form-hint">Your Discord username (not the display name)</p>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" 
                       placeholder="Min 6 characters" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" 
                       placeholder="Repeat your password" required>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                <span>🐧</span> Create Account
            </button>
        </form>
        
        <p style="text-align: center; margin-top: 25px; color: var(--text-secondary);">
            Already have an account? <a href="login.php">Login here</a>
        </p>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
