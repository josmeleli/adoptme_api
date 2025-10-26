<?php
/**
 * GET /messages/getUsersListForAdmin.php
 * 
 * Sprint 4 - HU-006
 * Obtener lista de usuarios que han enviado mensajes (para ADMIN)
 * 
 * Retorna lista de usuarios con:
 * - Último mensaje
 * - Cantidad de mensajes no leídos
 * - Información del usuario
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config.php';

try {
    if (!isset($_GET['admin_id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'admin_id es requerido'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $admin_id = intval($_GET['admin_id']);
    
    // Validar que es administrador
    $admin_sql = "SELECT id, role FROM users WHERE id = :admin_id";
    $admin_stmt = $pdo->prepare($admin_sql);
    $admin_stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
    $admin_stmt->execute();
    $admin = $admin_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Administrador no encontrado'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if ($admin['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Este endpoint es solo para administradores'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Obtener usuarios que han enviado mensajes a este admin
    $sql = "SELECT DISTINCT
                u.id as user_id,
                u.email,
                COALESCE(CONCAT(u.nombres, ' ', u.apellidos), u.email) as name,
                u.role,
                (SELECT message FROM messages 
                 WHERE (sender_id = u.id AND receiver_id = :admin_id)
                    OR (sender_id = :admin_id2 AND receiver_id = u.id)
                 ORDER BY created_at DESC LIMIT 1) as last_message,
                (SELECT created_at FROM messages 
                 WHERE (sender_id = u.id AND receiver_id = :admin_id3)
                    OR (sender_id = :admin_id4 AND receiver_id = u.id)
                 ORDER BY created_at DESC LIMIT 1) as last_message_time,
                (SELECT sender_id FROM messages 
                 WHERE (sender_id = u.id AND receiver_id = :admin_id5)
                    OR (sender_id = :admin_id6 AND receiver_id = u.id)
                 ORDER BY created_at DESC LIMIT 1) as last_sender_id,
                (SELECT COUNT(*) FROM messages 
                 WHERE sender_id = u.id 
                   AND receiver_id = :admin_id7
                   AND is_read = 0) as unread_count
            FROM users u
            INNER JOIN messages m ON (m.sender_id = u.id OR m.receiver_id = u.id)
            WHERE u.role = 'user'
              AND (m.sender_id = :admin_id8 OR m.receiver_id = :admin_id9)
              AND u.id != :admin_id10
            ORDER BY last_message_time DESC";
    
    $stmt = $pdo->prepare($sql);
    for ($i = 1; $i <= 10; $i++) {
        $param = ':admin_id' . ($i > 1 ? $i : '');
        $stmt->bindParam($param, $admin_id, PDO::PARAM_INT);
    }
    $stmt->execute();
    
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear datos
    foreach ($users as &$user) {
        $user['is_last_message_from_user'] = ($user['last_sender_id'] != $admin_id);
        $user['unread_count'] = intval($user['unread_count']);
        unset($user['last_sender_id']);
    }
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'users' => $users,
        'total_users' => count($users)
    ], JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener lista de usuarios',
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
