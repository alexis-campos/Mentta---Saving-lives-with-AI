<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentta - Tu Espacio de Bienestar Mental</title>
    <meta name="description"
        content="Un espacio seguro para tu bienestar emocional. Apoyo profesional, conversaciones con IA terapéutica y recursos de salud mental.">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --color-bg: #FAFAF8;
            --color-bg-subtle: #F5F4F1;
            --color-text: #3D3D3D;
            --color-text-soft: #6B6B6B;
            --color-text-muted: #9A9A9A;
            --color-accent: #7C9A8E;
            --color-accent-soft: #A8C4B8;
            --color-accent-muted: rgba(124, 154, 142, 0.15);
            --color-border: rgba(61, 61, 61, 0.08);
            --font-main: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            --transition-slow: 0.6s cubic-bezier(0.16, 1, 0.3, 1);
            --transition-medium: 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: var(--font-main);
            background: var(--color-bg);
            color: var(--color-text);
            line-height: 1.6;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* Scroll Container */
        .scroll-container {
            height: 180vh;
            position: relative;
        }

        /* Cinematic Section */
        .cinematic-section {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            transition: opacity 0.8s ease;
        }

        .cinematic-section.fade-out {
            opacity: 0;
            pointer-events: none;
        }

        .cinematic-frame {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .cinematic-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom,
                    rgba(250, 250, 248, 0.2) 0%,
                    rgba(250, 250, 248, 0) 40%,
                    rgba(250, 250, 248, 0.6) 80%,
                    rgba(250, 250, 248, 1) 100%);
            pointer-events: none;
        }

        .hero-cta.hidden {
            opacity: 0;
            transform: translateY(30px);
            pointer-events: none;
        }

        .branding-header {
            position: fixed;
            top: 2.5rem;
            right: 2.5rem;
            z-index: 100;
            display: flex;
            align-items: center;
            gap: 1.5rem;
            opacity: 0;
            animation: fadeIn 1.2s ease forwards;
            animation-delay: 0.8s;
        }

        .brand-info {
            display: flex;
            flex-direction: column;
            gap: 0.125rem;
        }

        .brand-name {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--color-text);
            letter-spacing: -0.02em;
            text-align: right;
        }

        .brand-summary {
            font-size: 0.75rem;
            color: var(--color-text-soft);
            max-width: 180px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            line-height: 1.4;
            text-align: right;
            /* Aligned to right */
        }

        .hero-headline {
            font-size: clamp(2rem, 5vw, 3.5rem);
            font-weight: 600;
            color: var(--color-text);
            margin-bottom: 1rem;
            letter-spacing: -0.02em;
            line-height: 1.2;
        }

        .hero-subtext {
            font-size: 1.125rem;
            color: var(--color-text-soft);
            max-width: 480px;
            margin-bottom: 2.5rem;
            font-weight: 400;
        }

        /* Primary Button */
        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 0.625rem;
            background: var(--color-accent);
            color: white;
            padding: 1rem 2rem;
            border-radius: 100px;
            font-size: 1rem;
            font-weight: 500;
            text-decoration: none;
            transition: all var(--transition-medium);
            border: none;
            cursor: pointer;
        }

        .btn-primary:hover {
            background: #6B8A7E;
            transform: translateY(-2px);
        }

        .btn-primary svg {
            width: 18px;
            height: 18px;
            transition: transform 0.3s ease;
        }

        .btn-primary:hover svg {
            transform: translateX(3px);
        }

        /* Scroll Indicator */
        .scroll-indicator {
            position: fixed;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%);
            z-index: 40;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            opacity: 1;
            transition: opacity 0.5s ease;
        }

        .scroll-indicator.hidden {
            opacity: 0;
            pointer-events: none;
        }

        .scroll-indicator span {
            font-size: 0.75rem;
            color: var(--color-text-muted);
            text-transform: uppercase;
            letter-spacing: 0.15em;
            font-weight: 500;
        }

        .scroll-indicator .line {
            width: 1px;
            height: 40px;
            background: linear-gradient(to bottom, var(--color-text-muted), transparent);
            animation: scrollLine 2s ease-in-out infinite;
        }

        @keyframes scrollLine {

            0%,
            100% {
                opacity: 0.3;
                transform: scaleY(1);
            }

            50% {
                opacity: 1;
                transform: scaleY(1.2);
            }
        }

        /* ========== CONTENT SECTIONS ========== */
        .content-wrapper {
            position: relative;
            z-index: 50;
            /* Above fixed hero */
            margin-top: 180vh;
            background: var(--color-bg);
            box-shadow: 0 -30px 60px rgba(0, 0, 0, 0.1);
        }

        /* Section Base */
        .section {
            padding: 8rem 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-narrow {
            max-width: 800px;
        }

        /* Fade-in Animation */
        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.8s cubic-bezier(0.16, 1, 0.3, 1),
                transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* About Section */
        .about-section {
            text-align: center;
            border-bottom: 1px solid var(--color-border);
        }

        .about-section h2 {
            font-size: clamp(1.75rem, 3vw, 2.5rem);
            font-weight: 600;
            color: var(--color-text);
            margin-bottom: 1.5rem;
            letter-spacing: -0.02em;
        }

        .about-section p {
            font-size: 1.125rem;
            color: var(--color-text-soft);
            line-height: 1.8;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Services Section */
        .services-section h2 {
            font-size: clamp(1.75rem, 3vw, 2.5rem);
            font-weight: 600;
            color: var(--color-text);
            text-align: center;
            margin-bottom: 4rem;
            letter-spacing: -0.02em;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
        }

        .service-card {
            background: var(--color-bg-subtle);
            border-radius: 16px;
            padding: 2.5rem 2rem;
            transition: all var(--transition-medium);
            border: 1px solid transparent;
        }

        .service-card:hover {
            background: white;
            border-color: var(--color-border);
            transform: translateY(-4px);
        }

        .service-card h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--color-text);
            margin-bottom: 0.75rem;
        }

        .service-card p {
            font-size: 0.9375rem;
            color: var(--color-text-soft);
            line-height: 1.7;
        }

        .service-card .service-tag {
            display: inline-block;
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--color-accent);
            background: var(--color-accent-muted);
            padding: 0.375rem 0.875rem;
            border-radius: 100px;
            margin-bottom: 1.25rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .service-illustration {
            width: 100%;
            height: 160px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--color-accent-muted) 0%, rgba(168, 196, 184, 0.05) 100%);
            border-radius: 12px;
            overflow: hidden;
        }

        .service-illustration svg {
            width: 100%;
            height: 100%;
            opacity: 0.9;
        }

        /* Why Section */
        .why-section {
            text-align: center;
            background: var(--color-bg-subtle);
            border-radius: 24px;
            padding: 5rem 3rem;
            margin: 0 2rem;
            max-width: 1000px;
            margin-left: auto;
            margin-right: auto;
        }

        .why-section h2 {
            font-size: clamp(1.75rem, 3vw, 2.5rem);
            font-weight: 600;
            color: var(--color-text);
            margin-bottom: 2rem;
            letter-spacing: -0.02em;
        }

        .why-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2.5rem;
            margin-top: 3rem;
        }

        .why-item h4 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--color-text);
            margin-bottom: 0.5rem;
        }

        .why-item p {
            font-size: 0.9375rem;
            color: var(--color-text-soft);
        }

        /* Final CTA */
        .final-cta {
            text-align: center;
            padding: 8rem 2rem;
        }

        .final-cta h2 {
            font-size: clamp(1.75rem, 3vw, 2.5rem);
            font-weight: 600;
            color: var(--color-text);
            margin-bottom: 1rem;
            letter-spacing: -0.02em;
        }

        .final-cta p {
            font-size: 1.125rem;
            color: var(--color-text-soft);
            max-width: 500px;
            margin: 0 auto 2.5rem;
        }

        /* Footer */
        .footer {
            padding: 3rem 2rem;
            text-align: center;
            border-top: 1px solid var(--color-border);
        }

        .footer p {
            font-size: 0.875rem;
            color: var(--color-text-muted);
        }

        .footer a {
            color: var(--color-text-soft);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer a:hover {
            color: var(--color-accent);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .section {
                padding: 5rem 1.5rem;
            }

            .why-section {
                margin: 0 1rem;
                padding: 3rem 1.5rem;
            }

            .services-grid {
                gap: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <!-- Branding Header -->
    <header class="branding-header">
        <div class="brand-info">
            <span class="brand-name">Mentta</span>
            <p class="brand-summary">Apoyo emocional e IA profesional</p>
        </div>
    </header>

    <!-- Progress Bar -->
    <div class="progress-bar" id="progress-bar"></div>

    <!-- Hero Section -->
    <div class="scroll-container" id="scroll-container">
        <!-- Hero Visual Section -->
        <section class="cinematic-section" id="cinematic-section">
            <!-- SVG Hero Illustration -->
            <svg class="cinematic-frame" viewBox="0 0 1920 1080" preserveAspectRatio="xMidYMid slice"
                xmlns="http://www.w3.org/2000/svg">
                <!-- Background -->
                <rect width="1920" height="1080" fill="#FAFAF8" />

                <!-- Subtle gradient overlay -->
                <defs>
                    <linearGradient id="bgGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#F5F4F1;stop-opacity:1" />
                        <stop offset="100%" style="stop-color:#FAFAF8;stop-opacity:1" />
                    </linearGradient>
                    <linearGradient id="accentGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#A8C4B8;stop-opacity:0.4" />
                        <stop offset="100%" style="stop-color:#7C9A8E;stop-opacity:0.2" />
                    </linearGradient>
                    <radialGradient id="glowGradient" cx="50%" cy="50%" r="50%">
                        <stop offset="0%" style="stop-color:#A8C4B8;stop-opacity:0.3" />
                        <stop offset="100%" style="stop-color:#A8C4B8;stop-opacity:0" />
                    </radialGradient>
                </defs>

                <rect width="1920" height="1080" fill="url(#bgGradient)" />

                <!-- Background organic shapes - right layer only -->
                <ellipse cx="1600" cy="200" rx="400" ry="300" fill="#A8C4B8" opacity="0.08" />
                <circle cx="960" cy="540" r="450" fill="url(#glowGradient)" />

                <!-- Flowing abstract lines -->
                <path d="M0 600 Q400 450 800 550 T1600 480 T1920 520" stroke="#A8C4B8" stroke-width="2" fill="none"
                    opacity="0.3" />
                <path d="M0 650 Q500 500 900 600 T1500 530 T1920 580" stroke="#7C9A8E" stroke-width="1.5" fill="none"
                    opacity="0.2" />
                <path d="M0 700 Q300 600 700 680 T1400 600 T1920 650" stroke="#A8C4B8" stroke-width="1" fill="none"
                    opacity="0.15" />

                <!-- Central composition - abstract human forms -->
                <g transform="translate(960, 480)">
                    <!-- Outer glow ring -->
                    <circle cx="0" cy="0" r="200" fill="none" stroke="#A8C4B8" stroke-width="1" opacity="0.2" />
                    <circle cx="0" cy="0" r="280" fill="none" stroke="#7C9A8E" stroke-width="0.5" opacity="0.1" />

                    <!-- Abstract figure - main -->
                    <g transform="translate(0, 0)">
                        <ellipse cx="0" cy="-60" rx="35" ry="40" fill="#A8C4B8" opacity="0.25" />
                        <ellipse cx="0" cy="30" rx="45" ry="70" fill="#7C9A8E" opacity="0.2" />
                        <circle cx="0" cy="-80" r="25" fill="#A8C4B8" opacity="0.3" />
                    </g>

                    <!-- Subtle connection detail -->
                    <ellipse cx="0" cy="0" rx="60" ry="40" fill="#7C9A8E" opacity="0.1" />
                </g>

                <!-- Decorative elements - floating shapes -->
                <circle cx="300" cy="300" r="8" fill="#7C9A8E" opacity="0.3" />
                <circle cx="350" cy="250" r="5" fill="#A8C4B8" opacity="0.4" />
                <circle cx="280" cy="350" r="6" fill="#7C9A8E" opacity="0.25" />

                <circle cx="1600" cy="700" r="10" fill="#A8C4B8" opacity="0.3" />
                <circle cx="1650" cy="750" r="6" fill="#7C9A8E" opacity="0.35" />
                <circle cx="1550" cy="680" r="4" fill="#A8C4B8" opacity="0.4" />

                <circle cx="1700" cy="350" r="7" fill="#7C9A8E" opacity="0.25" />
                <circle cx="1750" cy="400" r="5" fill="#A8C4B8" opacity="0.3" />

                <circle cx="150" cy="500" r="6" fill="#A8C4B8" opacity="0.3" />
                <circle cx="100" cy="550" r="4" fill="#7C9A8E" opacity="0.35" />

                <!-- Subtle leaf/organic shapes -->
                <path d="M1500 150 Q1530 120 1560 150 Q1530 180 1500 150" fill="#A8C4B8" opacity="0.2" />
                <path d="M1750 550 Q1780 520 1810 550 Q1780 580 1750 550" fill="#A8C4B8" opacity="0.18" />

                <!-- Bottom fade -->
                <rect x="0" y="900" width="1920" height="180" fill="url(#bgGradient)" opacity="0.8" />
            </svg>

            <div class="cinematic-overlay" style="z-index: 25;"></div>

            <!-- New Center Content (Visible Immediately) -->
            <div class="hero-center-content"
                style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 30; text-align: center; width: 100%; max-width: 800px; padding: 0 2rem;">
                <h2
                    style="font-size: 0.875rem; letter-spacing: 0.3em; text-transform: uppercase; color: var(--color-accent); margin-bottom: 2rem; font-weight: 600;">
                    Mentta</h2>
                <h1
                    style="font-size: clamp(2.5rem, 6vw, 4.5rem); font-weight: 600; line-height: 1.1; margin-bottom: 2.5rem; color: var(--color-text);">
                    Tu viaje hacia el bienestar <br><span
                        style="color: var(--color-text-soft); font-weight: 400; font-style: italic;">comienza
                        aquí.</span></h1>

                <a href="login.php" class="btn-primary" style="opacity: 1; pointer-events: auto;">
                    Comenzar
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </a>
            </div>
        </section>
    </div>

    <!-- Scroll Indicator -->
    <div class="scroll-indicator" id="scroll-indicator">
        <span>Explora</span>
        <div class="line"></div>
    </div>

    <!-- Content Wrapper -->
    <div class="content-wrapper">

        <!-- About Section -->
        <section class="section section-narrow about-section fade-in">
            <h2>Un enfoque centrado en ti</h2>
            <p>
                Creemos que el bienestar mental es un camino personal. Por eso, combinamos tecnología empática
                con atención profesional para ofrecerte un espacio donde puedas expresarte sin juicios,
                encontrar apoyo cuando lo necesites y avanzar a tu propio ritmo.
            </p>
        </section>

        <!-- Services Section -->
        <section class="section services-section">
            <h2 class="fade-in">Cómo podemos ayudarte</h2>
            <div class="services-grid">
                <div class="service-card fade-in">
                    <div class="service-illustration">
                        <svg viewBox="0 0 200 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <!-- AI Chat Illustration -->
                            <rect x="30" y="20" width="80" height="60" rx="8" fill="#A8C4B8" opacity="0.3" />
                            <rect x="35" y="25" width="50" height="6" rx="3" fill="#7C9A8E" opacity="0.6" />
                            <rect x="35" y="35" width="65" height="6" rx="3" fill="#7C9A8E" opacity="0.4" />
                            <rect x="35" y="45" width="40" height="6" rx="3" fill="#7C9A8E" opacity="0.4" />
                            <circle cx="145" cy="50" r="25" fill="#7C9A8E" opacity="0.2" />
                            <circle cx="145" cy="50" r="15" fill="#7C9A8E" opacity="0.4" />
                            <path d="M140 45 L145 50 L150 45" stroke="#7C9A8E" stroke-width="2" stroke-linecap="round"
                                fill="none" />
                            <path d="M140 50 L145 55 L150 50" stroke="#7C9A8E" stroke-width="2" stroke-linecap="round"
                                fill="none" />
                            <circle cx="60" cy="95" r="4" fill="#A8C4B8" />
                            <circle cx="75" cy="95" r="4" fill="#7C9A8E" opacity="0.6" />
                            <circle cx="90" cy="95" r="4" fill="#7C9A8E" opacity="0.3" />
                        </svg>
                    </div>
                    <span class="service-tag">Disponible 24/7</span>
                    <h3>Conversación con IA</h3>
                    <p>Un asistente terapéutico que te escucha sin juzgar. Técnicas de respiración, reflexión guiada y
                        apoyo emocional inmediato.</p>
                </div>
                <div class="service-card fade-in">
                    <div class="service-illustration">
                        <svg viewBox="0 0 200 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <!-- Map Illustration -->
                            <rect x="25" y="15" width="150" height="90" rx="8" fill="#A8C4B8" opacity="0.15" />
                            <path d="M40 85 L60 55 L80 70 L110 35 L140 60 L160 40" stroke="#7C9A8E" stroke-width="2"
                                stroke-linecap="round" fill="none" opacity="0.4" />
                            <circle cx="85" cy="50" r="20" fill="#7C9A8E" opacity="0.15" />
                            <circle cx="85" cy="50" r="10" fill="#7C9A8E" opacity="0.3" />
                            <circle cx="85" cy="50" r="4" fill="#7C9A8E" />
                            <path d="M85 30 L85 25" stroke="#7C9A8E" stroke-width="2" stroke-linecap="round" />
                            <path d="M105 50 L110 50" stroke="#7C9A8E" stroke-width="2" stroke-linecap="round" />
                            <path d="M85 70 L85 75" stroke="#7C9A8E" stroke-width="2" stroke-linecap="round" />
                            <path d="M65 50 L60 50" stroke="#7C9A8E" stroke-width="2" stroke-linecap="round" />
                            <circle cx="140" cy="75" r="12" fill="#A8C4B8" opacity="0.4" />
                            <path d="M140 69 L140 75 L145 75" stroke="#7C9A8E" stroke-width="1.5"
                                stroke-linecap="round" />
                        </svg>
                    </div>
                    <span class="service-tag">Geolocalización</span>
                    <h3>Recursos cercanos</h3>
                    <p>Encuentra centros de atención, líneas de ayuda y profesionales cerca de ti. En momentos
                        difíciles, la ayuda está a un toque.</p>
                </div>
                <div class="service-card fade-in">
                    <div class="service-illustration">
                        <svg viewBox="0 0 200 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <!-- Video Call Illustration -->
                            <rect x="30" y="25" width="90" height="70" rx="8" fill="#A8C4B8" opacity="0.25" />
                            <rect x="35" y="30" width="80" height="50" rx="4" fill="#7C9A8E" opacity="0.15" />
                            <circle cx="75" cy="50" r="15" fill="#7C9A8E" opacity="0.3" />
                            <circle cx="75" cy="47" r="5" fill="#7C9A8E" opacity="0.5" />
                            <path d="M67 58 Q75 62 83 58" stroke="#7C9A8E" stroke-width="2" stroke-linecap="round"
                                fill="none" opacity="0.5" />
                            <polygon points="130,40 160,55 130,70" fill="#7C9A8E" opacity="0.4" />
                            <rect x="125" y="38" width="10" height="34" rx="2" fill="#A8C4B8" opacity="0.3" />
                            <circle cx="55" cy="85" r="6" fill="#7C9A8E" opacity="0.3" />
                            <rect x="70" y="82" width="30" height="6" rx="3" fill="#A8C4B8" opacity="0.4" />
                        </svg>
                    </div>
                    <span class="service-tag">Profesionales</span>
                    <h3>Videollamadas</h3>
                    <p>Conecta con psicólogos certificados desde la comodidad de tu hogar. Sesiones seguras, privadas y
                        a tu horario.</p>
                </div>
            </div>
        </section>

        <!-- Why Section -->
        <section class="why-section fade-in">
            <h2>¿Por qué elegir este espacio?</h2>
            <div class="why-grid">
                <div class="why-item">
                    <h4>Confidencial</h4>
                    <p>Tu privacidad es sagrada. Encriptación total.</p>
                </div>
                <div class="why-item">
                    <h4>Sin presiones</h4>
                    <p>Avanza a tu ritmo, sin compromisos.</p>
                </div>
                <div class="why-item">
                    <h4>Siempre disponible</h4>
                    <p>Apoyo las 24 horas, cada día del año.</p>
                </div>
                <div class="why-item">
                    <h4>Humano + IA</h4>
                    <p>Lo mejor de ambos mundos, cuando lo necesites.</p>
                </div>
            </div>
        </section>

        <!-- Final CTA -->
        <section class="final-cta fade-in">
            <h2>Tu bienestar importa</h2>
            <p>El primer paso siempre es el más difícil. Estamos aquí para acompañarte.</p>
            <a href="login.php" class="btn-primary">
                Dar el primer paso
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </a>
        </section>

        <!-- Footer -->
        <footer class="footer">
            <p>© 2026 Mentta · <a href="#">Privacidad</a> · <a href="#">Términos</a></p>
        </footer>

    </div>

    <script>
        // Configuration
        const frameCount = 80;
        const folderPath = 'images/Smooth_cinematic_transition_202602032027_smq_000/';
        const baseFileName = 'Smooth_cinematic_transition_202602032027_smq_';

        // Elements
        const cinematicSection = document.getElementById('cinematic-section');
        const scrollIndicator = document.getElementById('scroll-indicator');
        const scrollContainer = document.getElementById('scroll-container');

        // Preload images
        const images = [];
        let loadedCount = 0;

        function preloadImages() {
            // No images to preload as requested
        }

        // Update frame based on scroll
        function updateFrame() {
            const scrollTop = window.scrollY;
            const maxScroll = scrollContainer.offsetHeight - window.innerHeight;
            const scrollProgress = Math.min(scrollTop / maxScroll, 1);

            const frameIndex = Math.min(
                Math.floor(scrollProgress * frameCount),
                frameCount - 1
            );

            // Hide scroll indicator
            if (scrollProgress > 0.6) {
                scrollIndicator.classList.add('hidden');
            } else {
                scrollIndicator.classList.remove('hidden');
            }

            // Fade out hero content as user scrolls down
            const fadeThreshold = 0.7;
            if (scrollProgress > fadeThreshold) {
                const heroOpacity = 1 - ((scrollProgress - fadeThreshold) / (1 - fadeThreshold));
                cinematicSection.style.opacity = Math.max(0, heroOpacity).toString();
                if (heroOpacity <= 0) {
                    cinematicSection.style.pointerEvents = "none";
                } else {
                    cinematicSection.style.pointerEvents = "auto";
                }
            } else {
                cinematicSection.style.opacity = "1";
                cinematicSection.style.pointerEvents = "auto";
            }
        }

        // Intersection Observer for fade-in animations
        const observerOptions = {
            root: null,
            rootMargin: '0px 0px -20px 0px',
            threshold: 0.01
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.fade-in').forEach(el => observer.observe(el));

        // Initialize
        preloadImages();
        window.addEventListener('scroll', updateFrame);
        updateFrame();
    </script>
</body>

</html>