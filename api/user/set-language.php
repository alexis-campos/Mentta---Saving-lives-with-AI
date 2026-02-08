<?php
/**
 * MENTTA - API: Set User Language Preference
 * Updates the user's preferred language in the database
 */

require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');
setSecurityHeaders();

// Only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, null, 'Método no permitido', 405);
}

// Verify authentication
$user = checkAuth();
if (!$user) {
    jsonResponse(false, null, 'No autenticado', 401);
}

// Get input (accept both JSON and form data)
$input = json_decode(file_get_contents('php://input'), true);
$language = $input['language'] ?? $_POST['language'] ?? '';

// Validate language
$supportedLanguages = ['en', 'es'];
if (!in_array($language, $supportedLanguages)) {
    jsonResponse(false, null, 'Idioma no soportado. Use: en, es');
}

try {
    $db = getDB();
    
    // Update user's language preference
    $stmt = $db->prepare("UPDATE users SET language = ? WHERE id = ?");
    $stmt->execute([$language, $user['id']]);
    
    // Also update session if it's stored there
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION['language'] = $language;
    }
    
    jsonResponse(true, [
        'language' => $language,
        'message' => $language === 'es' ? 'Idioma actualizado' : 'Language updated'
    ]);
    
} catch (Exception $e) {
    logError('Error updating language preference', [
        'user_id' => $user['id'],
        'error' => $e->getMessage()
    ]);
    jsonResponse(false, null, 'Error al guardar preferencia', 500);
}
