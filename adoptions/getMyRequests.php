<?php
/**
 * GET /adoptions/getMyRequests.php
 * 
 * Sprint 3 - HU-005
 * Obtener historial de solicitudes del usuario
 * 
 * Parámetros:
 * - user_id (required): ID del usuario
 * - status (optional): Filtrar por estado
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once '../config.php';

try {
    // Validar user_id
    if (!isset($_GET['user_id']) || empty($_GET['user_id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'El parámetro user_id es requerido'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $user_id = intval($_GET['user_id']);
    
    // Construir query
    $sql = "SELECT 
                ar.id,
                ar.pet_id,
                ar.status,
                ar.created_at,
                ar.updated_at,
                ar.fecha_revision,
                ar.notas_admin,
                p.name as pet_name,
                p.especie,
                p.raza,
                p.edad,
                p.sexo,
                p.foto_url as image_url,
                p.is_urgent as urgencia
            FROM adoption_requests ar
            INNER JOIN pets p ON ar.pet_id = p.id
            WHERE ar.user_id = :user_id";
    
    // Filtro opcional por estado
    $params = ['user_id' => $user_id];
    
    if (isset($_GET['status']) && !empty($_GET['status'])) {
        $sql .= " AND ar.status = :status";
        $params['status'] = $_GET['status'];
    }
    
    $sql .= " ORDER BY ar.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    
    foreach ($params as $key => $value) {
        $stmt->bindValue(':' . $key, $value);
    }
    
    $stmt->execute();
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear resultados
    foreach ($requests as &$request) {
        // Añadir estado traducido
        $status_map = [
            'pendiente' => 'Pendiente de revisión',
            'en_revision' => 'En revisión',
            'aprobada' => 'Aprobada',
            'rechazada' => 'Rechazada'
        ];
        
        $request['status_text'] = $status_map[$request['status']] ?? $request['status'];
        
        // Añadir color según estado
        $color_map = [
            'pendiente' => '#FFA500',    // Naranja
            'en_revision' => '#2196F3',  // Azul
            'aprobada' => '#4CAF50',     // Verde
            'rechazada' => '#F44336'     // Rojo
        ];
        
        $request['status_color'] = $color_map[$request['status']] ?? '#9E9E9E';
        
        // Formatear fechas
        $request['created_at_formatted'] = date('d/m/Y H:i', strtotime($request['created_at']));
        
        if ($request['fecha_revision']) {
            $request['fecha_revision_formatted'] = date('d/m/Y H:i', strtotime($request['fecha_revision']));
        }
        
        // Añadir edad formateada
        $request['pet_edad_text'] = $request['edad'] . ' ' . ($request['edad'] == 1 ? 'año' : 'años');
    }
    
    // Contar por estado
    $stats_sql = "SELECT status, COUNT(*) as count 
                  FROM adoption_requests 
                  WHERE user_id = :user_id 
                  GROUP BY status";
    $stats_stmt = $pdo->prepare($stats_sql);
    $stats_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stats_stmt->execute();
    $stats = $stats_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stats_formatted = [
        'total' => count($requests),
        'pendiente' => 0,
        'en_revision' => 0,
        'aprobada' => 0,
        'rechazada' => 0
    ];
    
    foreach ($stats as $stat) {
        $stats_formatted[$stat['status']] = intval($stat['count']);
    }
    
    echo json_encode([
        'success' => true,
        'requests' => $requests,
        'stats' => $stats_formatted
    ], JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener solicitudes',
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
