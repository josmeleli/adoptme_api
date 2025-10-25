<?php
require_once __DIR__ . '/../config.php';

$user_id = $_GET['user_id'] ?? null;
if (!$user_id) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de usuario es requerido']);
    exit;
}

try {
    // Obtener perfil completo del usuario
    $stmt = $pdo->prepare('
        SELECT u.id, u.email, u.nombres, u.apellidos, u.dni, u.telefono, u.distrito, u.created_at,
               p.especie_preferida, p.tamano_preferido, p.edad_preferida
        FROM users u
        LEFT JOIN user_preferences p ON u.id = p.user_id
        WHERE u.id = :user_id LIMIT 1
    ');
    $stmt->execute([':user_id' => $user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        http_response_code(404);
        echo json_encode(['error' => 'Usuario no encontrado']);
        exit;
    }
    
    // Respuesta con estructura actualizada
    $response = [
        'success' => true,
        'user' => [
            'id' => $user['id'],
            'email' => $user['email'],
            'nombres' => $user['nombres'],
            'apellidos' => $user['apellidos'],
            'dni' => $user['dni'],
            'telefono' => $user['telefono'],
            'distrito' => $user['distrito'],
            'created_at' => $user['created_at']
        ]
    ];
    
    // Agregar preferencias si existen
    if ($user['especie_preferida'] || $user['tamano_preferido'] || $user['edad_preferida']) {
        $response['user']['preferencias'] = [
            'especie_preferida' => $user['especie_preferida'],
            'tamano_preferido' => $user['tamano_preferido'],
            'edad_preferida' => $user['edad_preferida']
        ];
    }
    
    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'No se pudo obtener el usuario', 'details' => $e->getMessage()]);
}

?>
