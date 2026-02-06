<?php
/**
 * MENTTA - Database Connection Test
 * Temporal endpoint para diagnosticar conexión a MySQL en Railway
 */

header('Content-Type: text/plain');

// Cargar config
require_once __DIR__ . '/includes/config.php';

echo "=== MENTTA DB Diagnostic ===\n\n";

echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";

echo "=== Environment Variables ===\n";
echo "APP_ENV: " . APP_ENV . "\n";
echo "DB_HOST: " . DB_HOST . "\n";
echo "DB_PORT: " . (defined('DB_PORT') ? DB_PORT : 'NOT_DEFINED') . "\n";
echo "DB_NAME: " . DB_NAME . "\n";
echo "DB_USER: " . DB_USER . "\n";
echo "DB_PASS: " . (strlen(DB_PASS) > 0 ? '***SET (' . strlen(DB_PASS) . ' chars)***' : 'EMPTY') . "\n\n";

echo "=== PHP Extensions ===\n";
echo "PDO drivers: " . implode(', ', PDO::getAvailableDrivers()) . "\n";
echo "mysqlnd: " . (function_exists('mysqli_get_client_stats') ? 'YES' : 'NO') . "\n";
echo "openssl: " . (extension_loaded('openssl') ? 'YES' : 'NO') . "\n\n";

echo "=== Testing Direct PDO Connection (with SSL) ===\n";

try {
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        DB_HOST,
        defined('DB_PORT') ? DB_PORT : '3306',
        DB_NAME
    );
    
    echo "DSN: " . $dsn . "\n";
    
    // Force SSL mode - required for caching_sha2_password
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 10,
        PDO::MYSQL_ATTR_SSL_CA => true,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
    ]);
    
    echo "\n✅ CONNECTION SUCCESSFUL!\n";
    
    // Test query
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Users in database: " . $result['count'] . "\n";
    
} catch (PDOException $e) {
    echo "\n❌ CONNECTION FAILED!\n";
    echo "Error Code: " . $e->getCode() . "\n";
    echo "Error Message: " . $e->getMessage() . "\n";
}
