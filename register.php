<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta | MENTTA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,700;0,800;0,900;1,400&display=swap"
        rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['Inter', 'sans-serif'],
                        'serif': ['Playfair Display', 'serif'],
                    },
                    colors: {
                        'mentta-bg': '#FAFAF8',
                        'mentta-fg': '#111111',
                        'mentta-muted': '#888888',
                        'mentta-border': 'rgba(0,0,0,0.08)',
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: var(--mentta-bg);
            -webkit-font-smoothing: antialiased;
        }

        .register-card {
            box-shadow: 0 40px 100px rgba(0, 0, 0, 0.04);
        }

        .input-field {
            transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        .input-field:focus {
            transform: translateY(-1px);
        }

        .custom-checkbox {
            appearance: none;
            width: 1.25rem;
            height: 1.25rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 6px;
            background-color: #f9f9f9;
            cursor: pointer;
            position: relative;
            transition: all 0.2s ease;
        }

        .custom-checkbox:checked {
            background-color: #111111;
            border-color: #111111;
        }

        .custom-checkbox:checked::after {
            content: "✓";
            color: white;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 0.7rem;
            font-weight: bold;
        }
    </style>
</head>

<body class="min-h-screen bg-mentta-bg flex items-center justify-center p-6 md:py-16">

    <div class="w-full max-w-[600px]">
        <!-- Brand / Logo -->
        <div class="text-center mb-12">
            <a href="index.php" class="inline-block">
                <div
                    class="w-16 h-16 mx-auto mb-6 rounded-full overflow-hidden border border-mentta-border p-1 bg-white">
                    <img src="Images/Menta icono.jpg" alt="Mentta Logo" class="w-full h-full object-cover rounded-full">
                </div>
                <h1 class="text-3xl font-serif font-bold tracking-tight text-mentta-fg">MENTTA</h1>
                <p class="text-[10px] uppercase tracking-[0.4em] text-mentta-muted mt-2">Personal Growth</p>
            </a>
        </div>

        <!-- Card -->
        <div
            class="bg-white rounded-[2.5rem] p-10 md:p-14 register-card border border-mentta-border relative overflow-hidden">
            <!-- Subtle background decoration -->
            <div class="absolute -top-32 -right-32 w-64 h-64 bg-mentta-bg rounded-full opacity-50"></div>

            <div class="relative z-10">
                <div class="mb-12 text-center md:text-left">
                    <h2 class="text-4xl font-serif font-bold text-mentta-fg mb-4">Start Your Journey</h2>
                    <p class="text-mentta-muted text-sm leading-relaxed max-w-sm">A professional and secure space
                        designed for your total well-being.</p>
                </div>

                <form id="registerForm" onsubmit="handleRegister(event)" class="space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label for="name"
                                class="text-[10px] font-bold uppercase tracking-widest text-mentta-fg ml-4">Full
                                Name</label>
                            <input type="text" id="name" name="name" required autocomplete="name"
                                class="input-field w-full px-6 py-4 rounded-full bg-mentta-bg border-transparent focus:bg-white focus:border-mentta-border outline-none text-mentta-fg placeholder:text-mentta-muted/50 text-sm shadow-sm"
                                placeholder="Your name">
                        </div>

                        <div class="space-y-2">
                            <label for="age"
                                class="text-[10px] font-bold uppercase tracking-widest text-mentta-fg ml-4">Age</label>
                            <input type="number" id="age" name="age" min="13" max="120" required
                                class="input-field w-full px-6 py-4 rounded-full bg-mentta-bg border-transparent focus:bg-white focus:border-mentta-border outline-none text-mentta-fg placeholder:text-mentta-muted/50 text-sm shadow-sm"
                                placeholder="Ex: 25">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="email"
                            class="text-[10px] font-bold uppercase tracking-widest text-mentta-fg ml-4">Email
                            Address</label>
                        <input type="email" id="email" name="email" required autocomplete="email"
                            class="input-field w-full px-6 py-4 rounded-full bg-mentta-bg border-transparent focus:bg-white focus:border-mentta-border outline-none text-mentta-fg placeholder:text-mentta-muted/50 text-sm shadow-sm"
                            placeholder="name@example.com">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label for="password"
                                class="text-[10px] font-bold uppercase tracking-widest text-mentta-fg ml-4">Password</label>
                            <input type="password" id="password" name="password" required minlength="8"
                                autocomplete="new-password"
                                class="input-field w-full px-6 py-4 rounded-full bg-mentta-bg border-transparent focus:bg-white focus:border-mentta-border outline-none text-mentta-fg placeholder:text-mentta-muted/50 text-sm shadow-sm"
                                placeholder="••••••••">
                        </div>

                        <div class="space-y-2">
                            <label for="password_confirm"
                                class="text-[10px] font-bold uppercase tracking-widest text-mentta-fg ml-4">Confirm</label>
                            <input type="password" id="password_confirm" name="password_confirm" required minlength="8"
                                autocomplete="new-password"
                                class="input-field w-full px-6 py-4 rounded-full bg-mentta-bg border-transparent focus:bg-white focus:border-mentta-border outline-none text-mentta-fg placeholder:text-mentta-muted/50 text-sm shadow-sm"
                                placeholder="••••••••">
                        </div>
                    </div>

                    <!-- Terms -->
                    <div class="p-6 bg-mentta-bg/50 rounded-3xl border border-mentta-border">
                        <label class="flex items-start gap-4 cursor-pointer group">
                            <input type="checkbox" id="terms" name="terms" required class="custom-checkbox mt-1">
                            <span
                                class="text-[11px] text-mentta-muted leading-relaxed group-hover:text-mentta-fg transition-colors">
                                I understand that my information will be handled with absolute confidentiality and used
                                solely to provide emotional support. Specialists may access my data only if necessary
                                for clinical safety.
                            </span>
                        </label>
                    </div>

                    <button type="submit" id="submitBtn"
                        class="w-full bg-mentta-fg text-white py-5 rounded-full font-bold text-xs uppercase tracking-[0.2em] transition-all hover:opacity-90 active:scale-[0.98] shadow-lg shadow-black/5 flex items-center justify-center gap-3">
                        <span>Create Account</span>
                        <svg id="loadingSpinner" class="hidden animate-spin h-4 w-4 text-white"
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
                <div id="error-message" class="mt-6 text-red-500 text-xs text-center font-medium hidden"></div>
                <div id="success-message" class="mt-6 text-emerald-600 text-xs text-center font-medium hidden"></div>

                <!-- Footer Links -->
                <div class="mt-12 pt-8 border-t border-mentta-border text-center">
                    <p class="text-sm text-mentta-muted">
                        Already have an account?
                        <a href="login.php"
                            class="text-mentta-fg font-bold hover:underline underline-offset-4 decoration-mentta-border transition-all">Sign
                            In Here</a>
                    </p>
                </div>
            </div>
        </div>

        <p class="mt-12 text-center text-mentta-muted text-[10px] uppercase tracking-[0.3em] font-medium">
            &copy; 2026 MENTTA &bull; Private &bull; Secure &bull; Human
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
                errorDiv.textContent = 'Passwords do not match.';
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
                    successDiv.textContent = 'Account created successfully. Initializing your journey...';
                    successDiv.classList.remove('hidden');

                    setTimeout(() => {
                        window.location.href = 'chat.php';
                    }, 1500);
                } else {
                    errorDiv.textContent = data.error || 'Registration failed. Please attempt again.';
                    errorDiv.classList.remove('hidden');
                    resetBtn();
                }
            } catch (error) {
                errorDiv.textContent = 'Connection interrupted. Please verify your link.';
                errorDiv.classList.remove('hidden');
                resetBtn();
            }

            function resetBtn() {
                submitBtn.disabled = false;
                spinner.classList.add('hidden');
                submitBtn.querySelector('span').classList.remove('opacity-50');
            }
        }
    </script>
</body>

</html>