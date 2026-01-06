<?php
require_once '../config.php';

header('Content-Type: application/json');

$id = intval($_GET['id'] ?? 0);
$ip = $_SERVER['REMOTE_ADDR'];

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Invalid wallpaper']);
    exit;
}

// Check if already liked
$stmt = $pdo->prepare("SELECT id FROM wallpaper_likes WHERE wallpaper_id = ? AND user_ip = ?");
$stmt->execute([$id, $ip]);

if ($stmt->fetch()) {
    // Get current likes
    $stmt = $pdo->prepare("SELECT likes FROM wallpapers WHERE id = ?");
    $stmt->execute([$id]);
    $likes = $stmt->fetchColumn();
    
    echo json_encode(['success' => false, 'message' => 'Already liked', 'likes' => $likes]);
    exit;
}

// Add like
try {
    $pdo->beginTransaction();
    
    $stmt = $pdo->prepare("INSERT INTO wallpaper_likes (wallpaper_id, user_ip) VALUES (?, ?)");
    $stmt->execute([$id, $ip]);
    
    $stmt = $pdo->prepare("UPDATE wallpapers SET likes = likes + 1 WHERE id = ?");
    $stmt->execute([$id]);
    
    $pdo->commit();
    
    // Get new count
    $stmt = $pdo->prepare("SELECT likes FROM wallpapers WHERE id = ?");
    $stmt->execute([$id]);
    $likes = $stmt->fetchColumn();
    
    echo json_encode(['success' => true, 'likes' => $likes]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error']);
}
?>
