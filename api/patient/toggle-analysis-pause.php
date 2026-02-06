<?php
/**
 * MENTTA - API: Toggle Analysis Pause
 * Pauses or resumes emotional analysis for 24 hours
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

$pause = isset($_POST['pause']) && $_POST['pause'] === 'true';

try {
    $db = getDB();
    
    if ($pause) {
        // Activate pause for 24 hours
        $pausedUntil = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        $stmt = $db->prepare("
            INSERT INTO user_preferences (user_id, analysis_paused, analysis_paused_until)
            VALUES (:user_id, TRUE, :paused_until)
            ON DUPLICATE KEY UPDATE 
                analysis_paused = TRUE,
                analysis_paused_until = :paused_until
        ");
        $stmt->execute([
            'user_id' => $user['id'],
            'paused_until' => $pausedUntil
        ]);
        
        jsonResponse(true, [
            'paused' => true,
            'paused_until' => $pausedUntil,
            'message' => 'Análisis pausado por 24 horas'
        ]);
    } else {
        // Manually deactivate pause
        $stmt = $db->prepare("
            UPDATE user_preferences 
            SET analysis_paused = FALSE, analysis_paused_until = NULL
            WHERE user_id = :user_id
        ");
        $stmt->execute(['user_id' => $user['id']]);
        
        jsonResponse(true, [
            'paused' => false,
            'message' => 'Análisis reactivado'
        ]);
    }
} catch (Exception $e) {
    logError('Error en toggle-analysis-pause.php', ['error' => $e->getMessage()]);
    jsonResponse(false, null, 'Error al cambiar configuración');
}
