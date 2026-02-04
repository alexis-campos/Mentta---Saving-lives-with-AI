<?php
/**
 * MENTTA - Logout
 * Cierra la sesión con una transición elegante
 */

require_once 'includes/auth.php';

// Ejecutar logout (esto limpia la sesión)
logout();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cerrando Sesión - Mentta</title>
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
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Playfair+Display:wght@700&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f5f5f0;
            overflow: hidden;
        }

        h2 {
            font-family: 'Playfair Display', serif;
        }

        .fade-out-up {
            animation: fadeOutUp 1s ease-out forwards;
            animation-delay: 1s;
        }

        @keyframes fadeOutUp {
            from {
                opacity: 1;
                transform: translateY(0);
            }

            to {
                opacity: 0;
                transform: translateY(-20px);
            }
        }

        .pulse-soft {
            animation: pulseSoft 2s infinite;
        }

        @keyframes pulseSoft {

            0%,
            100% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.05);
                opacity: 0.8;
            }
        }
    </style>
</head>

<body class="min-h-screen flex items-center justify-center">
    <div class="text-center fade-out-up">
        <div class="mb-8 relative inline-block">
            <div class="w-20 h-20 bg-mentta-primary rounded-3xl flex items-center justify-center shadow-2xl pulse-soft">
                <span class="text-white font-bold text-4xl">M</span>
            </div>
            <div
                class="absolute -bottom-2 -right-2 w-8 h-8 bg-mentta-secondary rounded-full flex items-center justify-center shadow-lg">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                </svg>
            </div>
        </div>

        <h2 class="text-3xl font-bold text-mentta-primary mb-3">Sesión cerrada</h2>
        <p class="text-mentta-accent font-medium">Gracias por confiar en tu bienestar.</p>

        <div class="mt-10 flex flex-col items-center">
            <p class="text-[10px] text-gray-400 uppercase tracking-[0.3em] font-bold mb-4">Redirigiendo</p>
            <div class="flex gap-1.5">
                <div class="w-1.5 h-1.5 rounded-full bg-mentta-primary animate-bounce" style="animation-delay: 0.1s">
                </div>
                <div class="w-1.5 h-1.5 rounded-full bg-mentta-primary animate-bounce" style="animation-delay: 0.2s">
                </div>
                <div class="w-1.5 h-1.5 rounded-full bg-mentta-primary animate-bounce" style="animation-delay: 0.3s">
                </div>
            </div>
        </div>
    </div>

    <script>
        // Redirigir después de la animación
        setTimeout(() => {
            window.location.href = 'index.php';
        }, 1800);
    </script>
</body>

</html>