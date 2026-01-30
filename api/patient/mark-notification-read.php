<?php
/**
 * MENTTA - API: Mark Notification Read
 * Marks a single notification as read
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

$notificationId = intval($_POST['notification_id'] ?? 0);

if (!$notificationId) {
    jsonResponse(false, null, 'ID de notificación requerido');
}

try {
    $db = getDB();
    
    // Update only if belongs to user
    $stmt = $db->prepare("
        UPDATE notifications 
        SET is_read = TRUE 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$notificationId, $user['id']]);
    
    jsonResponse(true, ['message' => 'Notificación marcada como leída']);
    
} catch (Exception $e) {
    logError('Error en mark-notification-read.php', ['error' => $e->getMessage()]);
    jsonResponse(false, null, 'Error al actualizar notificación');
}
