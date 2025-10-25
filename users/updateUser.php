<?php
require_once __DIR__ . '/../config.php';

$data = json_input();
$user_id = $data['user_id'] ?? null;

if (!$user_id) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de usuario es requerido']);
    exit;
}

// VALIDACIONES
// Validar nombres (si se envían)
if (isset($data['nombres'])) {
    $nombres = trim($data['nombres']);
    if (empty($nombres) || !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $nombres)) {
        http_response_code(400);
        echo json_encode(['error' => 'Nombres inválidos. Solo se permiten letras y espacios']);
        exit;
    }
}

// Validar apellidos (si se envían)
if (isset($data['apellidos'])) {
    $apellidos = trim($data['apellidos']);
    if (empty($apellidos) || !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $apellidos)) {
        http_response_code(400);
        echo json_encode(['error' => 'Apellidos inválidos. Solo se permiten letras y espacios']);
        exit;
    }
}

// Validar teléfono (si se envía)
if (isset($data['telefono']) && !preg_match('/^\d{9}$/', $data['telefono'])) {
    http_response_code(400);
    echo json_encode(['error' => 'El teléfono debe tener exactamente 9 dígitos']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    // Actualizar campos del usuario principal
    $user_fields = [];
    $user_params = [':user_id' => $user_id];
    
    if (isset($data['nombres'])) { 
        $user_fields[] = 'nombres = :nombres'; 
        $user_params[':nombres'] = trim($data['nombres']); 
    }
    if (isset($data['apellidos'])) { 
        $user_fields[] = 'apellidos = :apellidos'; 
        $user_params[':apellidos'] = trim($data['apellidos']); 
    }
    if (isset($data['telefono'])) { 
        $user_fields[] = 'telefono = :telefono'; 
        $user_params[':telefono'] = $data['telefono']; 
    }
    if (isset($data['distrito'])) { 
        $user_fields[] = 'distrito = :distrito'; 
        $user_params[':distrito'] = $data['distrito']; 
    }
    
    if (!empty($user_fields)) {
        $sql = 'UPDATE users SET ' . implode(', ', $user_fields) . ' WHERE id = :user_id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($user_params);
    }
    
    // Actualizar preferencias opcionales (especie, tamaño, edad)
    if (isset($data['especie_preferida']) || isset($data['tamano_preferido']) || isset($data['edad_preferida'])) {
        // Verificar si ya tiene preferencias
        $stmt = $pdo->prepare('SELECT user_id FROM user_preferences WHERE user_id = :user_id');
        $stmt->execute([':user_id' => $user_id]);
        $has_preferences = $stmt->fetch();
        
        if ($has_preferences) {
            // Actualizar preferencias existentes
            $pref_fields = [];
            $pref_params = [':user_id' => $user_id];
            
            if (isset($data['especie_preferida'])) { 
                $pref_fields[] = 'especie_preferida = :especie'; 
                $pref_params[':especie'] = $data['especie_preferida']; 
            }
            if (isset($data['tamano_preferido'])) { 
                $pref_fields[] = 'tamano_preferido = :tamano'; 
                $pref_params[':tamano'] = $data['tamano_preferido']; 
            }
            if (isset($data['edad_preferida'])) { 
                $pref_fields[] = 'edad_preferida = :edad'; 
                $pref_params[':edad'] = $data['edad_preferida']; 
            }
            
            if (!empty($pref_fields)) {
                $sql = 'UPDATE user_preferences SET ' . implode(', ', $pref_fields) . ' WHERE user_id = :user_id';
                $stmt = $pdo->prepare($sql);
                $stmt->execute($pref_params);
            }
        } else {
            // Crear nuevas preferencias
            $stmt = $pdo->prepare('INSERT INTO user_preferences (user_id, especie_preferida, tamano_preferido, edad_preferida) VALUES (:user_id, :especie, :tamano, :edad)');
            $stmt->execute([
                ':user_id' => $user_id,
                ':especie' => $data['especie_preferida'] ?? null,
                ':tamano' => $data['tamano_preferido'] ?? null,
                ':edad' => $data['edad_preferida'] ?? null
            ]);
        }
    }
    
    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Perfil actualizado correctamente']);
    
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'No se pudo actualizar el perfil', 'details' => $e->getMessage()]);
}

?>
    http_response_code(500);
    echo json_encode(['error' => 'No se pudo actualizar el perfil', 'details' => $e->getMessage()]);
}

?>
