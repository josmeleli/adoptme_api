<?php
require_once __DIR__ . '/../config.php';

$user_id = $_GET['user_id'] ?? null;
if (!$user_id) {
    http_response_code(400);
    echo json_encode(['error' => 'user_id required']);
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT * FROM notifications WHERE user_id = :uid ORDER BY created_at DESC LIMIT 100');
    $stmt->execute([':uid'=>$user_id]);
    $notes = $stmt->fetchAll();
    echo json_encode($notes);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Could not fetch notifications', 'details' => $e->getMessage()]);
}

?>
