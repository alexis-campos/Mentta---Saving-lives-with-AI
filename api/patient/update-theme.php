<?php
/**
 * MENTTA - API: Update Theme Preference
 * Saves user's light/dark mode preference
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

$theme = $_POST['theme'] ?? '';

if (!in_array($theme, ['light', 'dark'])) {
    jsonResponse(false, null, 'Tema inválido');
}

try {
    $db = getDB();
    
    $stmt = $db->prepare("UPDATE users SET theme_preference = ? WHERE id = ?");
    $stmt->execute([$theme, $user['id']]);
    
    jsonResponse(true, ['theme' => $theme, 'message' => 'Tema actualizado']);
    
} catch (Exception $e) {
    logError('Error en update-theme.php', ['error' => $e->getMessage()]);
    jsonResponse(false, null, 'Error al guardar preferencia');
}
