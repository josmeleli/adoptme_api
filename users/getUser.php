<?php
require_once __DIR__ . '/../config.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de usuario es requerido']);
    exit;
}

try {
    // CA-001: Obtener campos obligatorios y opcionales del perfil
    $stmt = $pdo->prepare('
        SELECT u.id, u.email, u.name, u.phone, u.distrito, u.created_at,
               p.especie_preferida, p.tamano_preferido, p.edad_preferida
        FROM users u
        LEFT JOIN user_preferences p ON u.id = p.user_id
        WHERE u.id = :id LIMIT 1
    ');
    $stmt->execute([':id' => $id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        http_response_code(404);
        echo json_encode(['error' => 'Usuario no encontrado']);
        exit;
    }
    
    // No devolver contraseña
    echo json_encode([
        'id' => $user['id'],
        'email' => $user['email'],
        'name' => $user['name'],
        'phone' => $user['phone'],
        'distrito' => $user['distrito'],
        'created_at' => $user['created_at'],
        'preferences' => [
            'especie' => $user['especie_preferida'],
            'tamano' => $user['tamano_preferido'],
            'edad' => $user['edad_preferida']
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'No se pudo obtener el usuario', 'details' => $e->getMessage()]);
}

?>
