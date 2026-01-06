<article class="wallpaper-card">
    <div class="wallpaper-image">
        <?php if ($wallpaper['type'] === 'live'): ?>
            <video src="<?php echo sanitize($wallpaper['url']); ?>" muted loop playsinline></video>
        <?php else: ?>
            <img src="<?php echo sanitize($wallpaper['thumbnail_url'] ?: $wallpaper['url']); ?>" 
                 alt="<?php echo sanitize($wallpaper['name']); ?>" 
                 loading="lazy">
        <?php endif; ?>
        <span class="wallpaper-type type-<?php echo $wallpaper['type']; ?>">
            <?php echo $wallpaper['type'] === 'live' ? '▶ Live' : '🖼 Static'; ?>
        </span>
        <div class="wallpaper-overlay">
            <div class="overlay-actions">
                <a href="share.php?id=<?php echo sanitize($wallpaper['unique_id']); ?>" class="btn btn-primary">View</a>
                <button class="btn btn-secondary like-btn" data-id="<?php echo $wallpaper['id']; ?>">
                    ❤️ <?php echo number_format($wallpaper['likes']); ?>
                </button>
            </div>
        </div>
    </div>
    <div class="wallpaper-info">
        <h3 class="wallpaper-name"><?php echo sanitize($wallpaper['name']); ?></h3>
        <div class="wallpaper-meta">
            <div class="wallpaper-stats">
                <span class="stat">
                    <span class="stat-icon">❤️</span>
                    <?php echo number_format($wallpaper['likes']); ?>
                </span>
                <span class="stat">
                    <span class="stat-icon">⬇️</span>
                    <?php echo number_format($wallpaper['downloads']); ?>
                </span>
            </div>
            <?php if (!empty($wallpaper['uploader_discord'])): ?>
                <div class="uploader">
                    <span>by <?php echo sanitize($wallpaper['uploader_discord']); ?></span>
                    <?php echo getSpecialTag($wallpaper['uploader_discord']); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</article>
