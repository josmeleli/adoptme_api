<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/email_service.php';

$data = json_input();
$user_id = $data['user_id'] ?? null;
$code = $data['code'] ?? null;

if (!$user_id || !$code) {
    http_response_code(400);
    echo json_encode(['error' => 'user_id y código son requeridos']);
    exit;
}

try {
    // Buscar código de verificación válido
    $stmt = $pdo->prepare('
        SELECT id FROM verification_codes 
        WHERE user_id = :user_id 
        AND code = :code 
        AND expires_at > NOW() 
        AND used = 0
        LIMIT 1
    ');
    $stmt->execute([':user_id' => $user_id, ':code' => $code]);
    $verification = $stmt->fetch();
    
    if (!$verification) {
        http_response_code(400);
        echo json_encode(['error' => 'Código inválido o expirado']);
        exit;
    }
    
    $pdo->beginTransaction();
    
    // Marcar usuario como verificado
    $stmt = $pdo->prepare('UPDATE users SET is_verified = 1 WHERE id = :user_id');
    $stmt->execute([':user_id' => $user_id]);
    
    // Marcar código como usado
    $stmt = $pdo->prepare('UPDATE verification_codes SET used = 1 WHERE id = :id');
    $stmt->execute([':id' => $verification['id']]);
    
    // Obtener datos del usuario para el email de bienvenida
    $stmt = $pdo->prepare('SELECT nombres, email FROM users WHERE id = :user_id LIMIT 1');
    $stmt->execute([':user_id' => $user_id]);
    $usuario = $stmt->fetch();
    
    $pdo->commit();
    
    // Enviar email de bienvenida
    if ($usuario) {
        enviarEmailBienvenida($usuario['email'], $usuario['nombres']);
    }
    
    echo json_encode(['success' => true, 'message' => 'Usuario verificado correctamente']);
    
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Error al verificar usuario', 'details' => $e->getMessage()]);
}

?>
