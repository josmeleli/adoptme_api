<?php
// test_direct.php - Test directo sin servidor web
echo "=== Test Directo API AdoptMe ===\n\n";

// Simular REQUEST
$_SERVER['REQUEST_METHOD'] = 'POST';

// TEST 1: Registro
echo "TEST 1: Registro de usuario\n";
$_POST = [];
file_put_contents('php://input', json_encode([
    'email' => 'testdirect@adoptme.com',
    'password' => '123456',
    'name' => 'Test Directo',
    'phone' => '987654321'
]));

ob_start();
include 'register.php';
$output = ob_get_clean();
$result = json_decode($output, true);

if ($result && isset($result['success']) && $result['success']) {
    echo "✓ Registro exitoso - User ID: {$result['user_id']}\n";
} else {
    echo "✗ Error en registro\n";
    echo "Salida: $output\n";
}

echo "\n=== Estructura de Archivos Creada ===\n\n";

$files = [
    'config.php' => 'Configuración y conexión DB',
    'register.php' => 'Registro con validaciones (HU-001)',
    'verify.php' => 'Verificación de cuenta',
    'login.php' => 'Login seguro (HU-002)',
    'logout.php' => 'Cerrar sesión',
    'refresh_token.php' => 'Renovar token',
    'users/getUser.php' => 'Obtener perfil (HU-003)',
    'users/updateUser.php' => 'Actualizar perfil y preferencias (HU-003)',
    'chat/sendMessage.php' => 'Enviar mensajes',
    'chat/getMessages.php' => 'Obtener conversación',
    'adoption/createAdoption.php' => 'Crear solicitud de adopción',
    'adoption/trackAdoption.php' => 'Seguimiento de adopción',
    'notifications/getNotifications.php' => 'Obtener notificaciones',
    'database_schema.sql' => 'Schema de base de datos',
    'install_database.php' => 'Instalador de BD',
    'test_connection.php' => 'Test de conexión',
    'README.md' => 'Documentación completa'
];

foreach ($files as $file => $description) {
    $exists = file_exists(__DIR__ . '/' . $file);
    $icon = $exists ? '✓' : '✗';
    echo "$icon $file - $description\n";
}

echo "\n";

?>
