<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrarse - Mentta</title>
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
            <p class="text-gray-600">Crear una cuenta</p>
        </div>

        <!-- Register Form -->
        <form id="registerForm" onsubmit="handleRegister(event)">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-semibold mb-2" for="name">
                    Nombre Completo
                </label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    required
                    autocomplete="name"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                    placeholder="Tu nombre"
                >
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-semibold mb-2" for="age">
                    Edad
                </label>
                <input 
                    type="number" 
                    id="age" 
                    name="age" 
                    min="13"
                    max="120"
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                    placeholder="Ej: 25"
                >
            </div>

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

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-semibold mb-2" for="password">
                    Contraseña
                </label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required
                    minlength="8"
                    autocomplete="new-password"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                    placeholder="Mínimo 8 caracteres"
                >
                <p class="text-xs text-gray-500 mt-1">Mínimo 8 caracteres</p>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-semibold mb-2" for="password_confirm">
                    Confirmar Contraseña
                </label>
                <input 
                    type="password" 
                    id="password_confirm" 
                    name="password_confirm" 
                    required
                    minlength="8"
                    autocomplete="new-password"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                    placeholder="Repite tu contraseña"
                >
            </div>

            <!-- Terms -->
            <div class="mb-6">
                <label class="flex items-start gap-2 cursor-pointer">
                    <input type="checkbox" id="terms" name="terms" required class="mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm text-gray-600">
                        Acepto que mi información sea usada para brindarme apoyo emocional y que un profesional de salud mental pueda acceder a ella si es necesario.
                    </span>
                </label>
            </div>

            <button 
                type="submit" 
                id="submitBtn"
                class="w-full bg-indigo-600 text-white py-3 rounded-lg font-semibold hover:bg-indigo-700 transition flex items-center justify-center gap-2"
            >
                <span>Crear Cuenta</span>
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

        <!-- Login Link -->
        <div class="text-center">
            <p class="text-sm text-gray-600">
                ¿Ya tienes cuenta? 
                <a href="login.php" class="text-indigo-600 font-semibold hover:underline">Inicia sesión</a>
            </p>
        </div>

        <!-- Error Message -->
        <div id="error-message" class="mt-4 bg-red-50 text-red-600 text-sm text-center p-3 rounded-lg hidden"></div>
        
        <!-- Success Message -->
        <div id="success-message" class="mt-4 bg-green-50 text-green-600 text-sm text-center p-3 rounded-lg hidden"></div>
    </div>

    <script>
        async function handleRegister(event) {
            event.preventDefault();
            
            const form = event.target;
            const submitBtn = document.getElementById('submitBtn');
            const spinner = document.getElementById('loadingSpinner');
            const errorDiv = document.getElementById('error-message');
            const successDiv = document.getElementById('success-message');
            
            // Reset messages
            errorDiv.classList.add('hidden');
            successDiv.classList.add('hidden');
            
            // Validate passwords match
            const password = document.getElementById('password').value;
            const passwordConfirm = document.getElementById('password_confirm').value;
            
            if (password !== passwordConfirm) {
                errorDiv.textContent = 'Las contraseñas no coinciden';
                errorDiv.classList.remove('hidden');
                return;
            }
            
            // Show loading
            submitBtn.disabled = true;
            spinner.classList.remove('hidden');
            
            const formData = new FormData(form);
            
            try {
                const response = await fetch('api/auth/register.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    successDiv.textContent = '¡Cuenta creada! Redirigiendo al chat...';
                    successDiv.classList.remove('hidden');
                    
                    // Redirect to chat
                    setTimeout(() => {
                        window.location.href = 'chat.php';
                    }, 1500);
                } else {
                    errorDiv.textContent = data.error || 'Error al crear la cuenta';
                    errorDiv.classList.remove('hidden');
                    submitBtn.disabled = false;
                    spinner.classList.add('hidden');
                }
            } catch (error) {
                console.error('Register error:', error);
                errorDiv.textContent = 'Error de conexión. Intenta de nuevo.';
                errorDiv.classList.remove('hidden');
                submitBtn.disabled = false;
                spinner.classList.add('hidden');
            }
        }
    </script>
</body>
</html>
