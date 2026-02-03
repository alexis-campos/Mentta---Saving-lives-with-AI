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
        <div class="sidebar-header">
            <div class="sidebar-user-info">
                <h3><?= htmlspecialchars($user['name']) ?></h3>
                <p><?= htmlspecialchars($user['email']) ?></p>
                <div class="mood-badge"><?= $moodEmoji ?> Ánimo de la semana</div>
            </div>
            <button class="sidebar-close" onclick="closeMenu()" aria-label="Cerrar menú">&times;</button>
        </div>

        <!-- Content -->
        <div class="sidebar-content">
            <!-- Main Actions -->
            <div class="sidebar-section">
                <button class="menu-btn" onclick="newChat()">
                    <span class="menu-btn-icon">➕</span>
                    <span>Nuevo Chat</span>
                </button>
                <button class="menu-btn" onclick="window.location.href='map.php'">
                    <span class="menu-btn-icon">🗺️</span>
                    <span>Mapa de Centros</span>
                </button>
                <button class="menu-btn menu-btn-crisis" onclick="openCrisisModal()">
                    <span class="menu-btn-icon">🆘</span>
                    <span>Buscar Contacto Inmediato</span>
                </button>
                <button class="menu-btn menu-btn-resources" onclick="openResourcesModal()">
                    <span class="menu-btn-icon">💚</span>
                    <span>Recursos de Ayuda</span>
                </button>
                <button class="menu-btn menu-btn-live" onclick="openLiveCallModal()"
                    style="background: linear-gradient(135deg, #6366F1, #8B5CF6); color: white;">
                    <span class="menu-btn-icon">📹</span>
                    <span>Llamada con Mentta Live</span>
                </button>
            </div>

            <!-- Notifications (if any) -->
            <?php if ($notifCount > 0): ?>
                <div class="notifications-section">
                    <div class="sidebar-section-title">🔔 Notificaciones (<?= $notifCount ?>)</div>
                    <div id="notifications-list">
                        <!-- Populated by JS -->
                    </div>
                    <button class="menu-btn" onclick="Menu.markAllNotificationsRead()"
                        style="font-size: 0.75rem; color: var(--text-tertiary);">
                        Marcar todas como leídas
                    </button>
                </div>
            <?php endif; ?>

            <!-- Chat History -->
            <div class="sidebar-section">
                <div class="sidebar-section-title">Conversaciones Anteriores</div>
                <div id="chat-history-list" class="chat-history-list">
                    <!-- Populated by JS -->
                    <div class="px-4 py-3 text-center">
                        <p style="color: var(--text-tertiary); font-size: 0.8125rem;">Cargando...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="sidebar-footer">
            <button class="menu-btn" onclick="Menu.openProfile()">
                <span class="menu-btn-icon">👤</span>
                <span>Mi Cuenta</span>
            </button>
            <p class="sidebar-version">Mentta v1.1</p>
        </div>
    </aside>

    <!-- Analysis Paused Banner -->
    <div id="analysis-paused-banner" class="analysis-paused-banner"></div>

    <!-- Header -->
    <header class="fixed top-0 left-0 right-0 z-50"
        style="background-color: var(--bg-secondary); border-bottom: 1px solid var(--border-color);">
        <div class="max-w-2xl mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <!-- Hamburger Button with Badge -->
                <button class="hamburger-btn" onclick="toggleMenu()" aria-label="Abrir menú">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <span id="notification-badge" class="notification-badge <?= $notifCount > 0 ? '' : 'hidden' ?>">
                        <?= $notifCount > 99 ? '99+' : $notifCount ?>
                    </span>
                </button>

                <div
                    class="w-10 h-10 bg-gradient-to-br from-mentta-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg shadow-mentta-500/20">
                    <span class="text-white font-bold text-lg">M</span>
                </div>
                <div>
                    <h1 class="text-lg font-semibold" style="color: var(--text-primary);">Mentta</h1>
                    <p class="text-xs" style="color: var(--text-secondary);">Tu espacio seguro</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-sm hidden sm:inline"
                    style="color: var(--text-secondary);"><?= htmlspecialchars($user['name']) ?></span>
                <button onclick="confirmLogout()" class="p-2 transition-colors" style="color: var(--text-tertiary);"
                    title="Cerrar sesión">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
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
                    <div
                        class="w-20 h-20 bg-gradient-to-br from-mentta-500 to-purple-600 rounded-2xl mx-auto mb-4 flex items-center justify-center shadow-xl shadow-mentta-500/30">
                        <span class="text-white font-bold text-3xl">M</span>
                    </div>
                    <h2 class="text-xl font-semibold mb-2" style="color: var(--text-primary);">Hola,
                        <?= htmlspecialchars(explode(' ', $user['name'])[0]) ?> 👋
                    </h2>
                    <p style="color: var(--text-secondary);" class="max-w-sm mx-auto">Estoy aquí para escucharte.
                        Cuéntame, ¿cómo te sientes hoy?</p>
                </div>

                <!-- Messages will be rendered here -->
            </div>
        </div>
    </main>

    <!-- Sentiment Indicator (minimal) -->
    <div id="sentimentIndicator" class="fixed bottom-32 left-1/2 transform -translate-x-1/2 hidden">
        <div class="rounded-full px-4 py-2 shadow-lg flex items-center gap-3"
            style="background-color: var(--bg-secondary); border: 1px solid var(--border-color);">
            <span class="text-xs" style="color: var(--text-secondary);">Tu ánimo:</span>
            <div class="flex gap-1">
                <div id="moodDot1" class="w-2 h-2 rounded-full transition-colors"
                    style="background-color: var(--border-color);"></div>
                <div id="moodDot2" class="w-2 h-2 rounded-full transition-colors"
                    style="background-color: var(--border-color);"></div>
                <div id="moodDot3" class="w-2 h-2 rounded-full transition-colors"
                    style="background-color: var(--border-color);"></div>
                <div id="moodDot4" class="w-2 h-2 rounded-full transition-colors"
                    style="background-color: var(--border-color);"></div>
                <div id="moodDot5" class="w-2 h-2 rounded-full transition-colors"
                    style="background-color: var(--border-color);"></div>
            </div>
        </div>
    </div>

    <!-- Loading Indicator -->
    <div id="loadingIndicator" class="fixed bottom-32 left-1/2 transform -translate-x-1/2 hidden">
        <div class="rounded-full px-5 py-3 shadow-lg flex items-center gap-3"
            style="background-color: var(--bg-secondary); border: 1px solid var(--border-color);">
            <div class="flex gap-1">
                <div class="w-2 h-2 rounded-full bg-mentta-500 animate-bounce" style="animation-delay: 0ms"></div>
                <div class="w-2 h-2 rounded-full bg-mentta-500 animate-bounce" style="animation-delay: 150ms"></div>
                <div class="w-2 h-2 rounded-full bg-mentta-500 animate-bounce" style="animation-delay: 300ms"></div>
            </div>
            <span class="text-sm" style="color: var(--text-secondary);">Mentta está escribiendo...</span>
        </div>
    </div>

    <!-- Input Area -->
    <div class="fixed bottom-0 left-0 right-0"
        style="background-color: var(--bg-secondary); border-top: 1px solid var(--border-color);">
        <div class="max-w-2xl mx-auto px-4 py-4">
            <div class="rounded-2xl transition-all"
                style="background-color: var(--bg-input); border: 1px solid var(--border-color);">
                <div class="flex items-end gap-2 p-2">
                    <textarea id="messageInput" placeholder="Escribe lo que sientes..."
                        class="flex-1 bg-transparent resize-none focus:outline-none px-2 py-2 min-h-[44px] max-h-[120px]"
                        style="color: var(--text-primary);" rows="1" onkeydown="handleKeyDown(event)"
                        oninput="autoResize(this)"></textarea>
                    <button id="sendButton" onclick="sendMessage()"
                        class="w-10 h-10 bg-gradient-to-br from-mentta-500 to-purple-600 text-white rounded-xl flex items-center justify-center hover:shadow-lg hover:shadow-mentta-500/30 transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none"
                        disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path
                                d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                        </svg>
                    </button>
                </div>
            </div>
            <p class="text-center text-xs mt-2" style="color: var(--text-tertiary);">Todo lo que compartas es
                confidencial y seguro 🔒</p>
        </div>
    </div>

    <!-- Live Call FAB Button -->
    <button id="liveCallFab" onclick="openLiveCallModal()"
        class="fixed right-4 bottom-36 z-40 w-14 h-14 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full shadow-lg shadow-purple-500/30 flex items-center justify-center text-white hover:scale-110 transition-transform"
        title="Llamar a Mentta Live">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="2">
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
            <div class="modal-header">
                <h3 class="modal-title">🆘 Buscar Contacto Inmediato</h3>
                <button class="modal-close" onclick="closeCrisisModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 1rem;">
                    Si necesitas ayuda ahora mismo, selecciona una opción:
                </p>

                <div class="crisis-option" onclick="selectCrisisOption('psychologist')">
                    <span class="crisis-option-icon">👨‍⚕️</span>
                    <div class="crisis-option-content">
                        <h4>Contactar a mi Psicólogo</h4>
                        <p>Notificar a tu psicólogo vinculado</p>
                    </div>
                </div>

                <div class="crisis-option" onclick="selectCrisisOption('emergency_contact')">
                    <span class="crisis-option-icon">👪</span>
                    <div class="crisis-option-content">
                        <h4>Contacto de Emergencia</h4>
                        <p>Notificar a tu contacto de confianza</p>
                    </div>
                </div>

                <div class="crisis-option" onclick="window.location.href='tel:113'">
                    <span class="crisis-option-icon">📞</span>
                    <div class="crisis-option-content">
                        <h4>Línea de Crisis Nacional</h4>
                        <p>Llamar al 113</p>
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
        <div class="modal-content" style="max-height: 90vh;">
            <div class="modal-header">
                <h3 class="modal-title">💚 Recursos de Ayuda</h3>
                <button class="modal-close" onclick="closeResourcesModal()">&times;</button>
            </div>
            <div class="modal-body" style="overflow-y: auto;">

                <!-- Breathing Exercises -->
                <div id="resource-breathing" class="resource-card">
                    <div class="resource-card-header" onclick="toggleResourceCard('resource-breathing')">
                        <span class="resource-card-title">
                            <span class="resource-card-icon">🫁</span>
                            Ejercicios de Respiración
                        </span>
                        <span class="resource-card-chevron">▼</span>
                    </div>
                    <div class="resource-card-content">
                        <h4 style="color: var(--text-primary); font-weight: 600; margin-bottom: 0.5rem;">Respiración
                            4-7-8 (Calma rápida)</h4>
                        <p>Esta técnica ayuda a reducir la ansiedad y promover la relajación:</p>
                        <ol>
                            <li><strong>Inhala</strong> por la nariz contando hasta <strong>4</strong></li>
                            <li><strong>Mantén</strong> el aire contando hasta <strong>7</strong></li>
                            <li><strong>Exhala</strong> por la boca contando hasta <strong>8</strong></li>
                            <li>Repite <strong>4 veces</strong></li>
                        </ol>
                        <h4 style="color: var(--text-primary); font-weight: 600; margin: 1rem 0 0.5rem 0;">Box Breathing
                            (Respiración cuadrada)</h4>
                        <ol>
                            <li>Inhala contando hasta 4</li>
                            <li>Mantén contando hasta 4</li>
                            <li>Exhala contando hasta 4</li>
                            <li>Mantén vacío contando hasta 4</li>
                            <li>Repite 4 rondas</li>
                        </ol>
                    </div>
                </div>

                <!-- Grounding 5-4-3-2-1 -->
                <div id="resource-grounding" class="resource-card">
                    <div class="resource-card-header" onclick="toggleResourceCard('resource-grounding')">
                        <span class="resource-card-title">
                            <span class="resource-card-icon">🧠</span>
                            Técnica de Grounding 5-4-3-2-1
                        </span>
                        <span class="resource-card-chevron">▼</span>
                    </div>
                    <div class="resource-card-content">
                        <p>Esta técnica te ayuda a volver al presente cuando sientes ansiedad o pánico.</p>
                        <p style="color: var(--text-primary); font-weight: 500;">Nombra en voz alta:</p>
                        <ol>
                            <li><strong>5 cosas</strong> que PUEDES VER</li>
                            <li><strong>4 cosas</strong> que PUEDES TOCAR</li>
                            <li><strong>3 cosas</strong> que PUEDES ESCUCHAR</li>
                            <li><strong>2 cosas</strong> que PUEDES OLER</li>
                            <li><strong>1 cosa</strong> que PUEDES SABOREAR</li>
                        </ol>
                        <p style="font-style: italic; margin-top: 0.75rem;">Tómate tu tiempo con cada paso. No hay
                            prisa.</p>
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
            <div class="modal-header">
                <h3 class="modal-title">📹 Mentta Live</h3>
                <button class="modal-close" onclick="closeLiveCallModal()">&times;</button>
            </div>
            <div class="modal-body text-center py-6">
                <div
                    class="w-20 h-20 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl mx-auto mb-4 flex items-center justify-center shadow-xl shadow-purple-500/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                </div>
                <h2 class="text-xl font-semibold mb-2" style="color: var(--text-primary);">Habla con Mentta en Vivo</h2>
                <p style="color: var(--text-secondary);" class="mb-4 text-sm">
                    Inicia una videollamada con nuestra IA de apoyo emocional. Mentta puede verte y escucharte para
                    darte apoyo en tiempo real.
                </p>

                <div class="bg-indigo-500/10 border border-indigo-500/30 rounded-xl p-4 mb-4 text-left">
                    <h4 class="font-medium text-indigo-400 mb-2 text-sm">📋 Lo que necesitas:</h4>
                    <ul class="text-sm space-y-1" style="color: var(--text-secondary);">
                        <li>✓ Conexión a internet estable</li>
                        <li>✓ Micrófono (obligatorio)</li>
                        <li>✓ Cámara (opcional pero recomendada)</li>
                    </ul>
                </div>

                <p class="text-xs mb-6" style="color: var(--text-tertiary);">
                    Si detectamos que estás en riesgo, alertaremos a un profesional. Tu seguridad es nuestra prioridad.
                </p>

                <div class="flex gap-3">
                    <button onclick="closeLiveCallModal()" class="flex-1 py-3 px-4 rounded-xl transition-colors"
                        style="border: 1px solid var(--border-color); color: var(--text-primary); background-color: var(--bg-tertiary);">
                        Cancelar
                    </button>
                    <button onclick="startLiveCall()"
                        class="flex-1 py-3 px-4 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-medium hover:shadow-lg hover:shadow-purple-500/30 transition-all flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        Iniciar Llamada
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Call Fullscreen Overlay -->
    <div id="live-overlay" class="fixed inset-0 z-[100] bg-slate-900 hidden">
        <!-- Close button floating -->
        <button onclick="closeLiveOverlay()" 
            class="absolute top-4 right-4 z-20 text-slate-400 hover:text-white transition-colors p-3 bg-slate-800/80 hover:bg-slate-700 rounded-full backdrop-blur-sm shadow-lg"
            title="Cerrar llamada">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
        <!-- Timer floating -->
        <div class="absolute top-4 left-4 z-20 bg-slate-800/80 backdrop-blur-sm px-4 py-2 rounded-full shadow-lg">
            <span class="text-slate-400 text-sm">⏱️ <span id="live-timer">00:00</span></span>
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

                // Set iframe source (dev: localhost:3001, prod: /multimodal/)
                const liveAppUrl = 'http://localhost:3001';
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