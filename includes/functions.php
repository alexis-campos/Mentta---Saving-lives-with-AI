<?php
/**
 * MENTTA - Funciones Generales del Sistema
 */

require_once __DIR__ . '/config.php';

/**
 * Retorna respuesta JSON consistente
 */
function jsonResponse($success, $data = null, $error = null, $statusCode = 200)
{
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
function sanitizeInput($data)
{
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Valida formato de email
 */
function validateEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Genera UUID v4
 */
function generateUUID()
{
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

/**
 * Log de errores a archivo
 */
function logError($message, $context = [])
{
    if (!defined('LOGS_PATH'))
        return;

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
 * DEV-010 FIX: Limpieza optimizada con probabilidad (solo 1 de cada 100 requests)
 */
function checkRateLimit($userId, $action, $maxRequests = null, $timeWindow = null)
{
    require_once __DIR__ . '/db.php';

    $maxRequests = $maxRequests ?? RATE_LIMIT_MESSAGES;
    $timeWindow = $timeWindow ?? RATE_LIMIT_WINDOW;

    $db = getDB();

    // DEV-010 FIX: Limpieza probabilística (1% de las requests)
    // Evita DELETE costoso en cada request
    if (mt_rand(1, 100) === 1) {
        $db->prepare("DELETE FROM rate_limits WHERE window_start < DATE_SUB(NOW(), INTERVAL ? SECOND)")
            ->execute([$timeWindow * 2]); // Doble del window para margen
    }

    // Verificar límite actual
    $stmt = $db->prepare("SELECT request_count, window_start FROM rate_limits 
                          WHERE user_id = ? AND action = ? AND window_start >= DATE_SUB(NOW(), INTERVAL ? SECOND)");
    $stmt->execute([$userId, $action, $timeWindow]);
    $record = $stmt->fetch();

    if (!$record) {
        // Primera request o window expirado
        $db->prepare("REPLACE INTO rate_limits (user_id, action, request_count, window_start) VALUES (?, ?, 1, NOW())")
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
function getLanguage($userLanguage = null)
{
    $lang = $userLanguage ?? $_SESSION['language'] ?? DEFAULT_LANGUAGE;
    return in_array($lang, SUPPORTED_LANGUAGES) ? $lang : DEFAULT_LANGUAGE;
}

/**
 * DEV-011 FIX: Traducción expandida con más strings
 */
function __($key, $lang = null)
{
    static $translations = null;

    if ($translations === null) {
        $translations = [
            'es' => [
                // General
                'welcome' => 'Bienvenido',
                'login' => 'Iniciar Sesión',
                'logout' => 'Cerrar Sesión',
                'email' => 'Correo electrónico',
                'password' => 'Contraseña',
                'save' => 'Guardar',
                'cancel' => 'Cancelar',
                'confirm' => 'Confirmar',
                'loading' => 'Cargando...',
                'success' => 'Éxito',

                // Errores
                'error_login' => 'Credenciales incorrectas',
                'error_required' => 'Este campo es requerido',
                'error_connection' => 'Error de conexión',
                'error_server' => 'Error del servidor',
                'error_rate_limit' => 'Demasiadas solicitudes. Espera un momento.',
                'error_input_too_long' => 'El texto es demasiado largo',

                // Chat
                'chat_new' => 'Nueva conversación',
                'chat_history' => 'Historial',
                'chat_send' => 'Enviar',
                'chat_placeholder' => '¿Cómo te sientes hoy?',
                'chat_analyzing' => 'Analizando...',
                'chat_writing' => 'Escribiendo...',

                // Dashboard
                'dashboard_patients' => 'Pacientes',
                'dashboard_alerts' => 'Alertas',
                'dashboard_no_patients' => 'No hay pacientes asignados',
                'dashboard_no_alerts' => 'No hay alertas pendientes',

                // Tiempo
                'time_now' => 'Ahora',
                'time_minutes_ago' => 'Hace %d minutos',
                'time_hours_ago' => 'Hace %d horas',
                'time_days_ago' => 'Hace %d días',
                'time_today' => 'Hoy',
                'time_yesterday' => 'Ayer',

                // Crisis
                'crisis_help' => 'Necesitas ayuda',
                'crisis_line' => 'Línea de crisis: 113',
                'crisis_contact' => 'Contactar psicólogo'
            ],
            'en' => [
                // General
                'welcome' => 'Welcome',
                'login' => 'Login',
                'logout' => 'Logout',
                'email' => 'Email',
                'password' => 'Password',
                'save' => 'Save',
                'cancel' => 'Cancel',
                'confirm' => 'Confirm',
                'loading' => 'Loading...',
                'success' => 'Success',

                // Errors
                'error_login' => 'Invalid credentials',
                'error_required' => 'This field is required',
                'error_connection' => 'Connection error',
                'error_server' => 'Server error',
                'error_rate_limit' => 'Too many requests. Please wait.',
                'error_input_too_long' => 'Text is too long',

                // Chat
                'chat_new' => 'New conversation',
                'chat_history' => 'History',
                'chat_send' => 'Send',
                'chat_placeholder' => 'How are you feeling today?',
                'chat_analyzing' => 'Analyzing...',
                'chat_writing' => 'Writing...',

                // Dashboard
                'dashboard_patients' => 'Patients',
                'dashboard_alerts' => 'Alerts',
                'dashboard_no_patients' => 'No assigned patients',
                'dashboard_no_alerts' => 'No pending alerts',

                // Time
                'time_now' => 'Now',
                'time_minutes_ago' => '%d minutes ago',
                'time_hours_ago' => '%d hours ago',
                'time_days_ago' => '%d days ago',
                'time_today' => 'Today',
                'time_yesterday' => 'Yesterday',

                // Crisis
                'crisis_help' => 'You need help',
                'crisis_line' => 'Crisis line: 113',
                'crisis_contact' => 'Contact psychologist'
            ]
        ];
    }

    $lang = $lang ?? getLanguage();
    return $translations[$lang][$key] ?? $key;
}

/**
 * Formatea fecha para mostrar
 */
function formatDate($date, $format = 'd/m/Y H:i')
{
    if (empty($date))
        return '';
    $dt = new DateTime($date);
    return $dt->format($format);
}

/**
 * Calcula tiempo transcurrido (ej: "hace 5 minutos") - DEV-012 FIXED
 */
function timeAgo($datetime, $lang = 'es')
{
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $units = [
        'es' => [
            'prefix' => 'hace ',
            'suffix' => '',
            'year' => ['año', 'años'],
            'month' => ['mes', 'meses'],
            'day' => ['día', 'días'],
            'hour' => ['hora', 'horas'],
            'minute' => ['minuto', 'minutos'],
            'now' => 'ahora mismo'
        ],
        'en' => [
            'prefix' => '',
            'suffix' => ' ago',
            'year' => ['year', 'years'],
            'month' => ['month', 'months'],
            'day' => ['day', 'days'],
            'hour' => ['hour', 'hours'],
            'minute' => ['minute', 'minutes'],
            'now' => 'just now'
        ]
    ];

    $u = $units[$lang] ?? $units['es'];
    $prefix = $u['prefix'];
    $suffix = $u['suffix'];

    if ($diff->y > 0)
        return $prefix . $diff->y . ' ' . $u['year'][$diff->y > 1 ? 1 : 0] . $suffix;
    if ($diff->m > 0)
        return $prefix . $diff->m . ' ' . $u['month'][$diff->m > 1 ? 1 : 0] . $suffix;
    if ($diff->d > 0)
        return $prefix . $diff->d . ' ' . $u['day'][$diff->d > 1 ? 1 : 0] . $suffix;
    if ($diff->h > 0)
        return $prefix . $diff->h . ' ' . $u['hour'][$diff->h > 1 ? 1 : 0] . $suffix;
    if ($diff->i > 0)
        return $prefix . $diff->i . ' ' . $u['minute'][$diff->i > 1 ? 1 : 0] . $suffix;

    return $u['now'];
}

/**
 * Genera token aleatorio seguro
 */
function generateSecureToken($length = 64)
{
    return bin2hex(random_bytes($length / 2));
}

/**
 * Valida que la request sea AJAX
 */
function isAjaxRequest()
{
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Obtiene IP del cliente - DEV-019 FIXED: Solo confiar en REMOTE_ADDR
 * Los otros headers son spoofables a menos que haya un proxy confiable
 */
function getClientIP($trustProxy = false)
{
    // Solo confiar en X-Forwarded-For si estamos detrás de un proxy conocido
    if ($trustProxy && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($ips[0]);
        // Validar que sea una IP pública válida (no privada, no reservada)
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return $ip;
        }
    }

    // REMOTE_ADDR es la única fuente confiable sin proxy
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    if (filter_var($ip, FILTER_VALIDATE_IP)) {
        return $ip;
    }

    return '0.0.0.0';
}
