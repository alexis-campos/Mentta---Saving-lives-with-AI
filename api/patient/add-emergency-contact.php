<?php
/**
 * MENTTA - API: Add Emergency Contact
 * Adds a new emergency contact for the patient
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

$name = sanitizeInput($_POST['name'] ?? '');
$phone = sanitizeInput($_POST['phone'] ?? '');
$relationship = sanitizeInput($_POST['relationship'] ?? '');
$priority = intval($_POST['priority'] ?? 1);

// Validation
if (empty($name) || empty($phone) || empty($relationship)) {
    jsonResponse(false, null, 'Nombre, teléfono y relación son requeridos');
}

if ($priority < 1 || $priority > 10) {
    $priority = 1;
}

try {
    $db = getDB();
    
    // Check max contacts (limit to 5)
    $stmt = $db->prepare("SELECT COUNT(*) FROM emergency_contacts WHERE patient_id = ?");
    $stmt->execute([$user['id']]);
    $count = $stmt->fetchColumn();
    
    if ($count >= 5) {
        jsonResponse(false, null, 'Máximo 5 contactos de emergencia permitidos');
    }
    
    // Insert contact
    $stmt = $db->prepare("
        INSERT INTO emergency_contacts (patient_id, contact_name, contact_phone, contact_relationship, priority)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$user['id'], $name, $phone, $relationship, $priority]);
    
    $contactId = $db->lastInsertId();
    
    jsonResponse(true, [
        'id' => $contactId,
        'message' => 'Contacto agregado correctamente'
    ]);
    
} catch (Exception $e) {
    logError('Error en add-emergency-contact.php', ['error' => $e->getMessage()]);
    jsonResponse(false, null, 'Error al agregar contacto');
}
