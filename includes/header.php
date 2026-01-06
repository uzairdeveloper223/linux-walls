<?php require_once __DIR__ . '/../config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?><?php echo SITE_NAME; ?></title>
    <meta name="description" content="Free Linux wallpapers for the community. Static and live wallpapers for your desktop.">
    <meta property="og:title" content="<?php echo SITE_NAME; ?>">
    <meta property="og:description" content="Free Linux wallpapers for larpmaxxers and Linux enthusiasts">
    <meta property="og:url" content="<?php echo SITE_URL; ?>">
    <link rel="icon" type="image/svg+xml" href="<?php echo SITE_URL; ?>/favicon.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo SITE_URL; ?>/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo SITE_URL; ?>/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo SITE_URL; ?>/apple-touch-icon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="<?php echo SITE_URL; ?>" class="logo">
                    <span class="logo-icon">🐧</span>
                    <span>Linux Walls</span>
                </a>
                <nav class="nav">
                    <a href="<?php echo SITE_URL; ?>" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Home</a>
                    <a href="<?php echo SITE_URL; ?>/browse.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'browse.php' ? 'active' : ''; ?>">Browse</a>
                    <?php if (isLoggedIn()): ?>
                        <a href="<?php echo SITE_URL; ?>/upload.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'upload.php' ? 'active' : ''; ?>">Upload</a>
                        <a href="<?php echo SITE_URL; ?>/profile.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">Profile</a>
                        <a href="<?php echo SITE_URL; ?>/logout.php" class="btn btn-secondary">Logout</a>
                    <?php else: ?>
                        <a href="<?php echo SITE_URL; ?>/login.php" class="nav-link">Login</a>
                        <a href="<?php echo SITE_URL; ?>/register.php" class="btn btn-primary">Join Us</a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>
