<?php
/**
 * MENTTA - API: Change Password
 * Changes user's password
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
    jsonResponse(false, null, 'No autorizado', 401);
}

$currentPassword = $_POST['current_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// Validation
if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
    jsonResponse(false, null, 'Todos los campos son requeridos');
}

if ($newPassword !== $confirmPassword) {
    jsonResponse(false, null, 'Las contraseñas no coinciden');
}

if (mb_strlen($newPassword) < 8) {
    jsonResponse(false, null, 'La contraseña debe tener al menos 8 caracteres');
}

try {
    $db = getDB();
    
    // Get current password hash
    $stmt = $db->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmt->execute([$user['id']]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$userData || !password_verify($currentPassword, $userData['password_hash'])) {
        jsonResponse(false, null, 'Contraseña actual incorrecta');
    }
    
    // Update password
    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
    $stmt->execute([$newHash, $user['id']]);
    
    jsonResponse(true, ['message' => 'Contraseña actualizada']);
    
} catch (Exception $e) {
    logError('Error en change-password.php', ['error' => $e->getMessage()]);
    jsonResponse(false, null, 'Error al cambiar contraseña');
}
