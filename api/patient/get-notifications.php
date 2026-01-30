<?php
/**
 * MENTTA - API: Get Notifications
 * Returns user notifications (optionally just count)
 */

require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');
setSecurityHeaders();

$user = checkAuth();
if (!$user) {
    jsonResponse(false, null, 'No autenticado', 401);
}

$countOnly = isset($_GET['count_only']) && $_GET['count_only'] === '1';

try {
    $db = getDB();
    
    // Get unread count
    $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = FALSE");
    $stmt->execute([$user['id']]);
    $unreadCount = $stmt->fetchColumn();
    
    if ($countOnly) {
        jsonResponse(true, ['unread_count' => intval($unreadCount)]);
        exit;
    }
    
    // Get recent notifications
    $stmt = $db->prepare("
        SELECT id, type, title, message, action_url, is_read, created_at
        FROM notifications 
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT 20
    ");
    $stmt->execute([$user['id']]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format for frontend
    $formatted = array_map(function($n) {
        return [
            'id' => intval($n['id']),
            'type' => $n['type'],
            'title' => $n['title'],
            'message' => $n['message'],
            'action_url' => $n['action_url'],
            'is_read' => boolval($n['is_read']),
            'created_at' => $n['created_at']
        ];
    }, $notifications);
    
    jsonResponse(true, [
        'notifications' => $formatted,
        'unread_count' => intval($unreadCount)
    ]);
    
} catch (Exception $e) {
    logError('Error en get-notifications.php', ['error' => $e->getMessage()]);
    jsonResponse(false, null, 'Error al obtener notificaciones');
}
