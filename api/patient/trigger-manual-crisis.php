<?php
/**
 * MENTTA - API: Trigger Manual Crisis Alert
 * Creates a manual crisis alert when patient requests help
 */

require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');
setSecurityHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, null, 'Método no permitido', 405);
}

$user = checkAuth();
if (!$user || $user['role'] !== 'patient') {
    jsonResponse(false, null, 'No autorizado', 401);
}

$type = $_POST['type'] ?? '';

if (!in_array($type, ['psychologist', 'emergency_contact', 'crisis_line', 'calming_exercises'])) {
    jsonResponse(false, null, 'Tipo de solicitud inválido');
}

try {
    $db = getDB();
    
    // For calming exercises, just return success (no alert needed)
    if ($type === 'calming_exercises') {
        jsonResponse(true, ['message' => 'Redirigiendo a ejercicios de calma']);
        exit;
    }
    
    // Get linked psychologist (if any)
    $stmt = $db->prepare("
        SELECT psychologist_id FROM patient_psychologist_link 
        WHERE patient_id = ? AND status = 'active'
        LIMIT 1
    ");
    $stmt->execute([$user['id']]);
    $link = $stmt->fetch(PDO::FETCH_ASSOC);
    $psychologistId = $link ? $link['psychologist_id'] : null;
    
    // Create alert
    $alertMessage = "Paciente solicitó ayuda manualmente: ";
    switch ($type) {
        case 'psychologist':
            $alertMessage .= "Contactar a psicólogo";
            break;
        case 'emergency_contact':
            $alertMessage .= "Notificar contacto de emergencia";
            break;
        case 'crisis_line':
            $alertMessage .= "Llamar línea de crisis";
            break;
    }
    
    $stmt = $db->prepare("
        INSERT INTO alerts (patient_id, psychologist_id, alert_type, severity, message_snapshot, ai_analysis, status)
        VALUES (?, ?, 'manual_request', 'orange', ?, 'Solicitud manual de ayuda por parte del paciente', 'pending')
    ");
    $stmt->execute([
        $user['id'],
        $psychologistId,
        $alertMessage
    ]);
    
    $alertId = $db->lastInsertId();
    
    // Create notification for psychologist if linked
    if ($psychologistId) {
        $stmt = $db->prepare("
            INSERT INTO notifications (user_id, type, title, message, action_url)
            VALUES (?, 'psychologist_message', 'Paciente solicita ayuda', ?, ?)
        ");
        $stmt->execute([
            $psychologistId,
            "{$user['name']} ha solicitado contacto inmediato.",
            "dashboard.php?alert=$alertId"
        ]);
    }
    
    // Log the request
    logError('🆘 Solicitud manual de crisis', [
        'patient_id' => $user['id'],
        'type' => $type,
        'alert_id' => $alertId
    ]);
    
    jsonResponse(true, [
        'message' => 'Solicitud de ayuda registrada',
        'alert_id' => $alertId
    ]);
    
} catch (Exception $e) {
    logError('Error en trigger-manual-crisis.php', ['error' => $e->getMessage()]);
    jsonResponse(false, null, 'Error al procesar solicitud');
}
