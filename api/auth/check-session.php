<?php
/**
 * MENTTA - API: Check Session
 * Verifica si hay una sesión activa
 */

// Suppress HTML error output for API
ini_set('display_errors', 0);
error_reporting(0);

require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');
setSecurityHeaders();

$user = checkAuth();

if ($user) {
    jsonResponse(true, [
        'id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role'],
        'authenticated' => true
    ]);
} else {
    jsonResponse(false, null, 'No autenticado', 401);
}
