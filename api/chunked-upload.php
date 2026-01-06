<?php
/**
 * Chunked Upload Handler
 * Handles large file uploads by receiving chunks and reassembling them
 */

require_once '../config.php';

header('Content-Type: application/json');

// Require login
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Temp directory for chunks
$tempDir = sys_get_temp_dir() . '/wallpaper_chunks/';
if (!is_dir($tempDir)) {
    mkdir($tempDir, 0755, true);
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'init':
        // Initialize a new chunked upload
        handleInit();
        break;
    case 'chunk':
        // Receive a chunk
        handleChunk($tempDir);
        break;
    case 'complete':
        // Finalize and upload to Cloudinary
        handleComplete($tempDir);
        break;
    case 'cancel':
        // Cancel and cleanup
        handleCancel($tempDir);
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
}

function handleInit() {
    $fileName = $_POST['fileName'] ?? '';
    $fileSize = intval($_POST['fileSize'] ?? 0);
    $totalChunks = intval($_POST['totalChunks'] ?? 0);
    $fileType = $_POST['fileType'] ?? '';
    
    if (empty($fileName) || $fileSize <= 0 || $totalChunks <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid parameters']);
        return;
    }
    
    // Generate unique upload ID
    $uploadId = bin2hex(random_bytes(16));
    
    // Store upload info in session
    $_SESSION['chunked_uploads'][$uploadId] = [
        'fileName' => $fileName,
        'fileSize' => $fileSize,
        'totalChunks' => $totalChunks,
        'fileType' => $fileType,
        'receivedChunks' => [],
        'startTime' => time()
    ];
    
    echo json_encode([
        'success' => true,
        'uploadId' => $uploadId
    ]);
}

function handleChunk($tempDir) {
    $uploadId = $_POST['uploadId'] ?? '';
    $chunkIndex = intval($_POST['chunkIndex'] ?? -1);
    
    if (empty($uploadId) || $chunkIndex < 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid parameters']);
        return;
    }
    
    if (!isset($_SESSION['chunked_uploads'][$uploadId])) {
        http_response_code(400);
        echo json_encode(['error' => 'Upload session not found']);
        return;
    }
    
    if (!isset($_FILES['chunk']) || $_FILES['chunk']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['error' => 'No chunk received']);
        return;
    }
    
    // Save chunk to temp directory
    $chunkPath = $tempDir . $uploadId . '_chunk_' . $chunkIndex;
    
    if (!move_uploaded_file($_FILES['chunk']['tmp_name'], $chunkPath)) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save chunk']);
        return;
    }
    
    // Mark chunk as received
    $_SESSION['chunked_uploads'][$uploadId]['receivedChunks'][$chunkIndex] = true;
    
    $received = count($_SESSION['chunked_uploads'][$uploadId]['receivedChunks']);
    $total = $_SESSION['chunked_uploads'][$uploadId]['totalChunks'];
    
    echo json_encode([
        'success' => true,
        'chunkIndex' => $chunkIndex,
        'received' => $received,
        'total' => $total
    ]);
}

function handleComplete($tempDir) {
    global $pdo;
    
    $uploadId = $_POST['uploadId'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $type = $_POST['type'] ?? 'static';
    
    if (empty($uploadId) || empty($name)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid parameters']);
        return;
    }
    
    if (!isset($_SESSION['chunked_uploads'][$uploadId])) {
        http_response_code(400);
        echo json_encode(['error' => 'Upload session not found']);
        return;
    }
    
    $uploadInfo = $_SESSION['chunked_uploads'][$uploadId];
    $totalChunks = $uploadInfo['totalChunks'];
    
    // Verify all chunks received
    if (count($uploadInfo['receivedChunks']) !== $totalChunks) {
        http_response_code(400);
        echo json_encode(['error' => 'Not all chunks received']);
        return;
    }
    
    // Reassemble file
    $assembledPath = $tempDir . $uploadId . '_assembled';
    $assembledFile = fopen($assembledPath, 'wb');
    
    if (!$assembledFile) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create assembled file']);
        return;
    }
    
    for ($i = 0; $i < $totalChunks; $i++) {
        $chunkPath = $tempDir . $uploadId . '_chunk_' . $i;
        if (!file_exists($chunkPath)) {
            fclose($assembledFile);
            http_response_code(500);
            echo json_encode(['error' => 'Missing chunk ' . $i]);
            return;
        }
        
        $chunkData = file_get_contents($chunkPath);
        fwrite($assembledFile, $chunkData);
        unlink($chunkPath); // Clean up chunk
    }
    
    fclose($assembledFile);
    
    // Upload to Cloudinary
    $result = uploadToCloudinary($assembledPath, $uploadInfo['fileType']);
    
    // Clean up assembled file
    unlink($assembledPath);
    
    // Clean up session
    unset($_SESSION['chunked_uploads'][$uploadId]);
    
    if (!$result['success']) {
        http_response_code(500);
        echo json_encode(['error' => $result['error']]);
        return;
    }
    
    // Save to database
    $unique_id = generateUniqueId();
    $url = $result['url'];
    $public_id = $result['public_id'];
    
    // Generate thumbnail for videos
    $thumbnail_url = $url;
    if ($type === 'live') {
        $thumbnail_url = str_replace('/video/upload/', '/video/upload/so_0,w_400,h_250,c_fill/', $url);
        $thumbnail_url = preg_replace('/\.(mp4|webm)$/i', '.jpg', $thumbnail_url);
    }
    
    $stmt = $pdo->prepare("INSERT INTO wallpapers (unique_id, name, url, thumbnail_url, cloudinary_public_id, type, uploader_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    if ($stmt->execute([$unique_id, $name, $url, $thumbnail_url, $public_id, $type, $_SESSION['user_id']])) {
        echo json_encode([
            'success' => true,
            'unique_id' => $unique_id,
            'share_url' => SITE_URL . '/share.php?id=' . $unique_id
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save to database']);
    }
}

function handleCancel($tempDir) {
    $uploadId = $_POST['uploadId'] ?? '';
    
    if (empty($uploadId)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid upload ID']);
        return;
    }
    
    // Clean up chunks
    if (isset($_SESSION['chunked_uploads'][$uploadId])) {
        $totalChunks = $_SESSION['chunked_uploads'][$uploadId]['totalChunks'];
        for ($i = 0; $i < $totalChunks; $i++) {
            $chunkPath = $tempDir . $uploadId . '_chunk_' . $i;
            if (file_exists($chunkPath)) {
                unlink($chunkPath);
            }
        }
        unset($_SESSION['chunked_uploads'][$uploadId]);
    }
    
    echo json_encode(['success' => true]);
}

function uploadToCloudinary($filePath, $mimeType) {
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
        'file' => new CURLFile($filePath, $mimeType),
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
    curl_setopt($ch, CURLOPT_TIMEOUT, 300); // 5 min timeout for large files
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        $result = json_decode($response, true);
        return [
            'success' => true,
            'url' => $result['secure_url'],
            'public_id' => $result['public_id']
        ];
    }
    
    return [
        'success' => false,
        'error' => 'Cloudinary upload failed: ' . $response
    ];
}
?>
