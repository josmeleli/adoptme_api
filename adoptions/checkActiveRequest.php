<?php
/**
 * GET /adoptions/checkActiveRequest.php
 * 
 * Sprint 3 - HU-005 - CA-002
 * Validar si el usuario ya tiene una solicitud activa para la mascota
 * 
 * Parámetros requeridos:
 * - user_id: ID del usuario
 * - pet_id: ID de la mascota
 * 
 * Estados activos: pendiente, en_revision
 * Estados inactivos: aprobada, rechazada
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config.php';

try {
    // Validar parámetros requeridos
    if (!isset($_GET['user_id']) || empty($_GET['user_id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'El parámetro user_id es requerido'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if (!isset($_GET['pet_id']) || empty($_GET['pet_id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'El parámetro pet_id es requerido'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $user_id = intval($_GET['user_id']);
    $pet_id = intval($_GET['pet_id']);
    
    // CA-002: Verificar si ya tiene solicitud activa (pendiente o en_revision)
    $sql = "SELECT 
                id, 
                status, 
                created_at 
            FROM adoption_requests 
            WHERE user_id = :user_id 
            AND pet_id = :pet_id 
            AND status IN ('pendiente', 'en_revision')
            LIMIT 1";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':pet_id', $pet_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $active_request = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Verificar que la mascota esté disponible
    $pet_sql = "SELECT estado FROM pets WHERE id = :pet_id";
    $pet_stmt = $pdo->prepare($pet_sql);
    $pet_stmt->bindParam(':pet_id', $pet_id, PDO::PARAM_INT);
    $pet_stmt->execute();
    $pet = $pet_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pet) {
        echo json_encode([
            'success' => false,
            'has_active_request' => false,
            'can_apply' => false,
            'message' => 'Mascota no encontrada'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $pet_disponible = $pet['estado'] === 'Disponible';
    
    if ($active_request) {
        // Tiene solicitud activa - CA-002
        echo json_encode([
            'success' => true,
            'has_active_request' => true,
            'can_apply' => false,
            'request_id' => intval($active_request['id']),
            'request_status' => $active_request['status'],
            'created_at' => $active_request['created_at'],
            'message' => 'Ya tienes una solicitud ' . $active_request['status'] . ' para esta mascota'
        ], JSON_UNESCAPED_UNICODE);
    } else if (!$pet_disponible) {
        // Mascota no disponible
        echo json_encode([
            'success' => true,
            'has_active_request' => false,
            'can_apply' => false,
            'message' => 'Esta mascota ya no está disponible para adopción'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        // Puede enviar solicitud
        echo json_encode([
            'success' => true,
            'has_active_request' => false,
            'can_apply' => true,
            'message' => 'Puedes enviar tu solicitud de adopción'
        ], JSON_UNESCAPED_UNICODE);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al verificar solicitud',
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
