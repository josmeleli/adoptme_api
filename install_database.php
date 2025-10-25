<?php
// install_database.php - Ejecutar schema SQL desde PHP
require_once __DIR__ . '/config.php';

echo "=== Instalando Base de Datos AdoptMe ===\n\n";

// Leer archivo SQL
$sql_file = __DIR__ . '/database_schema.sql';
if (!file_exists($sql_file)) {
    die("ERROR: No se encontró database_schema.sql\n");
}

$sql = file_get_contents($sql_file);

// Eliminar comentarios
$sql = preg_replace('/^\s*--.*$/m', '', $sql);
$sql = preg_replace('/\/\*.*?\*\//s', '', $sql);

// Separar por statements (punto y coma)
$statements = explode(';', $sql);
$statements = array_filter(array_map('trim', $statements), function($stmt) {
    return !empty($stmt);
});

echo "Se ejecutarán " . count($statements) . " comandos SQL...\n\n";

$success = 0;
$errors = 0;

foreach ($statements as $statement) {
    if (empty(trim($statement))) continue;
    
    try {
        $pdo->exec($statement);
        $success++;
        
        // Detectar nombre de tabla creada
        if (preg_match('/CREATE TABLE.*?`?(\w+)`?\s*\(/i', $statement, $matches)) {
            echo "✓ Tabla '{$matches[1]}' creada correctamente\n";
        }
    } catch (PDOException $e) {
        $errors++;
        echo "✗ Error: " . $e->getMessage() . "\n";
    }
}

echo "\n=== Resultado ===\n";

echo "Exitosos: $success\n";
echo "Errores: $errors\n";

if ($errors === 0) {
    echo "\n✓ Base de datos instalada correctamente!\n";
} else {
    echo "\n⚠ Hubo algunos errores. Revisa los mensajes arriba.\n";
}

?>
