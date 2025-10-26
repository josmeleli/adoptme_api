<?php
/**
 * POST /messages/sendMessage.php
 * 
 * Sprint 4 - HU-006
 * Enviar mensaje en el sistema de chat
 * 
 * LÓGICA:
 * - Si sender es USER: El mensaje se envía a TODOS los admins (broadcast)
 * - Si sender es ADMIN: El mensaje se envía solo al USER específico
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config.php';

try {
    $data = json_input();
    
    // Validar campos requeridos
    if (!isset($data['sender_id']) || !isset($data['message'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'sender_id y message son requeridos'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $sender_id = intval($data['sender_id']);
    $message = trim($data['message']);
    
    // Validar mensaje no vacío
    if (empty($message)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'El mensaje no puede estar vacío'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Obtener información del remitente
    $sender_sql = "SELECT id, role, email, COALESCE(CONCAT(nombres, ' ', apellidos), email) as name 
                   FROM users WHERE id = :sender_id";
    $sender_stmt = $pdo->prepare($sender_sql);
    $sender_stmt->bindParam(':sender_id', $sender_id, PDO::PARAM_INT);
    $sender_stmt->execute();
    $sender = $sender_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$sender) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Usuario remitente no encontrado'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $messages_created = [];
    $notifications_created = 0;
    
    if ($sender['role'] === 'user') {
        // ══════════════════════════════════════════════════════════════
        // CASO 1: USER envía mensaje → va a TODOS los ADMINS
        // ══════════════════════════════════════════════════════════════
        
        // Obtener todos los administradores
        $admins_sql = "SELECT id FROM users WHERE role = 'admin'";
        $admins_stmt = $pdo->prepare($admins_sql);
        $admins_stmt->execute();
        $admins = $admins_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($admins)) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'No hay administradores disponibles'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // Insertar mensaje para cada administrador
        $insert_sql = "INSERT INTO messages (sender_id, receiver_id, message, is_read) 
                       VALUES (:sender_id, :receiver_id, :message, 0)";
        $insert_stmt = $pdo->prepare($insert_sql);
        
        foreach ($admins as $admin) {
            $insert_stmt->bindParam(':sender_id', $sender_id, PDO::PARAM_INT);
            $insert_stmt->bindParam(':receiver_id', $admin['id'], PDO::PARAM_INT);
            $insert_stmt->bindParam(':message', $message);
            $insert_stmt->execute();
            
            $messages_created[] = $pdo->lastInsertId();
        }
        
        // Crear notificaciones para todos los admins
        $notify_sql = "INSERT INTO notifications (user_id, type, title, message, related_id)
                       SELECT id, 'nuevo_mensaje', 'Nuevo mensaje', 
                              CONCAT('Mensaje de ', :sender_name), :msg_id
                       FROM users WHERE role = 'admin'";
        
        $notify_stmt = $pdo->prepare($notify_sql);
        $notify_stmt->bindParam(':sender_name', $sender['name']);
        $notify_stmt->bindParam(':msg_id', $messages_created[0], PDO::PARAM_INT);
        $notify_stmt->execute();
        $notifications_created = $notify_stmt->rowCount();
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Mensaje enviado a todos los administradores',
            'message_ids' => $messages_created,
            'admins_notified' => count($admins),
            'notifications_created' => $notifications_created,
            'created_at' => date('Y-m-d H:i:s')
        ], JSON_UNESCAPED_UNICODE);
        
    } elseif ($sender['role'] === 'admin') {
        // ══════════════════════════════════════════════════════════════
        // CASO 2: ADMIN responde → va solo al USER específico
        // ══════════════════════════════════════════════════════════════
        
        if (!isset($data['receiver_id'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'receiver_id es requerido para administradores'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        $receiver_id = intval($data['receiver_id']);
        
        // Validar que el receptor existe y es usuario
        $receiver_sql = "SELECT id, role FROM users WHERE id = :receiver_id";
        $receiver_stmt = $pdo->prepare($receiver_sql);
        $receiver_stmt->bindParam(':receiver_id', $receiver_id, PDO::PARAM_INT);
        $receiver_stmt->execute();
        $receiver = $receiver_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$receiver) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Usuario destinatario no encontrado'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        if ($receiver['role'] !== 'user') {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'Solo se puede responder a usuarios'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // Insertar mensaje
        $insert_sql = "INSERT INTO messages (sender_id, receiver_id, message, is_read) 
                       VALUES (:sender_id, :receiver_id, :message, 0)";
        $insert_stmt = $pdo->prepare($insert_sql);
        $insert_stmt->bindParam(':sender_id', $sender_id, PDO::PARAM_INT);
        $insert_stmt->bindParam(':receiver_id', $receiver_id, PDO::PARAM_INT);
        $insert_stmt->bindParam(':message', $message);
        $insert_stmt->execute();
        
        $message_id = $pdo->lastInsertId();
        
        // Crear notificación para el usuario
        $notify_sql = "INSERT INTO notifications (user_id, type, title, message, related_id)
                       VALUES (:user_id, 'nuevo_mensaje', 'Respuesta de administrador', 
                               CONCAT('Mensaje de ', :sender_name), :msg_id)";
        $notify_stmt = $pdo->prepare($notify_sql);
        $notify_stmt->bindParam(':user_id', $receiver_id, PDO::PARAM_INT);
        $notify_stmt->bindParam(':sender_name', $sender['name']);
        $notify_stmt->bindParam(':msg_id', $message_id, PDO::PARAM_INT);
        $notify_stmt->execute();
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Respuesta enviada al usuario',
            'message_id' => $message_id,
            'created_at' => date('Y-m-d H:i:s')
        ], JSON_UNESCAPED_UNICODE);
        
    } else {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Rol de usuario no válido'
        ], JSON_UNESCAPED_UNICODE);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al enviar mensaje',
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
