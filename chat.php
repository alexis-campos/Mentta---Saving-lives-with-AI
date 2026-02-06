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
<html lang="<?= htmlspecialchars($user['language'] ?? 'en') ?>" data-theme="<?= htmlspecialchars($theme) ?>">

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
    
    <!-- Translation System -->
    <script src="assets/js/translations.js"></script>
    
    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="images/Menta icono.jpg">
</head>

<body class="antialiased" style="background-color: #FAFAFA;">
    <script>
        // Pre-load script to handle initial reveal
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                document.body.classList.add('loaded');
                document.querySelectorAll('.initial-reveal-container').forEach(el => el.classList.add('active'));
            }, 100);
        });
    </script>

    <!-- Sidebar Backdrop -->
    <div id="sidebar-backdrop" class="sidebar-backdrop"></div>

    <!-- Sidebar Menu -->
    <aside id="sidebar-menu" class="sidebar-menu initial-reveal-container">
        <!-- Header -->
        <div class="sidebar-header"
            style="background-color: #F2F2F0; border-bottom: 1px solid rgba(0,0,0,0.05); padding: 2rem 1.5rem;">
            <div class="text-center w-full">
                <div
                    class="w-16 h-16 mx-auto mb-6 rounded-full overflow-hidden border border-white p-0.5 bg-white shadow-xl">
                    <img src="Images/Menta icono.jpg" alt="Mentta Logo" class="w-full h-full object-cover rounded-full">
                </div>
                <h3
                    style="color: #111; font-family: 'Playfair Display', serif; font-weight: 700; font-size: 1.5rem; margin-bottom: 0.5rem;">
                    <?= htmlspecialchars($user['name']) ?>
                </h3>
                <p style="color: #888; font-size: 0.75rem; font-weight: 500; letter-spacing: 0.02em;">
                    <?= htmlspecialchars($user['email']) ?>
                </p>
                <div class="mood-badge"
                    style="background: white; border: 1px solid rgba(0,0,0,0.05); padding: 8px 16px; border-radius: 99px; margin-top: 1.5rem; display: inline-flex; align-items: center; gap: 8px; shadow: var(--shadow-sm);">
                    <span style="font-size: 1.1rem;"><?= $moodEmoji ?></span>
                    <span
                        style="font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.15em; color: #111;">Weekly
                        Vitality</span>
                </div>
            </div>
            <button
                class="sidebar-close absolute top-4 right-4 text-xl w-8 h-8 flex items-center justify-center rounded-full hover:bg-black/5 transition-colors"
                onclick="closeMenu()" aria-label="Cerrar menú" style="color:#aaa;">&times;</button>
        </div>

        <!-- Content -->
        <div class="sidebar-content">
            <!-- Main Actions -->
            <div class="sidebar-section">
                <button class="menu-btn group" onclick="newChat()"
                    style="background: white; border: 1px solid rgba(0,0,0,0.03); box-shadow: var(--shadow-sm); margin-bottom: 0.75rem; border-radius: 1.5rem; padding: 1.25rem;">
                    <span class="menu-btn-icon text-black group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                    </span>
                    <span class="font-bold text-black text-sm tracking-tight">Nuevo Chat</span>
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
                    <span class="font-bold tracking-[0.25em] text-[10px] uppercase">Mentta Live Session</span>
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
    </aside>

    <!-- Analysis Paused Banner -->
    <div id="analysis-paused-banner" class="analysis-paused-banner"></div>

    <!-- Header -->
    <header class="fixed top-0 left-0 right-0 z-50 bg-[#FAFAFA] initial-reveal-container"
        style="border-bottom: 1px solid rgba(0,0,0,0.03); transition-delay: 0.1s;">
        <div class="max-w-xl mx-auto px-4 md:px-6 py-3 md:py-4 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <!-- Hamburger Button -->
                <!-- Floating Hamburger Trigger -->
                <button class="hamburger-btn group btn-bloom" onclick="toggleMenu()" aria-label="Abrir menú"
                    style="background: white; border-radius: 18px; box-shadow: 0 10px 30px rgba(0,0,0,0.04); border: 1px solid rgba(0,0,0,0.02); height: 52px; width: 52px; transition: all 0.4s ease;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#111"
                        class="w-6 h-6 transition-transform group-hover:rotate-90 mx-auto">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 8h16M4 16h16" />
                    </svg>
                </button>

                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full overflow-hidden border border-white p-0.5 bg-white shadow-md">
                        <img src="Images/Menta icono.jpg" alt="M" class="w-full h-full object-cover rounded-full">
                    </div>
                    <div>
                        <h1 class="text-base font-bold"
                            style="color: #111; font-family: 'Playfair Display', serif; line-height: 1;">
                            Mentta</h1>
                        <p class="text-[8px] uppercase tracking-[0.3em] font-bold opacity-40">Elite Care</p>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <!-- Language Switcher -->
                <div id="headerLangSwitcher" class="hidden md:block"></div>
                
                <!-- Logout Button -->
                <button onclick="openLogoutModal()"
                    class="w-10 h-10 rounded-full bg-white shadow-sm border border-black/5 flex items-center justify-center hover:bg-gray-50 transition-colors group"
                    title="Log out" aria-label="Log out">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-black/40 group-hover:text-black/70"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Chat Area -->
    <main class="pt-20 pb-40 initial-reveal-container" style="transition-delay: 0.2s;">
        <div class="max-w-2xl mx-auto">
            <!-- Messages Container -->
            <div id="messagesContainer" class="min-h-[calc(100vh-14rem)] px-4 py-6 space-y-6">
                <!-- Welcome message - UX-002 FIXED -->
                <!-- Welcome message - ANTIGRAVITY CENTRAL CARD -->
                <div id="welcomeMessage" class="py-10 md:py-20 animate-fade">
                    <div
                        class="bg-white rounded-[2rem] md:rounded-[3rem] p-6 md:p-12 shadow-xl border border-black/5 max-w-lg mx-auto relative overflow-hidden">
                        <!-- Abstract glow -->
                        <div
                            class="absolute -top-24 -right-24 w-64 h-64 bg-mentta-light rounded-full opacity-30 blur-3xl">
                        </div>

                        <div class="relative z-10 text-center">
                            <div
                                class="w-12 h-12 md:w-16 md:h-16 mx-auto mb-6 md:mb-10 rounded-full overflow-hidden border border-white p-0.5 bg-white shadow-xl">
                                <img src="Images/Menta icono.jpg" alt="Mentta Logo"
                                    class="w-full h-full object-cover rounded-full">
                            </div>
                            <h2 class="text-2xl md:text-4xl font-bold mb-4 md:mb-6"
                                style="color: #111; font-family: 'Playfair Display', serif; letter-spacing: -0.02em; line-height: 1.2;">
                                <?= htmlspecialchars($greeting) ?>,<br>
                                <span
                                    class="italic font-normal"><?= htmlspecialchars(explode(' ', $user['name'])[0]) ?></span>
                            </h2>
                            <p style="color: #666;"
                                class="text-base md:text-lg leading-relaxed font-sans font-light mb-6 md:mb-10">
                                <?= htmlspecialchars($contextMessage) ?> Tómate tu tiempo, este es tu refugio de
                                claridad.
                            </p>

                            <div class="flex justify-center gap-4">
                                <span
                                    class="text-[9px] font-bold uppercase tracking-[0.4em] text-black/20">Secure</span>
                                <span class="text-[9px] font-bold uppercase tracking-[0.4em] text-black/20">•</span>
                                <span
                                    class="text-[9px] font-bold uppercase tracking-[0.4em] text-black/20">Private</span>
                            </div>
                        </div>
                    </div>
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
    <div class="fixed bottom-0 left-0 right-0 z-50"
        style="background: #FAFAFA; border-top: 1px solid rgba(0,0,0,0.03);">
        <div class="max-w-xl mx-auto px-4 py-4">
            <div class="rounded-2xl shadow-sm transition-all overflow-hidden border border-black/5"
                style="background-color: white;">
                <div class="flex items-center gap-2 md:gap-4 p-2 md:p-4">
                    <textarea id="messageInput" placeholder="Escribe aquí..."
                        class="flex-1 bg-transparent resize-none focus:outline-none px-3 py-3 md:px-4 min-h-[48px] md:min-h-[56px] max-h-[140px] text-sm md:text-base leading-relaxed"
                        style="color: #111;" rows="1" onkeydown="handleKeyDown(event)"
                        oninput="autoResize(this)"></textarea>
                    <button id="sendButton" onclick="sendMessage()"
                        class="w-10 h-10 md:w-14 md:h-14 bg-black text-white rounded-full flex items-center justify-center hover:scale-105 active:scale-95 transition-all disabled:opacity-10 disabled:cursor-not-allowed btn-bloom"
                        disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-6 md:w-6" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M12 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </div>
            <p
                class="hidden md:block text-center text-[8px] font-bold uppercase tracking-[0.4em] mt-3 md:mt-6 text-black/20">
                ESPACIO PRIVADO • ENCRIPTADO • SEGURO
            </p>
        </div>
    </div>

    <!-- Live Call FAB Button removed as per request -->

    <!-- ========================================
         MODALS
         ======================================== -->

    <!-- Logout Confirmation Modal -->
    <div id="logoutModal" class="modal-overlay">
        <div class="modal-content" style="max-width: 24rem;">
            <div class="modal-body text-center py-6">
                <h3 class="text-2xl font-serif font-bold mb-3" style="color: var(--text-primary);">Concluir Sesión</h3>
                <p style="color: var(--text-secondary);" class="mb-8 leading-relaxed">Siempre puedes volver cuando
                    necesites un momento de claridad. Cuídate mucho. 🌿</p>
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
        <div class="modal-content"
            style="border-radius: 32px; background: #FCFCFA; box-shadow: 0 40px 80px rgba(80, 60, 50, 0.08); border: 1px solid rgba(0,0,0,0.03); max-width: 28rem;">
            <div class="modal-header px-8 py-6 border-none flex justify-between items-center bg-transparent">
                <h3 class="font-serif text-2xl text-[#2A2A2A] font-bold">Soporte de Crisis</h3>
                <button onclick="closeCrisisModal()"
                    class="text-[10px] font-bold uppercase tracking-widest text-black/40 hover:text-black transition-colors">Cerrar</button>
            </div>
            <div class="modal-body px-8 pb-10 pt-2">
                <p class="text-[#555] text-sm leading-relaxed mb-8">
                    Este es un espacio seguro. Elige la opción que mejor se adapte a lo que necesitas en este momento:
                </p>

                <div class="space-y-4">
                    <!-- Option: Psychologist -->
                    <div class="group bg-white rounded-3xl p-5 shadow-sm border border-black/5 hover:shadow-xl hover:translate-y-[-2px] transition-all cursor-pointer flex items-center gap-4"
                        onclick="selectCrisisOption('psychologist')">
                        <div
                            class="w-12 h-12 rounded-2xl bg-[#C8553D]/5 flex items-center justify-center text-[#C8553D]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-[#2A2A2A] font-bold text-sm">Contactar a mi Psicólogo</h4>
                            <p class="text-[10px] text-[#888] uppercase tracking-wider mt-0.5">Notificación prioritaria
                            </p>
                        </div>
                    </div>

                    <!-- Option: Calming Exercises -->
                    <div class="group bg-white rounded-3xl p-5 shadow-sm border border-black/5 hover:shadow-xl hover:translate-y-[-2px] transition-all cursor-pointer flex items-center gap-4"
                        onclick="selectCrisisOption('calming_exercises')">
                        <div
                            class="w-12 h-12 rounded-2xl bg-[#C8553D]/5 flex items-center justify-center text-[#C8553D]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 3v17.25m0 0a9 9 0 100-18 9 9 0 000 18z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M14.25 9.25a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-[#2A2A2A] font-bold text-sm">Ejercicios de Calma</h4>
                            <p class="text-[10px] text-[#888] uppercase tracking-wider mt-0.5">Respiración y Grounding
                            </p>
                        </div>
                    </div>

                    <!-- Option: Emergency Contact -->
                    <div class="group bg-white rounded-3xl p-5 shadow-sm border border-black/5 hover:shadow-xl hover:translate-y-[-2px] transition-all cursor-pointer flex items-center gap-4"
                        onclick="selectCrisisOption('emergency_contact')">
                        <div
                            class="w-12 h-12 rounded-2xl bg-[#C8553D]/5 flex items-center justify-center text-[#C8553D]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-[#2A2A2A] font-bold text-sm">Contacto de Confianza</h4>
                            <p class="text-[10px] text-[#888] uppercase tracking-wider mt-0.5">Aviso a red de seguridad
                            </p>
                        </div>
                    </div>

                    <!-- Option: Call 113 -->
                    <div class="group bg-[#C8553D] rounded-3xl p-6 shadow-[0_20px_40px_rgba(200,85,61,0.2)] hover:shadow-[0_25px_50px_rgba(200,85,61,0.3)] hover:scale-[1.02] transition-all cursor-pointer flex items-center gap-5 mt-6"
                        onclick="window.location.href='tel:113'">
                        <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-white font-bold text-lg">Llamar Línea 113</h4>
                            <p class="text-[10px] text-white/70 uppercase tracking-[0.2em] font-bold">Urgencia Inmediata
                            </p>
                        </div>
                        <div class="w-5 h-5 rounded-full bg-white/10 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resources Modal -->
    <div id="resources-modal" class="modal-overlay">
        <div class="modal-content"
            style="border-radius: 32px; background: #FCFCFA; box-shadow: 0 40px 80px rgba(80, 60, 50, 0.08); border: 1px solid rgba(0,0,0,0.03); max-width: 32rem; max-height: 90vh;">
            <div class="modal-header px-8 py-6 border-none flex justify-between items-center bg-transparent">
                <h3 class="font-serif text-2xl text-[#2A2A2A] font-bold">Recursos de Bienestar</h3>
                <button onclick="closeResourcesModal()"
                    class="text-[10px] font-bold uppercase tracking-widest text-black/40 hover:text-black transition-colors">Cerrar</button>
            </div>
            <div class="modal-body px-8 pb-10 pt-2 overflow-y-auto">
                <p class="text-[#555] text-sm leading-relaxed mb-8">
                    Herramientas diseñadas para devolverte al centro. Tómate el tiempo que necesites.
                </p>

                <div class="space-y-6">
                    <!-- Breathing Guide -->
                    <div id="resource-breathing"
                        class="resource-card bg-white rounded-[2rem] overflow-hidden shadow-sm border border-black/5 transition-all">
                        <div class="px-6 py-5 flex items-center justify-between cursor-pointer hover:bg-black/[0.01]"
                            onclick="toggleResourceCard('resource-breathing')">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-10 h-10 rounded-xl bg-[#8B9D8B]/10 flex items-center justify-center text-[#8B9D8B]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 3v17.25m0 0a9 9 0 100-18 9 9 0 000 18z" />
                                    </svg>
                                </div>
                                <h4 class="font-bold text-sm text-[#2A2A2A]">Respiración Consciente</h4>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-black/20" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                        <div class="resource-card-content px-8 pb-8 pt-2">
                            <!-- Breathing Timer UI -->
                            <div class="text-center py-6">
                                <div class="relative w-48 h-48 mx-auto mb-8 flex items-center justify-center">
                                    <svg class="absolute inset-0 w-full h-full -rotate-90">
                                        <circle cx="96" cy="96" r="88" stroke="#F1F1EF" stroke-width="4"
                                            fill="transparent" />
                                        <circle id="breathing-progress-ring" cx="96" cy="96" r="88" stroke="#111"
                                            stroke-width="4" fill="transparent" stroke-dasharray="553"
                                            stroke-dashoffset="553" stroke-linecap="round"
                                            class="transition-all duration-1000 ease-linear" />
                                    </svg>
                                    <div id="breathing-circle"
                                        class="w-32 h-32 rounded-full bg-black flex flex-col items-center justify-center transition-all duration-1000 shadow-2xl">
                                        <span id="breathing-text"
                                            class="text-white font-bold text-lg uppercase tracking-widest text-[10px]">...</span>
                                        <span id="breathing-subtext"
                                            class="text-white/40 text-[8px] uppercase tracking-widest mt-1">Calma</span>
                                    </div>
                                </div>
                                <div id="breathing-instruction"
                                    class="text-xs text-[#888] mb-6 uppercase tracking-widest font-bold">Encuentra una
                                    postura cómoda</div>
                                <button id="breathing-start-btn" onclick="startBreathingExercise()"
                                    class="bg-black text-white px-10 py-4 rounded-full font-bold text-[10px] uppercase tracking-widest hover:scale-105 transition-all shadow-xl">Iniciar
                                    Práctica</button>
                                <button id="breathing-stop-btn" onclick="stopBreathingExercise()"
                                    class="hidden mt-4 text-[9px] uppercase tracking-widest text-red-500/60 hover:text-red-500 transition-colors font-bold">Finalizar</button>
                            </div>
                        </div>
                    </div>

                    <!-- Grounding 5-4-3-2-1 -->
                    <div id="resource-grounding"
                        class="resource-card bg-white rounded-[2rem] overflow-hidden shadow-sm border border-black/5 transition-all">
                        <div class="px-6 py-5 flex items-center justify-between cursor-pointer hover:bg-black/[0.01]"
                            onclick="toggleResourceCard('resource-grounding')">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-10 h-10 rounded-xl bg-[#8B9D8B]/10 flex items-center justify-center text-[#8B9D8B]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <h4 class="font-bold text-sm text-[#2A2A2A]">Técnica de Grounding</h4>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-black/20" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                        <div class="resource-card-content px-8 pb-10 pt-2">
                            <p class="text-xs text-[#888] mb-6">Conecta con tus sentidos para reducir la ansiedad.
                                <strong>Toca cada paso al completarlo:</strong>
                            </p>
                            <div class="space-y-3">
                                <div class="grounding-item p-4 rounded-2xl border border-black/5 bg-[#F9F9F7] text-xs font-medium text-[#2A2A2A] flex justify-between items-center group cursor-pointer"
                                    onclick="toggleGroundingItem(this, 5)">
                                    <span>5 cosas que puedes <strong>ver</strong></span>
                                    <div
                                        class="grounding-check w-6 h-6 rounded-full border border-black/10 flex items-center justify-center bg-white transition-all text-[10px]">
                                        5</div>
                                </div>
                                <div class="grounding-item p-4 rounded-2xl border border-black/5 bg-[#F9F9F7] text-xs font-medium text-[#2A2A2A] flex justify-between items-center group cursor-pointer"
                                    onclick="toggleGroundingItem(this, 4)">
                                    <span>4 cosas que puedes <strong>tocar</strong></span>
                                    <div
                                        class="grounding-check w-6 h-6 rounded-full border border-black/10 flex items-center justify-center bg-white transition-all text-[10px]">
                                        4</div>
                                </div>
                                <div class="grounding-item p-4 rounded-2xl border border-black/5 bg-[#F9F9F7] text-xs font-medium text-[#2A2A2A] flex justify-between items-center group cursor-pointer"
                                    onclick="toggleGroundingItem(this, 3)">
                                    <span>3 cosas que puedes <strong>oír</strong></span>
                                    <div
                                        class="grounding-check w-6 h-6 rounded-full border border-black/10 flex items-center justify-center bg-white transition-all text-[10px]">
                                        3</div>
                                </div>
                                <div class="grounding-item p-4 rounded-2xl border border-black/5 bg-[#F9F9F7] text-xs font-medium text-[#2A2A2A] flex justify-between items-center group cursor-pointer"
                                    onclick="toggleGroundingItem(this, 2)">
                                    <span>2 cosas que puedes <strong>oler</strong></span>
                                    <div
                                        class="grounding-check w-6 h-6 rounded-full border border-black/10 flex items-center justify-center bg-white transition-all text-[10px]">
                                        2</div>
                                </div>
                                <div class="grounding-item p-4 rounded-2xl border border-black/5 bg-[#F9F9F7] text-xs font-medium text-[#2A2A2A] flex justify-between items-center group cursor-pointer"
                                    onclick="toggleGroundingItem(this, 1)">
                                    <span>1 cosa que puedes <strong>saborear</strong></span>
                                    <div
                                        class="grounding-check w-6 h-6 rounded-full border border-black/10 flex items-center justify-center bg-white transition-all text-[10px]">
                                        1</div>
                                </div>
                            </div>
                            <div id="grounding-complete" class="hidden mt-8 text-center animate-fade">
                                <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#8B9D8B]">Bien hecho.
                                    Estás aquí.</p>
                                <button onclick="resetGroundingChecklist()"
                                    class="mt-4 text-[9px] uppercase tracking-widest text-black/30 hover:text-black transition-colors">Reiniciar</button>
                            </div>
                        </div>
                    </div>

                    <!-- Phrases -->
                    <div id="resource-phrases"
                        class="resource-card bg-white rounded-[2rem] overflow-hidden shadow-sm border border-black/5 transition-all">
                        <div class="px-6 py-5 flex items-center justify-between cursor-pointer hover:bg-black/[0.01]"
                            onclick="toggleResourceCard('resource-phrases')">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-10 h-10 rounded-xl bg-[#8B9D8B]/10 flex items-center justify-center text-[#8B9D8B]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
                                    </svg>
                                </div>
                                <h4 class="font-bold text-sm text-[#2A2A2A]">Afirmaciones</h4>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-black/20" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                        <div class="resource-card-content px-8 pb-10 pt-4 text-center">
                            <div id="supportive-phrase" class="mb-6">
                                <p class="italic text-[#4A4A4A] text-lg font-serif leading-relaxed">"Esto que sientes es
                                    temporal. Has superado días difíciles antes y lo harás de nuevo."</p>
                            </div>
                            <button onclick="showNewPhrase()"
                                class="text-[10px] font-bold uppercase tracking-widest text-[#8B9D8B] hover:text-[#111] transition-colors">Siguiente
                                afirmación</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Call Modal -->
    <div id="live-call-modal" class="modal-overlay">
        <div class="modal-content" style="max-width: 28rem;">
            <div class="modal-header" style="background: var(--bg-tertiary);">
                <h3 class="modal-title text-base"
                    style="color: var(--text-primary); font-family: 'Playfair Display', serif;">📽️
                    Mentta Live</h3>
                <button class="modal-close text-lg" onclick="closeLiveCallModal()">&times;</button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="w-12 h-12 rounded-[1rem] mx-auto mb-4 flex items-center justify-center shadow-lg rotate-3"
                    style="background-color: #2d3a2d;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                </div>
                <h2 class="text-lg font-bold mb-2"
                    style="color: var(--text-primary); font-family: 'Playfair Display', serif;">Sesión en Tiempo Real
                </h2>
                <p style="color: var(--text-secondary);" class="mb-4 text-xs leading-relaxed">
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
        <!-- Session Timer -->
        <div id="live-timer" class="absolute top-4 left-1/2 -translate-x-1/2 z-20 px-6 py-2 rounded-full bg-white/10 backdrop-blur-xl border border-white/20 text-white font-mono text-lg tracking-wider shadow-lg">
            00:00
        </div>
        <!-- Close button floating -- Reduced by ~50% -->
        <button onclick="closeLiveOverlay()"
            class="absolute top-4 right-4 z-20 text-white/50 hover:text-white transition-colors p-2 bg-white/5 hover:bg-white/10 rounded-full backdrop-blur-xl border border-white/10"
            title="Terminar sesión">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
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
        // ============================================
        // CRITICAL: Navigation Guard - Prevent back button to login
        // ============================================
        (function preventBackToLogin() {
            // Replace current history entry
            history.replaceState(null, '', location.href);
            
            // Block back button navigation
            window.addEventListener('popstate', function(e) {
                history.pushState(null, '', location.href);
            });
        })();

        // ============================================
        // Logout Modal Functions
        // ============================================
        function openLogoutModal() {
            closeMenu();
            document.getElementById('logoutModal').classList.add('active');
            document.getElementById('logoutModal').setAttribute('aria-hidden', 'false');
        }

        function closeLogoutModal() {
            document.getElementById('logoutModal').classList.remove('active');
            document.getElementById('logoutModal').setAttribute('aria-hidden', 'true');
        }

        // ============================================
        // ESC Key Handler for All Modals
        // ============================================
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                // Close all active modals
                document.querySelectorAll('.modal-overlay.active').forEach(modal => {
                    modal.classList.remove('active');
                    modal.setAttribute('aria-hidden', 'true');
                });
                // Close sidebar if open
                const sidebar = document.getElementById('sidebar-menu');
                if (sidebar && sidebar.classList.contains('active')) {
                    closeMenu();
                }
                // Close live overlay if open
                const liveOverlay = document.getElementById('live-overlay');
                if (liveOverlay && !liveOverlay.classList.contains('hidden')) {
                    closeLiveOverlay();
                }
            }
        });

        // ============================================
        // Loading State with Timeout Safety
        // ============================================
        let loadingTimeout = null;
        const LOADING_TIMEOUT_MS = 30000; // 30 seconds

        function showLoading(text) {
            const indicator = document.getElementById('loadingIndicator');
            const loadingText = document.getElementById('loadingText');
            if (indicator) {
                indicator.classList.remove('hidden');
                if (loadingText && text) {
                    loadingText.textContent = text;
                }
            }
            
            // Safety timeout
            clearTimeout(loadingTimeout);
            loadingTimeout = setTimeout(() => {
                hideLoading();
                showToast(i18n.t('errors.operationTimeout'), 'error');
            }, LOADING_TIMEOUT_MS);
        }

        function hideLoading() {
            clearTimeout(loadingTimeout);
            const indicator = document.getElementById('loadingIndicator');
            if (indicator) {
                indicator.classList.add('hidden');
            }
        }

        // Simple toast notification
        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `fixed bottom-24 left-1/2 -translate-x-1/2 px-6 py-3 rounded-full text-sm font-medium shadow-lg z-50 transition-all animate-fade ${type === 'error' ? 'bg-red-500 text-white' : 'bg-black text-white'}`;
            toast.textContent = message;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }

        // ============================================
        // Session Timer
        // ============================================
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
            const timerElement = document.getElementById('live-timer');
            if (!timerElement) return;
            
            const elapsed = Math.floor((Date.now() - liveStartTime) / 1000);
            const minutes = Math.floor(elapsed / 60).toString().padStart(2, '0');
            const seconds = (elapsed % 60).toString().padStart(2, '0');
            timerElement.textContent = `${minutes}:${seconds}`;
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
                    alert(i18n.t('errors.connectionFailed') + ': ' + (data.error || 'Unknown error'));
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

                // Set iframe source - Uses MENTTA_LIVE_URL from config or production URL
                const liveAppUrl = '<?= defined('MENTTA_LIVE_URL') ? MENTTA_LIVE_URL : (APP_ENV === 'production' ? 'https://mentta-live-production.up.railway.app' : 'http://localhost:3001') ?>';
                iframe.src = liveAppUrl;

                // When iframe loads, send the session token via postMessage
                iframe.onload = function() {
                    // SECURITY FIX: Specify target origin instead of '*'
                    iframe.contentWindow.postMessage({
                        type: 'MENTTA_SESSION_TOKEN',
                        sessionToken: data.sessionToken,
                        sessionId: data.sessionId
                    }, liveAppUrl);
                };

                // Start timer
                liveStartTime = Date.now();
                liveTimerInterval = setInterval(updateLiveTimer, 1000);

            } catch (error) {
                console.error('Error starting live call:', error);
                alert(i18n.t('errors.connectionFailed'));
            }
        }

        // Listen for messages from iframe - with origin validation
        // PRODUCTION-READY: This automatically includes the current domain
        const ALLOWED_ORIGINS = [
            window.location.origin,                    // Current page origin (works in prod & dev)
            'http://localhost:3001',                   // Vite dev server
            'http://localhost',                        // XAMPP local
            'https://mentta-live-production.up.railway.app',  // Railway production
            '<?= defined('MENTTA_LIVE_URL') ? MENTTA_LIVE_URL : '' ?>',  // Custom from config
        ].filter(Boolean);
        
        window.addEventListener('message', function (event) {
            // SECURITY: Validate origin
            if (!ALLOWED_ORIGINS.includes(event.origin)) {
                console.warn('Blocked message from untrusted origin:', event.origin);
                return;
            }

            // When session ends
            if (event.data.type === 'MENTTA_LIVE_END') {
                closeLiveOverlay();
            }
            // If iframe requests the token (fallback)
            if (event.data.type === 'MENTTA_REQUEST_TOKEN') {
                const iframe = document.getElementById('live-iframe');
                const token = sessionStorage.getItem('liveSessionToken');
                const sessionId = sessionStorage.getItem('liveSessionId');
                if (iframe && token) {
                    iframe.contentWindow.postMessage({
                        type: 'MENTTA_SESSION_TOKEN',
                        sessionToken: token,
                        sessionId: sessionId
                    }, event.origin);
                }
            }
        });

        // ============================================
        // Initialize on DOM Ready
        // ============================================
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize language switcher
            i18n.createLanguageSwitcher('headerLangSwitcher');
            
            // Apply translations
            i18n.applyTranslations();
            
            // Set initial ARIA states on modals
            document.querySelectorAll('.modal-overlay').forEach(modal => {
                modal.setAttribute('aria-hidden', 'true');
                modal.setAttribute('role', 'dialog');
                modal.setAttribute('aria-modal', 'true');
            });
        });
    </script>
</body>

</html>