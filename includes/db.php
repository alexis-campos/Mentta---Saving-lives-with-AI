<?php
/**
 * MENTTA - Conexión a Base de Datos
 * Singleton pattern para conexión PDO
 */

require_once __DIR__ . '/config.php';

class Database
{
    private static $instance = null;
    private $connection = null;

    private function __construct()
    {
        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                DB_HOST,
                DB_PORT,
                DB_NAME,
                DB_CHARSET
            );

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];

            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);

        } catch (PDOException $e) {
            $this->logError('Database connection failed: ' . $e->getMessage());
            throw new Exception('Error de conexión a la base de datos');
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    private function logError($message)
    {
        $logFile = LOGS_PATH . '/error.log';
        $logDir = dirname($logFile);

        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] [DB_ERROR] {$message}" . PHP_EOL;
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }

    // Prevent cloning
    private function __clone()
    {
    }

    // Prevent unserialization
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }
}

/**
 * Helper function para obtener conexión PDO
 */
function getDB()
{
    return Database::getInstance()->getConnection();
}

/**
 * Helper para ejecutar queries con prepared statements
 */
function dbQuery($sql, $params = [])
{
    try {
        $db = getDB();
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        logError('Query error: ' . $e->getMessage(), ['sql' => $sql]);
        return false;
    }
}

/**
 * Obtener un solo registro
 */
function dbFetchOne($sql, $params = [])
{
    $stmt = dbQuery($sql, $params);
    return $stmt ? $stmt->fetch() : null;
}

/**
 * Obtener todos los registros
 */
function dbFetchAll($sql, $params = [])
{
    $stmt = dbQuery($sql, $params);
    return $stmt ? $stmt->fetchAll() : [];
}

/**
 * Tablas permitidas (whitelist de seguridad)
 */
function getAllowedTables()
{
    return [
        'users',
        'user_preferences',
        'sessions',
        'conversations',
        'patient_memory',
        'patient_psychologist_link',
        'alerts',
        'notifications',
        'emergency_contacts',
        'crisis_preferences',
        'crisis_resources',
        'psychoeducation_content',
        'mental_health_centers',
        'rate_limits',
        'error_logs',
        'system_config',
        'live_sessions'
    ];
}

/**
 * Valida nombre de tabla
 */
function validateTableName($table)
{
    if (!in_array($table, getAllowedTables(), true)) {
        throw new InvalidArgumentException("Tabla no permitida: {$table}");
    }
    return true;
}

/**
 * Insertar y retornar el último ID
 */
function dbInsert($table, $data)
{
    validateTableName($table);

    // Validar que las columnas solo contengan caracteres alfanuméricos y guiones bajos
    foreach (array_keys($data) as $column) {
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $column)) {
            throw new InvalidArgumentException("Nombre de columna inválido: {$column}");
        }
    }

    $columns = implode(', ', array_keys($data));
    $placeholders = ':' . implode(', :', array_keys($data));

    $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";

    try {
        $db = getDB();
        $stmt = $db->prepare($sql);
        $stmt->execute($data);
        return $db->lastInsertId();
    } catch (PDOException $e) {
        logError('Insert error: ' . $e->getMessage(), ['table' => $table]);
        return false;
    }
}

/**
 * Actualizar registros
 */
function dbUpdate($table, $data, $where, $whereParams = [])
{
    validateTableName($table);

    // Validar nombres de columnas
    foreach (array_keys($data) as $column) {
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $column)) {
            throw new InvalidArgumentException("Nombre de columna inválido: {$column}");
        }
    }

    $setClause = [];
    foreach (array_keys($data) as $column) {
        $setClause[] = "{$column} = :{$column}";
    }

    $sql = "UPDATE {$table} SET " . implode(', ', $setClause) . " WHERE {$where}";

    try {
        $db = getDB();
        $stmt = $db->prepare($sql);
        $stmt->execute(array_merge($data, $whereParams));
        return $stmt->rowCount();
    } catch (PDOException $e) {
        logError('Update error: ' . $e->getMessage(), ['table' => $table]);
        return false;
    }
}
