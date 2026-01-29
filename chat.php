<?php
/**
 * MENTTA - Chat Interface
 * Interfaz de chat para pacientes - Mobile-first design
 */

require_once 'includes/config.php';
require_once 'includes/auth.php';

// Requiere autenticación como paciente
$user = requireAuth('patient');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#6366F1">
    <title>Chat - Mentta</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        mentta: {
                            50: '#EEF2FF',
                            100: '#E0E7FF',
                            200: '#C7D2FE',
                            300: '#A5B4FC',
                            400: '#818CF8',
                            500: '#6366F1',
                            600: '#4F46E5',
                            700: '#4338CA',
                            800: '#3730A3',
                            900: '#312E81'
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/chat.css">
    
    <!-- PWA Meta -->
    <link rel="apple-touch-icon" href="assets/images/icon-192.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
</head>
<body class="bg-gray-50 antialiased">
    <!-- Header -->
    <header class="fixed top-0 left-0 right-0 bg-white/95 backdrop-blur-sm border-b border-gray-100 z-50">
        <div class="max-w-2xl mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-mentta-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg shadow-mentta-500/20">
                    <span class="text-white font-bold text-lg">M</span>
                </div>
                <div>
                    <h1 class="text-lg font-semibold text-gray-800">Mentta</h1>
                    <p class="text-xs text-gray-500">Tu espacio seguro</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-600 hidden sm:inline"><?= htmlspecialchars($user['name']) ?></span>
                <button onclick="confirmLogout()" class="p-2 text-gray-400 hover:text-gray-600 transition-colors" title="Cerrar sesión">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Chat Area -->
    <main class="pt-16 pb-36">
        <div class="max-w-2xl mx-auto">
            <!-- Messages Container -->
            <div id="messagesContainer" class="min-h-[calc(100vh-13rem)] px-4 py-6 space-y-4">
                <!-- Welcome message -->
                <div id="welcomeMessage" class="text-center py-8">
                    <div class="w-20 h-20 bg-gradient-to-br from-mentta-500 to-purple-600 rounded-2xl mx-auto mb-4 flex items-center justify-center shadow-xl shadow-mentta-500/30">
                        <span class="text-white font-bold text-3xl">M</span>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-800 mb-2">Hola, <?= htmlspecialchars(explode(' ', $user['name'])[0]) ?> 👋</h2>
                    <p class="text-gray-500 max-w-sm mx-auto">Estoy aquí para escucharte. Cuéntame, ¿cómo te sientes hoy?</p>
                </div>
                
                <!-- Messages will be rendered here -->
            </div>
        </div>
    </main>

    <!-- Sentiment Indicator (minimal) -->
    <div id="sentimentIndicator" class="fixed bottom-32 left-1/2 transform -translate-x-1/2 hidden">
        <div class="bg-white/95 backdrop-blur-sm rounded-full px-4 py-2 shadow-lg border border-gray-100 flex items-center gap-3">
            <span class="text-xs text-gray-500">Tu ánimo:</span>
            <div class="flex gap-1">
                <div id="moodDot1" class="w-2 h-2 rounded-full bg-gray-200 transition-colors"></div>
                <div id="moodDot2" class="w-2 h-2 rounded-full bg-gray-200 transition-colors"></div>
                <div id="moodDot3" class="w-2 h-2 rounded-full bg-gray-200 transition-colors"></div>
                <div id="moodDot4" class="w-2 h-2 rounded-full bg-gray-200 transition-colors"></div>
                <div id="moodDot5" class="w-2 h-2 rounded-full bg-gray-200 transition-colors"></div>
            </div>
        </div>
    </div>

    <!-- Loading Indicator -->
    <div id="loadingIndicator" class="fixed bottom-32 left-1/2 transform -translate-x-1/2 hidden">
        <div class="bg-white/95 backdrop-blur-sm rounded-full px-5 py-3 shadow-lg border border-gray-100 flex items-center gap-3">
            <div class="flex gap-1">
                <div class="w-2 h-2 rounded-full bg-mentta-500 animate-bounce" style="animation-delay: 0ms"></div>
                <div class="w-2 h-2 rounded-full bg-mentta-500 animate-bounce" style="animation-delay: 150ms"></div>
                <div class="w-2 h-2 rounded-full bg-mentta-500 animate-bounce" style="animation-delay: 300ms"></div>
            </div>
            <span class="text-sm text-gray-600">Mentta está escribiendo...</span>
        </div>
    </div>

    <!-- Input Area -->
    <div class="fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-sm border-t border-gray-100">
        <div class="max-w-2xl mx-auto px-4 py-4">
            <div class="bg-gray-50 rounded-2xl border border-gray-200 focus-within:border-mentta-400 focus-within:ring-2 focus-within:ring-mentta-100 transition-all">
                <div class="flex items-end gap-2 p-2">
                    <textarea 
                        id="messageInput"
                        placeholder="Escribe lo que sientes..."
                        class="flex-1 bg-transparent resize-none text-gray-800 placeholder-gray-400 focus:outline-none px-2 py-2 min-h-[44px] max-h-[120px]"
                        rows="1"
                        onkeydown="handleKeyDown(event)"
                        oninput="autoResize(this)"
                    ></textarea>
                    <button 
                        id="sendButton"
                        onclick="sendMessage()"
                        class="w-10 h-10 bg-gradient-to-br from-mentta-500 to-purple-600 text-white rounded-xl flex items-center justify-center hover:shadow-lg hover:shadow-mentta-500/30 transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none"
                        disabled
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                        </svg>
                    </button>
                </div>
            </div>
            <p class="text-center text-xs text-gray-400 mt-2">Todo lo que compartas es confidencial y seguro 🔒</p>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div id="logoutModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-2xl p-6 max-w-sm w-full shadow-xl">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">¿Cerrar sesión?</h3>
            <p class="text-gray-600 mb-6">Siempre puedes volver cuando necesites hablar. Cuídate mucho. 💜</p>
            <div class="flex gap-3">
                <button onclick="closeLogoutModal()" class="flex-1 py-2.5 px-4 rounded-xl border border-gray-200 text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancelar
                </button>
                <button onclick="logout()" class="flex-1 py-2.5 px-4 rounded-xl bg-mentta-500 text-white hover:bg-mentta-600 transition-colors">
                    Salir
                </button>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="assets/js/utils.js"></script>
    <script src="assets/js/chat.js"></script>
</body>
</html>
