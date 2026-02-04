<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Mentta</title>
    <!-- Tailwind CSS -->
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
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f5f5f0;
            background-image:
                radial-gradient(at 0% 0%, rgba(139, 157, 139, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(203, 170, 142, 0.1) 0px, transparent 50%);
        }

        h1,
        h2 {
            font-family: 'Playfair Display', serif;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 25px 50px -12px rgba(45, 58, 45, 0.08);
        }

        .login-input {
            background: white;
            border: 1px solid #e1e1d1;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .login-input:focus {
            border-color: #2d3a2d;
            box-shadow: 0 0 0 4px rgba(45, 58, 45, 0.05);
        }

        .custom-btn {
            background-color: #2d3a2d;
            transition: all 0.3s ease;
        }

        .custom-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(45, 58, 45, 0.2);
            filter: brightness(1.1);
        }

        .custom-btn:active {
            transform: translateY(0);
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

        .animate-fade {
            animation: fadeIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
    </style>
</head>

<body class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md animate-fade">
        <!-- Brand -->
        <div class="text-center mb-10">
            <a href="index.php" class="inline-flex items-center gap-4 group">
                <div
                    class="w-12 h-12 bg-mentta-primary rounded-2xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <span class="text-white font-bold text-2xl">M</span>
                </div>
                <div class="text-left">
                    <h1 class="text-3xl font-bold text-mentta-primary tracking-tight leading-none mb-1">Mentta</h1>
                    <p class="text-mentta-accent text-[10px] font-bold uppercase tracking-widest">Salud Mental de Élite
                    </p>
                </div>
            </a>
        </div>

        <!-- Login Card -->
        <div class="glass-card rounded-[2rem] p-10">
            <div class="mb-10">
                <h2 class="text-3xl font-bold text-mentta-primary mb-2">Bienvenido</h2>
                <p class="text-gray-500 text-sm">Ingresa a tu cuenta para continuar con tu proceso.</p>
            </div>

            <!-- Login Form -->
            <form id="loginForm" onsubmit="handleLogin(event)" class="space-y-6">
                <div>
                    <label class="block text-mentta-primary text-xs font-bold mb-2 uppercase tracking-widest"
                        for="email">
                        Correo Electrónico
                    </label>
                    <input type="email" id="email" name="email" required autocomplete="email"
                        class="login-input w-full px-5 py-4 rounded-2xl focus:outline-none placeholder-gray-300 text-mentta-primary"
                        placeholder="tu@email.com">
                </div>

                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-mentta-primary text-xs font-bold uppercase tracking-widest"
                            for="password">
                            Contraseña
                        </label>
                        <a href="#"
                            class="text-xs text-mentta-secondary font-semibold hover:text-mentta-primary transition-colors">¿Olvidaste
                            tu contraseña?</a>
                    </div>
                    <input type="password" id="password" name="password" required autocomplete="current-password"
                        class="login-input w-full px-5 py-4 rounded-2xl focus:outline-none placeholder-gray-300 text-mentta-primary"
                        placeholder="••••••••">
                </div>

                <button type="submit" id="submitBtn"
                    class="custom-btn w-full text-white py-4 rounded-2xl font-bold text-sm uppercase tracking-[0.2em] flex items-center justify-center gap-3 group mt-4">
                    <span>Ingresar</span>
                    <svg id="loadingSpinner" class="hidden animate-spin h-5 w-5 text-white"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                </button>
            </form>

            <!-- Status Messages -->
            <div id="error-message"
                class="mt-6 bg-red-50 border border-red-100 text-red-600 text-sm text-center p-4 rounded-2xl hidden animate-fade">
            </div>
            <div id="success-message"
                class="mt-6 bg-green-50 border border-green-100 text-green-700 text-sm text-center p-4 rounded-2xl hidden animate-fade">
            </div>

            <!-- Footer -->
            <div class="mt-10 pt-8 border-t border-gray-100 text-center">
                <p class="text-sm text-gray-500">
                    ¿No tienes una cuenta?
                    <a href="register.php"
                        class="text-mentta-primary font-bold hover:text-mentta-accent transition-colors">Regístrate
                        ahora</a>
                </p>
            </div>
        </div>

        <!-- Demo Credentials -->
        <div class="mt-8 p-6 bg-white/40 backdrop-blur-md rounded-[1.5rem] border border-white/60 shadow-sm">
            <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-mentta-accent mb-4 text-center">Acceso de
                Demostración</h3>
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-tighter">Paciente</p>
                    <p class="text-[11px] text-mentta-primary font-medium">paciente1@mentta.com</p>
                </div>
                <div class="space-y-1">
                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-tighter">Psicólogo</p>
                    <p class="text-[11px] text-mentta-primary font-medium">psicologo1@mentta.com</p>
                </div>
            </div>
            <p class="text-[10px] text-gray-400 mt-4 text-center italic">Contraseña: <span
                    class="text-mentta-secondary font-bold not-italic">Demo2025</span></p>
        </div>

        <p class="mt-10 text-center text-gray-400 text-[10px] uppercase tracking-widest font-medium">
            &copy; 2026 Mentta &bull; Espacio Seguro &bull; Confidencial
        </p>
    </div>

    <script>
        async function handleLogin(event) {
            event.preventDefault();

            const form = event.target;
            const submitBtn = document.getElementById('submitBtn');
            const spinner = document.getElementById('loadingSpinner');
            const errorDiv = document.getElementById('error-message');
            const successDiv = document.getElementById('success-message');

            errorDiv.classList.add('hidden');
            successDiv.classList.add('hidden');

            submitBtn.disabled = true;
            spinner.classList.remove('hidden');
            submitBtn.querySelector('span').classList.add('opacity-50');

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
                    errorDiv.textContent = data.error || 'Error de credenciales';
                    errorDiv.classList.remove('hidden');
                    submitBtn.disabled = false;
                    spinner.classList.add('hidden');
                    submitBtn.querySelector('span').classList.remove('opacity-50');
                }
            } catch (error) {
                errorDiv.textContent = 'Error de conexión. Intenta de nuevo.';
                errorDiv.classList.remove('hidden');
                submitBtn.disabled = false;
                spinner.classList.add('hidden');
                submitBtn.querySelector('span').classList.remove('opacity-50');
            }
        }
    </script>
</body>

</html>