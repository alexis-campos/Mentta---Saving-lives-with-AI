<?php
/**
 * MENTTA - API: Delete Chat History
 * Deletes all patient's conversations
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

// Require confirmation
$confirm = $_POST['confirm'] ?? '';

if ($confirm !== 'DELETE') {
    jsonResponse(false, null, 'Confirmación requerida. Envía confirm=DELETE');
}

try {
    $db = getDB();
    
    // Delete conversations
    $stmt = $db->prepare("DELETE FROM conversations WHERE patient_id = ?");
    $stmt->execute([$user['id']]);
    
    $deletedCount = $stmt->rowCount();
    
    // Also delete patient memories
    $stmt = $db->prepare("DELETE FROM patient_memory WHERE patient_id = ?");
    $stmt->execute([$user['id']]);
    
    logError('🗑️ Historial eliminado por paciente', [
        'patient_id' => $user['id'],
        'messages_deleted' => $deletedCount
    ]);
    
    jsonResponse(true, [
        'message' => 'Historial eliminado',
        'messages_deleted' => $deletedCount
    ]);
    
} catch (Exception $e) {
    logError('Error en delete-chat-history.php', ['error' => $e->getMessage()]);
    jsonResponse(false, null, 'Error al eliminar historial');
}
