<?php
/**
 * MENTTA - Chat Interface
 * Interfaz de chat para pacientes - Mobile-first design
 * v1.1 - Con menú hamburguesa estilo Claude.ai
 */

require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Requiere autenticación como paciente
$user = requireAuth('patient');

// Obtener preferencias del usuario
$theme = $user['theme_preference'] ?? 'light';

// Obtener conteo de notificaciones no leídas
$notifCount = 0;
try {
    $db = getDB();
    $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = FALSE");
    $stmt->execute([$user['id']]);
    $notifCount = $stmt->fetchColumn() ?: 0;
} catch (Exception $e) {
    // Silent fail
}

// Obtener estado de ánimo de la semana (promedio)
$weekMood = null;
try {
    $stmt = $db->prepare("
        SELECT AVG(JSON_EXTRACT(sentiment_score, '$.positive')) as avg_positive
        FROM conversations 
        WHERE patient_id = ? 
          AND sender = 'user' 
          AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
          AND sentiment_score IS NOT NULL
    ");
    $stmt->execute([$user['id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result && $result['avg_positive'] !== null) {
        $weekMood = floatval($result['avg_positive']);
    }
} catch (Exception $e) {
    // Silent fail
}

// Determinar emoji de ánimo
$moodEmoji = '😊';
if ($weekMood !== null) {
    if ($weekMood >= 0.6)
        $moodEmoji = '😊';
    elseif ($weekMood >= 0.4)
        $moodEmoji = '😐';
    else
        $moodEmoji = '😔';
}

// UX-002: Determinar saludo según hora del día
$hour = (int) date('H');
$greeting = 'Hola';
$contextMessage = '¿Cómo te sientes hoy?';
if ($hour >= 5 && $hour < 12) {
    $greeting = 'Buenos días';
    $contextMessage = '¿Cómo amaneciste hoy?';
} elseif ($hour >= 12 && $hour < 18) {
    $greeting = 'Buenas tardes';
    $contextMessage = '¿Cómo va tu día?';
} elseif ($hour >= 18 || $hour < 5) {
    $greeting = 'Buenas noches';
    $contextMessage = '¿Cómo te encuentras esta noche?';
}
?>
<!DOCTYPE html>
<html lang="es" data-theme="<?= htmlspecialchars($theme) ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="<?= $theme === 'dark' ? '#1F2937' : '#6366F1' ?>">
    <title>Chat - Mentta</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'mentta-primary': '#2d3a2d',
                        'mentta-secondary': '#cbaa8e',
                        'mentta-accent': '#8b9d8b',
                        'mentta-light': '#f5f5f0',
                        mentta: {
                            50: '#f0f2f0',
                            100: '#e8f0e8',
                            200: '#d1dbd1',
                            300: '#b4c2b4',
                            400: '#8b9d8b',
                            500: '#2d3a2d',
                            600: '#1e261e',
                            700: '#151a15',
                            800: '#0c0f0c',
                            900: '#000000'
                        }
                    }
                }
            }
        }
    </script>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/theme.css">
    <link rel="stylesheet" href="assets/css/chat.css">

    <!-- PWA Meta -->
    <link rel="apple-touch-icon" href="assets/images/icon-192.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
</head>

