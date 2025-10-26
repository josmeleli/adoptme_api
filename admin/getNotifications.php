<?php
/**
 * GET /admin/getNotifications.php
 * 
 * Sprint 3 - HU-005 - TR-11
 * Panel de administración: Obtener notificaciones de nuevas solicitudes
 * 
 * SOLO ADMINISTRADORES
 * 
 * Parámetros:
 * - admin_id (required): ID del usuario admin
 * - unread_only (optional): 'true' para solo no leídas (default: false)
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once '../config.php';

try {
    // Validar admin_id
    if (!isset($_GET['admin_id']) || empty($_GET['admin_id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'El parámetro admin_id es requerido'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $admin_id = intval($_GET['admin_id']);
    
    // Verificar que es administrador
    $admin_sql = "SELECT role FROM users WHERE id = :admin_id";
    $admin_stmt = $pdo->prepare($admin_sql);
    $admin_stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
    $admin_stmt->execute();
    $admin = $admin_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin || $admin['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Acceso denegado. Solo administradores pueden acceder'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Construir query
    $sql = "SELECT * FROM notifications WHERE user_id = :admin_id";
    
    // Filtro de no leídas
    $unread_only = isset($_GET['unread_only']) && $_GET['unread_only'] === 'true';
    if ($unread_only) {
        $sql .= " AND is_read = 0";
    }
    
    // Filtrar solo notificaciones de adopción
    $sql .= " AND type IN ('nueva_solicitud', 'solicitud_aprobada', 'solicitud_rechazada')";
    
    $sql .= " ORDER BY created_at DESC LIMIT 50";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear notificaciones
    foreach ($notifications as &$notif) {
        $notif['created_at_formatted'] = date('d/m/Y H:i', strtotime($notif['created_at']));
        
        // Calcular tiempo relativo
        $created = new DateTime($notif['created_at']);
        $now = new DateTime();
        $diff = $now->diff($created);
        
        if ($diff->days > 0) {
            $notif['time_ago'] = $diff->days . ' día' . ($diff->days > 1 ? 's' : '');
        } elseif ($diff->h > 0) {
            $notif['time_ago'] = $diff->h . ' hora' . ($diff->h > 1 ? 's' : '');
        } elseif ($diff->i > 0) {
            $notif['time_ago'] = $diff->i . ' minuto' . ($diff->i > 1 ? 's' : '');
        } else {
            $notif['time_ago'] = 'Ahora';
        }
    }
    
    // Contar no leídas
    $count_sql = "SELECT COUNT(*) as unread_count 
                  FROM notifications 
                  WHERE user_id = :admin_id 
                  AND is_read = 0
                  AND type = 'nueva_solicitud'";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
    $count_stmt->execute();
    $unread_count = $count_stmt->fetch(PDO::FETCH_ASSOC)['unread_count'];
    
    echo json_encode([
        'success' => true,
        'notifications' => $notifications,
        'unread_count' => intval($unread_count),
        'total' => count($notifications)
    ], JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener notificaciones',
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
