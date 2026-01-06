// Linux Wallpapers - Main JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Video hover play
    initVideoHover();
    
    // Like buttons
    initLikeButtons();
    
    // Lazy loading for videos
    initLazyVideos();
});

// Play videos on hover
function initVideoHover() {
    const videoCards = document.querySelectorAll('.wallpaper-card video');
    
    videoCards.forEach(video => {
        const card = video.closest('.wallpaper-card');
        
        card.addEventListener('mouseenter', () => {
            video.play().catch(() => {});
        });
        
        card.addEventListener('mouseleave', () => {
            video.pause();
            video.currentTime = 0;
        });
    });
}

// Like button functionality
function initLikeButtons() {
    const likeButtons = document.querySelectorAll('.like-btn');
    
    likeButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const id = this.dataset.id;
            if (!id) return;
            
            fetch(`/api/like.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.innerHTML = `❤️ ${data.likes.toLocaleString()}`;
                        this.style.background = 'rgba(45, 212, 191, 0.2)';
                    } else {
                        // Already liked - show feedback
                        this.style.background = 'rgba(248, 81, 73, 0.2)';
                        setTimeout(() => {
                            this.style.background = '';
                        }, 500);
                    }
                })
                .catch(err => console.error('Like error:', err));
        });
    });
}

// Lazy load videos when they come into view
function initLazyVideos() {
    if ('IntersectionObserver' in window) {
        const videoObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const video = entry.target;
                    if (video.dataset.src) {
                        video.src = video.dataset.src;
                        video.removeAttribute('data-src');
                    }
                    videoObserver.unobserve(video);
                }
            });
        }, { rootMargin: '100px' });
        
        document.querySelectorAll('video[data-src]').forEach(video => {
            videoObserver.observe(video);
        });
    }
}

// Copy to clipboard helper
function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            showToast('Copied to clipboard!');
        });
    } else {
        // Fallback
        const textarea = document.createElement('textarea');
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        showToast('Copied to clipboard!');
    }
}

// Simple toast notification
function showToast(message) {
    const existing = document.querySelector('.toast');
    if (existing) existing.remove();
    
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.textContent = message;
    toast.style.cssText = `
        position: fixed;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: var(--accent-primary);
        color: var(--bg-primary);
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 600;
        z-index: 9999;
        animation: fadeInUp 0.3s ease;
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'fadeOut 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 2000);
}

// Add animation keyframes
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateX(-50%) translateY(20px); }
        to { opacity: 1; transform: translateX(-50%) translateY(0); }
    }
    @keyframes fadeOut {
        from { opacity: 1; }
        to { opacity: 0; }
    }
`;
document.head.appendChild(style);

// Console easter egg for Linux nerds
console.log('%c🐧 Linux Wallpapers', 'font-size: 24px; font-weight: bold; color: #2dd4bf;');
console.log('%cMade with ❤️ for the Linux community', 'font-size: 14px; color: #8b949e;');
console.log('%cCreated by Uzair Mughal (mughal_x22)', 'font-size: 12px; color: #8b949e;');
