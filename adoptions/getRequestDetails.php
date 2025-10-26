<?php
/**
 * GET /adoptions/getRequestDetails.php
 * 
 * Sprint 3 - HU-005
 * Obtener detalles completos de una solicitud
 * 
 * Parámetros:
 * - request_id (required): ID de la solicitud
 * - user_id (required): ID del usuario (validación de propiedad)
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once '../config.php';

try {
    // Validar parámetros
    if (!isset($_GET['request_id']) || empty($_GET['request_id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'El parámetro request_id es requerido'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if (!isset($_GET['user_id']) || empty($_GET['user_id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'El parámetro user_id es requerido'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $request_id = intval($_GET['request_id']);
    $user_id = intval($_GET['user_id']);
    
    // Obtener detalles completos
    $sql = "SELECT 
                ar.*,
                p.name as pet_name,
                p.especie,
                p.raza,
                p.edad,
                p.sexo,
                p.color,
                p.tamano,
                p.peso_aprox,
                p.descripcion as pet_descripcion,
                p.foto_url as image_url,
                p.is_urgent as urgencia,
                p.estado as pet_estado,
                admin.nombres as revisado_por_nombre
            FROM adoption_requests ar
            INNER JOIN pets p ON ar.pet_id = p.id
            LEFT JOIN users admin ON ar.revisado_por = admin.id
            WHERE ar.id = :request_id AND ar.user_id = :user_id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':request_id', $request_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $request = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$request) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Solicitud no encontrada o no tienes permiso para verla'
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
    
    // Organizar por pantallas
    $formatted_response = [
        'success' => true,
        'request_id' => $request['id'],
        'status' => $request['status'],
        'status_text' => $request['status_text'],
        'created_at' => $request['created_at_formatted'],
        'fecha_revision' => $request['fecha_revision_formatted'] ?? null,
        'revisado_por' => $request['revisado_por_nombre'] ?? null,
        'notas_admin' => $request['notas_admin'],
        
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
            'image_url' => $request['image_url'],
            'urgencia' => $request['urgencia'],
            'estado' => $request['pet_estado']
        ],
        
        'pantalla_1_personal' => [
            'nombres_completos' => $request['nombres_completos'],
            'email' => $request['email'],
            'telefono' => $request['telefono'],
            'fecha_nacimiento' => $request['fecha_nacimiento_formatted'] ?? null,
            'edad' => $request['edad_solicitante'] ?? null,
            'direccion_completa' => $request['direccion_completa'],
            'ciudad' => $request['ciudad'],
            'distrito' => $request['distrito']
        ],
        
        'pantalla_2_hogar' => [
            'tipo_vivienda' => $request['tipo_vivienda'],
            'propietario_acepta_mascotas' => $request['propietario_acepta_mascotas'],
            'miembros_familia' => $request['miembros_familia'],
            'hay_ninos' => $request['hay_ninos'],
            'alergias_familia' => $request['alergias_familia']
        ],
        
        'pantalla_3_experiencia' => [
            'tiene_otras_mascotas' => $request['tiene_otras_mascotas'],
            'descripcion_otras_mascotas' => $request['descripcion_otras_mascotas'],
            'experiencia_previa' => $request['experiencia_previa'],
            'tiempo_sola_mascota' => $request['tiempo_sola_mascota'],
            'tiene_veterinario' => $request['tiene_veterinario'],
            'presupuesto_mensual' => $request['presupuesto_mensual']
        ],
        
        'pantalla_4_motivacion' => [
            'motivacion_adopcion' => $request['motivacion_adopcion'],
            'conocimiento_raza' => $request['conocimiento_raza'],
            'dispuesto_entrenar' => $request['dispuesto_entrenar'],
            'compromiso_largo_plazo' => $request['compromiso_largo_plazo']
        ]
    ];
    
    echo json_encode($formatted_response, JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener detalles de solicitud',
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
