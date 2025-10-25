<?php
require_once __DIR__ . '/config.php';

$data = json_input();

$email = $data['email'] ?? null;
$password = $data['password'] ?? null;
$name = $data['name'] ?? null;
$phone = $data['phone'] ?? null;

// CA-001: Validar formato de correo y contraseña
if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Email válido es requerido']);
    exit;
}

if (!$password || strlen($password) < 6) {
    http_response_code(400);
    echo json_encode(['error' => 'Contraseña debe tener al menos 6 caracteres']);
    exit;
}

// CA-003: No permitir registros duplicados
try {
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['error' => 'El email ya está registrado']);
        exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al verificar email', 'details' => $e->getMessage()]);
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);

// CA-002: Generar código de verificación (6 dígitos)
$verification_code = sprintf('%06d', rand(0, 999999));
$verification_expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));

try {
    $pdo->beginTransaction();
    
    // Insertar usuario
    $stmt = $pdo->prepare('INSERT INTO users (email, password_hash, name, phone, is_verified, created_at) VALUES (:email, :hash, :name, :phone, 0, NOW())');
    $stmt->execute([
        ':email' => $email, 
        ':hash' => $hash,
        ':name' => $name,
        ':phone' => $phone
    ]);
    $user_id = $pdo->lastInsertId();
    
    // Guardar código de verificación
    $stmt = $pdo->prepare('INSERT INTO verification_codes (user_id, code, expires_at, created_at) VALUES (:user_id, :code, :expires, NOW())');
    $stmt->execute([
        ':user_id' => $user_id,
        ':code' => $verification_code,
        ':expires' => $verification_expires
    ]);
    
    $pdo->commit();
    
    // En producción, aquí enviarías el código por email/SMS
    // Por ahora lo devolvemos en la respuesta (solo para desarrollo)
    echo json_encode([
        'success' => true, 
        'user_id' => $user_id,
        'message' => 'Usuario registrado. Revisa tu correo para el código de verificación.',
        'verification_code' => $verification_code // ELIMINAR EN PRODUCCIÓN
    ]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'No se pudo crear el usuario', 'details' => $e->getMessage()]);
}

?>
