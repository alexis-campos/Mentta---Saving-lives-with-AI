<?php
/**
 * MENTTA - API: Check Alerts (Long Polling)
 * Endpoint de long polling para alertas en tiempo real
 * FIXED: Respuesta compatible con dashboard.js
 */

// Suppress HTML error output for API
ini_set('display_errors', 0);
error_reporting(0);

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

// Timeout configurable (default 5s para compatibilidad con setInterval de 10s)
$timeout = isset($_GET['timeout']) ? min(intval($_GET['timeout']), 25) : 5;
$start_time = time();
$last_check = isset($_GET['last_check']) ? intval($_GET['last_check']) : null;

// Obtener conteo total de alertas pendientes
$db = getDB();
$countStmt = $db->prepare("
    SELECT COUNT(*) as count FROM alerts 
    WHERE psychologist_id = :psychologist_id AND status = 'pending'
");
$countStmt->execute(['psychologist_id' => $user['id']]);
$pendingCount = (int) $countStmt->fetch(PDO::FETCH_ASSOC)['count'];

// Buscar nuevas alertas (desde last_check o últimos 30 segundos si no hay last_check)
$newAlerts = [];
$since = $last_check ? date('Y-m-d H:i:s', $last_check) : date('Y-m-d H:i:s', time() - 30);

$alertsStmt = $db->prepare("
    SELECT 
        a.id, a.patient_id, a.alert_type, a.severity,
        a.message_snapshot, a.created_at, a.status,
        u.name as patient_name, u.age as patient_age
    FROM alerts a
    JOIN users u ON a.patient_id = u.id
    WHERE a.psychologist_id = :psychologist_id
    AND a.status = 'pending'
    AND a.created_at > :since
    ORDER BY a.severity DESC, a.created_at DESC
    LIMIT 10
");
$alertsStmt->execute([
    'psychologist_id' => $user['id'],
    'since' => $since
]);
$newAlerts = $alertsStmt->fetchAll(PDO::FETCH_ASSOC);

// Retornar en formato compatible con dashboard.js
jsonResponse(true, [
    'pending_count' => $pendingCount,
    'new_alerts' => $newAlerts,
    'alerts' => $newAlerts, // compatibilidad con alerts.js
    'count' => count($newAlerts),
    'timestamp' => time()
]);
