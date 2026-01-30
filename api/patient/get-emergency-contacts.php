<?php
/**
 * MENTTA - API: Get Emergency Contacts
 * Returns patient's emergency contacts list
 */

require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');
setSecurityHeaders();

$user = checkAuth();
if (!$user || $user['role'] !== 'patient') {
    jsonResponse(false, null, 'No autorizado', 401);
}

try {
    $db = getDB();
    
    $stmt = $db->prepare("
        SELECT id, contact_name, contact_phone, contact_relationship, priority, is_verified
        FROM emergency_contacts 
        WHERE patient_id = ?
        ORDER BY priority ASC
    ");
    $stmt->execute([$user['id']]);
    $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format for frontend
    $formatted = array_map(function($c) {
        return [
            'id' => intval($c['id']),
            'name' => $c['contact_name'],
            'phone' => $c['contact_phone'],
            'relationship' => $c['contact_relationship'],
            'priority' => intval($c['priority']),
            'is_verified' => boolval($c['is_verified'])
        ];
    }, $contacts);
    
    jsonResponse(true, ['contacts' => $formatted]);
    
} catch (Exception $e) {
    logError('Error en get-emergency-contacts.php', ['error' => $e->getMessage()]);
    jsonResponse(false, null, 'Error al obtener contactos');
}
