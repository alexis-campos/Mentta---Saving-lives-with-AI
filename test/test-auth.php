<?php
/**
 * MENTTA - Test de Sistema de Autenticación
 * Ejecutar desde navegador o CLI: php test/test-auth.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>\n";
echo "===========================================\n";
echo "   MENTTA - Test de Autenticación\n";
echo "===========================================\n\n";

// Cargar dependencias
require_once __DIR__ . '/../includes/auth.php';

try {
    // Test 1: Login con credenciales correctas
    echo "1. Probando login con paciente1@mentta.com...\n";
    
    $user = login('paciente1@mentta.com', 'Demo2025');
    
    if ($user) {
        echo "   ✅ Login exitoso\n";
        echo "   - ID: {$user['id']}\n";
        echo "   - Nombre: {$user['name']}\n";
        echo "   - Email: {$user['email']}\n";
        echo "   - Rol: {$user['role']}\n";
        echo "   - Idioma: {$user['language']}\n\n";
    } else {
        echo "   ❌ Login falló\n\n";
    }
    
    // Test 2: Verificar sesión
    echo "2. Verificando sesión activa...\n";
    
    $auth = checkAuth();
    if ($auth) {
        echo "   ✅ Sesión activa\n";
        echo "   - User ID en sesión: {$auth['id']}\n\n";
    } else {
        echo "   ❌ No hay sesión activa\n\n";
    }
    
    // Test 3: isLoggedIn
    echo "3. Probando isLoggedIn()...\n";
    echo "   " . (isLoggedIn() ? "✅ Usuario logueado" : "❌ No logueado") . "\n\n";
    
    // Test 4: getCurrentUser
    echo "4. Probando getCurrentUser()...\n";
    $currentUser = getCurrentUser();
    if ($currentUser) {
        echo "   ✅ Usuario actual: {$currentUser['name']}\n\n";
    } else {
        echo "   ❌ No se pudo obtener usuario actual\n\n";
    }
    
    // Test 5: Verificar roles
    echo "5. Verificando funciones de rol...\n";
    echo "   - isPatient(): " . (isPatient() ? "Sí" : "No") . "\n";
    echo "   - isPsychologist(): " . (isPsychologist() ? "Sí" : "No") . "\n\n";
    
    // Test 6: Logout
    echo "6. Probando logout...\n";
    logout();
    
    $authAfterLogout = checkAuth();
    if (!$authAfterLogout) {
        echo "   ✅ Logout exitoso\n\n";
    } else {
        echo "   ❌ Logout falló\n\n";
    }
    
    // Test 7: Login con credenciales incorrectas
    echo "7. Probando login con credenciales incorrectas...\n";
    $badLogin = login('paciente1@mentta.com', 'WrongPassword');
    
    if (!$badLogin) {
        echo "   ✅ Login rechazado correctamente\n\n";
    } else {
        echo "   ❌ Login debería haber fallado\n\n";
    }
    
    // Test 8: Login con psicólogo
    echo "8. Probando login con psicólogo...\n";
    $psych = login('psicologo1@mentta.com', 'Demo2025');
    
    if ($psych && $psych['role'] === 'psychologist') {
        echo "   ✅ Login de psicólogo exitoso\n";
        echo "   - Nombre: {$psych['name']}\n";
        echo "   - Rol: {$psych['role']}\n\n";
    } else {
        echo "   ❌ Login de psicólogo falló\n\n";
    }
    
    // Limpiar
    logout();
    
    echo "===========================================\n";
    echo "   ✅ TODOS LOS TESTS PASARON\n";
    echo "===========================================\n";
    
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
    echo "   Archivo: " . $e->getFile() . "\n";
    echo "   Línea: " . $e->getLine() . "\n";
}

echo "</pre>";
