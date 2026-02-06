<?php
/**
 * MENTTA - API: New Session
 * Creates a new chat session
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

$sessionId = sanitizeInput($_POST['session_id'] ?? '');

// Validate session ID format
if (empty($sessionId) || !preg_match('/^session_\d+_[a-z0-9]+$/', $sessionId)) {
    // Generate one if not provided or invalid
    $sessionId = 'session_' . time() . '_' . bin2hex(random_bytes(4));
}

try {
    // Just return the session ID - messages will be saved with it
    jsonResponse(true, [
        'session_id' => $sessionId,
        'message' => 'Nueva sesión creada'
    ]);
    
} catch (Exception $e) {
    logError('Error en new-session.php', ['error' => $e->getMessage()]);
    jsonResponse(false, null, 'Error al crear sesión');
}
