<?php
/**
 * GET /messages/getChatForUser.php
 * 
 * Sprint 4 - HU-006
 * Obtener el chat grupal para un USUARIO (role='user')
 * 
 * El usuario ve:
 * - Sus mensajes enviados (que fueron a todos los admins)
 * - Las respuestas de cualquier administrador
 * 
 * Todo en un único chat grupal
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config.php';

try {
    if (!isset($_GET['user_id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'user_id es requerido'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $user_id = intval($_GET['user_id']);
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 100;
    
    if ($limit < 1 || $limit > 500) {
        $limit = 100;
    }
    
    // Validar que el usuario existe y es role='user'
    $user_sql = "SELECT id, role FROM users WHERE id = :user_id";
    $user_stmt = $pdo->prepare($user_sql);
    $user_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $user_stmt->execute();
    $user = $user_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Usuario no encontrado'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if ($user['role'] !== 'user') {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Este endpoint es solo para usuarios con role=user'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Obtener todos los mensajes relacionados con este usuario
    // - Mensajes enviados por el usuario (sender_id = user_id)
    // - Mensajes recibidos de admins (receiver_id = user_id)
    $sql = "SELECT 
                m.id,
                m.sender_id,
                m.receiver_id,
                m.message,
                m.is_read,
                m.created_at,
                sender.email as sender_email,
                COALESCE(CONCAT(sender.nombres, ' ', sender.apellidos), sender.email) as sender_name,
                sender.role as sender_role,
                receiver.email as receiver_email,
                COALESCE(CONCAT(receiver.nombres, ' ', receiver.apellidos), receiver.email) as receiver_name,
                receiver.role as receiver_role
            FROM messages m
            INNER JOIN users sender ON m.sender_id = sender.id
            INNER JOIN users receiver ON m.receiver_id = receiver.id
            WHERE m.sender_id = :user_id OR m.receiver_id = :user_id2
            ORDER BY m.created_at ASC
            LIMIT :limit";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id2', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Marcar como leídos los mensajes recibidos por el usuario
    if (!empty($messages)) {
        $mark_read_sql = "UPDATE messages 
                         SET is_read = 1 
                         WHERE receiver_id = :user_id AND is_read = 0";
        $mark_stmt = $pdo->prepare($mark_read_sql);
        $mark_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $mark_stmt->execute();
    }
    
    // Formatear mensajes para indicar si es del usuario o de un admin
    foreach ($messages as &$msg) {
        $msg['is_mine'] = ($msg['sender_id'] == $user_id);
        $msg['is_from_admin'] = ($msg['sender_role'] === 'admin');
    }
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'messages' => $messages,
        'total_messages' => count($messages),
        'chat_type' => 'group_with_admins'
    ], JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener chat',
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
