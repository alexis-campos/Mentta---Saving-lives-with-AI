<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MENTTA | Sophisticated Mental Clarity</title>
    <meta name="description"
        content="Support for your mind, anytime. A serene and intuitive space for mental well-being and personal growth.">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,700;0,800;0,900;1,400&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --bg: #ffffff;
            --fg: #111111;
            --accent: #222222;
            --muted: #888888;
            --soft-bg: #f5f5f5;
            --border: rgba(0, 0, 0, 0.08);
            --font-main: 'Inter', sans-serif;
            --font-heading: 'Playfair Display', serif;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-main);
            background-color: var(--bg);
            color: var(--fg);
            line-height: 1.6;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        h1,
        h2,
        h3,
        h4 {
            font-family: var(--font-heading);
            font-weight: 700;
            letter-spacing: -0.01em;
            color: var(--fg);
            text-transform: none;
            /* Serifs usually look better in Title Case */
        }

        a {
            text-decoration: none;
            color: inherit;
            transition: all 0.3s ease;
        }

        /* --- HERO SECTION --- */
        .hero {
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            position: relative;
            background: #fff;
            padding: 2rem;
            overflow: hidden;
        }

        .hero-bg-img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            background-image: url('images/menta.png');
            background-size: cover;
            background-position: center;
            opacity: 0.15;
            /* Base opacity */
            transition: opacity 0.1s ease-out;
            pointer-events: none;
        }

        .hero-bg-svg {
            display: none;
            /* Hide the old SVG mountain background */
        }

        .hero-content {
            position: relative;
            z-index: 10;
            max-width: 900px;
        }

        .hero-top-text {
            font-size: 0.75rem;
            letter-spacing: 0.5em;
            color: var(--muted);
            margin-bottom: 2.5rem;
            font-weight: 600;
            text-transform: uppercase;
            animation: fadeInDown 1.2s cubic-bezier(0.19, 1, 0.22, 1) forwards;
        }

        .hero-title {
            font-size: clamp(3.5rem, 15vw, 10rem);
            line-height: 0.9;
            letter-spacing: -0.02em;
            margin-bottom: 2.5rem;
            font-weight: 900;
            color: var(--fg);
            animation: fadeInUp 1.2s cubic-bezier(0.19, 1, 0.22, 1) 0.2s forwards;
            opacity: 0;
            text-transform: uppercase;
        }

        .hero-subtitle {
            font-size: 1.1rem;
            color: var(--muted);
            max-width: 550px;
            margin: 0 auto 4rem;
            font-weight: 400;
            animation: fadeInUp 1.2s cubic-bezier(0.19, 1, 0.22, 1) 0.4s forwards;
            opacity: 0;
            line-height: 1.5;
        }

        .hero-btns {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            animation: fadeInUp 1.2s cubic-bezier(0.19, 1, 0.22, 1) 0.6s forwards;
            opacity: 0;
        }

        .btn {
            padding: 1.2rem 3.5rem;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            transition: all 0.5s cubic-bezier(0.19, 1, 0.22, 1);
            position: relative;
            overflow: hidden;
        }

        .btn::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 300%;
            height: 300%;
            background: rgba(255, 255, 255, 0.1);
            transform: translate(-50%, -50%) rotate(45deg) scale(0);
            transition: transform 0.5s ease;
        }

        .btn:hover::after {
            transform: translate(-50%, -50%) rotate(45deg) scale(1);
        }

        .btn-outline {
            border: 1px solid var(--fg);
            color: var(--fg);
        }

        .btn-outline:hover {
            background-color: var(--fg);
            color: #fff;
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .btn-solid {
            background-color: var(--fg);
            color: #fff;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .btn-solid:hover {
            opacity: 0.9;
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        /* --- ELEVATED EXPERIENCE --- */
        .experience {
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 100vh;
            border-top: 1px solid var(--border);
        }

        .exp-left {
            background-color: var(--soft-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4rem;
            position: relative;
        }

        .mockup {
            width: 100%;
            max-width: 380px;
            background: white;
            border-radius: 48px;
            padding: 12px;
            box-shadow: 0 50px 100px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(0, 0, 0, 0.05);
            position: relative;
        }

        .mockup-inner {
            background: white;
            border-radius: 38px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            border: 1px solid #f0f0f0;
        }

        .ig-header {
            padding: 14px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .ig-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
            padding: 2px;
        }

        .ig-avatar-inner {
            width: 100%;
            height: 100%;
            background-image: url('images/Menta icono.jpg');
            background-size: cover;
            background-position: center;
            border-radius: 50%;
            border: 2px solid white;
        }

        .ig-user-info {
            flex: 1;
        }

        .ig-username {
            font-size: 13px;
            font-weight: 700;
            color: #262626;
        }

        .ig-location {
            font-size: 11px;
            color: #8e8e8e;
        }

        .ig-media {
            width: 100%;
            aspect-ratio: 1/1;
            background: #fafafa;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .ig-media-content {
            width: 100%;
            height: 100%;
            background-image: url('images/Mentta post.jpg');
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .ig-icons {
            padding: 12px 14px;
            display: flex;
            gap: 16px;
        }

        .ig-icon {
            width: 24px;
            height: 24px;
            color: #262626;
        }

        .ig-caption-area {
            padding: 0 14px 20px;
        }

        .ig-likes {
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .ig-caption {
            font-size: 13px;
            line-height: 1.4;
        }

        .ig-caption b {
            margin-right: 6px;
        }

        .ig-comments-link {
            font-size: 13px;
            color: #8e8e8e;
            margin-top: 6px;
            display: block;
        }

        .ig-date {
            font-size: 10px;
            color: #8e8e8e;
            text-transform: uppercase;
            margin-top: 6px;
            display: block;
        }

        .exp-right {
            padding: 6rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .section-title {
            font-size: 4rem;
            margin-bottom: 2rem;
            max-width: 600px;
            line-height: 1.1;
            letter-spacing: -0.01em;
            font-style: italic;
            /* Adding an elegant serif touch */
        }

        .section-desc {
            font-size: 1.1rem;
            color: var(--muted);
            margin-bottom: 4rem;
            max-width: 500px;
        }

        .features {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            border-top: 1px solid var(--border);
            padding-top: 3rem;
        }

        .feature-num {
            font-size: 0.7rem;
            font-weight: 700;
            color: var(--muted);
            margin-bottom: 1rem;
        }

        .feature-title {
            font-size: 0.8rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-transform: uppercase;
        }

        .feature-text {
            font-size: 0.9rem;
            color: var(--muted);
        }

        /* --- SHOWCASE SECTION --- */
        .showcase {
            padding: 10rem 2rem;
            background-color: #fff;
            text-align: center;
        }

        .showcase-header {
            max-width: 800px;
            margin: 0 auto 5rem;
        }

        .showcase-title {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            font-style: italic;
        }

        .showcase-desc {
            font-size: 1.1rem;
            color: var(--muted);
        }

        .video-container {
            max-width: 1100px;
            margin: 0 auto;
            position: relative;
            border-radius: 32px;
            overflow: hidden;
            box-shadow: 0 60px 120px rgba(0, 0, 0, 0.15);
            background: var(--soft-bg);
            aspect-ratio: 16/9;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .video-placeholder {
            width: 100%;
            height: 100%;
            background-image: url('images/MENTTA VId.png');
            background-size: cover;
            background-position: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #fff;
            position: relative;
        }

        .play-button {
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.5s cubic-bezier(0.19, 1, 0.22, 1);
            z-index: 5;
        }

        .play-button:hover {
            transform: scale(1.1);
            background: rgba(255, 255, 255, 0.25);
            box-shadow: 0 0 50px rgba(255, 255, 255, 0.1);
        }

        .play-button svg {
            width: 32px;
            height: 32px;
            fill: #fff;
            margin-left: 6px;
        }

        .video-ui {
            position: absolute;
            bottom: 30px;
            left: 30px;
            right: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            opacity: 0.6;
            font-size: 0.8rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            font-weight: 600;
        }

        /* --- EXPERT CURATION --- */
        .curation {
            padding: 8rem 2rem;
            background-color: var(--bg);
        }

        .curation-header {
            max-width: 1200px;
            margin: 0 auto 6rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            align-items: end;
        }

        .curation-title {
            font-size: 5rem;
            line-height: 1;
            letter-spacing: -0.02em;
        }

        .curation-desc {
            font-size: 1.2rem;
            color: var(--muted);
            max-width: 500px;
        }

        .team-track {
            display: flex;
            gap: 4rem;
            animation: scroll-left 30s linear infinite;
            width: max-content;
        }

        .team-grid {
            max-width: 100%;
            overflow: hidden;
            margin: 0 auto;
            mask-image: linear-gradient(to right, transparent, black 15%, black 85%, transparent);
            -webkit-mask-image: linear-gradient(to right, transparent, black 15%, black 85%, transparent);
        }

        .team-card {
            text-align: center;
            width: 350px;
            /* Fixed width for consistent scrolling */
            flex-shrink: 0;
        }

        @keyframes scroll-left {
            0% {
                transform: translateX(0);
            }

            100% {
                transform: translateX(calc(-350px * 3 - 4rem * 3));
            }

            /* Adjust based on original member count */
        }

        .team-grid:hover .team-track {
            animation-play-state: paused;
        }

        .team-img-wrapper {
            margin-bottom: 2rem;
            border-radius: 50%;
            overflow: hidden;
            aspect-ratio: 1/1;
            background-color: #f0f0f0;
            position: relative;
        }

        .team-img-placeholder {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: grayscale(1);
            transition: transform 0.6s ease;
        }

        .team-card:hover .team-img-placeholder {
            transform: scale(1.05);
            filter: grayscale(0.5);
        }

        .member-name {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .member-role {
            font-size: 0.75rem;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 1.5rem;
        }

        .social-icons {
            display: flex;
            justify-content: center;
            gap: 1rem;
            opacity: 0.3;
        }

        /* --- FOOTER --- */
        .footer {
            padding: 6rem 4rem 2rem;
            border-top: 1px solid var(--border);
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
        }

        .footer-left {
            display: flex;
            flex-direction: column;
            gap: 4rem;
        }

        .footer-logo {
            font-size: 1.5rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .footer-logo::before {
            content: '';
            width: 8px;
            height: 8px;
            background: var(--fg);
            border-radius: 50%;
        }

        .footer-address {
            font-size: 0.8rem;
            color: var(--muted);
            max-width: 200px;
        }

        .footer-right {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: flex-end;
        }

        .footer-socials {
            display: flex;
            gap: 2rem;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.1em;
        }

        .footer-bottom {
            grid-column: 1 / -1;
            display: flex;
            justify-content: space-between;
            padding-top: 4rem;
            border-top: 1px solid var(--border);
            font-size: 0.7rem;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .footer-utility {
            display: flex;
            gap: 2rem;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 992px) {
            .experience {
                grid-template-columns: 1fr;
            }

            .curation-header {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .team-grid {
                grid-template-columns: 1fr;
            }

            .footer {
                grid-template-columns: 1fr;
            }

            .footer-right {
                align-items: flex-start;
            }

            .hero-title {
                font-size: 5rem;
            }
        }
    </style>
</head>

<body>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-bg-img"></div>

        <div class="hero-content">
            <p class="hero-top-text">SOPHISTICATED MENTAL CLARITY</p>
            <h1 class="hero-title">MENTTA</h1>
            <p class="hero-subtitle">
                Support for your mind, anytime. A serene and intuitive space for mental well-being and personal growth.
            </p>
            <div class="hero-btns">
                <a href="login.php" class="btn btn-outline">LOGIN</a>
                <a href="register.php" class="btn btn-solid">REGISTER</a>
            </div>
        </div>
    </section>

    <!-- Elevated Experience Section -->
    <section class="experience">
        <div class="exp-left">
            <div class="mockup">
                <div class="mockup-inner">
                    <!-- Header -->
                    <div class="ig-header">
                        <div class="ig-avatar">
                            <div class="ig-avatar-inner"></div>
                        </div>
                        <div class="ig-user-info">
                            <div class="ig-username">Mentta</div>
                            <div class="ig-location">Mind Ally</div>
                        </div>
                        <svg class="ig-icon" viewBox="0 0 24 24" fill="currentColor" style="width:18px;">
                            <circle cx="5" cy="12" r="1.5" />
                            <circle cx="12" cy="12" r="1.5" />
                            <circle cx="19" cy="12" r="1.5" />
                        </svg>
                    </div>

                    <!-- Media -->
                    <div class="ig-media">
                        <div class="ig-media-content">
                        </div>
                    </div>

                    <!-- Icons -->
                    <div class="ig-icons">
                        <svg class="ig-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path
                                d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l8.78-8.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                        </svg>
                        <svg class="ig-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path
                                d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z" />
                        </svg>
                        <svg class="ig-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="22" y1="2" x2="11" y2="13" />
                            <polygon points="22 2 15 22 11 13 2 9 22 2" />
                        </svg>
                        <div style="flex:1;"></div>
                        <svg class="ig-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z" />
                        </svg>
                    </div>

                    <!-- Caption Area -->
                    <div class="ig-caption-area">
                        <div class="ig-likes">Liked by <b>alex_rivera</b> and <b>12,482 others</b></div>
                        <div class="ig-caption">
                            <b>mentta_wellness</b>Your journey to well-being begins with a single mindful step. #Mentta
                        </div>
                        <span class="ig-comments-link">View all 42 comments</span>
                        <span class="ig-date">8 HOURS AGO</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="exp-right">
            <h2 class="section-title">ELEVATED EXPERIENCE</h2>
            <p class="section-desc">
                Mentta brings technology and mental well-being together. A seamless, thoughtful assistant guides you
                toward clarity, calm, and personal growth.
            </p>
            <div class="features">
                <div class="feature">
                    <p class="feature-num">01. EMPATHY</p>
                    <h3 class="feature-title">Advanced Intelligence</h3>
                    <p class="feature-text">Advanced language models trained on clinical psychology protocols.</p>
                </div>
                <div class="feature">
                    <p class="feature-num">02. DISCRETION</p>
                    <h3 class="feature-title">Total Privacy</h3>
                    <p class="feature-text">Tier-one encryption ensuring your private thoughts remain private.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Video Showcase Section -->
    <section class="showcase">
        <div class="showcase-header">
            <h2 class="showcase-title">A Journey Through Mentta.</h2>
            <p class="showcase-desc">Discover the premium experience of mental clarity. Watch how our assistant scales
                with your personal growth in real-time.</p>
        </div>

        <div class="video-container">
            <div class="video-placeholder">
                <!-- In a real scenario, replace this div with an <video> or <iframe> -->
                <div class="play-button">
                    <svg viewBox="0 0 24 24">
                        <path d="M8 5v14l11-7z" />
                    </svg>
                </div>
                <div class="video-ui">
                    <span>Product Tour 2026</span>
                    <span>04:12 / 12:00</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Expert Curation Section -->
    <section class="curation">
        <div class="curation-header">
            <h2 class="curation-title">EXPERT<br>CURATION.</h2>
            <p class="curation-desc">Our team bridges the gap between digital innovation and clinical excellence.</p>
        </div>

        <div class="team-grid">
            <div class="team-track">
                <div class="team-card">
                    <div class="team-img-wrapper">
                        <svg viewBox="0 0 200 200" class="team-img-placeholder">
                            <circle cx="100" cy="80" r="40" fill="#444" />
                            <path d="M60 160 Q100 120 140 160" stroke="#444" stroke-width="20" fill="none" />
                        </svg>
                    </div>
                    <h3 class="member-name">DR. ALEX RIVERA</h3>
                    <p class="member-role">LEAD CLINICAL PSYCHOLOGIST</p>
                </div>

                <div class="team-card">
                    <div class="team-img-wrapper">
                        <svg viewBox="0 0 200 200" class="team-img-placeholder">
                            <circle cx="100" cy="80" r="40" fill="#444" />
                            <path d="M60 160 Q100 120 140 160" stroke="#444" stroke-width="20" fill="none" />
                        </svg>
                    </div>
                    <h3 class="member-name">ELENA VANCE</h3>
                    <p class="member-role">ALETHEIA SPECIALIST</p>
                </div>

                <div class="team-card">
                    <div class="team-img-wrapper">
                        <svg viewBox="0 0 200 200" class="team-img-placeholder">
                            <circle cx="100" cy="80" r="40" fill="#444" />
                            <path d="M60 160 Q100 120 140 160" stroke="#444" stroke-width="20" fill="none" />
                        </svg>
                    </div>
                    <h3 class="member-name">MARCUS THORNE</h3>
                    <p class="member-role">AI ETHICS LEAD</p>
                </div>

                <!-- Duplicate for seamless scroll -->
                <div class="team-card">
                    <div class="team-img-wrapper">
                        <svg viewBox="0 0 200 200" class="team-img-placeholder">
                            <circle cx="100" cy="80" r="40" fill="#444" />
                            <path d="M60 160 Q100 120 140 160" stroke="#444" stroke-width="20" fill="none" />
                        </svg>
                    </div>
                    <h3 class="member-name">DR. ALEX RIVERA</h3>
                    <p class="member-role">LEAD CLINICAL PSYCHOLOGIST</p>
                </div>

                <div class="team-card">
                    <div class="team-img-wrapper">
                        <svg viewBox="0 0 200 200" class="team-img-placeholder">
                            <circle cx="100" cy="80" r="40" fill="#444" />
                            <path d="M60 160 Q100 120 140 160" stroke="#444" stroke-width="20" fill="none" />
                        </svg>
                    </div>
                    <h3 class="member-name">ELENA VANCE</h3>
                    <p class="member-role">ALETHEIA SPECIALIST</p>
                </div>

                <div class="team-card">
                    <div class="team-img-wrapper">
                        <svg viewBox="0 0 200 200" class="team-img-placeholder">
                            <circle cx="100" cy="80" r="40" fill="#444" />
                            <path d="M60 160 Q100 120 140 160" stroke="#444" stroke-width="20" fill="none" />
                        </svg>
                    </div>
                    <h3 class="member-name">MARCUS THORNE</h3>
                    <p class="member-role">AI ETHICS LEAD</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-left">
            <div class="footer-logo">MENTTA</div>
            <div class="footer-address">
                GLOBAL HEADQUARTERS<br>
                Lima, Lima<br>
                Peru
            </div>
        </div>
        <div class="footer-right">
            <div class="footer-socials">
                <a href="#">TWITTER</a>
                <a href="#">LINKEDIN</a>
                <a href="#">INSTAGRAM</a>
            </div>
            <p style="font-size: 0.7rem; color: #ccc;">hello@mentta.ai</p>
        </div>
        <div class="footer-bottom">
            <div>© 2026 MENTTA SYSTEMS</div>
            <div class="footer-utility">
                <a href="#">LEGAL</a>
                <a href="#">SECURITY</a>
                <a href="#">PRIVACY POLICY</a>
            </div>
        </div>
    </footer>

    <script>
        // Parallax and mouse effect for hero
        const hero = document.querySelector('.hero');
        const bgImg = document.querySelector('.hero-bg-img');

        hero.addEventListener('mousemove', (e) => {
            const x = (e.clientX / window.innerWidth - 0.5) * 30;
            const y = (e.clientY / window.innerHeight - 0.5) * 30;
            bgImg.style.transform = `scale(1.05) translate(${x}px, ${y}px)`;
        });

        // Fade out on scroll for hero
        const heroContent = document.querySelector('.hero-content');

        window.addEventListener('scroll', () => {
            const scrollPos = window.scrollY;
            const heroHeight = hero.offsetHeight;

            // Calculate opacity: starts at 1, reaches 0 at 70% of the hero height
            const opacity = 1 - (scrollPos / (heroHeight * 0.7));

            if (opacity >= 0) {
                heroContent.style.opacity = opacity;
                bgImg.style.opacity = opacity * 0.15; // Maintain proportional base opacity
                heroContent.style.transform = `translateY(${scrollPos * 0.3}px)`;
            } else {
                heroContent.style.opacity = 0;
                bgImg.style.opacity = 0;
            }
        });

        // Smooth reveal on scroll
        const observerOptions = {
            threshold: 0.1
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = "1";
                    entry.target.style.transform = "translateY(0)";
                }
            });
        }, observerOptions);

        document.querySelectorAll('.experience, .curation, .team-card, .feature').forEach(el => {
            el.style.opacity = "0";
            el.style.transform = "translateY(40px)";
            el.style.transition = "all 0.8s cubic-bezier(0.165, 0.84, 0.44, 1)";
            observer.observe(el);
        });
    </script>
</body>

</html>