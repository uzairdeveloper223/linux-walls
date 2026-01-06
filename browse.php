<?php
$pageTitle = 'Browse Wallpapers';
require_once 'includes/header.php';

// Filters
$type = $_GET['type'] ?? 'all';
$sort = $_GET['sort'] ?? 'latest';
$search = trim($_GET['search'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 24;
$offset = ($page - 1) * $per_page;

// Build query
$where = [];
$params = [];

if ($type === 'static' || $type === 'live') {
    $where[] = "w.type = ?";
    $params[] = $type;
}

if (!empty($search)) {
    $where[] = "(w.name LIKE ? OR u.discord_id LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Sort
$order = match($sort) {
    'popular' => 'w.likes DESC',
    'downloads' => 'w.downloads DESC',
    'oldest' => 'w.created_at ASC',
    default => 'w.created_at DESC'
};

// Get total count
$count_sql = "SELECT COUNT(*) FROM wallpapers w LEFT JOIN users u ON w.uploader_id = u.id $where_clause";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total = $stmt->fetchColumn();
$total_pages = ceil($total / $per_page);

// Get wallpapers
$sql = "SELECT w.*, u.discord_id as uploader_discord 
        FROM wallpapers w 
        LEFT JOIN users u ON w.uploader_id = u.id 
        $where_clause 
        ORDER BY $order 
        LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$wallpapers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <section class="hero" style="padding: 40px 0;">
        <h1>Browse Wallpapers</h1>
        <p>Discover <?php echo number_format($total); ?> wallpapers from the community</p>
    </section>
    
    <div class="filter-bar">
        <div class="filter-tabs">
            <a href="?type=all&sort=<?php echo $sort; ?>&search=<?php echo urlencode($search); ?>" 
               class="filter-tab <?php echo $type === 'all' ? 'active' : ''; ?>">All</a>
            <a href="?type=static&sort=<?php echo $sort; ?>&search=<?php echo urlencode($search); ?>" 
               class="filter-tab <?php echo $type === 'static' ? 'active' : ''; ?>">🖼️ Static</a>
            <a href="?type=live&sort=<?php echo $sort; ?>&search=<?php echo urlencode($search); ?>" 
               class="filter-tab <?php echo $type === 'live' ? 'active' : ''; ?>">▶️ Live</a>
        </div>
        
        <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
            <select onchange="window.location.href=this.value" style="padding: 8px 15px; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-primary); font-family: inherit;">
                <option value="?type=<?php echo $type; ?>&sort=latest&search=<?php echo urlencode($search); ?>" <?php echo $sort === 'latest' ? 'selected' : ''; ?>>Latest</option>
                <option value="?type=<?php echo $type; ?>&sort=popular&search=<?php echo urlencode($search); ?>" <?php echo $sort === 'popular' ? 'selected' : ''; ?>>Most Liked</option>
                <option value="?type=<?php echo $type; ?>&sort=downloads&search=<?php echo urlencode($search); ?>" <?php echo $sort === 'downloads' ? 'selected' : ''; ?>>Most Downloaded</option>
                <option value="?type=<?php echo $type; ?>&sort=oldest&search=<?php echo urlencode($search); ?>" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Oldest</option>
            </select>
            
            <form action="" method="GET" class="search-box">
                <input type="hidden" name="type" value="<?php echo $type; ?>">
                <input type="hidden" name="sort" value="<?php echo $sort; ?>">
                <span>🔍</span>
                <input type="text" name="search" placeholder="Search wallpapers..." 
                       value="<?php echo sanitize($search); ?>">
            </form>
        </div>
    </div>
    
    <?php if (!empty($wallpapers)): ?>
        <div class="wallpaper-grid">
            <?php foreach ($wallpapers as $wallpaper): ?>
                <?php include 'includes/wallpaper-card.php'; ?>
            <?php endforeach; ?>
        </div>
        
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?type=<?php echo $type; ?>&sort=<?php echo $sort; ?>&search=<?php echo urlencode($search); ?>&page=<?php echo $page - 1; ?>">← Prev</a>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <?php if ($i === $page): ?>
                        <span class="current"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?type=<?php echo $type; ?>&sort=<?php echo $sort; ?>&search=<?php echo urlencode($search); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?type=<?php echo $type; ?>&sort=<?php echo $sort; ?>&search=<?php echo urlencode($search); ?>&page=<?php echo $page + 1; ?>">Next →</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div style="text-align: center; padding: 80px 20px; color: var(--text-secondary);">
            <p style="font-size: 4rem; margin-bottom: 20px;">🔍</p>
            <p style="font-size: 1.2rem;">No wallpapers found</p>
            <?php if (!empty($search)): ?>
                <p style="margin-top: 10px;">Try a different search term</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
