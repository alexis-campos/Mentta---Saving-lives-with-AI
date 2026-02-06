<?php
/**
 * MENTTA - Logout
 * Cierra la sesión con una transición elegante y premium
 */

require_once 'includes/auth.php';

// Ejecutar logout
logout();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cerrando Sesión | MENTTA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:ital,wght@0,700;0,800;1,400&display=swap"
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
            overflow: hidden;
        }

        .logout-anim {
            animation: logoutEntry 1.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes logoutEntry {
            0% {
                opacity: 0;
                transform: scale(0.95) translateY(10px);
            }

            100% {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .exit-anim {
            animation: logoutExit 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            animation-delay: 1.5s;
        }

        @keyframes logoutExit {
            0% {
                opacity: 1;
                transform: translateY(0);
            }

            100% {
                opacity: 0;
                transform: translateY(-20px);
            }
        }

        .progress-bar {
            width: 100%;
            height: 2px;
            background: rgba(0, 0, 0, 0.05);
            border-radius: 10px;
            overflow: hidden;
            margin-top: 2rem;
        }

        .progress-inner {
            height: 100%;
            background: #111;
            width: 0%;
            animation: progressLoad 1.8s linear forwards;
        }

        @keyframes progressLoad {
            0% {
                width: 0%;
            }

            100% {
                width: 100%;
            }
        }
    </style>
</head>

<body class="min-h-screen flex items-center justify-center p-6 bg-mentta-bg">

    <div class="w-full max-w-sm text-center logout-anim exit-anim">
        <!-- Brand Logo -->
        <div class="mb-10 inline-block relative">
            <div
                class="w-20 h-20 mx-auto rounded-full overflow-hidden border border-mentta-border p-1 bg-white shadow-xl shadow-black/5">
                <img src="images/Menta_icono.jpg" alt="Mentta Logo" class="w-full h-full object-cover rounded-full">
            </div>
            <!-- Checkmark badge -->
            <div
                class="absolute -bottom-1 -right-1 w-7 h-7 bg-mentta-fg rounded-full flex items-center justify-center shadow-lg border-2 border-white">
                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                </svg>
            </div>
        </div>

        <!-- Message -->
        <h2 class="text-4xl font-serif font-bold text-mentta-fg mb-4">Session Securely Closed</h2>
        <p class="text-mentta-muted text-sm leading-relaxed max-w-[280px] mx-auto">
            Thank you for prioritizing your mental well-being with Mentta today.
        </p>

        <!-- Redirect Indicator -->
        <div class="mt-12">
            <p class="text-[10px] font-bold uppercase tracking-[0.4em] text-mentta-muted/60 mb-2">Redirecting to home
            </p>
            <div class="progress-bar max-w-[120px] mx-auto">
                <div class="progress-inner"></div>
            </div>
        </div>
    </div>

    <script>
        // Redirect after animation completes
        setTimeout(() => {
            window.location.href = 'index.php';
        }, 2200);
    </script>
</body>

</html>