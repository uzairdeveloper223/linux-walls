<?php
require_once 'config.php';

$id = $_GET['id'] ?? '';

if (empty($id)) {
    header('Location: ' . SITE_URL);
    exit;
}

$stmt = $pdo->prepare("SELECT w.*, u.discord_id as uploader_discord 
                       FROM wallpapers w 
                       LEFT JOIN users u ON w.uploader_id = u.id 
                       WHERE w.unique_id = ?");
$stmt->execute([$id]);
$wallpaper = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$wallpaper) {
    $pageTitle = 'Not Found';
    require_once 'includes/header.php';
    echo '<div class="container" style="text-align: center; padding: 100px 20px;">
            <h1 style="font-size: 4rem; margin-bottom: 20px;">404</h1>
            <p style="color: var(--text-secondary); margin-bottom: 30px;">Wallpaper not found</p>
            <a href="' . SITE_URL . '" class="btn btn-primary">Go Home</a>
          </div>';
    require_once 'includes/footer.php';
    exit;
}

$pageTitle = $wallpaper['name'];
require_once 'includes/header.php';

$share_url = SITE_URL . '/share.php?id=' . $wallpaper['unique_id'];
?>

<div class="share-container">
    <div class="share-wallpaper">
        <?php if ($wallpaper['type'] === 'live'): ?>
            <video class="share-image" src="<?php echo sanitize($wallpaper['url']); ?>" 
                   controls autoplay muted loop playsinline></video>
        <?php else: ?>
            <img class="share-image" src="<?php echo sanitize($wallpaper['url']); ?>" 
                 alt="<?php echo sanitize($wallpaper['name']); ?>">
        <?php endif; ?>
        
        <div class="share-details">
            <h1 class="share-title"><?php echo sanitize($wallpaper['name']); ?></h1>
            
            <div class="share-meta">
                <span class="stat">
                    <span class="stat-icon">❤️</span>
                    <span id="like-count"><?php echo number_format($wallpaper['likes']); ?></span> likes
                </span>
                <span class="stat">
                    <span class="stat-icon">⬇️</span>
                    <span id="download-count"><?php echo number_format($wallpaper['downloads']); ?></span> downloads
                </span>
                <span class="stat">
                    <span class="stat-icon"><?php echo $wallpaper['type'] === 'live' ? '▶️' : '🖼️'; ?></span>
                    <?php echo ucfirst($wallpaper['type']); ?> wallpaper
                </span>
                <?php if (!empty($wallpaper['uploader_discord'])): ?>
                    <span class="stat">
                        <span class="stat-icon">👤</span>
                        Uploaded by <?php echo sanitize($wallpaper['uploader_discord']); ?>
                        <?php echo getSpecialTag($wallpaper['uploader_discord']); ?>
                    </span>
                <?php endif; ?>
            </div>
            
            <div class="share-actions">
                <button class="btn btn-primary" onclick="downloadWallpaper(<?php echo $wallpaper['id']; ?>, '<?php echo sanitize($wallpaper['url']); ?>', '<?php echo sanitize($wallpaper['name']); ?>')">
                    <span>⬇️</span> Download
                </button>
                <button class="btn btn-secondary like-btn" data-id="<?php echo $wallpaper['id']; ?>" onclick="likeWallpaper(<?php echo $wallpaper['id']; ?>)">
                    <span>❤️</span> Like
                </button>
                <button class="btn btn-secondary" onclick="copyShareUrl()">
                    <span>🔗</span> Copy Link
                </button>
            </div>
            
            <div class="share-url">
                <label>Share this wallpaper:</label>
                <div class="share-url-input">
                    <input type="text" value="<?php echo $share_url; ?>" readonly id="share-url">
                    <button class="btn btn-primary" onclick="copyShareUrl()">Copy</button>
                </div>
            </div>
            
            <div class="terminal-box" style="margin-top: 25px;">
                <span class="terminal-prompt">$</span> <span class="terminal-command"># Set as wallpaper (example for various DEs)</span><br>
                <span style="color: var(--text-secondary);">
                    # GNOME/GTK<br>
                    gsettings set org.gnome.desktop.background picture-uri "file:///path/to/wallpaper"<br><br>
                    # KDE Plasma<br>
                    plasma-apply-wallpaperimage /path/to/wallpaper<br><br>
                    # Hyprland<br>
                    hyprctl hyprpaper wallpaper "eDP-1,/path/to/wallpaper"
                </span>
            </div>
        </div>
    </div>
    
    <div style="text-align: center; margin-top: 30px;">
        <a href="browse.php" class="btn btn-secondary">← Back to Browse</a>
    </div>
</div>

<script>
function copyShareUrl() {
    const input = document.getElementById('share-url');
    input.select();
    document.execCommand('copy');
    alert('Link copied to clipboard!');
}

function downloadWallpaper(id, url, name) {
    // Track download
    fetch('api/download.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('download-count').textContent = data.downloads.toLocaleString();
            }
        });
    
    // Download file
    const a = document.createElement('a');
    a.href = url;
    a.download = name;
    a.target = '_blank';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}

function likeWallpaper(id) {
    fetch('api/like.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('like-count').textContent = data.likes.toLocaleString();
            } else {
                alert(data.message || 'Already liked!');
            }
        });
}
</script>

<?php require_once 'includes/footer.php'; ?>
