<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | MENTTA</title>
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

        .login-card {
            box-shadow: 0 40px 100px rgba(0, 0, 0, 0.04);
        }

        .input-field {
            transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        .input-field:focus {
            transform: translateY(-1px);
        }

        .input-field.error {
            border-color: #ef4444 !important;
            background-color: #fef2f2 !important;
        }
    </style>
</head>

<body class="min-h-screen bg-mentta-bg flex items-center justify-center p-6">
    <!-- Language Switcher -->
    <div class="header-lang-switcher" id="langSwitcher"></div>

    <div class="w-full max-w-[440px]">
        <!-- Brand / Logo -->
        <div class="text-center mb-12">
            <a href="index.php" class="inline-block">
                <div
                    class="w-16 h-16 mx-auto mb-6 rounded-full overflow-hidden border border-mentta-border p-1 bg-white">
                    <img src="Images/Menta icono.jpg" alt="Mentta Logo" class="w-full h-full object-cover rounded-full">
                </div>
                <h1 class="text-3xl font-serif font-bold tracking-tight text-mentta-fg">MENTTA</h1>
                <p class="text-[10px] uppercase tracking-[0.4em] text-mentta-muted mt-2">Sophisticated Clarity</p>
            </a>
        </div>

        <!-- Card -->
        <div
            class="bg-white rounded-[2.5rem] p-10 md:p-12 login-card border border-mentta-border relative overflow-hidden">
            <!-- Subtle background decoration -->
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-mentta-bg rounded-full opacity-50"></div>

            <div class="relative z-10">
                <div class="mb-10">
                    <h2 id="loginTitle" class="text-3xl font-serif font-bold text-mentta-fg mb-3" data-i18n="login.title">Welcome Back</h2>
                    <p id="loginSubtitle" class="text-mentta-muted text-sm leading-relaxed" data-i18n="login.subtitle">Please enter your credentials to access your serene space.</p>
                </div>

                <form id="loginForm" onsubmit="handleLogin(event)" class="space-y-6">
                    <div class="space-y-2">
                        <label for="email"
                            class="text-[10px] font-bold uppercase tracking-widest text-mentta-fg ml-4" data-i18n="login.emailLabel">Email Address</label>
                        <input type="email" id="email" name="email" required autocomplete="email"
                            class="input-field w-full px-6 py-4 rounded-full bg-mentta-bg border-transparent focus:bg-white focus:border-mentta-border outline-none text-mentta-fg placeholder:text-mentta-muted/50 text-sm shadow-sm"
                            data-i18n-placeholder="login.emailPlaceholder"
                            placeholder="name@example.com">
                    </div>

                    <div class="space-y-2">
                        <div class="flex justify-between items-center px-4">
                            <label for="password"
                                class="text-[10px] font-bold uppercase tracking-widest text-mentta-fg" data-i18n="login.passwordLabel">Password</label>
                            <a href="#"
                                class="text-[10px] font-semibold text-mentta-muted hover:text-mentta-fg transition-colors" data-i18n="login.forgotPassword">Forgot?</a>
                        </div>
                        <input type="password" id="password" name="password" required autocomplete="current-password"
                            class="input-field w-full px-6 py-4 rounded-full bg-mentta-bg border-transparent focus:bg-white focus:border-mentta-border outline-none text-mentta-fg placeholder:text-mentta-muted/50 text-sm shadow-sm"
                            data-i18n-placeholder="login.passwordPlaceholder"
                            placeholder="••••••••">
                    </div>

                    <button type="submit" id="submitBtn"
                        class="w-full bg-mentta-fg text-white py-5 rounded-full font-bold text-xs uppercase tracking-[0.2em] transition-all hover:opacity-90 active:scale-[0.98] shadow-lg shadow-black/5 flex items-center justify-center gap-3 mt-4">
                        <span id="submitBtnText" data-i18n="login.signIn">Sign In</span>
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
                <div class="mt-10 pt-8 border-t border-mentta-border text-center">
                    <p class="text-sm text-mentta-muted">
                        <span data-i18n="login.noAccount">New to Mentta?</span>
                        <a href="register.php"
                            class="text-mentta-fg font-bold hover:underline underline-offset-4 decoration-mentta-border transition-all" data-i18n="login.createAccount">Create
                            Account</a>
                    </p>
                </div>
            </div>
        </div>

        <!-- Security Badge -->
        <div
            class="mt-12 flex items-center justify-center gap-6 opacity-40 grayscale group hover:opacity-100 hover:grayscale-0 transition-all">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <span class="text-[10px] font-bold uppercase tracking-widest" data-i18n="login.endToEndEncrypted">End-to-End Encrypted</span>
            </div>
            <div class="w-px h-3 bg-mentta-fg"></div>
            <span class="text-[10px] font-bold uppercase tracking-widest" data-i18n="login.premiumCare">Premium Care</span>
        </div>
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

        async function handleLogin(event) {
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

            // Disable button and show loading
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
                    successDiv.textContent = i18n.t('login.verifying');
                    successDiv.classList.remove('hidden');

                    setTimeout(() => {
                        // CRITICAL FIX: Use replace() instead of href to prevent back navigation
                        if (data.data.role === 'patient') {
                            window.location.replace('chat.php');
                        } else if (data.data.role === 'psychologist') {
                            window.location.replace('dashboard.php');
                        } else {
                            window.location.replace('index.php');
                        }
                    }, 1200);
                } else {
                    errorDiv.textContent = data.error || i18n.t('login.invalidCredentials');
                    errorDiv.classList.remove('hidden');
                    // Add error styling to inputs
                    document.querySelectorAll('.input-field').forEach(el => el.classList.add('error'));
                    resetBtn();
                }
            } catch (error) {
                errorDiv.textContent = i18n.t('login.connectionError');
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