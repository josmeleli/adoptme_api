<?php
/**
 * GET /pets/getPets.php
 * 
 * HU-004: Catálogo de Mascotas
 * TR-05: Implementar catálogo con fotos y detalles
 * TR-06: Desarrollar filtros de búsqueda
 * 
 * Criterios de Aceptación:
 * CA-001: Mostrar foto, especie, edad y ubicación
 * CA-002: Mascotas urgentes aparecen primero
 * CA-003: Paginación con mínimo 6 resultados
 * 
 * Filtros disponibles:
 * - especie: Perro, Gato
 * - tamano: Pequeño, Mediano, Grande
 * - edad: 0-2 (cachorro), 3-7 (adulto), 8+ (senior)
 * - distrito: filtro por ubicación
 * 
 * Paginación:
 * - page: número de página (default: 1)
 * - limit: resultados por página (default: 6, min: 6)
 * 
 * Ordenamiento:
 * - Por defecto: urgentes primero (priority DESC), luego por fecha
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config.php';

try {
    // Filtro genérico de búsqueda (busca en especie, raza, sexo, edad)
    $search = isset($_GET['search']) ? trim($_GET['search']) : null;
    
    // Obtener parámetros de filtro individuales
    $especie = isset($_GET['especie']) ? trim($_GET['especie']) : null;
    $raza = isset($_GET['raza']) ? trim($_GET['raza']) : null;
    $sexo = isset($_GET['sexo']) ? trim($_GET['sexo']) : null;
    $tamano = isset($_GET['tamano']) ? trim($_GET['tamano']) : null;
    $distrito = isset($_GET['distrito']) ? trim($_GET['distrito']) : null;
    
    // Filtro por edad (rango)
    $edad_min = isset($_GET['edad_min']) ? intval($_GET['edad_min']) : null;
    $edad_max = isset($_GET['edad_max']) ? intval($_GET['edad_max']) : null;
    $edad = isset($_GET['edad']) ? intval($_GET['edad']) : null;
    
    // Categoría de edad
    $categoria_edad = isset($_GET['categoria_edad']) ? trim($_GET['categoria_edad']) : null;
    if ($categoria_edad) {
        switch ($categoria_edad) {
            case 'cachorro':
                $edad_min = 0;
                $edad_max = 2;
                break;
            case 'adulto':
                $edad_min = 3;
                $edad_max = 7;
                break;
            case 'senior':
                $edad_min = 8;
                $edad_max = 99;
                break;
        }
    }
    
    // Parámetros de paginación (CA-003)
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? max(6, intval($_GET['limit'])) : 6; // Mínimo 6 resultados
    $offset = ($page - 1) * $limit;
    
    // Solo mascotas urgentes
    $solo_urgentes = isset($_GET['urgentes']) && $_GET['urgentes'] === 'true';
    
    // Construir consulta SQL con filtros
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
                created_at
            FROM pets 
            WHERE estado = 'Disponible'";
    
    $params = [];
    
    // Aplicar filtro genérico de búsqueda (busca en múltiples campos)
    if ($search) {
        $sql .= " AND (
            especie LIKE :search 
            OR raza LIKE :search 
            OR sexo LIKE :search 
            OR CAST(edad AS CHAR) LIKE :search
            OR name LIKE :search
        )";
        $params[':search'] = '%' . $search . '%';
    }
    
    // Aplicar filtros individuales
    if ($especie) {
        $sql .= " AND especie = :especie";
        $params[':especie'] = $especie;
    }
    
    if ($raza) {
        $sql .= " AND raza LIKE :raza";
        $params[':raza'] = '%' . $raza . '%';
    }
    
    if ($sexo) {
        $sql .= " AND sexo = :sexo";
        $params[':sexo'] = $sexo;
    }
    
    if ($tamano) {
        $sql .= " AND tamano = :tamano";
        $params[':tamano'] = $tamano;
    }
    
    if ($distrito) {
        $sql .= " AND distrito LIKE :distrito";
        $params[':distrito'] = '%' . $distrito . '%';
    }
    
    if ($edad !== null) {
        $sql .= " AND edad = :edad";
        $params[':edad'] = $edad;
    }
    
    if ($edad_min !== null) {
        $sql .= " AND edad >= :edad_min";
        $params[':edad_min'] = $edad_min;
    }
    
    if ($edad_max !== null) {
        $sql .= " AND edad <= :edad_max";
        $params[':edad_max'] = $edad_max;
    }
    
    if ($solo_urgentes) {
        $sql .= " AND is_urgent = 1";
    }
    
    // CA-002: Ordenamiento - urgentes primero, luego por prioridad
    $sql .= " ORDER BY is_urgent DESC, priority DESC, created_at DESC";
    
    // Contar total de resultados (para paginación)
    $count_sql = "SELECT COUNT(*) as total FROM pets WHERE estado = 'Disponible'";
    
    // Aplicar el mismo filtro genérico al count
    if ($search) {
        $count_sql .= " AND (
            especie LIKE :search 
            OR raza LIKE :search 
            OR sexo LIKE :search 
            OR CAST(edad AS CHAR) LIKE :search
            OR name LIKE :search
        )";
    }
    
    if ($especie) $count_sql .= " AND especie = :especie";
    if ($raza) $count_sql .= " AND raza LIKE :raza";
    if ($sexo) $count_sql .= " AND sexo = :sexo";
    if ($tamano) $count_sql .= " AND tamano = :tamano";
    if ($distrito) $count_sql .= " AND distrito LIKE :distrito";
    if ($edad !== null) $count_sql .= " AND edad = :edad";
    if ($edad_min !== null) $count_sql .= " AND edad >= :edad_min";
    if ($edad_max !== null) $count_sql .= " AND edad <= :edad_max";
    if ($solo_urgentes) $count_sql .= " AND is_urgent = 1";
    
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Aplicar paginación
    $sql .= " LIMIT :limit OFFSET :offset";
    
    $stmt = $pdo->prepare($sql);
    
    // Bind de parámetros
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $pets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear datos
    foreach ($pets as &$pet) {
        $pet['id'] = intval($pet['id']);
        $pet['edad'] = intval($pet['edad']);
        $pet['is_urgent'] = (bool)$pet['is_urgent'];
        $pet['priority'] = intval($pet['priority']);
        
        // Agregar etiqueta de urgencia
        $pet['etiqueta_urgencia'] = $pet['is_urgent'] ? 'URGENTE' : null;
        
        // Categoría de edad legible
        if ($pet['edad'] <= 2) {
            $pet['categoria_edad'] = 'Cachorro';
        } elseif ($pet['edad'] <= 7) {
            $pet['categoria_edad'] = 'Adulto';
        } else {
            $pet['categoria_edad'] = 'Senior';
        }
    }
    
    // Calcular información de paginación
    $total_pages = ceil($total / $limit);
    $has_next = $page < $total_pages;
    $has_prev = $page > 1;
    
    echo json_encode([
        'success' => true,
        'data' => $pets,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_items' => intval($total),
            'items_per_page' => $limit,
            'has_next_page' => $has_next,
            'has_previous_page' => $has_prev,
            'next_page' => $has_next ? $page + 1 : null,
            'previous_page' => $has_prev ? $page - 1 : null
        ],
        'filters_applied' => [
            'search' => $search,
            'especie' => $especie,
            'raza' => $raza,
            'sexo' => $sexo,
            'tamano' => $tamano,
            'distrito' => $distrito,
            'edad' => $edad,
            'edad_min' => $edad_min,
            'edad_max' => $edad_max,
            'categoria_edad' => $categoria_edad,
            'solo_urgentes' => $solo_urgentes
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener catálogo de mascotas',
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
