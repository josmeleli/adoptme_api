<?php
// test_connection.php - Test database connection
require_once __DIR__ . '/config.php';

echo json_encode([
    'status' => 'success',
    'message' => 'Database connection successful!',
    'database' => $DB_NAME,
    'host' => $DB_HOST
]);
?>
