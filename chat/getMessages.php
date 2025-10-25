<?php
require_once __DIR__ . '/../config.php';

$user1 = $_GET['user1'] ?? null;
$user2 = $_GET['user2'] ?? null;

if (!$user1 || !$user2) {
    http_response_code(400);
    echo json_encode(['error' => 'user1 and user2 required']);
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT * FROM messages WHERE (sender_id = :u1 AND receiver_id = :u2) OR (sender_id = :u2 AND receiver_id = :u1) ORDER BY created_at ASC');
    $stmt->execute([':u1'=>$user1, ':u2'=>$user2]);
    $msgs = $stmt->fetchAll();
    echo json_encode($msgs);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Could not fetch messages', 'details' => $e->getMessage()]);
}

?>
