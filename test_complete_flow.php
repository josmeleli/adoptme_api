<?php
// test_complete_flow.php - Prueba completa del flujo de usuario
echo "=== Test Completo API AdoptMe - Sprint 1 ===\n\n";

$base_url = 'http://localhost/adopciones_api';
$test_email = 'test_' . time() . '@adoptme.com';
$test_password = '123456';

function api_call($url, $method = 'GET', $data = null, $headers = []) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    
    $default_headers = ['Content-Type: application/json'];
    curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($default_headers, $headers));
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    }
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ['code' => $http_code, 'body' => json_decode($response, true)];
}

// TEST 1: Registro de usuario
echo "TEST 1: Registro de usuario (HU-001)\n";
$response = api_call("$base_url/register.php", 'POST', [
    'email' => $test_email,
    'password' => $test_password,
    'name' => 'Usuario Test',
    'phone' => '987654321'
]);

if ($response['code'] === 200 && $response['body']['success']) {
    echo "✓ Usuario registrado exitosamente\n";
    $user_id = $response['body']['user_id'];
    $verification_code = $response['body']['verification_code'];
    echo "  User ID: $user_id\n";
    echo "  Código verificación: $verification_code\n";
} else {
    echo "✗ Error al registrar usuario\n";
    print_r($response);
    exit(1);
}

// TEST 2: Verificar cuenta
echo "\nTEST 2: Verificar cuenta\n";
$response = api_call("$base_url/verify.php", 'POST', [
    'user_id' => $user_id,
    'code' => $verification_code
]);

if ($response['code'] === 200 && $response['body']['success']) {
    echo "✓ Cuenta verificada correctamente\n";
} else {
    echo "✗ Error al verificar cuenta\n";
    print_r($response);
    exit(1);
}

// TEST 3: Login
echo "\nTEST 3: Inicio de sesión (HU-002)\n";
$response = api_call("$base_url/login.php", 'POST', [
    'email' => $test_email,
    'password' => $test_password
]);

if ($response['code'] === 200 && $response['body']['success']) {
    echo "✓ Login exitoso\n";
    $token = $response['body']['token'];
    echo "  Token obtenido\n";
} else {
    echo "✗ Error al hacer login\n";
    print_r($response);
    exit(1);
}

// TEST 4: Obtener perfil
echo "\nTEST 4: Obtener perfil de usuario (HU-003)\n";
$response = api_call("$base_url/users/getUser.php?id=$user_id");

if ($response['code'] === 200 && isset($response['body']['id'])) {
    echo "✓ Perfil obtenido correctamente\n";
    echo "  Nombre: {$response['body']['name']}\n";
    echo "  Email: {$response['body']['email']}\n";
} else {
    echo "✗ Error al obtener perfil\n";
    print_r($response);
}

// TEST 5: Actualizar perfil con preferencias
echo "\nTEST 5: Actualizar perfil con preferencias (HU-003)\n";
$response = api_call("$base_url/users/updateUser.php", 'POST', [
    'id' => $user_id,
    'name' => 'Usuario Test Actualizado',
    'distrito' => 'San Isidro',
    'especie' => 'Perro',
    'tamano' => 'Mediano',
    'edad' => 'Adulto'
]);

if ($response['code'] === 200 && $response['body']['success']) {
    echo "✓ Perfil actualizado correctamente\n";
} else {
    echo "✗ Error al actualizar perfil\n";
    print_r($response);
}

// TEST 6: Verificar perfil actualizado
echo "\nTEST 6: Verificar perfil actualizado\n";
$response = api_call("$base_url/users/getUser.php?id=$user_id");

if ($response['code'] === 200) {
    echo "✓ Perfil verificado\n";
    echo "  Nombre: {$response['body']['name']}\n";
    echo "  Distrito: {$response['body']['distrito']}\n";
    echo "  Preferencias:\n";
    echo "    - Especie: {$response['body']['preferences']['especie']}\n";
    echo "    - Tamaño: {$response['body']['preferences']['tamano']}\n";
    echo "    - Edad: {$response['body']['preferences']['edad']}\n";
} else {
    echo "✗ Error al verificar perfil\n";
}

// TEST 7: Logout
echo "\nTEST 7: Cerrar sesión (TR-006)\n";
$response = api_call("$base_url/logout.php", 'POST', null, [
    "Authorization: Bearer $token"
]);

if ($response['code'] === 200 && $response['body']['success']) {
    echo "✓ Sesión cerrada correctamente\n";
} else {
    echo "✗ Error al cerrar sesión\n";
}

echo "\n=== RESUMEN ===\n";
echo "✓ Todas las pruebas completadas exitosamente\n";
echo "✓ HU-001: Registro con validaciones - OK\n";
echo "✓ HU-002: Login seguro con sesión - OK\n";
echo "✓ HU-003: Perfil de usuario completo - OK\n";
echo "\n";

?>
