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

    <style>
        :root {
            /* Colors */
            --bg-primary: #f5f5f0;
            --bg-secondary: #eeebe6;
            --bg-tertiary: #ffffff;
            --bg-chat: #fafaf8;
            --bg-message-user: #2d3a2d;
            --bg-message-ai: #ffffff;
            --text-primary: #2d3a2d;
            --text-secondary: #5a6b5a;
            --text-tertiary: #8b9d8b;
            --border-color: #e5e2dc;
            --accent-color: #cbaa8e;
            --success-color: #7fa87f;
            --shadow-soft: 0 2px 8px rgba(45, 58, 45, 0.04);
            --shadow-medium: 0 4px 16px rgba(45, 58, 45, 0.08);
            --shadow-strong: 0 8px 24px rgba(45, 58, 45, 0.12);
        }

        body {
            font-family: 'Spectral', serif;
            background-color: var(--bg-primary);
            color: var(--text-primary);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        h1,
        h2,
        h3,
        h4 {
            font-family: 'Crimson Pro', serif;
        }

        .font-sans {
            font-family: 'DM Sans', sans-serif;
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInFromLeft {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideInFromRight {
            from {
                opacity: 0;
                transform: translateX(20px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes breathe {

            0%,
            100% {
                transform: scale(1);
                opacity: 0.6;
            }

            50% {
                transform: scale(1.05);
                opacity: 0.8;
            }
        }

        /* Sidebar */
        .sidebar-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(45, 58, 45, 0.4);
            backdrop-filter: blur(4px);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
            z-index: 40;
        }

        .sidebar-backdrop.active {
            opacity: 1;
            pointer-events: all;
        }

        .sidebar-menu {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 320px;
            background: var(--bg-tertiary);
            box-shadow: var(--shadow-strong);
            transform: translateX(-100%);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 50;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .sidebar-menu.active {
            transform: translateX(0);
        }

        .sidebar-header {
            padding: 1.5rem;
            background: linear-gradient(135deg, var(--bg-message-user) 0%, #3a4a3a 100%);
            color: white;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-user-info h3 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .sidebar-user-info p {
            font-size: 0.875rem;
            opacity: 0.8;
            font-family: 'DM Sans', sans-serif;
        }

        .mood-badge {
            display: inline-block;
            margin-top: 0.75rem;
            padding: 0.375rem 0.75rem;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 1rem;
            font-size: 0.75rem;
            font-family: 'DM Sans', sans-serif;
        }

        .sidebar-content {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
        }

        .sidebar-section {
            margin-bottom: 1.5rem;
        }

        .sidebar-section-title {
            padding: 0.5rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-tertiary);
            font-family: 'DM Sans', sans-serif;
        }

        .menu-btn {
            width: 100%;
            padding: 0.875rem 1rem;
            border-radius: 0.75rem;
            background: transparent;
            border: none;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.9375rem;
            color: var(--text-primary);
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: 'DM Sans', sans-serif;
        }

        .menu-btn:hover {
            background: var(--bg-secondary);
            transform: translateX(2px);
        }

        .menu-btn-icon {
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .menu-btn-crisis {
            background: #fee;
            color: #c44;
        }

        .menu-btn-crisis:hover {
            background: #fdd;
        }

        .menu-btn-resources {
            background: #efe;
            color: #484;
        }

        .menu-btn-resources:hover {
            background: #dfd;
        }

        .sidebar-footer {
            padding: 1rem;
            border-top: 1px solid var(--border-color);
        }

        .sidebar-version {
            text-align: center;
            font-size: 0.75rem;
            color: var(--text-tertiary);
            margin-top: 0.5rem;
            font-family: 'DM Sans', sans-serif;
        }

        /* Header */
        .header-container {
            background: var(--bg-tertiary);
            border-bottom: 1px solid var(--border-color);
            box-shadow: var(--shadow-soft);
        }

        .hamburger-btn {
            position: relative;
            padding: 0.625rem;
            border-radius: 0.75rem;
            background: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .hamburger-btn:hover {
            background: var(--bg-secondary);
            border-color: var(--accent-color);
        }

        .hamburger-btn svg {
            width: 1.25rem;
            height: 1.25rem;
        }

        .notification-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            min-width: 18px;
            height: 18px;
            padding: 0 4px;
            background: #e74c3c;
            color: white;
            border-radius: 9px;
            font-size: 0.625rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'DM Sans', sans-serif;
        }

        /* Messages */
        .message {
            animation: fadeIn 0.4s ease-out;
            margin-bottom: 1.25rem;
        }

        .message-user {
            animation: slideInFromRight 0.4s ease-out;
        }

        .message-ai {
            animation: slideInFromLeft 0.4s ease-out;
        }

        .message-bubble {
            max-width: 85%;
            padding: 1rem 1.25rem;
            border-radius: 1.25rem;
            line-height: 1.6;
            font-size: 1rem;
        }

        .message-user .message-bubble {
            background: var(--bg-message-user);
            color: white;
            border-bottom-right-radius: 0.375rem;
            margin-left: auto;
            box-shadow: var(--shadow-medium);
        }

        .message-ai .message-bubble {
            background: var(--bg-message-ai);
            color: var(--text-primary);
            border-bottom-left-radius: 0.375rem;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-soft);
        }

        .message-avatar {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1.125rem;
            flex-shrink: 0;
        }

        .message-user .message-avatar {
            background: linear-gradient(135deg, var(--accent-color), #b89876);
            color: white;
        }

        .message-ai .message-avatar {
            background: linear-gradient(135deg, var(--bg-message-user), #3a4a3a);
            color: white;
        }

        .message-time {
            font-size: 0.75rem;
            color: var(--text-tertiary);
            margin-top: 0.375rem;
            font-family: 'DM Sans', sans-serif;
        }

        /* Welcome */
        .welcome-container {
            text-align: center;
            padding: 3rem 1rem;
            animation: fadeIn 0.6s ease-out;
        }

        .welcome-icon {
            width: 5rem;
            height: 5rem;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, var(--bg-message-user) 0%, #3a4a3a 100%);
            border-radius: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            box-shadow: var(--shadow-medium);
            animation: breathe 3s ease-in-out infinite;
        }

        .welcome-title {
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.75rem;
        }

        .welcome-text {
            font-size: 1.125rem;
            color: var(--text-secondary);
            max-width: 28rem;
            margin: 0 auto;
            line-height: 1.6;
        }

        .welcome-suggestions {
            margin-top: 2rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            justify-content: center;
        }

        .suggestion-btn {
            padding: 0.75rem 1.25rem;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-radius: 1.5rem;
            color: var(--text-primary);
            font-size: 0.9375rem;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: 'DM Sans', sans-serif;
            box-shadow: var(--shadow-soft);
        }

        .suggestion-btn:hover {
            border-color: var(--accent-color);
            background: var(--bg-secondary);
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        /* Input Area */
        .input-container {
            background: var(--bg-tertiary);
            border-top: 1px solid var(--border-color);
            box-shadow: 0 -4px 16px rgba(45, 58, 45, 0.04);
        }

        .input-wrapper {
            background: var(--bg-secondary);
            border: 2px solid var(--border-color);
            border-radius: 1.5rem;
            transition: all 0.3s ease;
        }

        .input-wrapper:focus-within {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 4px rgba(203, 170, 142, 0.1);
        }

        .message-input {
            background: transparent;
            border: none;
            outline: none;
            color: var(--text-primary);
            font-size: 1rem;
            font-family: 'Spectral', serif;
            resize: none;
            line-height: 1.5;
        }

        .message-input::placeholder {
            color: var(--text-tertiary);
        }

        .send-button {
            width: 2.75rem;
            height: 2.75rem;
            background: linear-gradient(135deg, var(--bg-message-user) 0%, #3a4a3a 100%);
            border: none;
            border-radius: 50%;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-medium);
        }

        .send-button:hover:not(:disabled) {
            transform: scale(1.05);
            box-shadow: var(--shadow-strong);
        }

        .send-button:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        /* Loading Indicator */
        .loading-indicator {
            position: fixed;
            bottom: 9rem;
            left: 50%;
            transform: translateX(-50%);
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-radius: 1.5rem;
            padding: 1rem 1.5rem;
            box-shadow: var(--shadow-medium);
            display: none;
            align-items: center;
            gap: 0.75rem;
            animation: fadeIn 0.3s ease-out;
        }

        .loading-indicator.active {
            display: flex;
        }

        .typing-dots {
            display: flex;
            gap: 0.375rem;
        }

        .typing-dot {
            width: 0.5rem;
            height: 0.5rem;
            background: var(--accent-color);
            border-radius: 50%;
            animation: bounce 1.4s infinite ease-in-out;
        }

        .typing-dot:nth-child(1) {
            animation-delay: -0.32s;
        }

        .typing-dot:nth-child(2) {
            animation-delay: -0.16s;
        }

        @keyframes bounce {

            0%,
            80%,
            100% {
                transform: scale(0);
                opacity: 0.5;
            }

            40% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .loading-text {
            color: var(--text-secondary);
            font-size: 0.9375rem;
            font-family: 'DM Sans', sans-serif;
        }

        /* Comfort Features */
        .comfort-note {
            text-align: center;
            padding: 0.75rem;
            font-size: 0.8125rem;
            color: var(--text-tertiary);
            font-family: 'DM Sans', sans-serif;
        }

        .safe-indicator {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            color: var(--success-color);
        }

        /* Modal */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(45, 58, 45, 0.5);
            backdrop-filter: blur(8px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 60;
            padding: 1rem;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-content {
            background: var(--bg-tertiary);
            border-radius: 1.5rem;
            max-width: 28rem;
            width: 100%;
            box-shadow: var(--shadow-strong);
            animation: fadeIn 0.3s ease-out;
        }

        .modal-body {
            padding: 2rem;
        }

        .modal-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.75rem;
        }

        .modal-text {
            color: var(--text-secondary);
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .modal-buttons {
            display: flex;
            gap: 0.75rem;
        }

        .modal-btn {
            flex: 1;
            padding: 0.875rem;
            border-radius: 0.75rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            font-family: 'DM Sans', sans-serif;
        }

        .modal-btn-secondary {
            background: var(--bg-secondary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }

        .modal-btn-secondary:hover {
            background: var(--bg-primary);
        }

        .modal-btn-primary {
            background: var(--bg-message-user);
            color: white;
        }

        .modal-btn-primary:hover {
            background: #3a4a3a;
        }

        /* Responsive */
        @media (max-width: 640px) {
            .sidebar-menu {
                width: 280px;
            }

            .message-bubble {
                max-width: 90%;
                font-size: 0.9375rem;
            }

            .welcome-title {
                font-size: 1.5rem;
            }

            .welcome-text {
                font-size: 1rem;
            }
        }
    </style>
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

    <!-- JavaScript -->
    <script>
        // Menu Toggle
        function toggleMenu() {
            const menu = document.getElementById('sidebar-menu');
            const backdrop = document.getElementById('sidebar-backdrop');
            menu.classList.toggle('active');
            backdrop.classList.toggle('active');
        }

        function closeMenu() {
            const menu = document.getElementById('sidebar-menu');
            const backdrop = document.getElementById('sidebar-backdrop');
            menu.classList.remove('active');
            backdrop.classList.remove('active');
        }

        // Auto-resize textarea
        function autoResize(textarea) {
            textarea.style.height = 'auto';
            textarea.style.height = textarea.scrollHeight + 'px';

            // Enable/disable send button
            const sendBtn = document.getElementById('sendButton');
            sendBtn.disabled = !textarea.value.trim();
        }

        // Handle Enter key
        function handleKeyDown(event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                sendMessage();
            }
        }

        // Send message
        function sendMessage() {
            const input = document.getElementById('messageInput');
            const message = input.value.trim();

            if (!message) return;

            // Hide welcome message
            const welcome = document.getElementById('welcomeMessage');
            if (welcome) welcome.style.display = 'none';

            // Add user message
            addMessage(message, 'user');

            // Clear input
            input.value = '';
            input.style.height = 'auto';
            document.getElementById('sendButton').disabled = true;

            // Show loading
            showLoading();

            // Simulate AI response
            setTimeout(() => {
                hideLoading();
                addMessage(
                    'Gracias por compartir eso conmigo. Entiendo que puede ser difícil. ¿Hay algo específico que te gustaría explorar más sobre lo que estás sintiendo?',
                    'ai'
                );
            }, 2000);
        }

        // Add message to chat
        function addMessage(text, sender) {
            const container = document.getElementById('messagesContainer');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message message-${sender}`;

            const now = new Date();
            const time = now.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit' });

            if (sender === 'user') {
                messageDiv.innerHTML = `
                    <div class="flex items-end gap-3 justify-end">
                        <div>
                            <div class="message-bubble">${escapeHtml(text)}</div>
                            <div class="message-time text-right">${time}</div>
                        </div>
                        <div class="message-avatar">A</div>
                    </div>
                `;
            } else {
                messageDiv.innerHTML = `
                    <div class="flex items-end gap-3">
                        <div class="message-avatar">M</div>
                        <div>
                            <div class="message-bubble">${escapeHtml(text)}</div>
                            <div class="message-time">${time}</div>
                        </div>
                    </div>
                `;
            }

            container.appendChild(messageDiv);
            messageDiv.scrollIntoView({ behavior: 'smooth', block: 'end' });
        }

        // Show/Hide Loading
        function showLoading() {
            document.getElementById('loadingIndicator').classList.add('active');
        }

        function hideLoading() {
            document.getElementById('loadingIndicator').classList.remove('active');
        }

        // Suggestion buttons
        function sendSuggestion(text) {
            const input = document.getElementById('messageInput');
            input.value = text;
            autoResize(input);
            sendMessage();
        }

        // Logout modal
        function openLogoutModal() {
            document.getElementById('logoutModal').classList.add('active');
        }

        function closeLogoutModal() {
            document.getElementById('logoutModal').classList.remove('active');
        }

        function logout() {
            window.location.href = 'api/auth/logout.php';
        }

        // Helper function
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Placeholder functions
        function newChat() {
            location.reload();
        }

        function openCrisisModal() {
            alert('Modal de crisis - implementar según necesidad');
        }

        function openResourcesModal() {
            alert('Modal de recursos - implementar según necesidad');
        }

        function openProfile() {
            alert('Perfil de usuario - implementar según necesidad');
        }
    </script>
</body>

</html>