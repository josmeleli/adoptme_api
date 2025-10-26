<?php
/**
 * POST /adoptions/createRequest.php
 * 
 * Sprint 3 - HU-005 - TR-10
 * Crear solicitud de adopción
 * 
 * Validaciones (TR-09):
 * - Usuario existe y está verificado
 * - Mascota existe y está disponible
 * - NO tiene solicitud activa (CA-002)
 * - Todos los campos obligatorios presentes
 * - Formatos válidos (email, teléfono, fecha)
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config.php';

try {
    $data = json_input();
    
    // Validar campos requeridos
    $required_fields = [
        'user_id', 'pet_id',
        // Pantalla 1
        'nombres_completos', 'email', 'telefono', 'fecha_nacimiento', 
        'direccion_completa', 'ciudad', 'distrito',
        // Pantalla 2
        'tipo_vivienda', 'propietario_acepta_mascotas', 'miembros_familia',
        'hay_ninos', 'alergias_familia',
        // Pantalla 3
        'tiene_otras_mascotas', 'experiencia_previa', 'tiempo_sola_mascota',
        'tiene_veterinario', 'presupuesto_mensual',
        // Pantalla 4
        'motivacion_adopcion', 'conocimiento_raza', 'dispuesto_entrenar',
        'compromiso_largo_plazo'
    ];
    
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => "El campo '{$field}' es requerido",
                'missing_field' => $field
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
    
    $user_id = intval($data['user_id']);
    $pet_id = intval($data['pet_id']);
    
    // TR-09: Validar que el usuario existe y está verificado
    $user_sql = "SELECT is_verified FROM users WHERE id = :user_id";
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
    
    if (!$user['is_verified']) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Debes verificar tu cuenta antes de enviar una solicitud'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // TR-09: Validar que la mascota existe y está disponible
    $pet_sql = "SELECT estado FROM pets WHERE id = :pet_id";
    $pet_stmt = $pdo->prepare($pet_sql);
    $pet_stmt->bindParam(':pet_id', $pet_id, PDO::PARAM_INT);
    $pet_stmt->execute();
    $pet = $pet_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pet) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Mascota no encontrada'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if ($pet['estado'] !== 'Disponible') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Esta mascota ya no está disponible para adopción'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // CA-002: Validar que NO tenga solicitud activa
    $check_sql = "SELECT id, status FROM adoption_requests 
                  WHERE user_id = :user_id AND pet_id = :pet_id 
                  AND status IN ('pendiente', 'en_revision')
                  LIMIT 1";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $check_stmt->bindParam(':pet_id', $pet_id, PDO::PARAM_INT);
    $check_stmt->execute();
    
    if ($check_stmt->fetch()) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error_code' => 'DUPLICATE_REQUEST',
            'message' => 'Ya tienes una solicitud pendiente para esta mascota'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // TR-09: Validar formato de email
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Formato de email inválido'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // TR-09: Validar formato de teléfono (9 dígitos)
    if (!preg_match('/^[0-9]{9}$/', $data['telefono'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'El teléfono debe tener 9 dígitos'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // TR-09: Validar fecha de nacimiento (mayor de 18 años)
    $fecha_nac = new DateTime($data['fecha_nacimiento']);
    $hoy = new DateTime();
    $edad = $hoy->diff($fecha_nac)->y;
    
    if ($edad < 18) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Debes ser mayor de 18 años para adoptar'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // TR-10: Insertar solicitud en base de datos
    $insert_sql = "INSERT INTO adoption_requests (
        user_id, pet_id,
        nombres_completos, email, telefono, fecha_nacimiento,
        direccion_completa, ciudad, distrito,
        tipo_vivienda, propietario_acepta_mascotas, miembros_familia,
        hay_ninos, alergias_familia,
        tiene_otras_mascotas, descripcion_otras_mascotas, experiencia_previa,
        tiempo_sola_mascota, tiene_veterinario, presupuesto_mensual,
        motivacion_adopcion, conocimiento_raza, dispuesto_entrenar,
        compromiso_largo_plazo,
        status
    ) VALUES (
        :user_id, :pet_id,
        :nombres_completos, :email, :telefono, :fecha_nacimiento,
        :direccion_completa, :ciudad, :distrito,
        :tipo_vivienda, :propietario_acepta_mascotas, :miembros_familia,
        :hay_ninos, :alergias_familia,
        :tiene_otras_mascotas, :descripcion_otras_mascotas, :experiencia_previa,
        :tiempo_sola_mascota, :tiene_veterinario, :presupuesto_mensual,
        :motivacion_adopcion, :conocimiento_raza, :dispuesto_entrenar,
        :compromiso_largo_plazo,
        'pendiente'
    )";
    
    $stmt = $pdo->prepare($insert_sql);
    
    // Bind de parámetros
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':pet_id', $pet_id, PDO::PARAM_INT);
    $stmt->bindParam(':nombres_completos', $data['nombres_completos']);
    $stmt->bindParam(':email', $data['email']);
    $stmt->bindParam(':telefono', $data['telefono']);
    $stmt->bindParam(':fecha_nacimiento', $data['fecha_nacimiento']);
    $stmt->bindParam(':direccion_completa', $data['direccion_completa']);
    $stmt->bindParam(':ciudad', $data['ciudad']);
    $stmt->bindParam(':distrito', $data['distrito']);
    $stmt->bindParam(':tipo_vivienda', $data['tipo_vivienda']);
    $stmt->bindParam(':propietario_acepta_mascotas', $data['propietario_acepta_mascotas']);
    $stmt->bindParam(':miembros_familia', $data['miembros_familia'], PDO::PARAM_INT);
    $stmt->bindParam(':hay_ninos', $data['hay_ninos']);
    $stmt->bindParam(':alergias_familia', $data['alergias_familia']);
    $stmt->bindParam(':tiene_otras_mascotas', $data['tiene_otras_mascotas']);
    
    $descripcion_otras = isset($data['descripcion_otras_mascotas']) ? $data['descripcion_otras_mascotas'] : null;
    $stmt->bindParam(':descripcion_otras_mascotas', $descripcion_otras);
    
    $stmt->bindParam(':experiencia_previa', $data['experiencia_previa']);
    $stmt->bindParam(':tiempo_sola_mascota', $data['tiempo_sola_mascota']);
    $stmt->bindParam(':tiene_veterinario', $data['tiene_veterinario']);
    $stmt->bindParam(':presupuesto_mensual', $data['presupuesto_mensual']);
    $stmt->bindParam(':motivacion_adopcion', $data['motivacion_adopcion']);
    $stmt->bindParam(':conocimiento_raza', $data['conocimiento_raza']);
    $stmt->bindParam(':dispuesto_entrenar', $data['dispuesto_entrenar']);
    $stmt->bindParam(':compromiso_largo_plazo', $data['compromiso_largo_plazo']);
    
    $stmt->execute();
    $request_id = $pdo->lastInsertId();
    
    // TR-11: Notificar a administradores (solo in-app, NO email)
    $pet_name_sql = "SELECT name FROM pets WHERE id = :pet_id";
    $pet_name_stmt = $pdo->prepare($pet_name_sql);
    $pet_name_stmt->bindParam(':pet_id', $pet_id, PDO::PARAM_INT);
    $pet_name_stmt->execute();
    $pet_data = $pet_name_stmt->fetch(PDO::FETCH_ASSOC);
    $pet_name = $pet_data['name'];
    
    // Insertar notificación para todos los administradores
    $notify_sql = "INSERT INTO notifications (user_id, type, title, message, related_id)
                   SELECT id, 'nueva_solicitud', 
                          'Nueva solicitud de adopción',
                          CONCAT(:user_name, ' quiere adoptar a ', :pet_name),
                          :request_id
                   FROM users WHERE role = 'admin'";
    
    $notify_stmt = $pdo->prepare($notify_sql);
    $notify_stmt->bindParam(':user_name', $data['nombres_completos']);
    $notify_stmt->bindParam(':pet_name', $pet_name);
    $notify_stmt->bindParam(':request_id', $request_id, PDO::PARAM_INT);
    $notify_stmt->execute();
    
    $admins_notified = $notify_stmt->rowCount();
    
    http_response_code(201);
    echo json_encode([
        'success' => true,
        'request_id' => $request_id,
        'message' => 'Solicitud enviada exitosamente. Será revisada en 3-4 días hábiles',
        'review_time' => '3-4 días hábiles',
        'admins_notified' => $admins_notified
    ], JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al crear solicitud',
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
