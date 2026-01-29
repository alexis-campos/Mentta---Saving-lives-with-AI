<?php
/**
 * MENTTA - API: Login
 * Endpoint para iniciar sesión
 */

// Suppress HTML error output for API
ini_set('display_errors', 0);
error_reporting(0);

require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');
setSecurityHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, null, 'Método no permitido', 405);
}

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if (empty($email) || empty($password)) {
    jsonResponse(false, null, 'Email y contraseña son requeridos');
}

try {
    $result = login($email, $password);
    
    if ($result) {
        jsonResponse(true, [
            'id' => $result['id'],
            'name' => $result['name'],
            'email' => $result['email'],
            'role' => $result['role']
        ]);
    } else {
        jsonResponse(false, null, 'Email o contraseña incorrectos');
    }
} catch (Exception $e) {
    logError('Error en login API', ['error' => $e->getMessage()]);
    jsonResponse(false, null, 'Error al iniciar sesión');
}
