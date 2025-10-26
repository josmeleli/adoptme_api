<?php
/**
 * GET /messages/getChatForAdmin.php
 * 
 * Sprint 4 - HU-006
 * Obtener conversación individual entre ADMIN y un USER específico
 * 
 * El admin ve:
 * - Mensajes enviados por el usuario
 * - Sus propias respuestas a ese usuario
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config.php';

try {
    if (!isset($_GET['admin_id']) || !isset($_GET['user_id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'admin_id y user_id son requeridos'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $admin_id = intval($_GET['admin_id']);
    $user_id = intval($_GET['user_id']);
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 100;
    
    if ($limit < 1 || $limit > 500) {
        $limit = 100;
    }
    
    // Validar que el admin existe y es admin
    $admin_sql = "SELECT id, role FROM users WHERE id = :admin_id";
    $admin_stmt = $pdo->prepare($admin_sql);
    $admin_stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
    $admin_stmt->execute();
    $admin = $admin_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin || $admin['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Acceso denegado'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Validar que el usuario existe y es user
    $user_sql = "SELECT id, role, email, COALESCE(CONCAT(nombres, ' ', apellidos), email) as name 
                 FROM users WHERE id = :user_id";
    $user_stmt = $pdo->prepare($user_sql);
    $user_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $user_stmt->execute();
    $user = $user_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user || $user['role'] !== 'user') {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Usuario no encontrado'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Obtener conversación entre admin y usuario
    // - Mensajes del usuario al admin
    // - Respuestas del admin al usuario
    $sql = "SELECT 
                m.id,
                m.sender_id,
                m.receiver_id,
                m.message,
                m.is_read,
                m.created_at,
                sender.email as sender_email,
                COALESCE(CONCAT(sender.nombres, ' ', sender.apellidos), sender.email) as sender_name,
                sender.role as sender_role
            FROM messages m
            INNER JOIN users sender ON m.sender_id = sender.id
            WHERE (m.sender_id = :user_id AND m.receiver_id = :admin_id)
               OR (m.sender_id = :admin_id2 AND m.receiver_id = :user_id2)
            ORDER BY m.created_at ASC
            LIMIT :limit";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id2', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
    $stmt->bindParam(':admin_id2', $admin_id, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Marcar como leídos los mensajes del usuario
    if (!empty($messages)) {
        $mark_read_sql = "UPDATE messages 
                         SET is_read = 1 
                         WHERE sender_id = :user_id AND receiver_id = :admin_id AND is_read = 0";
        $mark_stmt = $pdo->prepare($mark_read_sql);
        $mark_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $mark_stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
        $mark_stmt->execute();
    }
    
    // Formatear mensajes
    foreach ($messages as &$msg) {
        $msg['is_from_admin'] = ($msg['sender_id'] == $admin_id);
        $msg['is_from_user'] = ($msg['sender_id'] == $user_id);
    }
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'messages' => $messages,
        'total_messages' => count($messages),
        'user_info' => [
            'id' => $user['id'],
            'email' => $user['email'],
            'name' => $user['name'],
            'role' => $user['role']
        ]
    ], JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener chat',
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
