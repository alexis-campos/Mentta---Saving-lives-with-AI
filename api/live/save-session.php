<?php
/**
 * MENTTA - API: Guardar Sesión Live
 * Guarda el resumen y datos de la sesión cuando termina
 * 
 * Método: POST
 * Body: { sessionToken, summary, maxRiskLevel, emotions[], duration }
 */

require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Preflight CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

try {
    // Obtener datos del body
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
        exit;
    }

    $sessionToken = $input['sessionToken'] ?? null;
    $summary = $input['summary'] ?? null;
    $maxRiskLevel = intval($input['maxRiskLevel'] ?? 0);
    $emotions = $input['emotions'] ?? [];
    $duration = intval($input['duration'] ?? 0);
    $riskEvents = $input['riskEvents'] ?? [];
    $alertsTriggered = intval($input['alertsTriggered'] ?? 0);

    if (!$sessionToken) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Token de sesión requerido']);
        exit;
    }

    $db = getDB();

    // Verificar que la sesión existe y está activa
    $stmt = $db->prepare("
        SELECT id, patient_id, started_at 
        FROM live_sessions 
        WHERE session_token = ? AND status = 'active'
    ");
    $stmt->execute([$sessionToken]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$session) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Sesión no encontrada o ya cerrada']);
        exit;
    }

    // Calcular duración si no se proporciona
    if ($duration === 0) {
        $startTime = strtotime($session['started_at']);
        $duration = time() - $startTime;
    }

    // Actualizar sesión
    $stmt = $db->prepare("
        UPDATE live_sessions SET
            ended_at = NOW(),
            duration_seconds = ?,
            max_risk_level = ?,
            emotions_detected = ?,
            risk_events = ?,
            summary = ?,
            alerts_triggered = ?,
            status = 'completed'
        WHERE id = ?
    ");

    $stmt->execute([
        $duration,
        $maxRiskLevel,
        json_encode($emotions),
        json_encode($riskEvents),
        $summary,
        $alertsTriggered,
        $session['id']
    ]);

    // Registrar en log si hubo riesgo alto
    if ($maxRiskLevel >= 2) {
        error_log("MENTTA LIVE - Sesión #{$session['id']} cerrada con riesgo nivel {$maxRiskLevel}");
    }

    echo json_encode([
        'success' => true,
        'sessionId' => $session['id'],
        'duration' => $duration,
        'message' => 'Sesión guardada correctamente'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error al guardar sesión: ' . $e->getMessage()
    ]);
}
