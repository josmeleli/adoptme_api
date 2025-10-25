<?php
require_once __DIR__ . '/config.php';

$data = json_input();
$email = $data['email'] ?? null;
$password = $data['password'] ?? null;

if (!$email || !$password) {
    http_response_code(400);
    echo json_encode(['error' => 'Email y contraseña son requeridos']);
    exit;
}

try {
    // CA-001: Login con correo/contraseña
    $stmt = $pdo->prepare('SELECT id, email, name, password_hash, is_verified FROM users WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();
    
    if (!$user || !password_verify($password, $user['password_hash'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Credenciales inválidas']);
        exit;
    }
    
    // Verificar si el usuario está verificado
    if (!$user['is_verified']) {
        http_response_code(403);
        echo json_encode(['error' => 'Debes verificar tu correo antes de iniciar sesión', 'user_id' => $user['id']]);
        exit;
    }
    
    // Generar JWT token simple (secret key configurable)
    $secret_key = 'tu_clave_secreta_cambiar_en_produccion'; // CAMBIAR EN PRODUCCIÓN
    $issued_at = time();
    $expiration_time = $issued_at + (60 * 60 * 24 * 7); // 7 días
    
    $token_data = [
        'user_id' => $user['id'],
        'email' => $user['email'],
        'iat' => $issued_at,
        'exp' => $expiration_time
    ];
    
    // Token simple (base64) - En producción usar JWT real con librería
    $token = base64_encode(json_encode($token_data));
    
    // TR-005: Guardar sesión en BD para persistencia
    $stmt = $pdo->prepare('INSERT INTO sessions (user_id, token, expires_at, created_at) VALUES (:user_id, :token, :expires, NOW())');
    $stmt->execute([
        ':user_id' => $user['id'],
        ':token' => $token,
        ':expires' => date('Y-m-d H:i:s', $expiration_time)
    ]);
    
    echo json_encode([
        'success' => true,
        'token' => $token,
        'user' => [
            'id' => $user['id'],
            'email' => $user['email'],
            'name' => $user['name']
        ],
        'expires_at' => date('Y-m-d H:i:s', $expiration_time)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al iniciar sesión', 'details' => $e->getMessage()]);
}

?>
