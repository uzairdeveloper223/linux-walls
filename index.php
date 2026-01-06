<?php
$pageTitle = 'Home';
require_once 'includes/header.php';

// Get latest wallpapers
$stmt = $pdo->query("SELECT w.*, u.discord_id as uploader_discord 
                     FROM wallpapers w 
                     LEFT JOIN users u ON w.uploader_id = u.id 
                     ORDER BY w.created_at DESC 
                     LIMIT 12");
$latestWallpapers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get popular wallpapers
$stmt = $pdo->query("SELECT w.*, u.discord_id as uploader_discord 
                     FROM wallpapers w 
                     LEFT JOIN users u ON w.uploader_id = u.id 
                     ORDER BY w.likes DESC 
                     LIMIT 6");
$popularWallpapers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get stats
$totalWallpapers = $pdo->query("SELECT COUNT(*) FROM wallpapers")->fetchColumn();
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalDownloads = $pdo->query("SELECT SUM(downloads) FROM wallpapers")->fetchColumn() ?: 0;
?>

<section class="hero">
    <div class="container">
        <div class="hero-badge">
            🐧 Made for <span>Linux Users</span> & Larpmaxxers
        </div>
        <h1>Free Wallpapers for Your Linux Desktop</h1>
        <p>A community-driven collection of static and live wallpapers. Upload, share, and discover beautiful backgrounds for your setup.</p>
        <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
            <a href="browse.php" class="btn btn-primary">
                <span>🔍</span> Browse Wallpapers
            </a>
            <a href="<?php echo isLoggedIn() ? 'upload.php' : 'register.php'; ?>" class="btn btn-secondary">
                <span>📤</span> Start Uploading
            </a>
        </div>
        
        <div class="terminal-box" style="max-width: 500px; margin: 30px auto 0; text-align: left;">
            <span class="terminal-prompt">$</span> <span class="terminal-command">neofetch --stats</span><br>
            <span style="color: var(--text-secondary);">
                Wallpapers: <?php echo number_format($totalWallpapers); ?><br>
                Users: <?php echo number_format($totalUsers); ?><br>
                Downloads: <?php echo number_format($totalDownloads); ?>
            </span>
        </div>
    </div>
</section>

<?php if (!empty($popularWallpapers)): ?>
<section class="container" style="padding-top: 50px;">
    <h2 style="margin-bottom: 25px; display: flex; align-items: center; gap: 10px;">
        <span>🔥</span> Popular Wallpapers
    </h2>
    <div class="wallpaper-grid">
        <?php foreach ($popularWallpapers as $wallpaper): ?>
            <?php include 'includes/wallpaper-card.php'; ?>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<section class="container" style="padding-top: 50px;">
    <h2 style="margin-bottom: 25px; display: flex; align-items: center; gap: 10px;">
        <span>✨</span> Latest Uploads
    </h2>
    <?php if (!empty($latestWallpapers)): ?>
        <div class="wallpaper-grid">
            <?php foreach ($latestWallpapers as $wallpaper): ?>
                <?php include 'includes/wallpaper-card.php'; ?>
            <?php endforeach; ?>
        </div>
        <div style="text-align: center; margin-top: 40px;">
            <a href="browse.php" class="btn btn-secondary">View All Wallpapers →</a>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 60px; color: var(--text-secondary);">
            <p style="font-size: 3rem; margin-bottom: 15px;">🖼️</p>
            <p>No wallpapers yet. Be the first to upload!</p>
            <a href="upload.php" class="btn btn-primary" style="margin-top: 20px;">Upload Now</a>
        </div>
    <?php endif; ?>
</section>

<?php require_once 'includes/footer.php'; ?>
