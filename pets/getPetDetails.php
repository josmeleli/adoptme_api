<?php
/**
 * GET /pets/getPetDetails.php
 * 
 * Obtener detalles completos de una mascota específica
 * Complementa al catálogo con información detallada
 * 
 * Parámetro requerido:
 * - pet_id: ID de la mascota
 * 
 * Retorna:
 * - Información completa de la mascota
 * - Estado de adopción
 * - Detalles del refugio (si aplica)
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config.php';

try {
    // Validar parámetro pet_id
    if (!isset($_GET['pet_id']) || empty($_GET['pet_id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'El parámetro pet_id es requerido'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $pet_id = intval($_GET['pet_id']);
    
    // Consultar detalles de la mascota
    $sql = "SELECT 
                id,
                name,
                especie,
                raza,
                edad,
                tamano,
                sexo,
                descripcion,
                foto_url,
                distrito,
                is_urgent,
                priority,
                estado,
                refugio_id,
                created_at,
                updated_at
            FROM pets 
            WHERE id = :pet_id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':pet_id', $pet_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $pet = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pet) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Mascota no encontrada'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Formatear datos
    $pet['id'] = intval($pet['id']);
    $pet['edad'] = intval($pet['edad']);
    $pet['is_urgent'] = (bool)$pet['is_urgent'];
    $pet['priority'] = intval($pet['priority']);
    $pet['refugio_id'] = $pet['refugio_id'] ? intval($pet['refugio_id']) : null;
    
    // Categoría de edad
    if ($pet['edad'] <= 2) {
        $pet['categoria_edad'] = 'Cachorro';
    } elseif ($pet['edad'] <= 7) {
        $pet['categoria_edad'] = 'Adulto';
    } else {
        $pet['categoria_edad'] = 'Senior';
    }
    
    // Verificar si tiene solicitudes de adopción pendientes
    $adoption_sql = "SELECT COUNT(*) as solicitudes_pendientes 
                     FROM adoptions 
                     WHERE pet_id = :pet_id AND status = 'pending'";
    $adoption_stmt = $pdo->prepare($adoption_sql);
    $adoption_stmt->bindParam(':pet_id', $pet_id, PDO::PARAM_INT);
    $adoption_stmt->execute();
    $adoption_info = $adoption_stmt->fetch(PDO::FETCH_ASSOC);
    
    $pet['solicitudes_pendientes'] = intval($adoption_info['solicitudes_pendientes']);
    $pet['puede_adoptar'] = $pet['estado'] === 'Disponible';
    
    echo json_encode([
        'success' => true,
        'data' => $pet
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener detalles de la mascota',
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
