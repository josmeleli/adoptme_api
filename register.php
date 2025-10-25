<?php
require_once __DIR__ . '/config.php';

$data = json_input();

$nombres = trim($data['nombres'] ?? '');
$apellidos = trim($data['apellidos'] ?? '');
$dni = trim($data['dni'] ?? '');
$email = trim($data['email'] ?? '');
$telefono = trim($data['telefono'] ?? '');
$password = $data['password'] ?? null;

// VALIDACIONES COMPLETAS

// 1. Validar nombres (obligatorio, solo letras y espacios)
if (empty($nombres) || !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $nombres)) {
    http_response_code(400);
    echo json_encode(['error' => 'Nombres inválidos. Solo se permiten letras y espacios']);
    exit;
}

// 2. Validar apellidos (obligatorio, solo letras y espacios)
if (empty($apellidos) || !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $apellidos)) {
    http_response_code(400);
    echo json_encode(['error' => 'Apellidos inválidos. Solo se permiten letras y espacios']);
    exit;
}

// 3. Validar DNI (exactamente 8 dígitos numéricos)
if (!preg_match('/^\d{8}$/', $dni)) {
    http_response_code(400);
    echo json_encode(['error' => 'DNI inválido. Debe tener exactamente 8 números']);
    exit;
}

// 4. Validar email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Correo electrónico inválido']);
    exit;
}

// 5. Validar teléfono (exactamente 9 dígitos numéricos)
if (!preg_match('/^\d{9}$/', $telefono)) {
    http_response_code(400);
    echo json_encode(['error' => 'Teléfono inválido. Debe tener exactamente 9 números']);
    exit;
}

// 6. Validar contraseña (mínimo 6 caracteres)
if (!$password || strlen($password) < 6) {
    http_response_code(400);
    echo json_encode(['error' => 'Contraseña debe tener al menos 6 caracteres']);
    exit;
}

// 7. No permitir emails duplicados
try {
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['error' => 'El correo electrónico ya está registrado']);
        exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al verificar email', 'details' => $e->getMessage()]);
    exit;
}

// 8. No permitir DNI duplicados
try {
    $stmt = $pdo->prepare('SELECT id FROM users WHERE dni = :dni LIMIT 1');
    $stmt->execute([':dni' => $dni]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['error' => 'El DNI ya está registrado']);
        exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al verificar DNI', 'details' => $e->getMessage()]);
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);

// CA-002: Generar código de verificación (6 dígitos)
$verification_code = sprintf('%06d', rand(0, 999999));
$verification_expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));

try {
    $pdo->beginTransaction();
    
    // Insertar usuario con todos los campos
    $stmt = $pdo->prepare('INSERT INTO users (nombres, apellidos, dni, email, telefono, password_hash, is_verified, created_at) VALUES (:nombres, :apellidos, :dni, :email, :telefono, :hash, 0, NOW())');
    $stmt->execute([
        ':nombres' => $nombres,
        ':apellidos' => $apellidos,
        ':dni' => $dni,
        ':email' => $email,
        ':telefono' => $telefono,
        ':hash' => $hash
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
        'nombres' => $nombres,
        'apellidos' => $apellidos,
        'email' => $email,
        'message' => 'Usuario registrado exitosamente. Revisa tu correo para el código de verificación.',
        'verification_code' => $verification_code // ELIMINAR EN PRODUCCIÓN
    ]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'No se pudo crear el usuario', 'details' => $e->getMessage()]);
}

?>
