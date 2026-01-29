<?php
/**
 * MENTTA - Register Page
 * Página de registro para pacientes
 */

require_once 'includes/config.php';
require_once 'includes/auth.php';

initSession();

if (isLoggedIn()) {
    header('Location: chat.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $name = $_POST['name'] ?? '';
    $age = !empty($_POST['age']) ? (int)$_POST['age'] : null;
    
    if ($password !== $confirmPassword) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        $result = register($email, $password, $name, $age, 'patient');
        
        if (is_array($result) && isset($result['error'])) {
            $error = $result['error'];
        } else {
            $success = 'Cuenta creada exitosamente. Ahora puedes iniciar sesión.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrarse - Mentta</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl mx-auto mb-4 flex items-center justify-center shadow-lg">
                <span class="text-white font-bold text-2xl">M</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Crear cuenta</h1>
            <p class="text-gray-500 mt-1">Únete a nuestra comunidad de apoyo</p>
        </div>

        <!-- Form -->
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
                <?= htmlspecialchars($success) ?> 
                <a href="login.php" class="underline font-medium">Ir al login</a>
            </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nombre</label>
                    <input type="text" name="name" required 
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none"
                        placeholder="Tu nombre">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Correo electrónico</label>
                    <input type="email" name="email" required 
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none"
                        placeholder="tu@email.com">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Edad (opcional)</label>
                    <input type="number" name="age" min="10" max="120"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Contraseña</label>
                    <input type="password" name="password" required minlength="8"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none"
                        placeholder="Mínimo 8 caracteres">
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Confirmar contraseña</label>
                    <input type="password" name="confirm_password" required
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none"
                        placeholder="Repite tu contraseña">
                </div>
                
                <button type="submit" 
                    class="w-full bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-medium py-3 rounded-xl hover:shadow-lg transition-all">
                    Crear cuenta
                </button>
            </form>
            
            <div class="mt-6 text-center">
                <p class="text-gray-500 text-sm">
                    ¿Ya tienes cuenta? <a href="login.php" class="text-indigo-600 font-medium">Inicia sesión</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
