<?php
$pageTitle = 'Login';
require_once 'includes/header.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $discord_id = trim($_POST['discord_id'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($discord_id) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE discord_id = ?");
        $stmt->execute([$discord_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['discord_id'] = $user['discord_id'];
            header('Location: ' . SITE_URL);
            exit;
        } else {
            $error = 'Invalid Discord ID or password.';
        }
    }
}
?>

<div class="container">
    <div class="form-container">
        <h1 class="form-title">Welcome Back</h1>
        <p class="form-subtitle">Login to upload and manage your wallpapers</p>
        
        <?php if ($error): ?>
            <div class="message message-error">
                <span>⚠️</span> <?php echo sanitize($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="discord_id">Discord ID</label>
                <input type="text" id="discord_id" name="discord_id" 
                       placeholder="e.g., username_123" 
                       value="<?php echo sanitize($_POST['discord_id'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" 
                       placeholder="Your password" required>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                <span>🔐</span> Login
            </button>
        </form>
        
        <p style="text-align: center; margin-top: 25px; color: var(--text-secondary);">
            Don't have an account? <a href="register.php">Register here</a>
        </p>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
