<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Mentta</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'mentta-primary': '#2d3a2d',
                        'mentta-secondary': '#cbaa8e',
                        'mentta-light': '#f5f5f0',
                        'mentta-accent': '#8b9d8b',
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        
        h1, h2 {
            font-family: 'Playfair Display', serif;
        }

        @keyframes fadeIn {
            from { 
                opacity: 0; 
                transform: translateY(20px);
            }
            to { 
                opacity: 1; 
                transform: translateY(0);
            }
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }

        .slide-in-left {
            animation: slideInLeft 0.8s ease-out;
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-mentta-light via-white to-gray-50 min-h-screen">
    
    <!-- Header -->
    <header class="absolute top-0 w-full p-6 z-50">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <a href="index.php" class="flex items-center space-x-3 group">
                <img src="assets/img/icon-new.png" alt="Mentta" class="h-12 w-auto transition-transform group-hover:scale-105">
                <span class="text-2xl font-semibold text-mentta-primary">Mentta</span>
            </a>
            <a href="index.php" class="text-gray-600 hover:text-mentta-primary transition text-sm font-medium">
                ← Volver al inicio
            </a>
        </div>
    </header>

    <!-- Main Content -->
    <div class="min-h-screen flex items-center justify-center p-4 pt-24">
        <div class="w-full max-w-6xl grid lg:grid-cols-2 gap-8 items-center">
            
            <!-- Left Side - Info -->
            <div class="hidden lg:block slide-in-left">
                <div class="space-y-6">
                    <div class="inline-flex items-center space-x-2 bg-green-50 text-green-700 px-4 py-2 rounded-full text-sm font-medium">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>Plataforma Segura</span>
                    </div>

                    <h1 class="text-5xl font-bold text-mentta-primary leading-tight">
                        Bienvenido de vuelta
                    </h1>

                    <p class="text-xl text-gray-600 leading-relaxed">
                        Continúa tu camino hacia el bienestar emocional. Tu espacio seguro te está esperando.
                    </p>

                    <!-- Trust Indicators -->
                    <div class="space-y-4 pt-6">
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-mentta-primary">100% Confidencial</h3>
                                <p class="text-sm text-gray-600">Tus datos están protegidos con encriptación de nivel bancario</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-mentta-primary">Disponible 24/7</h3>
                                <p class="text-sm text-gray-600">Acceso ilimitado cuando más lo necesites</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0 w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-mentta-primary">Equipo Profesional</h3>
                                <p class="text-sm text-gray-600">Psicólogos certificados supervisando tu progreso</p>
                            </div>
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="grid grid-cols-3 gap-4 pt-8 border-t border-gray-200">
                        <div>
                            <div class="text-3xl font-bold text-mentta-primary">5K+</div>
                            <div class="text-sm text-gray-600">Usuarios</div>
                        </div>
                        <div>
                            <div class="text-3xl font-bold text-mentta-primary">4.9</div>
                            <div class="text-sm text-gray-600">Calificación</div>
                        </div>
                        <div>
                            <div class="text-3xl font-bold text-mentta-primary">150+</div>
                            <div class="text-sm text-gray-600">Psicólogos</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side - Login Form -->
            <div class="fade-in">
                <div class="glass-effect rounded-2xl shadow-2xl p-8 md:p-12 border border-white/50">
                    <!-- Header -->
                    <div class="mb-8">
                        <h2 class="text-3xl font-bold text-mentta-primary mb-2">Iniciar Sesión</h2>
                        <p class="text-gray-600">Ingresa a tu cuenta para continuar</p>
                    </div>

                    <!-- Role Selector -->
                    <div class="mb-6">
                        <div class="grid grid-cols-2 gap-3 p-1 bg-gray-100 rounded-lg">
                            <button 
                                type="button"
                                id="patientRoleBtn"
                                onclick="selectRole('patient')"
                                class="role-btn px-4 py-3 rounded-md text-sm font-medium transition-all bg-white text-mentta-primary shadow-sm"
                            >
                                <div class="flex items-center justify-center space-x-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <span>Paciente</span>
                                </div>
                            </button>
                            <button 
                                type="button"
                                id="psychologistRoleBtn"
                                onclick="selectRole('psychologist')"
                                class="role-btn px-4 py-3 rounded-md text-sm font-medium transition-all text-gray-600 hover:text-mentta-primary"
                            >
                                <div class="flex items-center justify-center space-x-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    <span>Profesional</span>
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- Login Form -->
                    <form id="loginForm" onsubmit="handleLogin(event)" class="space-y-5">
                        <input type="hidden" id="role" name="role" value="patient">

                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-2" for="email">
                                Correo Electrónico
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                                    </svg>
                                </div>
                                <input 
                                    type="email" 
                                    id="email" 
                                    name="email" 
                                    required
                                    autocomplete="email"
                                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-mentta-primary focus:border-transparent transition"
                                    placeholder="tu@email.com"
                                >
                            </div>
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-2" for="password">
                                Contraseña
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </div>
                                <input 
                                    type="password" 
                                    id="password" 
                                    name="password" 
                                    required
                                    autocomplete="current-password"
                                    class="w-full pl-10 pr-12 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-mentta-primary focus:border-transparent transition"
                                    placeholder="••••••••"
                                >
                                <button 
                                    type="button" 
                                    onclick="togglePassword()"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                                >
                                    <svg id="eyeIcon" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-sm">
                            <label class="flex items-center">
                                <input type="checkbox" class="w-4 h-4 text-mentta-primary border-gray-300 rounded focus:ring-mentta-primary">
                                <span class="ml-2 text-gray-600">Recordarme</span>
                            </label>
                            <a href="#" class="text-mentta-primary font-medium hover:underline">
                                ¿Olvidaste tu contraseña?
                            </a>
                        </div>

                        <button 
                            type="submit" 
                            id="submitBtn"
                            class="w-full bg-mentta-primary text-white py-3.5 rounded-lg font-semibold hover:bg-mentta-primary/90 transition-all shadow-lg shadow-mentta-primary/20 flex items-center justify-center gap-2 transform active:scale-95"
                        >
                            <span id="btnText">Iniciar Sesión</span>
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
                            <a href="register.php" class="text-mentta-primary font-semibold hover:underline">Regístrate gratis</a>
                        </p>
                    </div>

                    <!-- Messages -->
                    <div id="error-message" class="mt-6 bg-red-50 border border-red-200 text-red-700 text-sm p-4 rounded-lg hidden flex items-start gap-2">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <span id="error-text"></span>
                    </div>
                    
                    <div id="success-message" class="mt-6 bg-green-50 border border-green-200 text-green-700 text-sm p-4 rounded-lg hidden flex items-start gap-2">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span id="success-text"></span>
                    </div>

                    <!-- Demo Credentials -->
                    <div class="mt-6 p-4 bg-gradient-to-r from-mentta-light to-gray-50 rounded-lg border border-gray-200">
                        <div class="flex items-start gap-2 mb-2">
                            <svg class="w-5 h-5 text-mentta-accent flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <div class="flex-1">
                                <p class="text-xs font-semibold text-gray-700 mb-2">Credenciales de Prueba:</p>
                                <div class="text-xs text-gray-600 space-y-1.5">
                                    <div class="flex items-center justify-between bg-white p-2 rounded">
                                        <span><strong>Paciente:</strong> paciente1@mentta.com</span>
                                        <code class="bg-gray-100 px-2 py-1 rounded text-xs">Demo2025</code>
                                    </div>
                                    <div class="flex items-center justify-between bg-white p-2 rounded">
                                        <span><strong>Psicólogo:</strong> psicologo1@mentta.com</span>
                                        <code class="bg-gray-100 px-2 py-1 rounded text-xs">Demo2025</code>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mobile Stats -->
                <div class="lg:hidden mt-8 grid grid-cols-3 gap-4 text-center">
                    <div>
                        <div class="text-2xl font-bold text-mentta-primary">5K+</div>
                        <div class="text-xs text-gray-600">Usuarios</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-mentta-primary">4.9</div>
                        <div class="text-xs text-gray-600">Calificación</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-mentta-primary">150+</div>
                        <div class="text-xs text-gray-600">Psicólogos</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let selectedRole = 'patient';

        function selectRole(role) {
            selectedRole = role;
            document.getElementById('role').value = role;
            
            const patientBtn = document.getElementById('patientRoleBtn');
            const psychologistBtn = document.getElementById('psychologistRoleBtn');
            
            if (role === 'patient') {
                patientBtn.classList.add('bg-white', 'text-mentta-primary', 'shadow-sm');
                patientBtn.classList.remove('text-gray-600');
                psychologistBtn.classList.remove('bg-white', 'text-mentta-primary', 'shadow-sm');
                psychologistBtn.classList.add('text-gray-600');
            } else {
                psychologistBtn.classList.add('bg-white', 'text-mentta-primary', 'shadow-sm');
                psychologistBtn.classList.remove('text-gray-600');
                patientBtn.classList.remove('bg-white', 'text-mentta-primary', 'shadow-sm');
                patientBtn.classList.add('text-gray-600');
            }
        }

        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                `;
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                `;
            }
        }

        async function handleLogin(event) {
            event.preventDefault();
            
            const form = event.target;
            const submitBtn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            const spinner = document.getElementById('loadingSpinner');
            const errorDiv = document.getElementById('error-message');
            const errorText = document.getElementById('error-text');
            const successDiv = document.getElementById('success-message');
            const successText = document.getElementById('success-text');
            
            // Reset messages
            errorDiv.classList.add('hidden');
            successDiv.classList.add('hidden');
            
            // Show loading
            submitBtn.disabled = true;
            btnText.textContent = 'Iniciando sesión...';
            spinner.classList.remove('hidden');
            
            const formData = new FormData(form);
            
            try {
                const response = await fetch('api/auth/login.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    successText.textContent = '¡Bienvenido! Redirigiendo a tu panel...';
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
                    }, 1500);
                } else {
                    errorText.textContent = data.error || 'Credenciales incorrectas. Por favor, verifica tu correo y contraseña.';
                    errorDiv.classList.remove('hidden');
                    submitBtn.disabled = false;
                    btnText.textContent = 'Iniciar Sesión';
                    spinner.classList.add('hidden');
                }
            } catch (error) {
                console.error('Login error:', error);
                errorText.textContent = 'Error de conexión. Por favor, intenta de nuevo más tarde.';
                errorDiv.classList.remove('hidden');
                submitBtn.disabled = false;
                btnText.textContent = 'Iniciar Sesión';
                spinner.classList.add('hidden');
            }
        }

        // Check URL params for role
        const urlParams = new URLSearchParams(window.location.search);
        const roleParam = urlParams.get('role');
        if (roleParam === 'psychologist') {
            selectRole('psychologist');
        }
    </script>
</body>
</html>