<?php
/**
 * GET /messages/getUnreadCount.php
 * 
 * Sprint 4 - HU-006
 * Obtener contador de mensajes no leídos
 * 
 * Funciona tanto para USER como para ADMIN
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
    
    // Total de mensajes no leídos
    $total_sql = "SELECT COUNT(*) as total FROM messages 
                  WHERE receiver_id = :user_id AND is_read = 0";
    $total_stmt = $pdo->prepare($total_sql);
    $total_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $total_stmt->execute();
    $total_result = $total_stmt->fetch(PDO::FETCH_ASSOC);
    
    // Contar por remitente
    $by_sender_sql = "SELECT 
                        m.sender_id,
                        u.email as sender_email,
                        COALESCE(CONCAT(u.nombres, ' ', u.apellidos), u.email) as sender_name,
                        u.role as sender_role,
                        COUNT(*) as unread_count
                      FROM messages m
                      INNER JOIN users u ON m.sender_id = u.id
                      WHERE m.receiver_id = :user_id AND m.is_read = 0
                      GROUP BY m.sender_id, u.email, sender_name, u.role
                      ORDER BY unread_count DESC";
    
    $by_sender_stmt = $pdo->prepare($by_sender_sql);
    $by_sender_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $by_sender_stmt->execute();
    $unread_by_sender = $by_sender_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'total_unread' => intval($total_result['total']),
        'unread_by_sender' => $unread_by_sender
    ], JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener contador',
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
