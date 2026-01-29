<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Mentta</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .slide-in { animation: slideIn 0.3s ease-out; }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-500 to-purple-600 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md slide-in">
        <!-- Logo -->
        <div class="text-center mb-8">
            <a href="index.php" class="inline-block">
                <h1 class="text-3xl font-bold text-indigo-600 mb-2">Mentta</h1>
            </a>
            <p class="text-gray-600">Iniciar Sesión</p>
        </div>

        <!-- Login Form -->
        <form id="loginForm" onsubmit="handleLogin(event)">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-semibold mb-2" for="email">
                    Correo Electrónico
                </label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    required
                    autocomplete="email"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                    placeholder="tu@email.com"
                >
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-semibold mb-2" for="password">
                    Contraseña
                </label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required
                    autocomplete="current-password"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                    placeholder="••••••••"
                >
            </div>

            <button 
                type="submit" 
                id="submitBtn"
                class="w-full bg-indigo-600 text-white py-3 rounded-lg font-semibold hover:bg-indigo-700 transition flex items-center justify-center gap-2"
            >
                <span>Ingresar</span>
                <svg id="loadingSpinner" class="hidden animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </button>
        </form>

        <!-- Divider -->
        <div class="my-6 flex items-center">
            <div class="flex-1 border-t border-gray-300"></div>
            <span class="px-4 text-gray-500 text-sm">o</span>
            <div class="flex-1 border-t border-gray-300"></div>
        </div>

        <!-- Register Link -->
        <div class="text-center">
            <p class="text-sm text-gray-600">
                ¿No tienes cuenta? 
                <a href="register.php" class="text-indigo-600 font-semibold hover:underline">Regístrate gratis</a>
            </p>
        </div>

        <!-- Error Message -->
        <div id="error-message" class="mt-4 bg-red-50 text-red-600 text-sm text-center p-3 rounded-lg hidden"></div>
        
        <!-- Success Message -->
        <div id="success-message" class="mt-4 bg-green-50 text-green-600 text-sm text-center p-3 rounded-lg hidden"></div>

        <!-- Demo credentials -->
        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
            <p class="text-xs text-gray-500 text-center mb-2">Credenciales de demo:</p>
            <div class="text-xs text-gray-600 space-y-1">
                <p><strong>Paciente:</strong> paciente1@mentta.com / Demo2025</p>
                <p><strong>Psicólogo:</strong> psicologo1@mentta.com / Demo2025</p>
            </div>
        </div>
    </div>

    <script>
        async function handleLogin(event) {
            event.preventDefault();
            
            const form = event.target;
            const submitBtn = document.getElementById('submitBtn');
            const spinner = document.getElementById('loadingSpinner');
            const errorDiv = document.getElementById('error-message');
            const successDiv = document.getElementById('success-message');
            
            // Reset messages
            errorDiv.classList.add('hidden');
            successDiv.classList.add('hidden');
            
            // Show loading
            submitBtn.disabled = true;
            spinner.classList.remove('hidden');
            
            const formData = new FormData(form);
            
            try {
                const response = await fetch('api/auth/login.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    successDiv.textContent = '¡Bienvenido! Redirigiendo...';
                    successDiv.classList.remove('hidden');
                    
                    // Redirect based on role
                    setTimeout(() => {
                        if (data.data.role === 'patient') {
                            window.location.href = 'chat.php';
                        } else if (data.data.role === 'psychologist') {
                            window.location.href = 'dashboard.php';
                        } else {
                            window.location.href = 'index.php';
                        }
                    }, 1000);
                } else {
                    errorDiv.textContent = data.error || 'Error al iniciar sesión';
                    errorDiv.classList.remove('hidden');
                    submitBtn.disabled = false;
                    spinner.classList.add('hidden');
                }
            } catch (error) {
                console.error('Login error:', error);
                errorDiv.textContent = 'Error de conexión. Intenta de nuevo.';
                errorDiv.classList.remove('hidden');
                submitBtn.disabled = false;
                spinner.classList.add('hidden');
            }
        }
    </script>
</body>
</html>
