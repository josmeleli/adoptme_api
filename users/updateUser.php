<?php
require_once __DIR__ . '/../config.php';

$data = json_input();
$id = $data['id'] ?? null;
if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de usuario es requerido']);
    exit;
}

// CA-001: Validar campos obligatorios si se envían
if (isset($data['name']) && empty(trim($data['name']))) {
    http_response_code(400);
    echo json_encode(['error' => 'El nombre no puede estar vacío']);
    exit;
}

if (isset($data['phone']) && !preg_match('/^\d{9}$/', $data['phone'])) {
    http_response_code(400);
    echo json_encode(['error' => 'El teléfono debe tener 9 dígitos']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    // Actualizar campos del usuario principal
    $user_fields = [];
    $user_params = [':id' => $id];
    
    if (isset($data['name'])) { $user_fields[] = 'name = :name'; $user_params[':name'] = $data['name']; }
    if (isset($data['email'])) { $user_fields[] = 'email = :email'; $user_params[':email'] = $data['email']; }
    if (isset($data['phone'])) { $user_fields[] = 'phone = :phone'; $user_params[':phone'] = $data['phone']; }
    if (isset($data['distrito'])) { $user_fields[] = 'distrito = :distrito'; $user_params[':distrito'] = $data['distrito']; }
    
    if (!empty($user_fields)) {
        $sql = 'UPDATE users SET ' . implode(', ', $user_fields) . ' WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($user_params);
    }
    
    // CA-002: Actualizar preferencias opcionales (especie, tamaño, edad)
    // CA-003: Permite edición en cualquier momento
    if (isset($data['especie']) || isset($data['tamano']) || isset($data['edad'])) {
        // Verificar si ya tiene preferencias
        $stmt = $pdo->prepare('SELECT user_id FROM user_preferences WHERE user_id = :id');
        $stmt->execute([':id' => $id]);
        $has_preferences = $stmt->fetch();
        
        if ($has_preferences) {
            // Actualizar preferencias existentes
            $pref_fields = [];
            $pref_params = [':user_id' => $id];
            if (isset($data['especie'])) { $pref_fields[] = 'especie_preferida = :especie'; $pref_params[':especie'] = $data['especie']; }
            if (isset($data['tamano'])) { $pref_fields[] = 'tamano_preferido = :tamano'; $pref_params[':tamano'] = $data['tamano']; }
            if (isset($data['edad'])) { $pref_fields[] = 'edad_preferida = :edad'; $pref_params[':edad'] = $data['edad']; }
            
            if (!empty($pref_fields)) {
                $sql = 'UPDATE user_preferences SET ' . implode(', ', $pref_fields) . ' WHERE user_id = :user_id';
                $stmt = $pdo->prepare($sql);
                $stmt->execute($pref_params);
            }
        } else {
            // Crear nuevas preferencias
            $stmt = $pdo->prepare('INSERT INTO user_preferences (user_id, especie_preferida, tamano_preferido, edad_preferida) VALUES (:user_id, :especie, :tamano, :edad)');
            $stmt->execute([
                ':user_id' => $id,
                ':especie' => $data['especie'] ?? null,
                ':tamano' => $data['tamano'] ?? null,
                ':edad' => $data['edad'] ?? null
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
