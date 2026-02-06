<?php
/**
 * MENTTA - API: Guardar Preferencias de Crisis
 * 
 * Guarda las preferencias de escalamiento de crisis del usuario
 * con registro de consentimiento para opciones críticas.
 */

require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');
setSecurityHeaders();

// Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, null, 'Método no permitido', 405);
}

// Verificar autenticación
$user = checkAuth();
if (!$user || $user['role'] !== 'patient') {
    jsonResponse(false, null, 'No autorizado', 401);
}

// Obtener parámetros
$notify_psychologist = isset($_POST['notify_psychologist']) && 
    ($_POST['notify_psychologist'] === 'true' || $_POST['notify_psychologist'] === '1');

$notify_contacts = isset($_POST['notify_emergency_contacts']) && 
    ($_POST['notify_emergency_contacts'] === 'true' || $_POST['notify_emergency_contacts'] === '1');

$auto_call = isset($_POST['auto_call_emergency_line']) && 
    ($_POST['auto_call_emergency_line'] === 'true' || $_POST['auto_call_emergency_line'] === '1');

try {
    $db = getDB();
    
    // Verificar si ya existen preferencias
    $existing = dbFetchOne(
        "SELECT id FROM crisis_preferences WHERE user_id = ?",
        [$user['id']]
    );
    
    // Preparar datos de consentimiento (solo si auto_call está activado)
    $consent_time = $auto_call ? date('Y-m-d H:i:s') : null;
    $consent_ip = $auto_call ? getClientIP() : null;
    
    if ($existing) {
        // Actualizar preferencias existentes
        $stmt = $db->prepare("
            UPDATE crisis_preferences SET
                notify_psychologist = :notify_psych,
                notify_emergency_contacts = :notify_contacts,
                auto_call_emergency_line = :auto_call,
                consent_given_at = COALESCE(:consent_time, consent_given_at),
                consent_ip = COALESCE(:consent_ip, consent_ip),
                updated_at = NOW()
            WHERE user_id = :user_id
        ");
        
        $stmt->execute([
            'user_id' => $user['id'],
            'notify_psych' => $notify_psychologist ? 1 : 0,
            'notify_contacts' => $notify_contacts ? 1 : 0,
            'auto_call' => $auto_call ? 1 : 0,
            'consent_time' => $consent_time,
            'consent_ip' => $consent_ip
        ]);
    } else {
        // Insertar nuevas preferencias
        $stmt = $db->prepare("
            INSERT INTO crisis_preferences (
                user_id, notify_psychologist, notify_emergency_contacts, 
                auto_call_emergency_line, consent_given_at, consent_ip
            ) VALUES (
                :user_id, :notify_psych, :notify_contacts, 
                :auto_call, :consent_time, :consent_ip
            )
        ");
        
        $stmt->execute([
            'user_id' => $user['id'],
            'notify_psych' => $notify_psychologist ? 1 : 0,
            'notify_contacts' => $notify_contacts ? 1 : 0,
            'auto_call' => $auto_call ? 1 : 0,
            'consent_time' => $consent_time,
            'consent_ip' => $consent_ip
        ]);
    }
    
    // Log de cambio de preferencias (para auditoría)
    logError('CRISIS PREFERENCES UPDATED', [
        'user_id' => $user['id'],
        'notify_psychologist' => $notify_psychologist,
        'notify_contacts' => $notify_contacts,
        'auto_call' => $auto_call,
        'consent_given' => $auto_call
    ]);
    
    jsonResponse(true, [
        'message' => 'Preferencias guardadas correctamente',
        'preferences' => [
            'notify_psychologist' => $notify_psychologist,
            'notify_emergency_contacts' => $notify_contacts,
            'auto_call_emergency_line' => $auto_call
        ]
    ]);
    
} catch (Exception $e) {
    logError('Error en save-crisis-preferences', [
        'error' => $e->getMessage(),
        'user_id' => $user['id']
    ]);
    jsonResponse(false, null, 'Error al guardar preferencias');
}
