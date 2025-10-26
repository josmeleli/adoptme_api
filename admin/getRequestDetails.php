<?php
/**
 * GET /admin/getRequestDetails.php
 * 
 * Sprint 3 - HU-005 - TR-11
 * Panel de administración: Detalles completos de solicitud
 * 
 * SOLO ADMINISTRADORES
 * 
 * Parámetros:
 * - admin_id (required): ID del usuario admin
 * - request_id (required): ID de la solicitud
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once '../config.php';

try {
    // Validar parámetros
    if (!isset($_GET['admin_id']) || empty($_GET['admin_id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'El parámetro admin_id es requerido'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if (!isset($_GET['request_id']) || empty($_GET['request_id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'El parámetro request_id es requerido'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $admin_id = intval($_GET['admin_id']);
    $request_id = intval($_GET['request_id']);
    
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
    
    // Obtener detalles completos de la solicitud
    $sql = "SELECT 
                ar.*,
                u.nombres as usuario_nombres,
                u.email as usuario_email,
                u.telefono as usuario_telefono,
                u.created_at as usuario_registrado,
                p.name as pet_name,
                p.especie,
                p.raza,
                p.edad,
                p.sexo,
                p.color,
                p.tamano,
                p.peso_aprox,
                p.descripcion as pet_descripcion,
                p.personalidad,
                p.foto_url as image_url,
                p.is_urgent as urgencia,
                p.estado as pet_estado,
                admin_rev.nombres as revisado_por_nombre,
                admin_rev.email as revisado_por_email
            FROM adoption_requests ar
            INNER JOIN users u ON ar.user_id = u.id
            INNER JOIN pets p ON ar.pet_id = p.id
            LEFT JOIN users admin_rev ON ar.revisado_por = admin_rev.id
            WHERE ar.id = :request_id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':request_id', $request_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $request = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$request) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Solicitud no encontrada'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Formatear datos
    $status_map = [
        'pendiente' => 'Pendiente de revisión',
        'en_revision' => 'En revisión',
        'aprobada' => 'Aprobada',
        'rechazada' => 'Rechazada'
    ];
    
    $request['status_text'] = $status_map[$request['status']] ?? $request['status'];
    
    // Formatear fechas
    $request['created_at_formatted'] = date('d/m/Y H:i', strtotime($request['created_at']));
    
    if ($request['fecha_revision']) {
        $request['fecha_revision_formatted'] = date('d/m/Y H:i', strtotime($request['fecha_revision']));
    }
    
    if ($request['fecha_nacimiento']) {
        $fecha_nac = new DateTime($request['fecha_nacimiento']);
        $hoy = new DateTime();
        $edad = $hoy->diff($fecha_nac)->y;
        $request['edad_solicitante'] = $edad;
        $request['fecha_nacimiento_formatted'] = date('d/m/Y', strtotime($request['fecha_nacimiento']));
    }
    
    // Calcular tiempo desde solicitud
    $created = new DateTime($request['created_at']);
    $now = new DateTime();
    $diff = $now->diff($created);
    $request['dias_desde_solicitud'] = $diff->days;
    
    // Obtener historial de solicitudes del usuario
    $history_sql = "SELECT ar.id, ar.status, ar.created_at, p.name as pet_name
                    FROM adoption_requests ar
                    INNER JOIN pets p ON ar.pet_id = p.id
                    WHERE ar.user_id = :user_id AND ar.id != :current_id
                    ORDER BY ar.created_at DESC
                    LIMIT 5";
    
    $history_stmt = $pdo->prepare($history_sql);
    $history_stmt->bindParam(':user_id', $request['user_id'], PDO::PARAM_INT);
    $history_stmt->bindParam(':current_id', $request_id, PDO::PARAM_INT);
    $history_stmt->execute();
    $user_history = $history_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($user_history as &$hist) {
        $hist['created_at_formatted'] = date('d/m/Y', strtotime($hist['created_at']));
    }
    
    // Respuesta organizada
    $response = [
        'success' => true,
        'request_id' => $request['id'],
        'status' => $request['status'],
        'status_text' => $request['status_text'],
        'created_at' => $request['created_at_formatted'],
        'dias_desde_solicitud' => $request['dias_desde_solicitud'],
        'fecha_revision' => $request['fecha_revision_formatted'] ?? null,
        'revisado_por' => $request['revisado_por_nombre'] ?? null,
        'notas_admin' => $request['notas_admin'],
        
        'usuario' => [
            'id' => $request['user_id'],
            'nombres' => $request['usuario_nombres'],
            'email' => $request['usuario_email'],
            'telefono' => $request['usuario_telefono'],
            'registrado' => date('d/m/Y', strtotime($request['usuario_registrado'])),
            'historial_solicitudes' => $user_history
        ],
        
        'mascota' => [
            'id' => $request['pet_id'],
            'nombre' => $request['pet_name'],
            'especie' => $request['especie'],
            'raza' => $request['raza'],
            'edad' => $request['edad'],
            'sexo' => $request['sexo'],
            'color' => $request['color'],
            'tamano' => $request['tamano'],
            'peso_aprox' => $request['peso_aprox'],
            'descripcion' => $request['pet_descripcion'],
            'personalidad' => $request['personalidad'],
            'image_url' => $request['image_url'],
            'urgencia' => $request['urgencia'],
            'estado' => $request['pet_estado']
        ],
        
        'informacion_personal' => [
            'nombres_completos' => $request['nombres_completos'],
            'email' => $request['email'],
            'telefono' => $request['telefono'],
            'fecha_nacimiento' => $request['fecha_nacimiento_formatted'] ?? null,
            'edad' => $request['edad_solicitante'] ?? null,
            'direccion_completa' => $request['direccion_completa'],
            'ciudad' => $request['ciudad'],
            'distrito' => $request['distrito']
        ],
        
        'informacion_hogar' => [
            'tipo_vivienda' => $request['tipo_vivienda'],
            'propietario_acepta_mascotas' => $request['propietario_acepta_mascotas'],
            'miembros_familia' => $request['miembros_familia'],
            'hay_ninos' => $request['hay_ninos'],
            'alergias_familia' => $request['alergias_familia']
        ],
        
        'experiencia_mascotas' => [
            'tiene_otras_mascotas' => $request['tiene_otras_mascotas'],
            'descripcion_otras_mascotas' => $request['descripcion_otras_mascotas'],
            'experiencia_previa' => $request['experiencia_previa'],
            'tiempo_sola_mascota' => $request['tiempo_sola_mascota'],
            'tiene_veterinario' => $request['tiene_veterinario'],
            'presupuesto_mensual' => $request['presupuesto_mensual']
        ],
        
        'motivacion_compromiso' => [
            'motivacion_adopcion' => $request['motivacion_adopcion'],
            'conocimiento_raza' => $request['conocimiento_raza'],
            'dispuesto_entrenar' => $request['dispuesto_entrenar'],
            'compromiso_largo_plazo' => $request['compromiso_largo_plazo']
        ]
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener detalles de solicitud',
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
