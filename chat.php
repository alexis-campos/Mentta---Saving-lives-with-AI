<!DOCTYPE html>
<html lang="es" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#f5f5f0">
    <title>Chat - Mentta</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        mentta: {
                            primary: '#2d3a2d',
                            secondary: '#cbaa8e',
                            light: '#f5f5f0',
                            accent: '#8b9d8b',
                            cream: '#eeebe6',
                            sage: '#a8b5a0',
                            warm: '#f8f6f3'
                        }
                    },
                    fontFamily: {
                        display: ['Crimson Pro', 'serif'],
                        body: ['Spectral', 'serif'],
                        sans: ['DM Sans', 'sans-serif']
                    }
                }
            }
        }
    </script>

    <!-- Google Fonts - Warm, approachable fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@400;600;700&family=Spectral:wght@300;400;500&family=DM+Sans:wght@400;500;600&display=swap"
        rel="stylesheet">

    <!-- Custom Core CSS -->
    <link rel="stylesheet" href="assets/css/mentta-core.css">
</head>

<body>

    <!-- Sidebar Backdrop -->
    <div id="sidebar-backdrop" class="sidebar-backdrop" onclick="closeMenu()"></div>

    <!-- Sidebar Menu -->
    <aside id="sidebar-menu" class="sidebar-menu">
        <!-- Header -->
        <div class="sidebar-header">
            <div class="sidebar-user-info">
                <h3>Ana García</h3>
                <p>ana.garcia@email.com</p>
                <div class="mood-badge">😊 Ánimo de la semana</div>
            </div>
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
                    <span>Buscar Ayuda Inmediata</span>
                </button>
                <button class="menu-btn menu-btn-resources" onclick="openResourcesModal()">
                    <span class="menu-btn-icon">💚</span>
                    <span>Recursos de Bienestar</span>
                </button>
            </div>

            <!-- Chat History -->
            <div class="sidebar-section">
                <div class="sidebar-section-title">Conversaciones Recientes</div>
                <button class="menu-btn">
                    <span class="menu-btn-icon">💭</span>
                    <span>Hoy, 10:30 AM</span>
                </button>
                <button class="menu-btn">
                    <span class="menu-btn-icon">💭</span>
                    <span>Ayer, 3:15 PM</span>
                </button>
                <button class="menu-btn">
                    <span class="menu-btn-icon">💭</span>
                    <span>15 Ene, 8:45 PM</span>
                </button>
            </div>
        </div>

        <!-- Footer -->
        <div class="sidebar-footer">
            <button class="menu-btn" onclick="openProfile()">
                <span class="menu-btn-icon">👤</span>
                <span>Mi Cuenta</span>
            </button>
            <p class="sidebar-version">Mentta v2.0</p>
        </div>
    </aside>

    <!-- Header -->
    <header class="fixed top-0 left-0 right-0 z-40 header-container">
        <div class="max-w-3xl mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <!-- Hamburger Button -->
                <button class="hamburger-btn" onclick="toggleMenu()" aria-label="Abrir menú">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <span id="notification-badge" class="notification-badge hidden">3</span>
                </button>

                <!-- Logo -->
                <div class="flex items-center gap-2.5">
                    <div
                        class="w-10 h-10 bg-gradient-to-br from-mentta-primary to-mentta-accent rounded-xl flex items-center justify-center shadow-md">
                        <span class="text-white font-bold text-lg">M</span>
                    </div>
                    <div>
                        <h1 class="text-lg font-semibold text-mentta-primary">Mentta</h1>
                        <p class="text-xs text-mentta-accent font-sans">Tu espacio seguro</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span class="text-sm hidden sm:inline text-mentta-secondary font-sans">Ana</span>
                <button onclick="openLogoutModal()"
                    class="p-2 text-mentta-accent hover:text-mentta-primary transition-colors" title="Cerrar sesión">
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
    <main class="pt-16 pb-40">
        <!-- Sentiment Indicator (Managed by chat.js) -->
        <div id="sentimentIndicator"
            class="hidden fixed top-20 right-4 bg-white/80 backdrop-blur px-3 py-2 rounded-full shadow-sm z-30 flex items-center gap-1.5 transition-all duration-300">
            <div id="moodDot1" class="w-2 h-2 rounded-full bg-gray-300 transition-colors duration-300"></div>
            <div id="moodDot2" class="w-2 h-2 rounded-full bg-gray-300 transition-colors duration-300"></div>
            <div id="moodDot3" class="w-2 h-2 rounded-full bg-gray-300 transition-colors duration-300"></div>
            <div id="moodDot4" class="w-2 h-2 rounded-full bg-gray-300 transition-colors duration-300"></div>
            <div id="moodDot5" class="w-2 h-2 rounded-full bg-gray-300 transition-colors duration-300"></div>
        </div>

        <div class="max-w-3xl mx-auto">
            <!-- Messages Container -->
            <div id="messagesContainer" class="min-h-[calc(100vh-16rem)] px-4 py-6">

                <!-- Welcome Message -->
                <div id="welcomeMessage" class="welcome-container">
                    <div class="welcome-icon">
                        ☕
                    </div>
                    <h2 class="welcome-title">Hola, Ana 👋</h2>
                    <p class="welcome-text">
                        Este es un espacio seguro y sin juicios. Estoy aquí para escucharte, cualquier cosa que
                        necesites compartir.
                    </p>

                    <!-- Conversation Starters -->
                    <div class="welcome-suggestions">
                        <button class="suggestion-btn" onclick="sendSuggestion('Me siento un poco ansioso/a hoy')">
                            Me siento ansioso/a hoy
                        </button>
                        <button class="suggestion-btn" onclick="sendSuggestion('Necesito hablar sobre algo que pasó')">
                            Necesito hablar de algo
                        </button>
                        <button class="suggestion-btn" onclick="sendSuggestion('¿Puedes ayudarme a calmarme?')">
                            Ayúdame a calmarme
                        </button>
                        <button class="suggestion-btn" onclick="sendSuggestion('Solo necesito desahogarme')">
                            Solo necesito desahogarme
                        </button>
                    </div>
                </div>

                <!-- Sample Messages (for demo) -->
                <!-- 
                <div class="message message-user">
                    <div class="flex items-end gap-3 justify-end">
                        <div>
                            <div class="message-bubble">
                                Hola, me siento un poco abrumada con todo lo que está pasando últimamente.
                            </div>
                            <div class="message-time text-right">10:30 AM</div>
                        </div>
                        <div class="message-avatar">A</div>
                    </div>
                </div>

                <div class="message message-ai">
                    <div class="flex items-end gap-3">
                        <div class="message-avatar">M</div>
                        <div>
                            <div class="message-bubble">
                                Hola Ana, gracias por compartir eso conmigo. Siento que estés sintiendo esa abrumación. Es completamente válido sentirse así cuando las cosas se acumulan. ¿Te gustaría contarme más sobre lo que está pasando? Estoy aquí para escucharte sin juzgarte.
                            </div>
                            <div class="message-time">10:30 AM</div>
                        </div>
                    </div>
                </div>
                -->
            </div>
        </div>
    </main>

    <!-- Loading Indicator -->
    <div id="loadingIndicator" class="loading-indicator">
        <div class="typing-dots">
            <div class="typing-dot"></div>
            <div class="typing-dot"></div>
            <div class="typing-dot"></div>
        </div>
        <span class="loading-text">Mentta está escribiendo...</span>
    </div>

    <!-- Input Area -->
    <div class="fixed bottom-0 left-0 right-0 input-container">
        <div class="max-w-3xl mx-auto px-4 py-4">
            <div class="input-wrapper">
                <div class="flex items-end gap-2 p-3">
                    <textarea id="messageInput" placeholder="Escribe lo que sientes... puedo escucharte"
                        class="message-input flex-1 min-h-[44px] max-h-[120px] px-2 py-2" rows="1"
                        onkeydown="handleKeyDown(event)" oninput="autoResize(this)"></textarea>
                    <button id="sendButton" onclick="sendMessage()" class="send-button" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path
                                d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                        </svg>
                    </button>
                </div>
            </div>
            <p class="comfort-note">
                <span class="safe-indicator">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                            clip-rule="evenodd" />
                    </svg>
                    Confidencial y seguro
                </span>
                · Todo lo que compartas está protegido
            </p>
        </div>
    </div>

    <!-- Logout Modal -->
    <div id="logoutModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="text-5xl mb-4">👋</div>
                <h3 class="modal-title">¿Cerrar sesión?</h3>
                <p class="modal-text">
                    Siempre estaré aquí cuando necesites hablar. Cuídate mucho y recuerda que no estás solo/a. 💚
                </p>
                <div class="modal-buttons">
                    <button onclick="closeLogoutModal()" class="modal-btn modal-btn-secondary">
                        Quedarme
                    </button>
                    <button onclick="logout()" class="modal-btn modal-btn-primary">
                        Cerrar Sesión
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Resources Modal (Expanded) -->
    <div id="resourcesModal" class="modal-overlay z-[70]">
        <div class="modal-content !max-w-4xl max-h-[90vh] flex flex-col">
            <div class="flex items-center justify-between p-6 border-b border-gray-100">
                <h3 class="text-xl font-serif font-bold text-mentta-primary">Recursos de Bienestar</h3>
                <button onclick="closeResourcesModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <div class="p-6 overflow-y-auto custom-scrollbar space-y-8">

                <!-- Actionable Tool 1: Guided Breathing -->
                <section class="bg-blue-50/50 rounded-2xl p-6 border border-blue-100">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex gap-4">
                            <div
                                class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-2xl shrink-0">
                                🌬️
                            </div>
                            <div>
                                <h4 class="text-lg font-bold text-gray-800 font-serif">Respiración Guiada</h4>
                                <p class="text-sm text-gray-600">3 minutos para reducir tu ritmo cardíaco.</p>
                            </div>
                        </div>
                        <button onclick="startBreathingExercise()"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium text-sm transition-colors shadow-sm">
                            Iniciar Ahora
                        </button>
                    </div>

                    <!-- Hidden active state for breathing -->
                    <div id="breathing-widget" class="hidden mt-6 text-center">
                        <div
                            class="relative w-32 h-32 mx-auto mb-4 bg-blue-200 rounded-full flex items-center justify-center animate-pulse">
                            <span id="breathing-text"
                                class="font-serif text-lg font-bold text-blue-800">Inhala...</span>
                        </div>
                        <p class="text-xs text-blue-600">Sigue el ritmo del círculo</p>
                    </div>
                </section>

                <!-- Actionable Tool 2: Grounding Helper -->
                <section class="bg-green-50/50 rounded-2xl p-6 border border-green-100">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex gap-4">
                            <div
                                class="w-12 h-12 rounded-xl bg-green-100 text-green-600 flex items-center justify-center text-2xl shrink-0">
                                🦶
                            </div>
                            <div>
                                <h4 class="text-lg font-bold text-gray-800 font-serif">Grounding (5-4-3-2-1)</h4>
                                <p class="text-sm text-gray-600">Herramienta interactiva para volver al presente.</p>
                            </div>
                        </div>
                        <button onclick="startGrounding()"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium text-sm transition-colors shadow-sm">
                            Comenzar
                        </button>
                    </div>

                    <div id="grounding-widget"
                        class="hidden mt-4 bg-white p-4 rounded-xl border border-green-100 shadow-sm">
                        <div id="grounding-step-container">
                            <h5 class="font-bold text-gray-700 mb-2">Paso 1: Vista 👀</h5>
                            <p class="text-sm text-gray-600 mb-4">Encuentra <span class="font-bold text-green-600">5
                                    cosas</span> que puedas ver a tu alrededor y tócalas en la pantalla si puedes, o
                                dilas en voz alta.</p>
                            <button onclick="nextGroundingStep()"
                                class="w-full py-2 bg-green-100 text-green-700 rounded-lg text-sm font-bold hover:bg-green-200">
                                Listo, siguiente paso ➡️
                            </button>
                        </div>
                    </div>
                </section>

                <!-- Actionable Tool 3: Crisis Plan -->
                <section class="bg-red-50/50 rounded-2xl p-6 border border-red-100">
                    <div class="flex gap-4">
                        <div
                            class="w-12 h-12 rounded-xl bg-red-100 text-red-600 flex items-center justify-center text-2xl shrink-0">
                            🛡️
                        </div>
                        <div>
                            <h4 class="text-lg font-bold text-gray-800 font-serif mb-1">Plan de Crisis</h4>
                            <p class="text-sm text-gray-600 mb-3">Accede rápidamente a tus contactos seguros.</p>
                            <div class="flex gap-2">
                                <button onclick="window.location.href='profile.php'"
                                    class="text-xs bg-white border border-red-200 text-red-600 px-3 py-1.5 rounded-md hover:bg-red-50 font-medium">
                                    Ver mis contactos
                                </button>
                                <button onclick="openCrisisModal()"
                                    class="text-xs bg-red-600 text-white px-3 py-1.5 rounded-md hover:bg-red-700 font-medium shadow-sm">
                                    Pedir Ayuda
                                </button>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <div class="p-4 border-t border-gray-100 bg-gray-50/50 rounded-b-2xl">
                <p class="text-center text-xs text-gray-500">
                    Recuerda: Mentta es una herramienta de apoyo. Si estás en crisis, usa el botón SOS.
                </p>
            </div>
        </div>
    </div>
    <!-- Libraries -->
    <script src="assets/js/utils.js"></script>
    <script src="assets/js/chat.js"></script>

    <!-- UI Logic (Sidebar & Helpers) -->
    <script>
        // Sidebar Logic
        function toggleMenu() {
            const menu = document.getElementById('sidebar-menu');
            const backdrop = document.getElementById('sidebar-backdrop');
            if (menu && backdrop) {
                menu.classList.toggle('active');
                backdrop.classList.toggle('active');
            }
        }

        function closeMenu() {
            const menu = document.getElementById('sidebar-menu');
            const backdrop = document.getElementById('sidebar-backdrop');
            if (menu && backdrop) {
                menu.classList.remove('active');
                backdrop.classList.remove('active');
            }
        }

        // Suggestion Chips Adapter
        function sendSuggestion(text) {
            const input = document.getElementById('messageInput');
            if (input) {
                input.value = text;
                // Trigger input event to ensure UI updates (button state)
                input.dispatchEvent(new Event('input', { bubbles: true }));

                // Call the global sendMessage from chat.js
                if (window.sendMessage) {
                    window.sendMessage();
                } else {
                    console.error('Chat logic not loaded yet');
                }
            }
        }

        // Resources Modal Logic
        function openResourcesModal() {
            const modal = document.getElementById('resourcesModal');
            if (modal) {
                modal.classList.add('active');
            } else {
                console.error('Resources modal not found');
            }
        }

        function closeResourcesModal() {
            const modal = document.getElementById('resourcesModal');
            if (modal) {
                modal.classList.remove('active');
            }
        }

        // Header Logout Adapter
        function openLogoutModal() {
            // Use the modal already present in chat.php
            const modal = document.getElementById('logoutModal');
            if (modal) {
                modal.classList.add('active');
            }
        }

        function closeLogoutModal() {
            const modal = document.getElementById('logoutModal');
            if (modal) {
                modal.classList.remove('active');
            }
        }

        function logout() {
            window.location.href = 'logout.php'; // Corrected path
        }

        // Sidebar Actions
        async function newChat() {
            try {
                // Call API to get a distinct session ID
                const response = await Utils.api('api/chat/new-session.php', { method: 'POST' });

                if (response.success && response.data.session_id) {
                    // Redirect to chat with clean session
                    window.location.href = 'chat.php?session_id=' + response.data.session_id;
                } else {
                    console.error('Failed to create session');
                    // Fallback: reload to at least clear input
                    window.location.reload();
                }
            } catch (e) {
                console.error(e);
                window.location.reload();
            }
        }

        // Logic for Breathing Tool
        function startBreathingExercise() {
            const widget = document.getElementById('breathing-widget');
            const text = document.getElementById('breathing-text');
            if (widget) {
                widget.classList.remove('hidden');

                // Simple animation logic
                let isInhaling = true;
                setInterval(() => {
                    if (isInhaling) {
                        text.innerText = 'Exhala...';
                        isInhaling = false;
                    } else {
                        text.innerText = 'Inhala...';
                        isInhaling = true;
                    }
                }, 4000); // 4 seconds interval
            }
        }

        // Logic for Grounding Tool
        let groundingStep = 0;
        const groundingSteps = [
            { count: 5, action: 'Cosas que puedes VER 👀' },
            { count: 4, action: 'Cosas que puedes TOCAR ✋' },
            { count: 3, action: 'Cosas que puedes ESCUCHAR 👂' },
            { count: 2, action: 'Cosas que puedes OLER 👃' },
            { count: 1, action: 'Cosa que puedes SABOREAR 👅' }
        ];

        function startGrounding() {
            document.getElementById('grounding-widget').classList.remove('hidden');
            groundingStep = 0;
            updateGroundingStep();
        }

        function nextGroundingStep() {
            groundingStep++;
            if (groundingStep >= groundingSteps.length) {
                // Done
                document.getElementById('grounding-step-container').innerHTML = `
                    <h5 class="font-bold text-green-700 mb-2">¡Muy bien! 👏</h5>
                    <p class="text-sm text-gray-600 mb-4">Te has conectado con el presente. ¿Cómo te sientes?</p>
                    <button onclick="closeResourcesModal()" class="w-full py-2 bg-green-600 text-white rounded-lg text-sm font-bold">Volver al chat</button>
                `;
            } else {
                updateGroundingStep();
            }
        }

        function updateGroundingStep() {
            const step = groundingSteps[groundingStep];
            document.getElementById('grounding-step-container').innerHTML = `
                 <h5 class="font-bold text-gray-700 mb-2">Paso ${groundingStep + 1}: ${step.count}</h5>
                 <p class="text-sm text-gray-600 mb-4">Encuentra <span class="font-bold text-green-600">${step.count} ${step.action}</span>.</p>
                 <button onclick="nextGroundingStep()" class="w-full py-2 bg-green-100 text-green-700 rounded-lg text-sm font-bold hover:bg-green-200">
                    Listo, siguiente paso ➡️
                 </button>
            `;
        }
    </script>
</body>

</html>