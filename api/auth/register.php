<?php
/**
 * MENTTA - API: Register
 * Endpoint para registro de nuevos usuarios (pacientes)
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

// Obtener datos del formulario
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$name = trim($_POST['name'] ?? '');
$age = intval($_POST['age'] ?? 0);

// Validaciones
if (empty($email) || empty($password) || empty($name)) {
    jsonResponse(false, null, 'Todos los campos son requeridos');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(false, null, 'Email inválido');
}

if (strlen($password) < 8) {
    jsonResponse(false, null, 'La contraseña debe tener al menos 8 caracteres');
}

if ($age < 13 || $age > 120) {
    jsonResponse(false, null, 'Edad inválida (mínimo 13 años)');
}

try {
    // Registrar usuario (siempre como paciente desde el registro público)
    $result = register($email, $password, $name, $age, 'patient', 'es');
    
    if (is_array($result) && isset($result['error'])) {
        jsonResponse(false, null, $result['error']);
    }
    
    if ($result) {
        // Auto-login después del registro
        $loginResult = login($email, $password);
        
        if ($loginResult) {
            jsonResponse(true, [
                'id' => $loginResult['id'],
                'name' => $loginResult['name'],
                'email' => $loginResult['email'],
                'role' => $loginResult['role'],
                'message' => 'Cuenta creada exitosamente'
            ]);
        } else {
            // Registro exitoso pero login falló
            jsonResponse(true, [
                'message' => 'Cuenta creada. Por favor inicia sesión.'
            ]);
        }
    } else {
        jsonResponse(false, null, 'Error al crear la cuenta');
    }
} catch (Exception $e) {
    logError('Error en registro API', ['error' => $e->getMessage()]);
    jsonResponse(false, null, 'Error al crear la cuenta');
}
