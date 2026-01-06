<?php
$pageTitle = 'Upload Wallpaper';
require_once 'includes/header.php';

// Require login
if (!isLoggedIn()) {
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}

$error = '';
$success = '';

// Handle regular (small file) uploads via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['wallpaper'])) {
    $name = trim($_POST['name'] ?? '');
    $type = $_POST['type'] ?? 'static';
    
    if (empty($name)) {
        $error = 'Please provide a name for your wallpaper.';
    } elseif (!isset($_FILES['wallpaper']) || $_FILES['wallpaper']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Please select a file to upload.';
    } else {
        $file = $_FILES['wallpaper'];
        $allowed_images = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $allowed_videos = ['video/mp4', 'video/webm'];
        $allowed = $type === 'live' ? $allowed_videos : $allowed_images;
        
        if (!in_array($file['type'], $allowed)) {
            $error = $type === 'live' 
                ? 'Live wallpapers must be MP4 or WebM format.' 
                : 'Static wallpapers must be JPG, PNG, WebP, GIF format.';
        } elseif ($file['size'] > 50 * 1024 * 1024) {
            $error = 'File size must be under 50MB.';
        } else {
            // Upload to Cloudinary
            $cloudinary_url = 'https://api.cloudinary.com/v1_1/' . CLOUDINARY_CLOUD_NAME . '/auto/upload';
            
            $timestamp = time();
            $params_to_sign = [
                'folder' => 'linux_wallpapers',
                'timestamp' => $timestamp
            ];
            ksort($params_to_sign);
            $string_to_sign = http_build_query($params_to_sign) . CLOUDINARY_API_SECRET;
            $signature = sha1($string_to_sign);
            
            $post_data = [
                'file' => new CURLFile($file['tmp_name'], $file['type'], $file['name']),
                'folder' => 'linux_wallpapers',
                'timestamp' => $timestamp,
                'api_key' => CLOUDINARY_API_KEY,
                'signature' => $signature
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $cloudinary_url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($http_code === 200) {
                $result = json_decode($response, true);
                
                $unique_id = generateUniqueId();
                $url = $result['secure_url'];
                $public_id = $result['public_id'];
                
                $thumbnail_url = $url;
                if ($type === 'live') {
                    $thumbnail_url = str_replace('/video/upload/', '/video/upload/so_0,w_400,h_250,c_fill/', $url);
                    $thumbnail_url = preg_replace('/\.(mp4|webm)$/i', '.jpg', $thumbnail_url);
                }
                
                $stmt = $pdo->prepare("INSERT INTO wallpapers (unique_id, name, url, thumbnail_url, cloudinary_public_id, type, uploader_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
                
                if ($stmt->execute([$unique_id, $name, $url, $thumbnail_url, $public_id, $type, $_SESSION['user_id']])) {
                    $success = 'Wallpaper uploaded successfully!';
                    $share_url = SITE_URL . '/share.php?id=' . $unique_id;
                } else {
                    $error = 'Failed to save wallpaper info.';
                }
            } else {
                $error = 'Upload failed. Please try again.';
            }
        }
    }
}
?>

<div class="container">
    <div class="form-container" style="max-width: 600px;">
        <h1 class="form-title">Upload Wallpaper</h1>
        <p class="form-subtitle">Share your wallpaper with the Linux community</p>
        
        <div id="error-message" class="message message-error" style="display: none;">
            <span>⚠️</span> <span id="error-text"></span>
        </div>
        
        <div id="success-message" class="message message-success" style="display: none;">
            <span>✅</span> <span id="success-text"></span>
        </div>
        
        <div id="share-url-container" class="share-url" style="margin-bottom: 20px; display: none;">
            <label>Share URL:</label>
            <div class="share-url-input">
                <input type="text" id="share-url" readonly>
                <button class="btn btn-primary" onclick="copyShareUrl()">Copy</button>
            </div>
        </div>
        
        <?php if ($error): ?>
            <div class="message message-error">
                <span>⚠️</span> <?php echo sanitize($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="message message-success">
                <span>✅</span> <?php echo sanitize($success); ?>
            </div>
            <?php if (isset($share_url)): ?>
                <div class="share-url" style="margin-bottom: 20px;">
                    <label>Share URL:</label>
                    <div class="share-url-input">
                        <input type="text" value="<?php echo $share_url; ?>" readonly id="share-url-php">
                        <button class="btn btn-primary" onclick="copyShareUrl('share-url-php')">Copy</button>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <form method="POST" action="" enctype="multipart/form-data" id="upload-form">
            <div class="form-group">
                <label for="name">Wallpaper Name</label>
                <input type="text" id="name" name="name" 
                       placeholder="e.g., Arch Linux Minimal" required>
            </div>
            
            <div class="form-group">
                <label for="type">Wallpaper Type</label>
                <select id="type" name="type">
                    <option value="static">🖼️ Static (Image)</option>
                    <option value="live">▶️ Live (Video)</option>
                </select>
                <p class="form-hint" id="type-hint">Supported: JPG, PNG, WebP, GIF (max 50MB)</p>
            </div>
            
            <div class="form-group">
                <label>Wallpaper File</label>
                <div class="upload-area" id="upload-area">
                    <div class="upload-icon">📁</div>
                    <p class="upload-text">
                        Drag & drop your file here or <span>browse</span>
                    </p>
                    <input type="file" id="wallpaper" name="wallpaper" 
                           accept="image/*,video/mp4,video/webm" 
                           style="display: none;" required>
                </div>
                <div id="file-preview" style="margin-top: 15px; display: none;">
                    <p style="color: var(--accent-primary);">Selected: <span id="file-name"></span></p>
                    <p id="file-size-info" style="color: var(--text-secondary); font-size: 0.9em;"></p>
                </div>
            </div>
            
            <!-- Progress bar for chunked uploads -->
            <div id="upload-progress" style="display: none; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                    <span id="progress-status">Preparing upload...</span>
                    <span id="progress-percent">0%</span>
                </div>
                <div class="progress-bar-container" style="background: var(--bg-tertiary); border-radius: 8px; height: 10px; overflow: hidden;">
                    <div id="progress-bar" style="background: var(--accent-primary); height: 100%; width: 0%; transition: width 0.3s ease;"></div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary" id="submit-btn" style="width: 100%; justify-content: center;">
                <span>📤</span> Upload Wallpaper
            </button>
        </form>
    </div>
</div>

<script>
const CHUNK_SIZE = 2 * 1024 * 1024; // 2MB chunks (safe for InfinityFree)
const MAX_DIRECT_SIZE = 8 * 1024 * 1024; // 8MB - use chunked upload above this

// Type hint update
document.getElementById('type').addEventListener('change', function() {
    const hint = document.getElementById('type-hint');
    if (this.value === 'live') {
        hint.textContent = 'Supported: MP4, WebM (max 50MB)';
        document.getElementById('wallpaper').accept = 'video/mp4,video/webm';
    } else {
        hint.textContent = 'Supported: JPG, PNG, WebP, GIF (max 50MB)';
        document.getElementById('wallpaper').accept = 'image/*';
    }
});

// Upload area functionality
const uploadArea = document.getElementById('upload-area');
const fileInput = document.getElementById('wallpaper');
const filePreview = document.getElementById('file-preview');
const fileName = document.getElementById('file-name');
const fileSizeInfo = document.getElementById('file-size-info');
const uploadForm = document.getElementById('upload-form');

uploadArea.addEventListener('click', () => fileInput.click());

uploadArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadArea.classList.add('dragover');
});

