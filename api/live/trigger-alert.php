<?php
/**
 * MENTTA - API: Disparar Alerta de Crisis (Live)
 * Crea una alerta cuando la IA detecta riesgo alto durante la llamada
 * 
 * Método: POST
 * Body: { sessionToken, riskLevel, emotion, reason }
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
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
        exit;
    }

    $sessionToken = $input['sessionToken'] ?? null;
    $riskLevel = intval($input['riskLevel'] ?? 0);
    $emotion = $input['emotion'] ?? 'unknown';
    $reason = $input['reason'] ?? '';

    if (!$sessionToken) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Token de sesión requerido']);
        exit;
    }

    // Solo crear alerta si el riesgo es alto (2) o crítico (3)
    if ($riskLevel < 2) {
        echo json_encode([
            'success' => true,
            'alertCreated' => false,
            'message' => 'Nivel de riesgo no requiere alerta'
        ]);
        exit;
    }

    $db = getDB();

    // Obtener información de la sesión
    $stmt = $db->prepare("
        SELECT ls.id, ls.patient_id, u.name as patient_name
        FROM live_sessions ls
        JOIN users u ON ls.patient_id = u.id
        WHERE ls.session_token = ?
    ");
    $stmt->execute([$sessionToken]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$session) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Sesión no encontrada']);
        exit;
    }

    // Determinar tipo de alerta y severidad
    $alertType = 'crisis';
    $severity = 'orange';

    if ($riskLevel === 3) {
        $alertType = 'suicide';
        $severity = 'red';
    } elseif (stripos($emotion, 'self') !== false || stripos($reason, 'autolesion') !== false) {
        $alertType = 'self_harm';
        $severity = 'red';
    }

    // Buscar psicólogo asignado
    $stmt = $db->prepare("
        SELECT psychologist_id 
        FROM patient_psychologist_link 
        WHERE patient_id = ? AND status = 'active'
        LIMIT 1
    ");
    $stmt->execute([$session['patient_id']]);
    $link = $stmt->fetch(PDO::FETCH_ASSOC);
    $psychologistId = $link ? $link['psychologist_id'] : null;

    // Crear mensaje para la alerta
    $messageSnapshot = sprintf(
        "[MENTTA LIVE] Riesgo detectado durante videollamada.\n" .
        "Paciente: %s\n" .
        "Nivel de riesgo: %d/3\n" .
        "Emoción detectada: %s\n" .
        "Motivo: %s\n" .
        "Sesión ID: %d",
        $session['patient_name'],
        $riskLevel,
        $emotion,
        $reason,
        $session['id']
    );

    // Crear alerta
    $stmt = $db->prepare("
        INSERT INTO alerts (patient_id, psychologist_id, alert_type, severity, message_snapshot, ai_analysis, status)
        VALUES (?, ?, ?, ?, ?, ?, 'pending')
    ");

    $stmt->execute([
        $session['patient_id'],
        $psychologistId,
        $alertType,
        $severity,
        $messageSnapshot,
        json_encode([
            'source' => 'gemini_live',
            'session_id' => $session['id'],
            'emotion' => $emotion,
            'risk_level' => $riskLevel,
            'reason' => $reason,
            'detected_at' => date('Y-m-d H:i:s')
        ])
    ]);

    $alertId = $db->lastInsertId();

    // Incrementar contador de alertas en la sesión
    $stmt = $db->prepare("
        UPDATE live_sessions 
        SET alerts_triggered = alerts_triggered + 1,
            max_risk_level = GREATEST(max_risk_level, ?)
        WHERE id = ?
    ");
    $stmt->execute([$riskLevel, $session['id']]);

    // Log crítico
    error_log("MENTTA CRISIS ALERT - Alert ID: {$alertId}, Patient: {$session['patient_id']}, Risk: {$riskLevel}, Type: {$alertType}");

    echo json_encode([
        'success' => true,
        'alertCreated' => true,
        'alertId' => $alertId,
        'severity' => $severity,
        'message' => 'Alerta de crisis creada. Un profesional será notificado.'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    error_log("MENTTA LIVE ERROR: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Error al crear alerta: ' . $e->getMessage()
    ]);
}
