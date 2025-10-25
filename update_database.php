<?php
// update_database.php - Actualizar tabla users con nuevos campos
require_once __DIR__ . '/config.php';

echo "=== Actualizando estructura de BD ===\n\n";

try {
    // Deshabilitar foreign key checks temporalmente
    echo "Deshabilitando foreign key checks...\n";
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
    
    // Eliminar tablas con dependencias
    echo "Eliminando tablas con dependencias...\n";
    $pdo->exec('DROP TABLE IF EXISTS verification_codes');
    $pdo->exec('DROP TABLE IF EXISTS sessions');
    $pdo->exec('DROP TABLE IF EXISTS user_preferences');
    $pdo->exec('DROP TABLE IF EXISTS messages');
    $pdo->exec('DROP TABLE IF EXISTS adoptions');
    $pdo->exec('DROP TABLE IF EXISTS notifications');
    $pdo->exec('DROP TABLE IF EXISTS users');
    
    // Rehabilitar foreign key checks
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    
    echo "Recreando tabla users con nueva estructura...\n";
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombres VARCHAR(100) NOT NULL,
        apellidos VARCHAR(100) NOT NULL,
        dni CHAR(8) NOT NULL UNIQUE,
        email VARCHAR(255) NOT NULL UNIQUE,
        telefono CHAR(9) NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        distrito VARCHAR(100),
        is_verified TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_email (email),
        INDEX idx_dni (dni)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    
    echo "✓ Tabla users actualizada correctamente\n\n";
    echo "Nuevos campos:\n";
    echo "  - nombres (VARCHAR 100) - OBLIGATORIO\n";
    echo "  - apellidos (VARCHAR 100) - OBLIGATORIO\n";
    echo "  - dni (CHAR 8) - OBLIGATORIO, ÚNICO\n";
    echo "  - email (VARCHAR 255) - OBLIGATORIO, ÚNICO\n";
    echo "  - telefono (CHAR 9) - OBLIGATORIO\n";
    echo "  - password_hash (VARCHAR 255) - OBLIGATORIO\n";
    echo "  - distrito (VARCHAR 100) - OPCIONAL\n";
    echo "  - is_verified (TINYINT) - DEFAULT 0\n\n";
    
    echo "✓ Actualización completada exitosamente!\n";
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

?>
