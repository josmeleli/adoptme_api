<?php
/**
 * GET /admin/getAdoptionRequests.php
 * 
 * Sprint 3 - HU-005 - TR-11
 * Panel de administración: Lista de solicitudes de adopción
 * 
 * SOLO ADMINISTRADORES
 * 
 * Parámetros:
 * - admin_id (required): ID del usuario admin
 * - status (optional): Filtrar por estado
 * - urgencia (optional): Filtrar por urgencia de mascota
 * - page (optional): Número de página (default: 1)
 * - per_page (optional): Resultados por página (default: 20)
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
    
    // Paginación
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $per_page = isset($_GET['per_page']) ? min(100, max(1, intval($_GET['per_page']))) : 20;
    $offset = ($page - 1) * $per_page;
    
    // Construir query
    $sql = "SELECT 
                ar.id,
                ar.user_id,
                ar.pet_id,
                ar.status,
                ar.created_at,
                ar.updated_at,
                ar.fecha_revision,
                ar.nombres_completos as solicitante_nombre,
                ar.email as solicitante_email,
                ar.telefono as solicitante_telefono,
                ar.ciudad,
                ar.distrito,
                u.nombres as usuario_nombre,
                u.email as usuario_email,
                p.name as pet_name,
                p.especie,
                p.raza,
                p.edad,
                p.sexo,
                p.foto_url as image_url,
                p.is_urgent as urgencia
            FROM adoption_requests ar
            INNER JOIN users u ON ar.user_id = u.id
            INNER JOIN pets p ON ar.pet_id = p.id
            WHERE 1=1";
    
    $params = [];
    
    // Filtro por estado
    if (isset($_GET['status']) && !empty($_GET['status'])) {
        $sql .= " AND ar.status = :status";
        $params['status'] = $_GET['status'];
    }
    
    // Filtro por urgencia de mascota
    if (isset($_GET['urgencia']) && !empty($_GET['urgencia'])) {
        $sql .= " AND p.is_urgent = :urgencia";
        $params['urgencia'] = $_GET['urgencia'];
    }
    
    // Ordenar: urgentes primero, luego por fecha
    $sql .= " ORDER BY 
                CASE WHEN p.is_urgent = 1 THEN 0 ELSE 1 END,
                CASE ar.status 
                    WHEN 'pendiente' THEN 0
                    WHEN 'en_revision' THEN 1
                    WHEN 'aprobada' THEN 2
                    WHEN 'rechazada' THEN 3
                END,
                ar.created_at DESC";
    
    // Contar total antes de paginar
    $count_sql = str_replace(
        "SELECT ar.id,", 
        "SELECT COUNT(*) as total FROM (SELECT ar.id,", 
        $sql
    ) . ") as subquery";
    
    $count_stmt = $pdo->prepare($count_sql);
    foreach ($params as $key => $value) {
        $count_stmt->bindValue(':' . $key, $value);
    }
    $count_stmt->execute();
    $total = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Añadir paginación
    $sql .= " LIMIT :limit OFFSET :offset";
    
    $stmt = $pdo->prepare($sql);
    
    foreach ($params as $key => $value) {
        $stmt->bindValue(':' . $key, $value);
    }
    $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear resultados
    foreach ($requests as &$request) {
        $status_map = [
            'pendiente' => 'Pendiente',
            'en_revision' => 'En revisión',
            'aprobada' => 'Aprobada',
            'rechazada' => 'Rechazada'
        ];
        
        $request['status_text'] = $status_map[$request['status']] ?? $request['status'];
        
        // Formatear fechas
        $request['created_at_formatted'] = date('d/m/Y H:i', strtotime($request['created_at']));
        
        // Calcular días desde solicitud
        $created = new DateTime($request['created_at']);
        $now = new DateTime();
        $diff = $now->diff($created);
        $request['dias_desde_solicitud'] = $diff->days;
        
        // Alerta si lleva más de 3 días pendiente
        $request['requiere_atencion'] = ($request['status'] === 'pendiente' && $diff->days > 3) || $request['urgencia'] === 'Si';
        
        if ($request['fecha_revision']) {
            $request['fecha_revision_formatted'] = date('d/m/Y H:i', strtotime($request['fecha_revision']));
        }
    }
    
    // Estadísticas generales
    $stats_sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
                    SUM(CASE WHEN status = 'en_revision' THEN 1 ELSE 0 END) as en_revision,
                    SUM(CASE WHEN status = 'aprobada' THEN 1 ELSE 0 END) as aprobadas,
                    SUM(CASE WHEN status = 'rechazada' THEN 1 ELSE 0 END) as rechazadas,
                    SUM(CASE WHEN p.is_urgent = 1 AND ar.status IN ('pendiente', 'en_revision') THEN 1 ELSE 0 END) as urgentes_pendientes
                  FROM adoption_requests ar
                  INNER JOIN pets p ON ar.pet_id = p.id";
    
    $stats_stmt = $pdo->query($stats_sql);
    $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'requests' => $requests,
        'pagination' => [
            'page' => $page,
            'per_page' => $per_page,
            'total' => intval($total),
            'total_pages' => ceil($total / $per_page),
            'has_next' => $page < ceil($total / $per_page),
            'has_prev' => $page > 1
        ],
        'stats' => [
            'total' => intval($stats['total']),
            'pendientes' => intval($stats['pendientes']),
            'en_revision' => intval($stats['en_revision']),
            'aprobadas' => intval($stats['aprobadas']),
            'rechazadas' => intval($stats['rechazadas']),
            'urgentes_pendientes' => intval($stats['urgentes_pendientes'])
        ]
    ], JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener solicitudes',
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