<body class="antialiased" style="background-color: var(--bg-primary);">

    <!-- Sidebar Backdrop -->
    <div id="sidebar-backdrop" class="sidebar-backdrop"></div>

    <!-- Sidebar Menu -->
    <aside id="sidebar-menu" class="sidebar-menu">
        <!-- Header -->
        <div class="sidebar-header" style="background-color: var(--bg-tertiary);">
            <div class="sidebar-user-info">
                <h3 style="color: var(--text-primary); font-family: 'Playfair Display', serif;">
                    <?= htmlspecialchars($user['name']) ?>
                </h3>
                <p><?= htmlspecialchars($user['email']) ?></p>
                <div class="mood-badge"
                    style="background: var(--bg-secondary); border: 1px solid var(--border-color); padding: 4px 10px; border-radius: 99px;">
                    <?= $moodEmoji ?> <span
                        style="font-size: 0.7rem; font-weight: 600; text-transform: uppercase; tracking: 0.05em; margin-left: 4px;">Ánimo
                        Semanal</span>
                </div>
            </div>
            <button class="sidebar-close" onclick="closeMenu()" aria-label="Cerrar menú">&times;</button>
        </div>

        <!-- Content -->
        <div class="sidebar-content">
            <!-- Main Actions -->
            <div class="sidebar-section">
                <button class="menu-btn group" onclick="newChat()"
                    style="background: white; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); margin-bottom: 0.75rem;">
                    <span class="menu-btn-icon text-mentta-primary group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                    </span>
                    <span class="font-bold text-mentta-primary text-sm">Nuevo Chat</span>
                </button>
                <button class="menu-btn group" onclick="window.location.href='map.php'"
                    style="background: white; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); margin-bottom: 0.75rem;">
                    <span class="menu-btn-icon text-mentta-primary group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </span>
                    <span class="font-bold text-mentta-primary text-sm">Mapa de Centros</span>
                </button>
                <button class="menu-btn menu-btn-crisis group" onclick="openCrisisModal()"
                    style="background-color: #fef2f2; border: 2px solid #fee2e2; box-shadow: 0 4px 10px rgba(220, 38, 38, 0.1); margin-bottom: 0.75rem;">
                    <span class="menu-btn-icon text-red-600 group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </span>
                    <span class="font-black text-red-700 uppercase tracking-tighter text-xs">Ayuda Inmediata</span>
                </button>
                <button class="menu-btn group" onclick="openResourcesModal()"
                    style="background: white; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); margin-bottom: 1.5rem;">
                    <span class="menu-btn-icon text-mentta-primary group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    </span>
                    <span class="font-bold text-mentta-primary text-sm">Recursos de Bienestar</span>
                </button>
                <button class="menu-btn menu-btn-live hover:brightness-110" onclick="openLiveCallModal()"
                    style="background: #cbaa8e; color: white; border-radius: 1.25rem; box-shadow: 0 15px 30px -5px rgba(203, 170, 142, 0.5); border: 2px solid rgba(255,255,255,0.2); padding: 1.25rem; justify-content: center;">
                    <span class="menu-btn-icon">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </span>
                    <span class="font-black tracking-[0.25em] text-[11px] uppercase">Mentta Live</span>
                </button>
            </div>

            <!-- Notifications (if any) -->
            <?php if ($notifCount > 0): ?>
                <div class="notifications-section">
                    <div class="sidebar-section-title">Novedades</div>
                    <div id="notifications-list">
                        <!-- Populated by JS -->
                    </div>
                </div>
            <?php endif; ?>

            <!-- Chat History -->
            <div class="sidebar-section">
                <div class="sidebar-section-title">Historial</div>
                <div class="px-3 mb-3">
                    <input type="text" id="chat-search" placeholder="Reflexiones pasadas..."
                        oninput="Menu.filterChatHistory(this.value)" class="w-full px-4 py-2.5 rounded-xl text-xs"
                        style="background: var(--bg-secondary); border: 1px solid var(--border-color); color: var(--text-primary); focus: outline: none;">
                </div>
                <div id="chat-history-list" class="chat-history-list">
                    <!-- Populated by JS -->
                    <div class="px-4 py-3 text-center">
                        <p style="color: var(--text-tertiary); font-size: 0.75rem;">Cargando historial...</p>
                    </div>
                </div>
            </div>
        </div>

        </div>
    </aside>
    </aside>

    <!-- Analysis Paused Banner -->
    <div id="analysis-paused-banner" class="analysis-paused-banner"></div>

    <!-- Header -->
    <header class="fixed top-0 left-0 right-0 z-50 backdrop-blur-md"
        style="background-color: rgba(255, 255, 255, 0.8); border-bottom: 1px solid var(--border-color);">
        <div class="max-w-2xl mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <!-- Hamburger Button with Badge -->
                <button class="hamburger-btn" onclick="toggleMenu()" aria-label="Abrir menú"
                    style="background: var(--bg-tertiary); border-radius: 12px;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <span id="notification-badge" class="notification-badge <?= $notifCount > 0 ? '' : 'hidden' ?>"
                        style="background-color: var(--accent-secondary);">
                        <?= $notifCount > 99 ? '99+' : $notifCount ?>
                    </span>
                </button>

                <div
                    class="w-10 h-10 bg-mentta-500 rounded-xl flex items-center justify-center shadow-lg shadow-mentta-500/20">
                    <span class="text-white font-bold text-lg">M</span>
                </div>
                <div>
                    <h1 class="text-lg font-bold"
                        style="color: var(--text-primary); font-family: 'Playfair Display', serif; line-height: 1;">
                        Mentta</h1>
                    <p class="text-[10px] uppercase tracking-widest font-bold" style="color: var(--text-tertiary);">
                        Soporte Elite</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="confirmLogout()" class="p-2.5 rounded-xl transition-all hover:bg-red-50 group"
                    style="color: var(--text-tertiary);" title="Cerrar sesión">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 group-hover:text-red-500" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Chat Area -->
    <main class="pt-20 pb-40">
        <div class="max-w-2xl mx-auto">
            <!-- Messages Container -->
            <div id="messagesContainer" class="min-h-[calc(100vh-14rem)] px-4 py-6 space-y-6">
                <!-- Welcome message - UX-002 FIXED -->
                <div id="welcomeMessage" class="text-center py-12 animate-fade">
                    <div
                        class="w-20 h-20 bg-gradient-to-br from-mentta-400 to-mentta-600 rounded-[2rem] mx-auto mb-6 flex items-center justify-center shadow-2xl shadow-mentta-500/20 rotate-3">
                        <span class="text-white font-bold text-3xl">- M -</span>
                    </div>
                    <h2 class="text-3xl font-bold mb-3"
                        style="color: var(--text-primary); font-family: 'Playfair Display', serif;">
                        <?= htmlspecialchars($greeting) ?>,
                        <?= htmlspecialchars(explode(' ', $user['name'])[0]) ?>
                    </h2>
                    <p style="color: var(--text-secondary);" class="max-w-md mx-auto text-lg leading-relaxed">
                        <?= htmlspecialchars($contextMessage) ?> Tómate tu tiempo, este es tu refugio.
                    </p>
                    <?php if ($weekMood !== null && $weekMood < 0.4): ?>
                        <div class="mt-6 p-4 bg-mentta-100 rounded-2xl inline-block border border-mentta-200">
                            <p class="text-sm font-medium" style="color: var(--text-primary);">Veo que has tenido una semana
                                intensa. Estoy aquí para procesarlo contigo. 🌿</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Messages will be rendered here -->
            </div>
        </div>
    </main>

    <!-- Sentiment Indicator (minimal) -->
    <div id="sentimentIndicator" class="fixed bottom-36 left-1/2 transform -translate-x-1/2 hidden z-40">
        <div class="glass-card rounded-full px-5 py-2.5 shadow-xl flex items-center gap-4"
            style="border: 1px solid var(--border-color);">
            <span class="text-[10px] font-bold uppercase tracking-widest text-mentta-accent">Tu Energía</span>
            <div class="flex gap-1.5">
                <div id="moodDot1" class="w-1.5 h-1.5 rounded-full transition-all duration-500 bg-gray-200"></div>
                <div id="moodDot2" class="w-1.5 h-1.5 rounded-full transition-all duration-500 bg-gray-200"></div>
                <div id="moodDot3" class="w-1.5 h-1.5 rounded-full transition-all duration-500 bg-gray-200"></div>
                <div id="moodDot4" class="w-1.5 h-1.5 rounded-full transition-all duration-500 bg-gray-200"></div>
                <div id="moodDot5" class="w-1.5 h-1.5 rounded-full transition-all duration-500 bg-gray-200"></div>
            </div>
        </div>
    </div>

    <!-- Loading Indicator -->
    <div id="loadingIndicator" class="fixed bottom-36 left-1/2 transform -translate-x-1/2 hidden z-40">
        <div class="glass-card rounded-2xl px-6 py-4 shadow-2xl flex items-center gap-4 border border-white/50">
            <div class="flex gap-1.5">
                <div class="w-2 h-2 rounded-full bg-mentta-500 animate-bounce"
                    style="animation-duration: 0.8s; animation-delay: 0ms"></div>
                <div class="w-2 h-2 rounded-full bg-mentta-500 animate-bounce"
                    style="animation-duration: 0.8s; animation-delay: 200ms"></div>
                <div class="w-2 h-2 rounded-full bg-mentta-500 animate-bounce"
                    style="animation-duration: 0.8s; animation-delay: 400ms"></div>
            </div>
            <span id="loadingText"
                class="text-xs font-bold text-mentta-primary uppercase tracking-widest">Analizando...</span>
        </div>
    </div>

    <!-- Input Area -->
    <div class="fixed bottom-0 left-0 right-0 z-50 backdrop-blur-lg"
        style="background-color: rgba(255, 255, 255, 0.7); border-top: 1px solid var(--border-color);">
        <div class="max-w-2xl mx-auto px-4 py-6">
            <div class="rounded-3xl shadow-xl transition-all overflow-hidden"
                style="background-color: white; border: 1px solid var(--border-color); box-shadow: 0 10px 40px -10px rgba(45, 58, 45, 0.15);">
                <div class="flex items-end gap-3 p-3">
                    <textarea id="messageInput" placeholder="Escribe lo que sientes..."
                        class="flex-1 bg-transparent resize-none focus:outline-none px-3 py-3 min-h-[50px] max-h-[160px] text-base leading-relaxed"
                        style="color: var(--text-primary);" rows="1" onkeydown="handleKeyDown(event)"
                        oninput="autoResize(this)"></textarea>
                    <button id="sendButton" onclick="sendMessage()"
                        class="w-12 h-12 bg-mentta-primary text-white rounded-2xl flex items-center justify-center hover:shadow-lg hover:bg-mentta-600 transition-all disabled:opacity-30 disabled:cursor-not-allowed disabled:shadow-none"
                        disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                            <path
                                d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                        </svg>
                    </button>
                </div>
            </div>
            <p class="text-center text-[10px] font-bold uppercase tracking-[0.2em] mt-4"
                style="color: var(--text-tertiary);">Espacio Privado • Encriptado • Seguro</p>
        </div>
    </div>

    <!-- Live Call FAB Button -->
    <button id="liveCallFab" onclick="openLiveCallModal()"
        class="fixed right-6 bottom-40 z-40 w-16 h-16 rounded-[1.5rem] shadow-[0_20px_50px_rgba(203,170,142,0.6)] flex items-center justify-center text-white hover:scale-110 active:scale-95 transition-all group overflow-hidden border-2 border-white/20"
        style="background-color: #cbaa8e;" title="Sesión Mentta Live">
        <div
            class="absolute inset-0 bg-gradient-to-tr from-white/0 to-white/20 opacity-0 group-hover:opacity-100 transition-opacity">
        </div>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 relative z-10" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="3.5">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
        </svg>
    </button>

    <!-- ========================================
         MODALS
         ======================================== -->

    <!-- Logout Confirmation Modal -->
    <div id="logoutModal" class="modal-overlay">
        <div class="modal-content" style="max-width: 24rem;">
            <div class="modal-body text-center py-6">
                <h3 class="text-lg font-semibold mb-2" style="color: var(--text-primary);">¿Cerrar sesión?</h3>
                <p style="color: var(--text-secondary);" class="mb-6">Siempre puedes volver cuando necesites hablar.
                    Cuídate mucho. 💜</p>
                <div class="flex gap-3">
                    <button onclick="closeLogoutModal()" class="flex-1 py-2.5 px-4 rounded-xl transition-colors"
                        style="border: 1px solid var(--border-color); color: var(--text-primary); background-color: var(--bg-tertiary);">
                        Cancelar
                    </button>
                    <button onclick="logout()"
                        class="flex-1 py-2.5 px-4 rounded-xl bg-mentta-500 text-white hover:bg-mentta-600 transition-colors">
                        Salir
                    </button>
                </div>
            </div>
        </div>
    </div>


    <!-- Map is now a full page at map.php -->

    <!-- Crisis Contact Modal -->
    <div id="crisis-modal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header" style="background: #fef2f2;">
                <h3 class="modal-title" style="color: #991b1b; font-weight: 800;">🆘 Ayuda Inmediata</h3>
                <button class="modal-close" onclick="closeCrisisModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 1.5rem;">
                    Si te encuentras en una situación difícil, por favor elige una opción para recibir apoyo ahora:
                </p>

                <div class="crisis-option" onclick="selectCrisisOption('psychologist')"
                    style="border-left: 4px solid #ef4444; background: white; margin-bottom: 1rem; box-shadow: var(--shadow-sm);">
                    <span class="crisis-option-icon">👨‍⚕️</span>
                    <div class="crisis-option-content">
                        <h4 style="color: #991b1b; font-weight: 700;">Contactar a mi Psicólogo</h4>
                        <p>Notificar a tu profesional vinculado</p>
                    </div>
                </div>

                <div class="crisis-option" onclick="selectCrisisOption('emergency_contact')"
                    style="border-left: 4px solid #ef4444; background: white; margin-bottom: 1rem; box-shadow: var(--shadow-sm);">
                    <span class="crisis-option-icon">👪</span>
                    <div class="crisis-option-content">
                        <h4 style="color: #991b1b; font-weight: 700;">Contacto de Emergencia</h4>
                        <p>Avisar a tu persona de confianza</p>
                    </div>
                </div>

                <div class="crisis-option" onclick="window.location.href='tel:113'"
                    style="background: #ef4444; border: none; color: white; padding: 1.25rem;">
                    <span class="crisis-option-icon">📞</span>
                    <div class="crisis-option-content">
                        <h4 style="color: white; font-weight: 800; font-size: 1.1rem;">Línea de Crisis 113</h4>
                        <p style="color: rgba(255,255,255,0.9);">Llamar urgentemente ahora mismo</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="crisis-option" onclick="selectCrisisOption('calming_exercises')">
        <span class="crisis-option-icon">🧘</span>
        <div class="crisis-option-content">
            <h4>Ejercicios de Calma Rápida</h4>
            <p>Técnicas de respiración y grounding</p>
        </div>
    </div>
    </div>
    </div>
    </div>

    <!-- Resources Modal -->
    <div id="resources-modal" class="modal-overlay">
        <div class="modal-content" style="max-height: 90vh; max-width: 32rem;">
            <div class="modal-header" style="background: var(--bg-tertiary);">
                <h3 class="modal-title" style="color: var(--text-primary); font-family: 'Playfair Display', serif;">🌿
                    Recursos de Bienestar</h3>
                <button class="modal-close" onclick="closeResourcesModal()">&times;</button>
            </div>
            <div class="modal-body" style="overflow-y: auto; padding: 1.5rem;">

                <!-- Breathing Exercises - UX-001 FIXED: Herramienta interactiva -->
                <div id="resource-breathing" class="resource-card"
                    style="border: 1px solid rgba(45, 58, 45, 0.1); border-radius: 1.5rem; overflow: hidden; margin-bottom: 1.5rem; box-shadow: var(--shadow-sm); background: white;">
                    <div class="resource-card-header" onclick="toggleResourceCard('resource-breathing')"
                        style="background: white; padding: 1.5rem; border-bottom: 1px solid #f8faf8;">
                        <span class="resource-card-title"
                            style="font-weight: 800; color: #2d3a2d; font-family: 'Playfair Display', serif; font-size: 1.1rem;">
                            <span class="resource-card-icon">🌿</span>
                            Guía de Respiración Consciente
                        </span>
                    </div>
                    <div class="resource-card-content" style="padding: 2rem; background: white;">
                        <!-- Ultra-Clean Breathing Timer -->
                        <div id="breathing-timer" class="mb-6 text-center">

                            <!-- SVG Progress Ring -->
                            <div class="relative w-56 h-56 mx-auto mb-10 flex items-center justify-center">
                                <svg class="absolute inset-0 w-full h-full -rotate-90">
                                    <circle cx="112" cy="112" r="100" stroke="#f1f3f1" stroke-width="6"
                                        fill="transparent" />
                                    <circle id="breathing-progress-ring" cx="112" cy="112" r="100" stroke="#2d3a2d"
                                        stroke-width="6" fill="transparent" stroke-dasharray="628"
                                        stroke-dashoffset="628" stroke-linecap="round"
                                        class="transition-all duration-1000 ease-linear" />
                                </svg>

                                <!-- Core Circle -->
                                <div id="breathing-circle"
                                    class="w-40 h-40 rounded-full flex flex-col items-center justify-center transition-all duration-1000 relative z-10"
                                    style="background: #2d3a2d; box-shadow: 0 30px 60px -12px rgba(45, 58, 45, 0.25);">
                                    <span id="breathing-text"
                                        class="text-white font-black text-2xl uppercase tracking-[0.2em]">...</span>
                                    <span id="breathing-subtext"
                                        class="text-white/40 text-[9px] uppercase font-bold tracking-widest mt-1">Calma</span>
                                </div>
                            </div>

                            <div class="flex flex-col gap-4 items-center">
                                <button id="breathing-start-btn" onclick="startBreathingExercise()"
                                    class="bg-mentta-primary text-white px-10 py-4 rounded-2xl font-bold text-[11px] uppercase tracking-[0.2em] shadow-xl hover:bg-black transition-all">Iniciar
                                    Respiración</button>
                                <button id="breathing-stop-btn" onclick="stopBreathingExercise()"
                                    class="hidden text-red-500 font-bold text-[10px] uppercase tracking-widest border-b border-red-100 pb-1">Finalizar
                                    ejercicio</button>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-3 border-t border-gray-50 pt-8 mt-4">
                            <div class="text-center">
                                <div class="text-xs font-bold text-gray-400 uppercase tracking-tighter mb-1">Inhala
                                </div>
                                <div class="font-black text-mentta-primary text-xl">4s</div>
                            </div>
                            <div class="text-center border-x border-gray-50">
                                <div class="text-xs font-bold text-gray-400 uppercase tracking-tighter mb-1">Mantén
                                </div>
                                <div class="font-black text-stone-600 text-xl">7s</div>
                            </div>
                            <div class="text-center">
                                <div class="text-xs font-bold text-gray-400 uppercase tracking-tighter mb-1">Exhala
                                </div>
                                <div class="font-black text-mentta-accent text-xl">8s</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Grounding 5-4-3-2-1 - UX-001 FIXED: Checklist interactivo -->
                <div id="resource-grounding" class="resource-card"
                    style="border: 1px solid rgba(45, 58, 45, 0.1); border-radius: 1.25rem; overflow: hidden; margin-bottom: 1.25rem; box-shadow: var(--shadow-sm);">
                    <div class="resource-card-header" onclick="toggleResourceCard('resource-grounding')"
                        style="background: #fbfbf9; padding: 1.25rem;">
                        <span class="resource-card-title" style="font-weight: 700; color: #2d3a2d;">
                            <span class="resource-card-icon">🧠</span>
                            Conexión con el Presente (5-4-3-2-1)
                        </span>
                        <span class="resource-card-chevron" style="color: #8b9d8b;">▼</span>
                    </div>
                    <div class="resource-card-content" style="padding: 1.5rem; background: white;">
                        <p class="text-sm mb-6" style="color: #4b5563;">Esta técnica te ayuda a calmar la ansiedad
                            reconectando con tus sentidos. <strong>Toca cada paso al completarlo:</strong></p>

                        <!-- Checklist interactivo -->
                        <div id="grounding-checklist" class="mt-4 space-y-3">
                            <div class="grounding-item p-4 rounded-2xl cursor-pointer transition-all active:scale-[0.98]"
                                onclick="toggleGroundingItem(this, 1)"
                                style="border: 1px solid #eef0ee; background: #fbfbf9;">
                                <div class="flex items-center gap-4">
                                    <div class="grounding-check w-10 h-10 rounded-xl flex items-center justify-center text-lg font-bold"
                                        style="background: white; border: 1px solid #e2e8e2; color: #2d3a2d;">
                                        5</div>
                                    <div>
                                        <h5 class="font-bold text-sm" style="color: #2d3a2d;">Cosas que VES</h5>
                                        <p class="text-xs" style="color: #8b9d8b;">Detente y nombra 5 objetos</p>
                                    </div>
                                </div>
                            </div>
                            <!-- ... repeat styling for others ... -->
                            <div class="grounding-item p-4 rounded-2xl cursor-pointer transition-all active:scale-[0.98]"
                                onclick="toggleGroundingItem(this, 2)"
                                style="border: 1px solid #eef0ee; background: #fbfbf9;">
                                <div class="flex items-center gap-4">
                                    <div class="grounding-check w-10 h-10 rounded-xl flex items-center justify-center text-lg font-bold"
                                        style="background: white; border: 1px solid #e2e8e2; color: #2d3a2d;">
                                        4</div>
                                    <div>
                                        <h5 class="font-bold text-sm" style="color: #2d3a2d;">Cosas que TOCAS</h5>
                                        <p class="text-xs" style="color: #8b9d8b;">Siente texturas a tu alrededor</p>
                                    </div>
                                </div>
                            </div>
                            <div class="grounding-item p-4 rounded-2xl cursor-pointer transition-all active:scale-[0.98]"
                                onclick="toggleGroundingItem(this, 3)"
                                style="border: 1px solid #eef0ee; background: #fbfbf9;">
                                <div class="flex items-center gap-4">
                                    <div class="grounding-check w-10 h-10 rounded-xl flex items-center justify-center text-lg font-bold"
                                        style="background: white; border: 1px solid #e2e8e2; color: #2d3a2d;">
                                        3</div>
                                    <div>
                                        <h5 class="font-bold text-sm" style="color: #2d3a2d;">Cosas que ESCUCHAS</h5>
                                        <p class="text-xs" style="color: #8b9d8b;">Escucha sonidos lejanos o cercanos
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="grounding-item p-4 rounded-2xl cursor-pointer transition-all active:scale-[0.98]"
                                onclick="toggleGroundingItem(this, 4)"
                                style="border: 1px solid #eef0ee; background: #fbfbf9;">
                                <div class="flex items-center gap-4">
                                    <div class="grounding-check w-10 h-10 rounded-xl flex items-center justify-center text-lg font-bold"
                                        style="background: white; border: 1px solid #e2e8e2; color: #2d3a2d;">
                                        2</div>
                                    <div>
                                        <h5 class="font-bold text-sm" style="color: #2d3a2d;">Cosas que HUELES</h5>
                                        <p class="text-xs" style="color: #8b9d8b;">Identifica olores en el ambiente</p>
                                    </div>
                                </div>
                            </div>
                            <div class="grounding-item p-4 rounded-2xl cursor-pointer transition-all active:scale-[0.98]"
                                onclick="toggleGroundingItem(this, 5)"
                                style="border: 1px solid #eef0ee; background: #fbfbf9;">
                                <div class="flex items-center gap-4">
                                    <div class="grounding-check w-10 h-10 rounded-xl flex items-center justify-center text-lg font-bold"
                                        style="background: white; border: 1px solid #e2e8e2; color: #2d3a2d;">
                                        1</div>
                                    <div>
                                        <h5 class="font-bold text-sm" style="color: #2d3a2d;">Cosa que SABOREAS</h5>
                                        <p class="text-xs" style="color: #8b9d8b;">Presta atención al gusto en tu boca
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="grounding-complete" class="hidden mt-6 p-6 rounded-3xl text-center"
                            style="background: #f0fdf4; border: 1px solid #dcfce7;">
                            <span class="text-3xl">�</span>
                            <p class="font-bold mt-3 text-green-800">¡Muy bien hecho!</p>
                            <p class="text-xs text-green-600 mb-4">Has regresado al presente con éxito.</p>
                            <button onclick="resetGroundingChecklist()"
                                class="text-xs font-bold uppercase tracking-widest text-green-700 hover:text-green-900 border-b border-green-200">Reiniciar</button>
                        </div>
                    </div>
                </div>

                <!-- Crisis Lines -->
                <div id="resource-crisis-lines" class="resource-card">
                    <div class="resource-card-header" onclick="toggleResourceCard('resource-crisis-lines')">
                        <span class="resource-card-title">
                            <span class="resource-card-icon">📞</span>
                            Líneas de Crisis
                        </span>
                        <span class="resource-card-chevron">▼</span>
                    </div>
                    <div class="resource-card-content">
                        <p>Si necesitas hablar con alguien <strong>AHORA</strong>:</p>

                        <div class="crisis-lines-list">
                            <div class="crisis-line-country">🇵🇪 Perú</div>
                            <div class="crisis-line-item">
                                <span class="crisis-line-name">Emergencias</span>
                                <a href="tel:113" class="crisis-call-btn">📞 113</a>
                            </div>
                            <div class="crisis-line-item">
                                <span class="crisis-line-name">Línea de Prevención del Suicidio</span>
                                <a href="tel:0800-11-878" class="crisis-call-btn">📞 0800-11-878</a>
                            </div>

                            <div class="crisis-line-country">🌎 Internacional</div>
                            <div class="crisis-line-item">
                                <span class="crisis-line-name">Lifeline (USA)</span>
                                <span class="crisis-line-number">988</span>
                            </div>
                            <div class="crisis-line-item">
                                <span class="crisis-line-name">Samaritans (UK)</span>
                                <span class="crisis-line-number">116 123</span>
                            </div>
                        </div>

                        <p style="margin-top: 1rem; font-style: italic; text-align: center; color: var(--success);">
                            Recuerda: Pedir ayuda es un acto de valentía, no de debilidad.
                        </p>
                    </div>
                </div>

                <!-- Supportive Phrases -->
                <div id="resource-phrases" class="resource-card">
                    <div class="resource-card-header" onclick="toggleResourceCard('resource-phrases')">
                        <span class="resource-card-title">
                            <span class="resource-card-icon">💭</span>
                            Frases de Apoyo
                        </span>
                        <span class="resource-card-chevron">▼</span>
                    </div>
                    <div class="resource-card-content">
                        <div id="supportive-phrase" class="supportive-phrase">
                            <p>"Esto que sientes es temporal. Has superado días difíciles antes y lo harás de nuevo."
                            </p>
                        </div>
                        <button class="resource-btn resource-btn-secondary" onclick="showNewPhrase()"
                            style="width: 100%;">
                            Ver otra frase
                        </button>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button class="resource-btn" onclick="closeResourcesModal()">Volver al chat</button>
            </div>
        </div>
    </div>

    <!-- Live Call Modal -->
    <div id="live-call-modal" class="modal-overlay">
        <div class="modal-content" style="max-width: 28rem;">
            <div class="modal-header" style="background: var(--bg-tertiary);">
                <h3 class="modal-title" style="color: var(--text-primary); font-family: 'Playfair Display', serif;">📽️
                    Mentta Live</h3>
                <button class="modal-close" onclick="closeLiveCallModal()">&times;</button>
            </div>
            <div class="modal-body text-center py-8">
                <div class="w-24 h-24 rounded-[2rem] mx-auto mb-8 flex items-center justify-center shadow-2xl rotate-3"
                    style="background-color: #2d3a2d;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold mb-3"
                    style="color: var(--text-primary); font-family: 'Playfair Display', serif;">Sesión en Tiempo Real
                </h2>
                <p style="color: var(--text-secondary);" class="mb-6 text-base leading-relaxed">
                    Conéctate con nuestra IA de apoyo emocional a través de voz y video para una experiencia más humana
                    y profunda.
                </p>

                <div class="bg-mentta-50 border border-mentta-100 rounded-2xl p-5 mb-8 text-left">
                    <h4 class="font-bold text-mentta-primary mb-3 text-xs uppercase tracking-widest">Preparación:</h4>
                    <ul class="text-sm space-y-2" style="color: var(--text-secondary);">
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-mentta-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            Busca un lugar tranquilo y privado
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-mentta-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            Verifica tu micrófono y cámara
                        </li>
                    </ul>
                </div>

                <div class="flex gap-4">
                    <button onclick="closeLiveCallModal()"
                        class="flex-1 py-4 px-4 rounded-2xl font-bold text-xs uppercase tracking-widest transition-all"
                        style="border: 2px solid #e5e7eb; color: #4b5563; background-color: white;">
                        Ahora no
                    </button>
                    <button onclick="startLiveCall()"
                        class="flex-1 py-4 px-4 rounded-2xl text-white font-bold text-xs uppercase tracking-widest hover:shadow-xl transition-all flex items-center justify-center gap-2"
                        style="background-color: #cbaa8e;">
                        Comenzar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Call Fullscreen Overlay -->
    <div id="live-overlay" class="fixed inset-0 z-[100] bg-zinc-950 hidden">
        <!-- Close button floating -->
        <button onclick="closeLiveOverlay()"
            class="absolute top-6 right-6 z-20 text-white/50 hover:text-white transition-colors p-4 bg-white/5 hover:bg-white/10 rounded-full backdrop-blur-xl border border-white/10"
            title="Terminar sesión">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
        <!-- Timer floating -->
        <div
            class="absolute top-6 left-6 z-20 bg-mentta-primary/10 backdrop-blur-xl px-5 py-2.5 rounded-full border border-mentta-primary/30">
            <span class="text-mentta-primary text-sm font-bold tracking-widest"><span
                    id="live-timer">00:00</span></span>
        </div>
        <!-- iframe container -->
        <iframe id="live-iframe" class="w-full h-full border-0" allow="camera; microphone; autoplay"></iframe>
    </div>

    <!-- JavaScript -->
    <script src="assets/js/utils.js"></script>
    <script src="assets/js/theme.js"></script>
    <script src="assets/js/menu.js"></script>
    <script src="assets/js/chat.js"></script>

    <!-- Live Call Functions -->
    <script>
        // Session timer
        let liveTimerInterval = null;
        let liveStartTime = null;

        // Modal functions
        function openLiveCallModal() {
            closeMenu();
            document.getElementById('live-call-modal').classList.add('active');
        }

        function closeLiveCallModal() {
            document.getElementById('live-call-modal').classList.remove('active');
        }

        // Close Live Overlay
        function closeLiveOverlay() {
            const overlay = document.getElementById('live-overlay');
            const iframe = document.getElementById('live-iframe');

            // Stop timer
            if (liveTimerInterval) {
                clearInterval(liveTimerInterval);
                liveTimerInterval = null;
            }

            // Hide overlay
            overlay.classList.add('hidden');

            // Clear iframe
            iframe.src = '';

            // Re-enable body scroll
            document.body.style.overflow = '';
        }

        // Update timer display
        function updateLiveTimer() {
            if (!liveStartTime) return;
            const elapsed = Math.floor((Date.now() - liveStartTime) / 1000);
            const minutes = Math.floor(elapsed / 60).toString().padStart(2, '0');
            const seconds = (elapsed % 60).toString().padStart(2, '0');
            document.getElementById('live-timer').textContent = `${minutes}:${seconds}`;
        }

        // Start Live Call - Opens in overlay iframe
        async function startLiveCall() {
            try {
                // First, create a session in the backend to get token
                const response = await fetch('api/live/start-session.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' }
                });

                const data = await response.json();

                if (!data.success) {
                    alert('Error al iniciar sesión: ' + (data.error || 'Error desconocido'));
                    return;
                }

                // Store session token
                sessionStorage.setItem('liveSessionToken', data.sessionToken);
                sessionStorage.setItem('liveSessionId', data.sessionId);

                // Close confirmation modal
                closeLiveCallModal();

                // Show overlay
                const overlay = document.getElementById('live-overlay');
                const iframe = document.getElementById('live-iframe');

                overlay.classList.remove('hidden');
                document.body.style.overflow = 'hidden';

                // Set iframe source (dev: localhost:3000, prod: /multimodal/)
                const liveAppUrl = 'http://localhost:3000';
                iframe.src = liveAppUrl;

                // Start timer
                liveStartTime = Date.now();
                liveTimerInterval = setInterval(updateLiveTimer, 1000);

            } catch (error) {
                console.error('Error starting live call:', error);
                alert('Error de conexión. Por favor intenta de nuevo.');
            }
        }

        // Listen for messages from iframe (when session ends)
        window.addEventListener('message', function (event) {
            if (event.data.type === 'MENTTA_LIVE_END') {
                closeLiveOverlay();
            }
        });
    </script>
</body>

</html>