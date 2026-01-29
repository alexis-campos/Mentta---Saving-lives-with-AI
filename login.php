<?php
/**
 * MENTTA - Login Page
 * Página de inicio de sesión
 */

require_once 'includes/config.php';
require_once 'includes/auth.php';

initSession();

// Si ya está logueado, redirigir
if (isLoggedIn()) {
    $role = getCurrentUserRole();
    if ($role === 'psychologist') {
        header('Location: dashboard.php');
    } else {
        header('Location: chat.php');
    }
    exit;
}

$error = '';

// Procesar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $user = login($email, $password);
    
    if ($user) {
        if ($user['role'] === 'psychologist') {
            header('Location: dashboard.php');
        } else {
            header('Location: chat.php');
        }
        exit;
    } else {
        $error = 'Credenciales incorrectas. Por favor, intenta de nuevo.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#6366F1">
    <title>Iniciar Sesión - Mentta</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        .float-animation { animation: float 3s ease-in-out infinite; }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="w-20 h-20 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl mx-auto mb-4 flex items-center justify-center shadow-xl shadow-indigo-500/30 float-animation">
                <span class="text-white font-bold text-4xl">M</span>
            </div>
            <h1 class="text-3xl font-bold text-gray-800">Mentta</h1>
            <p class="text-gray-500 mt-2">Tu espacio seguro de apoyo emocional</p>
        </div>

        <!-- Login Form -->
        <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 p-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-6">Iniciar Sesión</h2>
            
            <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Correo electrónico
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition-all"
                        placeholder="tu@email.com"
                    >
                </div>
                
                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Contraseña
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition-all"
                        placeholder="••••••••"
                    >
                </div>
                
                <button 
                    type="submit"
                    class="w-full bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-medium py-3 rounded-xl hover:shadow-lg hover:shadow-indigo-500/30 transition-all"
                >
                    Entrar
                </button>
            </form>
            
            <div class="mt-6 text-center">
                <p class="text-gray-500 text-sm">
                    ¿No tienes cuenta? 
                    <a href="register.php" class="text-indigo-600 hover:text-indigo-700 font-medium">Regístrate</a>
                </p>
            </div>
        </div>

        <!-- Footer -->
        <p class="text-center text-gray-400 text-sm mt-8">
            🔒 Tu privacidad es nuestra prioridad
        </p>
    </div>
</body>
</html>
