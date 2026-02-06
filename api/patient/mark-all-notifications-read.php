<?php
/**
 * MENTTA - API: Mark All Notifications Read
 * Marks all user notifications as read
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
if (!$user) {
    jsonResponse(false, null, 'No autenticado', 401);
}

try {
    $db = getDB();
    
    $stmt = $db->prepare("UPDATE notifications SET is_read = TRUE WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    
    $count = $stmt->rowCount();
    
    jsonResponse(true, ['message' => "Se marcaron $count notificaciones como leídas"]);
    
} catch (Exception $e) {
    logError('Error en mark-all-notifications-read.php', ['error' => $e->getMessage()]);
    jsonResponse(false, null, 'Error al actualizar notificaciones');
}
