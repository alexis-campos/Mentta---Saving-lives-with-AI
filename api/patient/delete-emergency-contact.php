<?php
/**
 * MENTTA - API: Delete Emergency Contact
 * Removes an emergency contact
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

$contactId = intval($_POST['contact_id'] ?? 0);

if (!$contactId) {
    jsonResponse(false, null, 'ID de contacto requerido');
}

try {
    $db = getDB();
    
    // Delete only if belongs to user
    $stmt = $db->prepare("DELETE FROM emergency_contacts WHERE id = ? AND patient_id = ?");
    $stmt->execute([$contactId, $user['id']]);
    
    if ($stmt->rowCount() === 0) {
        jsonResponse(false, null, 'Contacto no encontrado');
    }
    
    jsonResponse(true, ['message' => 'Contacto eliminado']);
    
} catch (Exception $e) {
    logError('Error en delete-emergency-contact.php', ['error' => $e->getMessage()]);
    jsonResponse(false, null, 'Error al eliminar contacto');
}
