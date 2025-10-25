<?php
require_once __DIR__ . '/config.php';

// Obtener token del header
$headers = getallheaders();
$token = null;

if (isset($headers['Authorization'])) {
    $parts = explode(' ', $headers['Authorization']);
    if (count($parts) === 2 && $parts[0] === 'Bearer') {
        $token = $parts[1];
    }
}

if (!$token) {
    http_response_code(401);
    echo json_encode(['error' => 'Token no proporcionado']);
    exit;
}

try {
    // Validar token actual
    $stmt = $pdo->prepare('SELECT user_id, expires_at FROM sessions WHERE token = :token LIMIT 1');
    $stmt->execute([':token' => $token]);
    $session = $stmt->fetch();
    
    if (!$session) {
        http_response_code(401);
        echo json_encode(['error' => 'Token inválido']);
        exit;
    }
    
    // CA-002: Cierre de sesión invalida el token
    if (strtotime($session['expires_at']) < time()) {
        // Token expirado, eliminar
        $stmt = $pdo->prepare('DELETE FROM sessions WHERE token = :token');
        $stmt->execute([':token' => $token]);
        
        http_response_code(401);
        echo json_encode(['error' => 'Token expirado']);
        exit;
    }
    
    // Generar nuevo token
    $user_id = $session['user_id'];
    $issued_at = time();
    $expiration_time = $issued_at + (60 * 60 * 24 * 7); // 7 días
    
    $token_data = [
        'user_id' => $user_id,
        'iat' => $issued_at,
        'exp' => $expiration_time
    ];
    
    $new_token = base64_encode(json_encode($token_data));
    
    // Eliminar token antiguo y crear nuevo
    $pdo->beginTransaction();
    
    $stmt = $pdo->prepare('DELETE FROM sessions WHERE token = :token');
    $stmt->execute([':token' => $token]);
    
    $stmt = $pdo->prepare('INSERT INTO sessions (user_id, token, expires_at, created_at) VALUES (:user_id, :token, :expires, NOW())');
    $stmt->execute([
        ':user_id' => $user_id,
        ':token' => $new_token,
        ':expires' => date('Y-m-d H:i:s', $expiration_time)
    ]);
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'token' => $new_token,
        'expires_at' => date('Y-m-d H:i:s', $expiration_time)
    ]);
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['error' => 'Error al renovar token', 'details' => $e->getMessage()]);
}

?>
