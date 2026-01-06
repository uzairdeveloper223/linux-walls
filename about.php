<?php
$pageTitle = 'About';
require_once 'includes/header.php';
?>

<div class="container">
    <div class="form-container" style="max-width: 800px; margin-top: 50px;">
        <h1 class="form-title">About Linux Wallpapers</h1>
        
        <div style="margin-top: 30px; line-height: 1.8;">
            <p style="color: var(--text-secondary); margin-bottom: 25px;">
                Welcome to <strong style="color: var(--accent-primary);">Linux Wallpapers</strong> - a free, 
                community-driven platform for sharing beautiful wallpapers designed for Linux users and larpmaxxers alike.
            </p>
            
            <div class="terminal-box" style="margin-bottom: 25px;">
                <span class="terminal-prompt">$</span> <span class="terminal-command">cat /etc/about</span><br><br>
                <span style="color: var(--text-secondary);">
                    NAME="Linux Wallpapers"<br>
                    VERSION="1.0"<br>
                    CREATOR="<?php echo CREATOR_NAME; ?>"<br>
                    DISCORD="<?php echo CREATOR_DISCORD; ?>"<br>
                    URL="<?php echo SITE_URL; ?>"<br>
                    LICENSE="Free for everyone"
                </span>
            </div>
            
            <h2 style="margin: 30px 0 15px; font-size: 1.2rem;">🐧 Features</h2>
            <ul style="color: var(--text-secondary); padding-left: 25px;">
                <li>Upload and share static wallpapers (JPG, PNG, WebP, GIF)</li>
                <li>Upload and share live wallpapers (MP4, WebM)</li>
                <li>Like and download wallpapers from the community</li>
                <li>Easy sharing with unique URLs</li>
                <li>No ads, no tracking, just wallpapers</li>
            </ul>
            
            <h2 style="margin: 30px 0 15px; font-size: 1.2rem;">🎯 For Linux Users</h2>
            <p style="color: var(--text-secondary); margin-bottom: 15px;">
                This site is built by Linux users, for Linux users. Whether you're running GNOME, KDE, 
                Hyprland, i3, or any other DE/WM, we've got wallpapers that'll make your rice look amazing.
            </p>
            
            <h2 style="margin: 30px 0 15px; font-size: 1.2rem;">📞 Contact</h2>
            <p style="color: var(--text-secondary);">
                Got questions, suggestions, or just want to chat? Hit me up on Discord:
            </p>
            <div class="terminal-box" style="margin-top: 15px;">
                <span class="terminal-prompt">$</span> <span class="terminal-command">echo "Contact: <?php echo CREATOR_DISCORD; ?>"</span>
            </div>
            
            <div style="margin-top: 40px; padding: 25px; background: var(--bg-secondary); border-radius: 12px; text-align: center;">
                <p style="font-size: 1.1rem; margin-bottom: 15px;">Ready to contribute?</p>
                <a href="register.php" class="btn btn-primary">
                    <span>🚀</span> Join the Community
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
