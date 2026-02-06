<?php
/**
 * MENTTA - API: Update Profile
 * Updates patient's profile information
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
$age = isset($_POST['age']) ? intval($_POST['age']) : null;

// Validation
if (empty($name)) {
    jsonResponse(false, null, 'El nombre es requerido');
}

if (mb_strlen($name) > 100) {
    jsonResponse(false, null, 'El nombre es demasiado largo');
}

if ($age !== null && ($age < 13 || $age > 120)) {
    jsonResponse(false, null, 'Edad inválida');
}

try {
    $db = getDB();
    
    $stmt = $db->prepare("UPDATE users SET name = ?, age = ? WHERE id = ?");
    $stmt->execute([$name, $age, $user['id']]);
    
    jsonResponse(true, ['message' => 'Perfil actualizado']);
    
} catch (Exception $e) {
    logError('Error en update-profile.php', ['error' => $e->getMessage()]);
    jsonResponse(false, null, 'Error al actualizar perfil');
}
