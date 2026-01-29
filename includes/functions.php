<?php
/**
 * MENTTA - Funciones Generales del Sistema
 */

require_once __DIR__ . '/config.php';

/**
 * Retorna respuesta JSON consistente
 */
function jsonResponse($success, $data = null, $error = null, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    
    $response = ['success' => $success];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    if ($error !== null) {
        $response['error'] = $error;
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Sanitiza string para evitar XSS
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Valida formato de email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Genera UUID v4
 */
function generateUUID() {
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

/**
 * Log de errores a archivo
 */
function logError($message, $context = []) {
    if (!defined('LOGS_PATH')) return;
    
    $logFile = LOGS_PATH . '/error.log';
    $logDir = dirname($logFile);
    
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? ' | Context: ' . json_encode($context) : '';
    $logMessage = "[{$timestamp}] {$message}{$contextStr}" . PHP_EOL;
    
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}

/**
 * Rate limiting simple basado en base de datos
 */
function checkRateLimit($userId, $action, $maxRequests = null, $timeWindow = null) {
    require_once __DIR__ . '/db.php';
    
    $maxRequests = $maxRequests ?? RATE_LIMIT_MESSAGES;
    $timeWindow = $timeWindow ?? RATE_LIMIT_WINDOW;
    
    $db = getDB();
    
    // Limpiar registros viejos
    $db->prepare("DELETE FROM rate_limits WHERE window_start < DATE_SUB(NOW(), INTERVAL ? SECOND)")
       ->execute([$timeWindow]);
    
    // Verificar límite actual
    $stmt = $db->prepare("SELECT request_count, window_start FROM rate_limits 
                          WHERE user_id = ? AND action = ?");
    $stmt->execute([$userId, $action]);
    $record = $stmt->fetch();
    
    if (!$record) {
        // Primera request
        $db->prepare("INSERT INTO rate_limits (user_id, action, request_count) VALUES (?, ?, 1)")
           ->execute([$userId, $action]);
        return true;
    }
    
    if ($record['request_count'] >= $maxRequests) {
        return false; // Límite excedido
    }
    
    // Incrementar contador
    $db->prepare("UPDATE rate_limits SET request_count = request_count + 1 
                  WHERE user_id = ? AND action = ?")
       ->execute([$userId, $action]);
    
    return true;
}

/**
 * Obtener idioma del usuario o default
 */
function getLanguage($userLanguage = null) {
    $lang = $userLanguage ?? $_SESSION['language'] ?? DEFAULT_LANGUAGE;
    return in_array($lang, SUPPORTED_LANGUAGES) ? $lang : DEFAULT_LANGUAGE;
}

/**
 * Traducción simple (placeholder para sistema de i18n)
 */
function __($key, $lang = null) {
    static $translations = null;
    
    if ($translations === null) {
        $translations = [
            'es' => [
                'welcome' => 'Bienvenido',
                'login' => 'Iniciar Sesión',
                'logout' => 'Cerrar Sesión',
                'email' => 'Correo electrónico',
                'password' => 'Contraseña',
                'error_login' => 'Credenciales incorrectas',
                'error_required' => 'Este campo es requerido',
            ],
            'en' => [
                'welcome' => 'Welcome',
                'login' => 'Login',
                'logout' => 'Logout',
                'email' => 'Email',
                'password' => 'Password',
                'error_login' => 'Invalid credentials',
                'error_required' => 'This field is required',
            ]
        ];
    }
    
    $lang = $lang ?? getLanguage();
    return $translations[$lang][$key] ?? $key;
}

/**
 * Formatea fecha para mostrar
 */
function formatDate($date, $format = 'd/m/Y H:i') {
    if (empty($date)) return '';
    $dt = new DateTime($date);
    return $dt->format($format);
}

/**
 * Calcula tiempo transcurrido (ej: "hace 5 minutos")
 */
function timeAgo($datetime, $lang = 'es') {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    
    $units = [
        'es' => ['año' => 'años', 'mes' => 'meses', 'día' => 'días', 
                 'hora' => 'horas', 'minuto' => 'minutos', 'segundo' => 'segundos'],
        'en' => ['year' => 'years', 'month' => 'months', 'day' => 'days',
                 'hour' => 'hours', 'minute' => 'minutes', 'second' => 'seconds']
    ];
    
    $prefix = $lang === 'es' ? 'hace ' : '';
    $suffix = $lang === 'en' ? ' ago' : '';
    
    if ($diff->y > 0) return $prefix . $diff->y . ' ' . ($diff->y > 1 ? 'años' : 'año') . $suffix;
    if ($diff->m > 0) return $prefix . $diff->m . ' ' . ($diff->m > 1 ? 'meses' : 'mes') . $suffix;
    if ($diff->d > 0) return $prefix . $diff->d . ' ' . ($diff->d > 1 ? 'días' : 'día') . $suffix;
    if ($diff->h > 0) return $prefix . $diff->h . ' ' . ($diff->h > 1 ? 'horas' : 'hora') . $suffix;
    if ($diff->i > 0) return $prefix . $diff->i . ' ' . ($diff->i > 1 ? 'minutos' : 'minuto') . $suffix;
    
    return $lang === 'es' ? 'ahora mismo' : 'just now';
}

/**
 * Genera token aleatorio seguro
 */
function generateSecureToken($length = 64) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Valida que la request sea AJAX
 */
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Obtiene IP del cliente
 */
function getClientIP() {
    $headers = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    
    foreach ($headers as $header) {
        if (!empty($_SERVER[$header])) {
            $ip = $_SERVER[$header];
            if (strpos($ip, ',') !== false) {
                $ip = trim(explode(',', $ip)[0]);
            }
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    
    return '0.0.0.0';
}
