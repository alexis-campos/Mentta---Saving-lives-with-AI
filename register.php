<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | MENTTA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/theme.css">
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
            background-color: #FAFAF8;
            -webkit-font-smoothing: antialiased;
        }

        .register-card {
            box-shadow: 0 40px 100px rgba(0, 0, 0, 0.04);
        }

        .input-field {
            transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
            font-size: 16px; /* Prevents iOS zoom */
            min-height: 52px; /* Touch-friendly */
        }

        .input-field:focus {
            transform: translateY(-1px);
        }

        .input-field.error {
            border-color: #ef4444 !important;
            background-color: #fef2f2 !important;
        }

        .custom-checkbox {
            appearance: none;
            width: 1.5rem;
            height: 1.5rem;
            min-width: 1.5rem; /* Prevent shrinking */
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
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        /* Mobile-First Responsive Styles */
        @media (max-width: 480px) {
            body {
                padding: 1rem !important;
            }
            
            .register-card {
                padding: 1.5rem !important;
                border-radius: 1.5rem !important;
            }
            
            .register-card h2 {
                font-size: 1.75rem !important;
            }
            
            .brand-container {
                margin-bottom: 2rem !important;
            }
            
            .brand-container h1 {
                font-size: 1.5rem !important;
            }
            
            .input-field {
                padding: 0.875rem 1.25rem !important;
                min-height: 52px;
            }
            
            .terms-box {
                padding: 1rem !important;
            }
            
            .submit-btn {
                padding: 1rem !important;
                min-height: 54px;
            }
            
            /* Stack grids on mobile */
            .form-grid {
                grid-template-columns: 1fr !important;
                gap: 1rem !important;
            }
        }
        
        @media (max-width: 375px) {
            .register-card {
                padding: 1.25rem !important;
            }
            
            .register-card h2 {
                font-size: 1.5rem !important;
            }
        }
    </style>
</head>

<body class="min-h-screen bg-mentta-bg flex items-center justify-center p-6 md:py-16">
    <!-- Language Switcher -->
    <div class="header-lang-switcher" id="langSwitcher"></div>

    <div class="w-full max-w-[600px]">
        <!-- Brand / Logo -->
        <div class="text-center mb-12">
            <a href="index.php" class="inline-block">
                <div
                    class="w-16 h-16 mx-auto mb-6 rounded-full overflow-hidden border border-mentta-border p-1 bg-white">
                    <img src="images/Menta_icono.jpg" alt="Mentta Logo" class="w-full h-full object-cover rounded-full">
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
                    <h2 class="text-4xl font-serif font-bold text-mentta-fg mb-4" data-i18n="register.title">Start Your Journey</h2>
                    <p class="text-mentta-muted text-sm leading-relaxed max-w-sm" data-i18n="register.subtitle">A professional and secure space
                        designed for your total well-being.</p>
                </div>

                <form id="registerForm" onsubmit="handleRegister(event)" class="space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label for="name"
                                class="text-[10px] font-bold uppercase tracking-widest text-mentta-fg ml-4" data-i18n="register.nameLabel">Full
                                Name</label>
                            <input type="text" id="name" name="name" required autocomplete="name"
                                class="input-field w-full px-6 py-4 rounded-full bg-mentta-bg border-transparent focus:bg-white focus:border-mentta-border outline-none text-mentta-fg placeholder:text-mentta-muted/50 text-sm shadow-sm"
                                data-i18n-placeholder="register.namePlaceholder"
                                placeholder="Your name">
                        </div>

                        <div class="space-y-2">
                            <label for="age"
                                class="text-[10px] font-bold uppercase tracking-widest text-mentta-fg ml-4" data-i18n="register.ageLabel">Age</label>
                            <input type="number" id="age" name="age" min="13" max="120"
                                class="input-field w-full px-6 py-4 rounded-full bg-mentta-bg border-transparent focus:bg-white focus:border-mentta-border outline-none text-mentta-fg placeholder:text-mentta-muted/50 text-sm shadow-sm"
                                placeholder="Ex: 25">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="email"
                            class="text-[10px] font-bold uppercase tracking-widest text-mentta-fg ml-4" data-i18n="register.emailLabel">Email
                            Address</label>
                        <input type="email" id="email" name="email" required autocomplete="email"
                            class="input-field w-full px-6 py-4 rounded-full bg-mentta-bg border-transparent focus:bg-white focus:border-mentta-border outline-none text-mentta-fg placeholder:text-mentta-muted/50 text-sm shadow-sm"
                            data-i18n-placeholder="register.emailPlaceholder"
                            placeholder="name@example.com">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label for="password"
                                class="text-[10px] font-bold uppercase tracking-widest text-mentta-fg ml-4" data-i18n="register.passwordLabel">Password</label>
                            <input type="password" id="password" name="password" required minlength="8"
                                autocomplete="new-password"
                                class="input-field w-full px-6 py-4 rounded-full bg-mentta-bg border-transparent focus:bg-white focus:border-mentta-border outline-none text-mentta-fg placeholder:text-mentta-muted/50 text-sm shadow-sm"
                                data-i18n-placeholder="register.passwordPlaceholder"
                                placeholder="At least 8 characters">
                        </div>

                        <div class="space-y-2">
                            <label for="password_confirm"
                                class="text-[10px] font-bold uppercase tracking-widest text-mentta-fg ml-4" data-i18n="register.confirmPasswordLabel">Confirm</label>
                            <input type="password" id="password_confirm" name="password_confirm" required minlength="8"
                                autocomplete="new-password"
                                class="input-field w-full px-6 py-4 rounded-full bg-mentta-bg border-transparent focus:bg-white focus:border-mentta-border outline-none text-mentta-fg placeholder:text-mentta-muted/50 text-sm shadow-sm"
                                data-i18n-placeholder="register.confirmPasswordPlaceholder"
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
                        <span id="submitBtnText" data-i18n="register.signUp">Create Account</span>
                        <svg id="loadingSpinner" class="hidden animate-spin h-4 w-4 text-white"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                    </button>
                </form>

                <!-- Status Messages -->
                <div id="error-message" class="mt-6 text-red-500 text-xs text-center font-medium hidden" role="alert" aria-live="polite"></div>
                <div id="success-message" class="mt-6 text-emerald-600 text-xs text-center font-medium hidden" role="status" aria-live="polite"></div>

                <!-- Footer Links -->
                <div class="mt-12 pt-8 border-t border-mentta-border text-center">
                    <p class="text-sm text-mentta-muted">
                        <span data-i18n="register.hasAccount">Already have an account?</span>
                        <a href="login.php"
                            class="text-mentta-fg font-bold hover:underline underline-offset-4 decoration-mentta-border transition-all" data-i18n="register.signIn">Sign
                            In Here</a>
                    </p>
                </div>
            </div>
        </div>

        <p class="mt-12 text-center text-mentta-muted text-[10px] uppercase tracking-[0.3em] font-medium">
            &copy; 2026 MENTTA &bull; Private &bull; Secure &bull; Human
        </p>
    </div>

    <!-- Translation System -->
    <script src="assets/js/translations.js"></script>
    
    <script>
        // Initialize language switcher
        document.addEventListener('DOMContentLoaded', () => {
            // CRITICAL: Trigger the reveal animation from theme.css
            document.body.classList.add('loaded');
            
            i18n.createLanguageSwitcher('langSwitcher');
            i18n.applyTranslations();
        });

        async function handleRegister(event) {
            event.preventDefault();

            const form = event.target;
            const submitBtn = document.getElementById('submitBtn');
            const spinner = document.getElementById('loadingSpinner');
            const errorDiv = document.getElementById('error-message');
            const successDiv = document.getElementById('success-message');

            // Clear previous states
            errorDiv.classList.add('hidden');
            successDiv.classList.add('hidden');
            document.querySelectorAll('.input-field').forEach(el => el.classList.remove('error'));

            const password = document.getElementById('password').value;
            const passwordConfirm = document.getElementById('password_confirm').value;

            if (password !== passwordConfirm) {
                errorDiv.textContent = i18n.t('register.passwordMismatch');
                errorDiv.classList.remove('hidden');
                document.getElementById('password').classList.add('error');
                document.getElementById('password_confirm').classList.add('error');
                return;
            }

            // Disable button and show loading
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
                    successDiv.textContent = i18n.t('register.registrationSuccess');
                    successDiv.classList.remove('hidden');

                    setTimeout(() => {
                        // CRITICAL FIX: Use replace() instead of href to prevent back navigation
                        window.location.replace('chat.php');
                    }, 1500);
                } else {
                    errorDiv.textContent = data.error || i18n.t('errors.connectionFailed');
                    errorDiv.classList.remove('hidden');
                    resetBtn();
                }
            } catch (error) {
                errorDiv.textContent = i18n.t('errors.connectionFailed');
                errorDiv.classList.remove('hidden');
                resetBtn();
            }

            function resetBtn() {
                submitBtn.disabled = false;
                spinner.classList.add('hidden');
                submitBtn.querySelector('span').classList.remove('opacity-50');
            }
        }

        // Clear error styling on input focus
        document.querySelectorAll('.input-field').forEach(input => {
            input.addEventListener('focus', () => {
                input.classList.remove('error');
                document.getElementById('error-message').classList.add('hidden');
            });
        });
    </script>
</body>

</html>