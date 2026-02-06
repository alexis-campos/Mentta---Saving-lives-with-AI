<?php
/**
 * MENTTA - Database Connection Test
 * Temporal endpoint para diagnosticar conexión a MySQL en Railway
 */

header('Content-Type: application/json');

// Cargar config
require_once __DIR__ . '/includes/config.php';

echo json_encode([
    'timestamp' => date('Y-m-d H:i:s'),
    'env' => [
        'APP_ENV' => APP_ENV,
        'DB_HOST' => DB_HOST,
        'DB_PORT' => defined('DB_PORT') ? DB_PORT : 'NOT_DEFINED',
        'DB_NAME' => DB_NAME,
        'DB_USER' => DB_USER,
        'DB_PASS' => strlen(DB_PASS) > 0 ? '***SET***' : 'EMPTY'
    ]
], JSON_PRETTY_PRINT);

echo "\n\n--- Testing Connection ---\n";

try {
    require_once __DIR__ . '/includes/db.php';
    $db = getDB();
    echo json_encode(['database' => 'CONNECTED OK!']);
} catch (Exception $e) {
    echo json_encode(['database' => 'FAILED', 'error' => $e->getMessage()]);
}
