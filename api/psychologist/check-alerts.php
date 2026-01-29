<?php
/**
 * MENTTA - API: Check Alerts (Long Polling)
 * Endpoint de long polling para alertas en tiempo real
 */

require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';
require_once '../../includes/alert-system.php';

header('Content-Type: application/json');
setSecurityHeaders();

// Verificar autenticación
$user = checkAuth();
if (!$user || $user['role'] !== 'psychologist') {
    jsonResponse(false, null, 'No autorizado');
}

// Long polling: esperar hasta 25 segundos por nuevas alertas
$timeout = 25;
$start_time = time();
$last_check = isset($_GET['last_check']) ? intval($_GET['last_check']) : null;

while (time() - $start_time < $timeout) {
    // Buscar alertas pendientes
    $alerts = getPendingAlerts($user['id'], $last_check);
    
    if (!empty($alerts)) {
        // Hay nuevas alertas, retornar inmediatamente
        jsonResponse(true, [
            'alerts' => $alerts,
            'count' => count($alerts),
            'timestamp' => time()
        ]);
    }
    
    // No hay alertas, esperar 2 segundos antes de revisar de nuevo
    sleep(2);
}

// Timeout alcanzado, retornar vacío
jsonResponse(true, [
    'alerts' => [],
    'count' => 0,
    'timestamp' => time()
]);
