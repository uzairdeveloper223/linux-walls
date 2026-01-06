<?php
$pageTitle = 'My Profile';
require_once 'includes/header.php';

if (!isLoggedIn()) {
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}

$user = getCurrentUser();

// Get user's wallpapers
$stmt = $pdo->prepare("SELECT * FROM wallpapers WHERE uploader_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$myWallpapers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get stats
$totalLikes = $pdo->prepare("SELECT SUM(likes) FROM wallpapers WHERE uploader_id = ?");
$totalLikes->execute([$_SESSION['user_id']]);
$totalLikesCount = $totalLikes->fetchColumn() ?: 0;

$totalDownloads = $pdo->prepare("SELECT SUM(downloads) FROM wallpapers WHERE uploader_id = ?");
$totalDownloads->execute([$_SESSION['user_id']]);
$totalDownloadsCount = $totalDownloads->fetchColumn() ?: 0;

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);
    
    // Verify ownership
    $stmt = $pdo->prepare("SELECT * FROM wallpapers WHERE id = ? AND uploader_id = ?");
    $stmt->execute([$delete_id, $_SESSION['user_id']]);
    $wallpaper = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($wallpaper) {
        // Delete from Cloudinary (optional - you might want to keep the file)
        // Delete from database
        $stmt = $pdo->prepare("DELETE FROM wallpapers WHERE id = ?");
        $stmt->execute([$delete_id]);
        
        header('Location: ' . SITE_URL . '/profile.php?deleted=1');
        exit;
    }
}
?>

<div class="container">
    <div style="max-width: 900px; margin: 40px auto;">
        <div class="form-container" style="max-width: 100%; margin-bottom: 30px;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
                <div>
                    <h1 style="margin-bottom: 5px; display: flex; align-items: center; gap: 10px;">
                        👤 <?php echo sanitize($user['discord_id']); ?>
                        <?php echo getSpecialTag($user['discord_id']); ?>
                    </h1>
                    <p style="color: var(--text-secondary);">Member since <?php echo date('F Y', strtotime($user['created_at'])); ?></p>
                </div>
                <a href="upload.php" class="btn btn-primary">
                    <span>📤</span> Upload New
                </a>
            </div>
            
            <div style="display: flex; gap: 30px; margin-top: 25px; flex-wrap: wrap;">
                <div class="terminal-box" style="flex: 1; min-width: 150px;">
                    <p style="color: var(--text-secondary); font-size: 0.85rem;">Wallpapers</p>
                    <p style="font-size: 1.5rem; color: var(--accent-primary);"><?php echo count($myWallpapers); ?></p>
                </div>
                <div class="terminal-box" style="flex: 1; min-width: 150px;">
                    <p style="color: var(--text-secondary); font-size: 0.85rem;">Total Likes</p>
                    <p style="font-size: 1.5rem; color: var(--accent-primary);"><?php echo number_format($totalLikesCount); ?></p>
                </div>
                <div class="terminal-box" style="flex: 1; min-width: 150px;">
                    <p style="color: var(--text-secondary); font-size: 0.85rem;">Total Downloads</p>
                    <p style="font-size: 1.5rem; color: var(--accent-primary);"><?php echo number_format($totalDownloadsCount); ?></p>
                </div>
            </div>
        </div>
        
        <?php if (isset($_GET['deleted'])): ?>
            <div class="message message-success" style="margin-bottom: 20px;">
                <span>✅</span> Wallpaper deleted successfully.
            </div>
        <?php endif; ?>
        
        <h2 style="margin-bottom: 20px;">My Wallpapers</h2>
        
        <?php if (!empty($myWallpapers)): ?>
            <div class="wallpaper-grid">
                <?php foreach ($myWallpapers as $wallpaper): ?>
                    <article class="wallpaper-card">
                        <div class="wallpaper-image">
                            <?php if ($wallpaper['type'] === 'live'): ?>
                                <video src="<?php echo sanitize($wallpaper['url']); ?>" muted loop playsinline></video>
                            <?php else: ?>
                                <img src="<?php echo sanitize($wallpaper['thumbnail_url'] ?: $wallpaper['url']); ?>" 
                                     alt="<?php echo sanitize($wallpaper['name']); ?>" loading="lazy">
                            <?php endif; ?>
                            <span class="wallpaper-type type-<?php echo $wallpaper['type']; ?>">
                                <?php echo $wallpaper['type'] === 'live' ? '▶ Live' : '🖼 Static'; ?>
                            </span>
                        </div>
                        <div class="wallpaper-info">
                            <h3 class="wallpaper-name"><?php echo sanitize($wallpaper['name']); ?></h3>
                            <div class="wallpaper-meta">
                                <div class="wallpaper-stats">
                                    <span class="stat">❤️ <?php echo number_format($wallpaper['likes']); ?></span>
                                    <span class="stat">⬇️ <?php echo number_format($wallpaper['downloads']); ?></span>
                                </div>
                            </div>
                            <div style="display: flex; gap: 10px; margin-top: 15px;">
                                <a href="share.php?id=<?php echo $wallpaper['unique_id']; ?>" class="btn btn-secondary" style="flex: 1; justify-content: center;">View</a>
                                <form method="POST" style="flex: 1;" onsubmit="return confirm('Delete this wallpaper?');">
                                    <input type="hidden" name="delete_id" value="<?php echo $wallpaper['id']; ?>">
                                    <button type="submit" class="btn btn-secondary" style="width: 100%; justify-content: center; color: var(--error);">Delete</button>
                                </form>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 60px; background: var(--bg-card); border-radius: 12px; border: 1px solid var(--border-color);">
                <p style="font-size: 3rem; margin-bottom: 15px;">🖼️</p>
                <p style="color: var(--text-secondary); margin-bottom: 20px;">You haven't uploaded any wallpapers yet</p>
                <a href="upload.php" class="btn btn-primary">Upload Your First Wallpaper</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
