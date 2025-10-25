<?php
require_once __DIR__ . '/../config.php';

$adoption_id = $_GET['id'] ?? null;
if (!$adoption_id) {
    http_response_code(400);
    echo json_encode(['error' => 'id required']);
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT * FROM adoptions WHERE id = :id LIMIT 1');
    $stmt->execute([':id'=>$adoption_id]);
    $ad = $stmt->fetch();
    if (!$ad) { http_response_code(404); echo json_encode(['error'=>'Not found']); exit; }
    echo json_encode($ad);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Could not fetch adoption', 'details' => $e->getMessage()]);
}

?>
