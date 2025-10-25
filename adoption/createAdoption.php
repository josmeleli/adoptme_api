<?php
require_once __DIR__ . '/../config.php';

$data = json_input();
$pet_id = $data['pet_id'] ?? null;
$user_id = $data['user_id'] ?? null;

if (!$pet_id || !$user_id) {
    http_response_code(400);
    echo json_encode(['error' => 'pet_id and user_id required']);
    exit;
}

try {
    $stmt = $pdo->prepare('INSERT INTO adoptions (pet_id, user_id, status, created_at) VALUES (:pet_id, :user_id, :status, NOW())');
    $stmt->execute([':pet_id'=>$pet_id, ':user_id'=>$user_id, ':status'=>'pending']);
    echo json_encode(['success' => true, 'adoption_id' => $pdo->lastInsertId()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Could not create adoption', 'details' => $e->getMessage()]);
}

?>
