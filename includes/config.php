<?php
/**
 * MENTTA - Main Configuration
 * Loads environment variables from .env file
 */

// Load environment variables from .env
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || $line[0] === '#') continue;
        if (strpos($line, '=') === false) continue;
        
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        
        if (!isset($_ENV[$key])) {
            $_ENV[$key] = $value;
            putenv("{$key}={$value}");
        }
    }
}

/**
 * Get environment variable with fallback
 */
function env($key, $default = null) {
    $value = getenv($key);
    if ($value === false) {
        $value = $_ENV[$key] ?? $default;
    }
    return $value;
}

// Database Configuration
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_NAME', env('DB_NAME', 'mentta'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));
define('DB_CHARSET', 'utf8mb4');

// AI Configuration (Google Gemini)
define('AI_API_KEY', env('AI_API_KEY', 'YOUR_API_KEY_HERE'));
define('AI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models/');
define('AI_MODEL', 'gemini-3-flash-preview');
define('AI_TIMEOUT', 30);

// Security Settings
define('SESSION_LIFETIME', 86400);
define('RATE_LIMIT_MESSAGES', 30);
define('RATE_LIMIT_WINDOW', 60);
define('PASSWORD_MIN_LENGTH', 8);

// Application Settings
define('APP_NAME', 'Mentta');
define('APP_VERSION', '0.5.2');
define('APP_URL', env('APP_URL', 'http://localhost/Mentta - Salvando vidas con la IA'));
define('APP_ENV', env('APP_ENV', 'development'));
define('APP_TIMEZONE', 'America/Lima');
define('DEFAULT_LANGUAGE', 'es');
define('SUPPORTED_LANGUAGES', ['es', 'en']);

// Google Maps API (for mental health centers map)
define('GOOGLE_MAPS_API_KEY', env('GOOGLE_MAPS_API_KEY', ''));

// Emergency Settings (Peru)
define('EMERGENCY_LINE', '113');
define('EMERGENCY_LINE_NAME', 'Línea 113 - MINSA');

// Path Settings
define('ROOT_PATH', dirname(__DIR__));
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('LOGS_PATH', ROOT_PATH . '/logs');

// Chat & Alerts Settings
define('CHAT_HISTORY_LIMIT', 10);
define('CHAT_MAX_MESSAGE_LENGTH', 5000);
define('ALERT_POLLING_INTERVAL', 5000);

// Environment Configuration
date_default_timezone_set(APP_TIMEZONE);

// Set UTF-8 encoding if mbstring is available
if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('UTF-8');
}

if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

/**
 * Set security headers
 */
function setSecurityHeaders() {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
}