uploadArea.addEventListener('dragleave', () => {
    uploadArea.classList.remove('dragover');
});

uploadArea.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadArea.classList.remove('dragover');
    if (e.dataTransfer.files.length) {
        fileInput.files = e.dataTransfer.files;
        showFileInfo(e.dataTransfer.files[0]);
    }
});

fileInput.addEventListener('change', () => {
    if (fileInput.files.length) {
        showFileInfo(fileInput.files[0]);
    }
});

function showFileInfo(file) {
    fileName.textContent = file.name;
    const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
    
    if (file.size > MAX_DIRECT_SIZE) {
        fileSizeInfo.innerHTML = `Size: ${sizeMB}MB - <span style="color: var(--accent-primary);">Will use chunked upload</span>`;
    } else {
        fileSizeInfo.textContent = `Size: ${sizeMB}MB`;
    }
    
    filePreview.style.display = 'block';
}

// Handle form submission
uploadForm.addEventListener('submit', async function(e) {
    const file = fileInput.files[0];
    const name = document.getElementById('name').value.trim();
    const type = document.getElementById('type').value;
    
    if (!file || !name) return; // Let normal validation handle it
    
    // For small files, let the form submit normally
    if (file.size <= MAX_DIRECT_SIZE) {
        return; // Normal form submission
    }
    
    // For large files, use chunked upload
    e.preventDefault();
    
    const submitBtn = document.getElementById('submit-btn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span>⏳</span> Uploading...';
    
    hideMessages();
    showProgress();
    
    try {
        // For images, try to compress first
        let fileToUpload = file;
        if (type === 'static' && file.type.startsWith('image/')) {
            updateProgress('Compressing image...', 0);
            fileToUpload = await compressImage(file);
            
            // If compressed file is small enough, use direct upload
            if (fileToUpload.size <= MAX_DIRECT_SIZE) {
                updateProgress('Uploading compressed image...', 50);
                await directUpload(fileToUpload, name, type);
                return;
            }
        }
        
        // Chunked upload
        await chunkedUpload(fileToUpload, name, type);
        
    } catch (error) {
        showError(error.message || 'Upload failed. Please try again.');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<span>📤</span> Upload Wallpaper';
        hideProgress();
    }
});

// Compress image using canvas
async function compressImage(file) {
    return new Promise((resolve, reject) => {
        const img = new Image();
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        
        img.onload = () => {
            // Keep original dimensions but compress quality
            let width = img.width;
            let height = img.height;
            
            // Max dimension 4K
            const maxDim = 3840;
            if (width > maxDim || height > maxDim) {
                if (width > height) {
                    height = (height / width) * maxDim;
                    width = maxDim;
                } else {
                    width = (width / height) * maxDim;
                    height = maxDim;
                }
            }
            
            canvas.width = width;
            canvas.height = height;
            ctx.drawImage(img, 0, 0, width, height);
            
            // Try different quality levels
            let quality = 0.9;
            let blob;
            
            const tryCompress = () => {
                canvas.toBlob((b) => {
                    blob = b;
                    if (blob.size > MAX_DIRECT_SIZE && quality > 0.5) {
                        quality -= 0.1;
                        tryCompress();
                    } else {
                        const compressedFile = new File([blob], file.name, { type: 'image/jpeg' });
                        console.log(`Compressed: ${(file.size/1024/1024).toFixed(2)}MB -> ${(compressedFile.size/1024/1024).toFixed(2)}MB`);
                        resolve(compressedFile);
                    }
                }, 'image/jpeg', quality);
            };
            
            tryCompress();
        };
        
        img.onerror = () => reject(new Error('Failed to load image for compression'));
        img.src = URL.createObjectURL(file);
    });
}

// Direct upload for small/compressed files
async function directUpload(file, name, type) {
    const formData = new FormData();
    formData.append('wallpaper', file);
    formData.append('name', name);
    formData.append('type', type);
    
    const response = await fetch('upload.php', {
        method: 'POST',
        body: formData
    });
    
    // Reload page to show result
    window.location.reload();
}

// Chunked upload for large files
async function chunkedUpload(file, name, type) {
    const totalChunks = Math.ceil(file.size / CHUNK_SIZE);
    
    // 1. Initialize upload
    updateProgress('Initializing upload...', 0);
    
    const initData = new FormData();
    initData.append('action', 'init');
    initData.append('fileName', file.name);
    initData.append('fileSize', file.size);
    initData.append('totalChunks', totalChunks);
    initData.append('fileType', file.type);
    
    const initResponse = await fetch('api/chunked-upload.php', {
        method: 'POST',
        body: initData
    });
    
    const initResult = await initResponse.json();
    if (!initResult.success) {
        throw new Error(initResult.error || 'Failed to initialize upload');
    }
    
    const uploadId = initResult.uploadId;
    
    // 2. Upload chunks
    for (let i = 0; i < totalChunks; i++) {
        const start = i * CHUNK_SIZE;
        const end = Math.min(start + CHUNK_SIZE, file.size);
        const chunk = file.slice(start, end);
        
        const chunkData = new FormData();
        chunkData.append('action', 'chunk');
        chunkData.append('uploadId', uploadId);
        chunkData.append('chunkIndex', i);
        chunkData.append('chunk', chunk);
        
        const chunkResponse = await fetch('api/chunked-upload.php', {
            method: 'POST',
            body: chunkData
        });
        
        const chunkResult = await chunkResponse.json();
        if (!chunkResult.success) {
            // Cancel upload on error
            await cancelUpload(uploadId);
            throw new Error(chunkResult.error || 'Failed to upload chunk');
        }
        
        const progress = Math.round(((i + 1) / totalChunks) * 80); // 80% for chunks
        updateProgress(`Uploading chunk ${i + 1}/${totalChunks}...`, progress);
    }
    
    // 3. Complete upload
    updateProgress('Finalizing upload to Cloudinary...', 85);
    
    const completeData = new FormData();
    completeData.append('action', 'complete');
    completeData.append('uploadId', uploadId);
    completeData.append('name', name);
    completeData.append('type', type);
    
    const completeResponse = await fetch('api/chunked-upload.php', {
        method: 'POST',
        body: completeData
    });
    
    const completeResult = await completeResponse.json();
    if (!completeResult.success) {
        throw new Error(completeResult.error || 'Failed to complete upload');
    }
    
    updateProgress('Upload complete!', 100);
    
    // Show success
    showSuccess('Wallpaper uploaded successfully!');
    showShareUrl(completeResult.share_url);
    
    // Reset form
    uploadForm.reset();
    filePreview.style.display = 'none';
}

async function cancelUpload(uploadId) {
    const cancelData = new FormData();
    cancelData.append('action', 'cancel');
    cancelData.append('uploadId', uploadId);
    
    await fetch('api/chunked-upload.php', {
        method: 'POST',
        body: cancelData
    });
}

// UI helpers
function showProgress() {
    document.getElementById('upload-progress').style.display = 'block';
}

function hideProgress() {
    document.getElementById('upload-progress').style.display = 'none';
}

function updateProgress(status, percent) {
    document.getElementById('progress-status').textContent = status;
    document.getElementById('progress-percent').textContent = percent + '%';
    document.getElementById('progress-bar').style.width = percent + '%';
}

function showError(message) {
    const errorDiv = document.getElementById('error-message');
    document.getElementById('error-text').textContent = message;
    errorDiv.style.display = 'flex';
}

function showSuccess(message) {
    const successDiv = document.getElementById('success-message');
    document.getElementById('success-text').textContent = message;
    successDiv.style.display = 'flex';
}

function showShareUrl(url) {
    const container = document.getElementById('share-url-container');
    document.getElementById('share-url').value = url;
    container.style.display = 'block';
}

function hideMessages() {
    document.getElementById('error-message').style.display = 'none';
    document.getElementById('success-message').style.display = 'none';
    document.getElementById('share-url-container').style.display = 'none';
}

function copyShareUrl(inputId = 'share-url') {
    const input = document.getElementById(inputId);
    input.select();
    document.execCommand('copy');
    alert('URL copied to clipboard!');
}
</script>

<?php require_once 'includes/footer.php'; ?>
