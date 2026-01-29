<?php
/**
 * MENTTA - API: Acknowledge Alert
 * Marca una alerta como reconocida por el psicólogo
 */

require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';
require_once '../../includes/alert-system.php';

header('Content-Type: application/json');
setSecurityHeaders();

// Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, null, 'Método no permitido');
}

// Verificar autenticación
$user = checkAuth();
if (!$user || $user['role'] !== 'psychologist') {
    jsonResponse(false, null, 'No autorizado');
}

// Obtener alert_id
$alert_id = isset($_POST['alert_id']) ? intval($_POST['alert_id']) : 0;

if (!$alert_id) {
    jsonResponse(false, null, 'ID de alerta requerido');
}

try {
    $result = acknowledgeAlert($alert_id, $user['id']);
    
    if ($result) {
        jsonResponse(true, ['message' => 'Alerta reconocida']);
    } else {
        jsonResponse(false, null, 'No se pudo reconocer la alerta');
    }
} catch (Exception $e) {
    logError('Error acknowledging alert', ['error' => $e->getMessage()]);
    jsonResponse(false, null, 'Error procesando solicitud');
}
