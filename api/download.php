<?php
require_once '../config.php';

header('Content-Type: application/json');

$id = intval($_GET['id'] ?? 0);
$ip = $_SERVER['REMOTE_ADDR'];

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Invalid wallpaper']);
    exit;
}

try {
    // Track download
    $stmt = $pdo->prepare("INSERT INTO wallpaper_downloads (wallpaper_id, user_ip) VALUES (?, ?)");
    $stmt->execute([$id, $ip]);
    
    // Update count
    $stmt = $pdo->prepare("UPDATE wallpapers SET downloads = downloads + 1 WHERE id = ?");
    $stmt->execute([$id]);
    
    // Get new count
    $stmt = $pdo->prepare("SELECT downloads FROM wallpapers WHERE id = ?");
    $stmt->execute([$id]);
    $downloads = $stmt->fetchColumn();
    
    echo json_encode(['success' => true, 'downloads' => $downloads]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error']);
}
?>
