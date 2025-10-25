<?php
require_once __DIR__ . '/config.php';

// Obtener token del header Authorization
$headers = getallheaders();
$token = null;

if (isset($headers['Authorization'])) {
    $parts = explode(' ', $headers['Authorization']);
    if (count($parts) === 2 && $parts[0] === 'Bearer') {
        $token = $parts[1];
    }
}

if (!$token) {
    http_response_code(400);
    echo json_encode(['error' => 'Token no proporcionado']);
    exit;
}

try {
    // TR-006: Implementar cierre de sesión
    // Eliminar sesión de la base de datos
    $stmt = $pdo->prepare('DELETE FROM sessions WHERE token = :token');
    $stmt->execute([':token' => $token]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Sesión cerrada correctamente']);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Sesión no encontrada o ya cerrada']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al cerrar sesión', 'details' => $e->getMessage()]);
}

?>
