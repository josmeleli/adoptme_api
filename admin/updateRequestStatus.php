<?php
/**
 * POST /admin/updateRequestStatus.php
 * 
 * Sprint 3 - HU-005 - TR-11
 * Panel de administración: Aprobar o rechazar solicitud
 * 
 * SOLO ADMINISTRADORES
 * 
 * Body JSON:
 * - admin_id (required): ID del usuario admin
 * - request_id (required): ID de la solicitud
 * - new_status (required): 'aprobada' o 'rechazada'
 * - notas_admin (optional): Comentarios del administrador
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config.php';

try {
    $data = json_input();
    
    // Validar campos requeridos
    if (!isset($data['admin_id']) || empty($data['admin_id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'El campo admin_id es requerido'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if (!isset($data['request_id']) || empty($data['request_id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'El campo request_id es requerido'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if (!isset($data['new_status']) || empty($data['new_status'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'El campo new_status es requerido'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $admin_id = intval($data['admin_id']);
    $request_id = intval($data['request_id']);
    $new_status = $data['new_status'];
    $notas_admin = isset($data['notas_admin']) ? $data['notas_admin'] : null;
    
    // Validar que el nuevo estado es válido
    $valid_statuses = ['en_revision', 'aprobada', 'rechazada'];
    if (!in_array($new_status, $valid_statuses)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Estado inválido. Debe ser: en_revision, aprobada o rechazada'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Verificar que es administrador
    $admin_sql = "SELECT role, nombres FROM users WHERE id = :admin_id";
    $admin_stmt = $pdo->prepare($admin_sql);
    $admin_stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
    $admin_stmt->execute();
    $admin = $admin_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin || $admin['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Acceso denegado. Solo administradores pueden actualizar solicitudes'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Obtener información de la solicitud antes de actualizar
    $request_sql = "SELECT ar.user_id, ar.pet_id, ar.status as current_status, 
                           p.name as pet_name, p.estado as pet_estado
                    FROM adoption_requests ar
                    INNER JOIN pets p ON ar.pet_id = p.id
                    WHERE ar.id = :request_id";
    $request_stmt = $pdo->prepare($request_sql);
    $request_stmt->bindParam(':request_id', $request_id, PDO::PARAM_INT);
    $request_stmt->execute();
    $request = $request_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$request) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Solicitud no encontrada'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // No permitir cambios si ya fue revisada (aprobada/rechazada)
    if (in_array($request['current_status'], ['aprobada', 'rechazada'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'No se puede modificar una solicitud ya revisada'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Iniciar transacción
    $pdo->beginTransaction();
    
    try {
        // Actualizar estado de la solicitud
        $update_sql = "UPDATE adoption_requests 
                       SET status = :new_status,
                           notas_admin = :notas_admin,
                           revisado_por = :admin_id,
                           fecha_revision = NOW(),
                           updated_at = NOW()
                       WHERE id = :request_id";
        
        $update_stmt = $pdo->prepare($update_sql);
        $update_stmt->bindParam(':new_status', $new_status);
        $update_stmt->bindParam(':notas_admin', $notas_admin);
        $update_stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
        $update_stmt->bindParam(':request_id', $request_id, PDO::PARAM_INT);
        $update_stmt->execute();
        
        // Si se aprueba, actualizar estado de la mascota
        if ($new_status === 'aprobada') {
            // Cambiar mascota a "En Proceso de Adopción"
            $update_pet_sql = "UPDATE pets SET estado = 'En Proceso de Adopción' WHERE id = :pet_id";
            $update_pet_stmt = $pdo->prepare($update_pet_sql);
            $update_pet_stmt->bindParam(':pet_id', $request['pet_id'], PDO::PARAM_INT);
            $update_pet_stmt->execute();
            
            // Rechazar automáticamente otras solicitudes pendientes para esta mascota
            $reject_others_sql = "UPDATE adoption_requests 
                                  SET status = 'rechazada',
                                      notas_admin = 'Solicitud rechazada automáticamente: la mascota fue adoptada por otro solicitante',
                                      revisado_por = :admin_id,
                                      fecha_revision = NOW(),
                                      updated_at = NOW()
                                  WHERE pet_id = :pet_id 
                                  AND id != :request_id 
                                  AND status IN ('pendiente', 'en_revision')";
            
            $reject_stmt = $pdo->prepare($reject_others_sql);
            $reject_stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
            $reject_stmt->bindParam(':pet_id', $request['pet_id'], PDO::PARAM_INT);
            $reject_stmt->bindParam(':request_id', $request_id, PDO::PARAM_INT);
            $reject_stmt->execute();
            $others_rejected = $reject_stmt->rowCount();
            
            // Notificar a otros solicitantes rechazados
            if ($others_rejected > 0) {
                $notify_others_sql = "INSERT INTO notifications (user_id, type, title, message, related_id)
                                      SELECT ar.user_id, 'solicitud_rechazada',
                                             'Solicitud de adopción rechazada',
                                             CONCAT('Tu solicitud para adoptar a ', :pet_name, ' ha sido rechazada: la mascota fue adoptada por otro solicitante'),
                                             ar.id
                                      FROM adoption_requests ar
                                      WHERE ar.pet_id = :pet_id 
                                      AND ar.id != :request_id
                                      AND ar.status = 'rechazada'
                                      AND ar.updated_at >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)";
                
                $notify_others_stmt = $pdo->prepare($notify_others_sql);
                $notify_others_stmt->bindParam(':pet_name', $request['pet_name']);
                $notify_others_stmt->bindParam(':pet_id', $request['pet_id'], PDO::PARAM_INT);
                $notify_others_stmt->bindParam(':request_id', $request_id, PDO::PARAM_INT);
                $notify_others_stmt->execute();
            }
        }
        
        // Notificar al solicitante principal (in-app, NO email)
        $notification_types = [
            'en_revision' => 'solicitud_en_revision',
            'aprobada' => 'solicitud_aprobada',
            'rechazada' => 'solicitud_rechazada'
        ];
        
        $notification_titles = [
            'en_revision' => 'Solicitud en revisión',
            'aprobada' => '¡Solicitud aprobada!',
            'rechazada' => 'Solicitud rechazada'
        ];
        
        $notification_messages = [
            'en_revision' => "Tu solicitud para adoptar a {$request['pet_name']} está siendo revisada por nuestro equipo",
            'aprobada' => "¡Felicidades! Tu solicitud para adoptar a {$request['pet_name']} ha sido aprobada. Nos contactaremos contigo pronto",
            'rechazada' => "Tu solicitud para adoptar a {$request['pet_name']} ha sido rechazada"
        ];
        
        if ($notas_admin && $new_status === 'rechazada') {
            $notification_messages['rechazada'] .= ". Motivo: {$notas_admin}";
        }
        
        $notify_sql = "INSERT INTO notifications (user_id, type, title, message, related_id)
                       VALUES (:user_id, :type, :title, :message, :request_id)";
        
        $notify_stmt = $pdo->prepare($notify_sql);
        $notify_stmt->bindParam(':user_id', $request['user_id'], PDO::PARAM_INT);
        $notify_stmt->bindParam(':type', $notification_types[$new_status]);
        $notify_stmt->bindParam(':title', $notification_titles[$new_status]);
        $notify_stmt->bindParam(':message', $notification_messages[$new_status]);
        $notify_stmt->bindParam(':request_id', $request_id, PDO::PARAM_INT);
        $notify_stmt->execute();
        
        $pdo->commit();
        
        $status_texts = [
            'en_revision' => 'en revisión',
            'aprobada' => 'aprobada',
            'rechazada' => 'rechazada'
        ];
        
        $response = [
            'success' => true,
            'message' => "Solicitud marcada como {$status_texts[$new_status]}",
            'request_id' => $request_id,
            'new_status' => $new_status,
            'reviewed_by' => $admin['nombres'],
            'user_notified' => true
        ];
        
        if ($new_status === 'aprobada' && isset($others_rejected)) {
            $response['pet_status_updated'] = 'En Proceso de Adopción';
            $response['other_requests_rejected'] = $others_rejected;
        }
        
        http_response_code(200);
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al actualizar solicitud',
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
