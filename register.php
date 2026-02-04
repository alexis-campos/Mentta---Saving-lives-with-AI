<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta - Mentta</title>
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

        .register-input {
            background: white;
            border: 1px solid #e1e1d1;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .register-input:focus {
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

        /* Custom Checkbox */
        .custom-checkbox {
            appearance: none;
            width: 1.25rem;
            height: 1.25rem;
            border: 2px solid #e1e1d1;
            border-radius: 0.375rem;
            background-color: white;
            cursor: pointer;
            position: relative;
            transition: all 0.2s ease;
        }

        .custom-checkbox:checked {
            background-color: #2d3a2d;
            border-color: #2d3a2d;
        }

        .custom-checkbox:checked::after {
            content: "✓";
            color: white;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 0.75rem;
            font-weight: bold;
        }
    </style>
</head>

<body class="p-4 md:py-12 flex flex-col items-center justify-center">
    <div class="w-full max-w-xl animate-fade">
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

        <!-- Register Card -->
        <div class="glass-card rounded-[2.5rem] p-8 md:p-12">
            <div class="mb-10 text-center md:text-left">
                <h2 class="text-4xl font-bold text-mentta-primary mb-3">Únete a Mentta</h2>
                <p class="text-gray-500">Un espacio profesional y seguro diseñado para tu bienestar.</p>
            </div>

            <!-- Register Form -->
            <form id="registerForm" onsubmit="handleRegister(event)" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-mentta-primary text-xs font-bold mb-2 uppercase tracking-widest"
                            for="name">
                            Nombre Completo
                        </label>
                        <input type="text" id="name" name="name" required autocomplete="name"
                            class="register-input w-full px-5 py-4 rounded-2xl focus:outline-none placeholder-gray-300 text-mentta-primary"
                            placeholder="Tu nombre">
                    </div>

                    <div>
                        <label class="block text-mentta-primary text-xs font-bold mb-2 uppercase tracking-widest"
                            for="age">
                            Edad
                        </label>
                        <input type="number" id="age" name="age" min="13" max="120" required
                            class="register-input w-full px-5 py-4 rounded-2xl focus:outline-none placeholder-gray-300 text-mentta-primary"
                            placeholder="Ej: 25">
                    </div>
                </div>

                <div>
                    <label class="block text-mentta-primary text-xs font-bold mb-2 uppercase tracking-widest"
                        for="email">
                        Correo Electrónico
                    </label>
                    <input type="email" id="email" name="email" required autocomplete="email"
                        class="register-input w-full px-5 py-4 rounded-2xl focus:outline-none placeholder-gray-300 text-mentta-primary"
                        placeholder="nombre@ejemplo.com">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-mentta-primary text-xs font-bold mb-2 uppercase tracking-widest"
                            for="password">
                            Contraseña
                        </label>
                        <input type="password" id="password" name="password" required minlength="8"
                            autocomplete="new-password"
                            class="register-input w-full px-5 py-4 rounded-2xl focus:outline-none placeholder-gray-300 text-mentta-primary"
                            placeholder="••••••••">
                        <p class="text-[9px] text-gray-400 mt-2 italic uppercase">Mínimo 8 caracteres</p>
                    </div>

                    <div>
                        <label class="block text-mentta-primary text-xs font-bold mb-2 uppercase tracking-widest"
                            for="password_confirm">
                            Confirmar
                        </label>
                        <input type="password" id="password_confirm" name="password_confirm" required minlength="8"
                            autocomplete="new-password"
                            class="register-input w-full px-5 py-4 rounded-2xl focus:outline-none placeholder-gray-300 text-mentta-primary"
                            placeholder="••••••••">
                    </div>
                </div>

                <!-- Terms -->
                <div class="p-6 bg-mentta-light/30 rounded-2xl border border-mentta-light select-none">
                    <label class="flex items-start gap-4 cursor-pointer group">
                        <input type="checkbox" id="terms" name="terms" required class="custom-checkbox mt-1">
                        <span
                            class="text-xs text-gray-500 leading-relaxed group-hover:text-mentta-primary transition-colors">
                            Acepto que mi información sea usada para brindarme apoyo emocional y que un profesional de
                            salud mental pueda acceder a ella si es necesario para mi seguridad.
                        </span>
                    </label>
                </div>

                <button type="submit" id="submitBtn"
                    class="custom-btn w-full text-white py-5 rounded-2xl font-bold text-sm uppercase tracking-[0.2em] flex items-center justify-center gap-3 group">
                    <span>Crear Cuenta</span>
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

            <!-- Login Link -->
            <div class="mt-10 pt-8 border-t border-gray-100 text-center">
                <p class="text-sm text-gray-500">
                    ¿Ya eres parte de Mentta?
                    <a href="login.php"
                        class="text-mentta-primary font-bold hover:text-mentta-accent transition-colors">Inicia sesión
                        aquí</a>
                </p>
            </div>
        </div>

        <p class="mt-12 text-center text-gray-400 text-[10px] uppercase tracking-widest font-bold">
            &copy; 2026 Mentta &bull; Tu bienestar es nuestra meta
        </p>
    </div>

    <script>
        async function handleRegister(event) {
            event.preventDefault();

            const form = event.target;
            const submitBtn = document.getElementById('submitBtn');
            const spinner = document.getElementById('loadingSpinner');
            const errorDiv = document.getElementById('error-message');
            const successDiv = document.getElementById('success-message');

            errorDiv.classList.add('hidden');
            successDiv.classList.add('hidden');

            const password = document.getElementById('password').value;
            const passwordConfirm = document.getElementById('password_confirm').value;

            if (password !== passwordConfirm) {
                errorDiv.textContent = 'Las contraseñas no coinciden';
                errorDiv.classList.remove('hidden');
                return;
            }

            submitBtn.disabled = true;
            spinner.classList.remove('hidden');
            submitBtn.querySelector('span').classList.add('opacity-50');

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

                    setTimeout(() => {
                        window.location.href = 'chat.php';
                    }, 1500);
                } else {
                    errorDiv.textContent = data.error || 'Error al crear la cuenta';
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