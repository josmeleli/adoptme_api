<?php
require_once __DIR__ . '/../config.php';

$data = json_input();
$from = $data['from'] ?? null;
$to = $data['to'] ?? null;
$message = $data['message'] ?? null;

if (!$from || !$to || !$message) {
    http_response_code(400);
    echo json_encode(['error' => 'from, to and message are required']);
    exit;
}

try {
    $stmt = $pdo->prepare('INSERT INTO messages (sender_id, receiver_id, message, created_at) VALUES (:from, :to, :message, NOW())');
    $stmt->execute([':from'=>$from, ':to'=>$to, ':message'=>$message]);
    echo json_encode(['success' => true, 'message_id' => $pdo->lastInsertId()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Could not send message', 'details' => $e->getMessage()]);
}

?>
