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
            background-image: url('images/Menta_icono.jpg');
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

        /* --- IMPACT STATISTICS --- */
        .statistics {
            padding: 6rem 2rem;
            background-color: #f8f9f8;
            color: var(--fg);
        }

        .statistics-header {
            text-align: center;
            margin-bottom: 4rem;
        }

        .statistics-label {
            font-size: 0.7rem;
            letter-spacing: 0.3em;
            color: var(--muted);
            text-transform: uppercase;
            margin-bottom: 1rem;
        }

        .statistics-title {
            font-family: var(--font-heading);
            font-size: 2.5rem;
            font-weight: 600;
            color: var(--fg);
        }

        .stats-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
        }

        .stat-item {
            text-align: center;
            padding: 1.5rem;
            border-radius: 16px;
            background: #fff;
            border: 1px solid var(--border);
            transition: all 0.4s ease;
        }

        .stat-item:hover {
            box-shadow: 0 8px 24px rgba(0,0,0,0.05);
        }

        .stat-number {
            font-family: var(--font-heading);
            font-size: 2.75rem;
            font-weight: 600;
            color: var(--fg);
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--muted);
        }

        /* --- HOW IT WORKS --- */
        .how-it-works {
            padding: 8rem 2rem;
            background-color: var(--soft-bg);
        }

        .hiw-header {
            text-align: center;
            margin-bottom: 5rem;
        }

        .hiw-label {
            font-size: 0.7rem;
            letter-spacing: 0.4em;
            color: var(--muted);
            text-transform: uppercase;
            margin-bottom: 1.5rem;
        }

        .hiw-title {
            font-family: var(--font-heading);
            font-size: 3rem;
            font-weight: 700;
            font-style: italic;
        }

        .hiw-subtitle {
            font-size: 1.1rem;
            color: var(--muted);
            max-width: 600px;
            margin: 1.5rem auto 0;
        }

        .steps-grid {
            max-width: 1100px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2.5rem;
            position: relative;
        }

        .step-card {
            background: #fff;
            border-radius: 32px;
            padding: 3rem 2rem;
            text-align: center;
            border: 1px solid var(--border);
            transition: all 0.5s ease;
            position: relative;
        }

        .step-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 30px 60px rgba(0,0,0,0.08);
        }

        .step-number {
            width: 60px;
            height: 60px;
            background: var(--fg);
            color: #fff;
            font-family: var(--font-heading);
            font-size: 1.5rem;
            font-weight: 700;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
        }

        .step-title {
            font-family: var(--font-heading);
            font-size: 1.5rem;
            margin-bottom: 1rem;
            font-weight: 700;
        }

        .step-desc {
            color: var(--muted);
            font-size: 0.95rem;
            line-height: 1.7;
        }

        /* --- FEATURES GRID --- */
        .features-section {
            padding: 8rem 2rem;
            background-color: var(--bg);
        }

        .features-header {
            text-align: center;
            margin-bottom: 5rem;
        }

        .features-label {
            font-size: 0.7rem;
            letter-spacing: 0.4em;
            color: var(--muted);
            text-transform: uppercase;
            margin-bottom: 1.5rem;
        }

        .features-title {
            font-family: var(--font-heading);
            font-size: 3rem;
            font-weight: 700;
            font-style: italic;
        }

        .features-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
        }

        .feature-card {
            background: var(--soft-bg);
            border-radius: 24px;
            padding: 2.5rem;
            border: 1px solid var(--border);
            transition: all 0.4s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.05);
            border-color: rgba(0,0,0,0.12);
        }

        .feature-icon {
            width: 56px;
            height: 56px;
            background: var(--fg);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .feature-icon svg {
            width: 28px;
            height: 28px;
            stroke: #fff;
            stroke-width: 1.5;
            fill: none;
        }

        .feature-card-title {
            font-family: var(--font-heading);
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
        }

        .feature-card-desc {
            color: var(--muted);
            font-size: 0.9rem;
            line-height: 1.6;
        }

        /* --- TESTIMONIALS --- */
        .testimonials {
            padding: 8rem 2rem;
            background: linear-gradient(180deg, var(--soft-bg) 0%, var(--bg) 100%);
        }

        .testimonials-header {
            text-align: center;
            margin-bottom: 5rem;
        }

        .testimonials-label {
            font-size: 0.7rem;
            letter-spacing: 0.4em;
            color: var(--muted);
            text-transform: uppercase;
            margin-bottom: 1.5rem;
        }

        .testimonials-title {
            font-family: var(--font-heading);
            font-size: 3rem;
            font-weight: 700;
            font-style: italic;
        }

        .testimonials-grid {
            max-width: 1100px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
        }

        .testimonial-card {
            background: #fff;
            border-radius: 24px;
            padding: 2.5rem;
            border: 1px solid var(--border);
            position: relative;
            transition: all 0.4s ease;
        }

        .testimonial-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.06);
        }

        .testimonial-quote {
            font-size: 3rem;
            color: var(--border);
            position: absolute;
            top: 1.5rem;
            left: 2rem;
            font-family: var(--font-heading);
        }

        .testimonial-text {
            font-size: 1rem;
            line-height: 1.8;
            color: var(--fg);
            margin-bottom: 2rem;
            padding-top: 1rem;
            font-style: italic;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .testimonial-avatar {
            width: 48px;
            height: 48px;
            background: var(--soft-bg);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: var(--muted);
        }

        .testimonial-info h4 {
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
            font-family: var(--font-main);
        }

        .testimonial-info p {
            font-size: 0.75rem;
            color: var(--muted);
        }

        /* --- FAQ --- */
        .faq {
            padding: 8rem 2rem;
            background-color: var(--bg);
        }

        .faq-header {
            text-align: center;
            margin-bottom: 4rem;
        }

        .faq-label {
            font-size: 0.7rem;
            letter-spacing: 0.4em;
            color: var(--muted);
            text-transform: uppercase;
            margin-bottom: 1.5rem;
        }

        .faq-title {
            font-family: var(--font-heading);
            font-size: 3rem;
            font-weight: 700;
            font-style: italic;
        }

        .faq-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .faq-item {
            border-bottom: 1px solid var(--border);
        }

        .faq-question {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.75rem 0;
            background: none;
            border: none;
            cursor: pointer;
            text-align: left;
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--fg);
            transition: color 0.3s ease;
        }

        .faq-question:hover {
            color: var(--muted);
        }

        .faq-icon {
            width: 24px;
            height: 24px;
            transition: transform 0.3s ease;
        }

        .faq-item.active .faq-icon {
            transform: rotate(45deg);
        }

        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s ease, padding 0.4s ease;
        }

        .faq-item.active .faq-answer {
            max-height: 300px;
            padding-bottom: 1.5rem;
        }

        .faq-answer p {
            color: var(--muted);
            line-height: 1.8;
        }

        /* --- FINAL CTA --- */
        .final-cta {
            padding: 8rem 2rem;
            background: var(--fg);
            text-align: center;
            color: #fff;
            position: relative;
            overflow: hidden;
        }

        .final-cta::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.03) 0%, transparent 60%);
            pointer-events: none;
        }

        .cta-content {
            position: relative;
            z-index: 10;
            max-width: 700px;
            margin: 0 auto;
        }

        .cta-label {
            font-size: 0.7rem;
            letter-spacing: 0.4em;
            color: rgba(255,255,255,0.4);
            text-transform: uppercase;
            margin-bottom: 1.5rem;
        }

        .cta-title {
            font-family: var(--font-heading);
            font-size: 3.5rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 1.5rem;
            font-style: italic;
        }

        .cta-desc {
            font-size: 1.1rem;
            color: rgba(255,255,255,0.6);
            margin-bottom: 3rem;
            line-height: 1.7;
        }

        .cta-buttons {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            margin-bottom: 3rem;
        }

        .btn-cta {
            padding: 1.25rem 3rem;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            background: #fff;
            color: var(--fg);
            border: none;
            transition: all 0.4s ease;
            cursor: pointer;
        }

        .btn-cta:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }

        .btn-cta-outline {
            background: transparent;
            color: #fff;
            border: 1px solid rgba(255,255,255,0.3);
        }

        .btn-cta-outline:hover {
            background: rgba(255,255,255,0.1);
            border-color: rgba(255,255,255,0.5);
        }

        .cta-emergency {
            font-size: 0.85rem;
            color: rgba(255,255,255,0.4);
        }

        .cta-emergency strong {
            color: #C8553D;
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

        /* ============================================
           MOBILE-FIRST RESPONSIVE DESIGN
           Base styles are mobile, enhanced for larger screens
           ============================================ */

        /* Extra Small Phones (375px and below) */
        @media (max-width: 374px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-subtitle {
                font-size: 0.95rem;
            }
            
            .btn {
                padding: 1rem 1.5rem;
                font-size: 0.65rem;
            }
            
            .section-title {
                font-size: 1.75rem;
            }
            
            .stat-number {
                font-size: 2rem;
            }
        }

        /* Mobile Base (375px - 479px) */
        @media (max-width: 479px) {
            .hero {
                padding: 1rem;
                min-height: 100vh;
                height: auto;
            }
            
            .hero-title {
                font-size: 3rem;
                margin-bottom: 1.5rem;
            }
            
            .hero-subtitle {
                font-size: 1rem;
                margin-bottom: 2.5rem;
            }
            
            .hero-btns {
                flex-direction: column;
                gap: 1rem;
                width: 100%;
            }
            
            .btn {
                width: 100%;
                text-align: center;
                padding: 1.1rem 2rem;
                font-size: 0.7rem;
                min-height: 48px;
            }
            
            /* Experience Section */
            .experience {
                grid-template-columns: 1fr;
            }
            
            .exp-left {
                padding: 2rem 1rem;
            }
            
            .exp-right {
                padding: 2rem 1rem;
            }
            
            .section-title {
                font-size: 2rem;
                margin-bottom: 1.5rem;
            }
            
            .section-desc {
                font-size: 1rem;
                margin-bottom: 2rem;
            }
            
            .features {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            
            /* Stats */
            .statistics {
                padding: 3rem 1rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .stat-item {
                padding: 1.25rem;
            }
            
            .stat-number {
                font-size: 2.25rem;
            }
            
            /* How It Works */
            .how-it-works {
                padding: 3rem 1rem;
            }
            
            .steps-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            
            .step-card {
                padding: 1.5rem;
                border-radius: 20px;
            }
            
            .step-number {
                width: 50px;
                height: 50px;
                font-size: 1.25rem;
            }
            
            .step-title {
                font-size: 1.25rem;
            }
            
            /* Features Grid */
            .features-section {
                padding: 3rem 1rem;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .feature-card {
                padding: 1.5rem;
                border-radius: 16px;
            }
            
            .feature-icon {
                width: 48px;
                height: 48px;
            }
            
            /* Testimonials */
            .testimonials {
                padding: 3rem 1rem;
            }
            
            .testimonials-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .testimonial-card {
                padding: 1.5rem;
            }
            
            /* FAQ */
            .faq {
                padding: 3rem 1rem;
            }
            
            .faq-question {
                font-size: 0.95rem;
                padding: 1.25rem 0;
            }
            
            /* CTA */
            .final-cta {
                padding: 4rem 1rem;
            }
            
            .cta-title {
                font-size: 1.75rem;
            }
            
            .cta-desc {
                font-size: 1rem;
            }
            
            .cta-buttons {
                flex-direction: column;
                width: 100%;
                gap: 0.75rem;
            }
            
            .btn-cta {
                width: 100%;
                min-height: 48px;
            }
            
            /* Footer */
            .footer {
                grid-template-columns: 1fr;
                padding: 3rem 1rem 1.5rem;
                gap: 2rem;
            }
            
            .footer-right {
                align-items: flex-start;
            }
            
            .footer-bottom {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .footer-utility {
                justify-content: center;
            }
            
            /* Typography Sections */
            .statistics-title,
            .hiw-title,
            .features-title,
            .testimonials-title,
            .faq-title {
                font-size: 1.75rem;
            }
            
            /* Curation Section */
            .curation {
                padding: 3rem 1rem;
            }
            
            .curation-header {
                grid-template-columns: 1fr;
                gap: 1.5rem;
                margin-bottom: 3rem;
            }
            
            .curation-title {
                font-size: 2rem;
            }
            
            .team-card {
                width: 280px;
            }
            
            /* Showcase */
            .showcase {
                padding: 4rem 1rem;
            }
            
            .showcase-title {
                font-size: 2rem;
            }
            
            .video-container {
                border-radius: 16px;
            }
            
            .play-button {
                width: 70px;
                height: 70px;
            }
            
            .video-ui {
                bottom: 15px;
                left: 15px;
                right: 15px;
                font-size: 0.65rem;
            }
        }

        /* Large Phones / Phablets (480px - 767px) */
        @media (min-width: 480px) and (max-width: 767px) {
            .hero-title {
                font-size: 4rem;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .features-grid {
                grid-template-columns: 1fr;
            }
            
            .step-card {
                padding: 2rem;
            }
        }

        /* Tablets (768px - 1023px) */
        @media (min-width: 768px) and (max-width: 1023px) {
            .hero-title {
                font-size: 6rem;
            }
            
            .experience {
                grid-template-columns: 1fr;
            }
            
            .exp-right {
                padding: 4rem 2rem;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .steps-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .features-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .testimonials-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .footer {
                grid-template-columns: 1fr 1fr;
            }
            
            .curation-header {
                grid-template-columns: 1fr;
            }
        }

        /* Small Desktops (1024px+) */
        @media (min-width: 1024px) {
            .hero-title {
                font-size: 8rem;
            }
            
            .experience {
                grid-template-columns: 1fr 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(4, 1fr);
            }
            
            .steps-grid {
                grid-template-columns: repeat(3, 1fr);
            }
            
            .features-grid {
                grid-template-columns: repeat(3, 1fr);
            }
            
            .testimonials-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        /* Large Desktops (1280px+) */
        @media (min-width: 1280px) {
            .hero-title {
                font-size: 10rem;
            }
        }

        /* Touch Device Optimizations */
        @media (hover: none) and (pointer: coarse) {
            .btn:hover,
            .step-card:hover,
            .feature-card:hover,
            .testimonial-card:hover,
            .stat-item:hover {
                transform: none;
            }
            
            .btn:active,
            .step-card:active {
                transform: scale(0.98);
            }
        }
    </style>
</head>

<body>

    <!-- Language Selector -->
    <div style="position: absolute; top: 20px; right: 20px; z-index: 100;">
        <select id="languageSelector" onchange="changeLanguage(this.value)" style="padding: 8px; border-radius: 8px; border: 1px solid rgba(0,0,0,0.1); background: rgba(255,255,255,0.8); backdrop-filter: blur(10px); cursor: pointer; font-family: var(--font-main); font-size: 14px; color: var(--fg); outline: none;">
            <option value="es">Español</option>
            <option value="en">English</option>
        </select>
    </div>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-bg-img"></div>

        <div class="hero-content">
            <p class="hero-top-text" data-i18n="landing.topText">SOPHISTICATED MENTAL CLARITY</p>
            <h1 class="hero-title" data-i18n="landing.title">MENTTA</h1>
            <p class="hero-subtitle" data-i18n="landing.subtitle">
                Support for your mind, anytime. A serene and intuitive space for mental well-being and personal growth.
            </p>
            <div class="hero-btns">
                <a href="login.php" class="btn btn-outline" data-i18n="landing.login">LOGIN</a>
                <a href="register.php" class="btn btn-solid" data-i18n="landing.register">REGISTER</a>
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

    <!-- Impact Statistics Section -->
    <section class="statistics">
        <div class="statistics-header">
            <p class="statistics-label" data-i18n="landing.statsLabel">Our Impact</p>
            <h2 class="statistics-title" data-i18n="landing.statsTitle">Numbers That Matter</h2>
        </div>
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number">10K+</div>
                <div class="stat-label" data-i18n="landing.activeUsers">Active Users</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">50K+</div>
                <div class="stat-label" data-i18n="landing.sessionsCompleted">Sessions Completed</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">98%</div>
                <div class="stat-label" data-i18n="landing.userSatisfaction">User Satisfaction</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">24/7</div>
                <div class="stat-label" data-i18n="landing.alwaysAvailable">Always Available</div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="how-it-works">
        <div class="hiw-header">
            <p class="hiw-label" data-i18n="landing.hiwLabel">Simple Process</p>
            <h2 class="hiw-title" data-i18n="landing.hiwTitle">How Mentta Works</h2>
            <p class="hiw-subtitle" data-i18n="landing.hiwSubtitle">Begin your journey to mental clarity in three simple steps</p>
        </div>
        <div class="steps-grid">
            <div class="step-card">
                <div class="step-number">1</div>
                <h3 class="step-title" data-i18n="landing.step1Title">Create Your Account</h3>
                <p class="step-desc" data-i18n="landing.step1Desc">Sign up in seconds with just your email. Your privacy is our priority from day one.</p>
            </div>
            <div class="step-card">
                <div class="step-number">2</div>
                <h3 class="step-title" data-i18n="landing.step2Title">Start a Conversation</h3>
                <p class="step-desc" data-i18n="landing.step2Desc">Open a chat with our AI companion. It's trained to listen, understand, and support without judgment.</p>
            </div>
            <div class="step-card">
                <div class="step-number">3</div>
                <h3 class="step-title" data-i18n="landing.step3Title">Receive Personalized Support</h3>
                <p class="step-desc" data-i18n="landing.step3Desc">Get insights, techniques, and real-time emotional support tailored to your unique needs.</p>
            </div>
        </div>
    </section>

    <!-- Features Grid Section -->
    <section class="features-section">
        <div class="features-header">
            <p class="features-label" data-i18n="landing.featuresLabel">Capabilities</p>
            <h2 class="features-title" data-i18n="landing.featuresTitle">What Makes Us Different</h2>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24"><path d="M12 2a10 10 0 0 1 10 10c0 5.52-4.48 10-10 10S2 17.52 2 12 6.48 2 12 2m0 2a8 8 0 1 0 0 16 8 8 0 0 0 0-16m-1 4h2v4h2l-3 4-3-4h2V8z"/></svg>
                </div>
                <h3 class="feature-card-title" data-i18n="landing.feature1Title">AI Emotional Analysis</h3>
                <p class="feature-card-desc" data-i18n="landing.feature1Desc">Advanced algorithms detect emotional patterns and provide real-time insights to support your wellbeing.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                </div>
                <h3 class="feature-card-title" data-i18n="landing.feature2Title">Crisis Detection</h3>
                <p class="feature-card-desc" data-i18n="landing.feature2Desc">Intelligent monitoring identifies signs of distress and provides immediate resources when you need them most.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24"><path d="M15 10l4.55-2.28A1 1 0 0 1 21 8.62v6.76a1 1 0 0 1-1.45.89L15 14M5 18h8a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2z"/></svg>
                </div>
                <h3 class="feature-card-title" data-i18n="landing.feature3Title">Mentta Live Sessions</h3>
                <p class="feature-card-desc" data-i18n="landing.feature3Desc">Connect through video calls for a more personal, human-like experience with our AI companion.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24"><path d="M16 4a4 4 0 0 1 4 4v8a4 4 0 0 1-4 4H8a4 4 0 0 1-4-4V8a4 4 0 0 1 4-4h8m0-2H8a6 6 0 0 0-6 6v8a6 6 0 0 0 6 6h8a6 6 0 0 0 6-6V8a6 6 0 0 0-6-6z"/><path d="M12 8v8M8 12h8"/></svg>
                </div>
                <h3 class="feature-card-title" data-i18n="landing.feature4Title">Professional Connection</h3>
                <p class="feature-card-desc" data-i18n="landing.feature4Desc">Seamlessly connect with licensed psychologists when you need human expertise beyond AI support.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 2.18l7 3.12V11c0 4.52-2.98 8.69-7 9.93-4.02-1.24-7-5.41-7-9.93V6.3l7-3.12zM11 7v6h2V7h-2zm0 8v2h2v-2h-2z"/></svg>
                </div>
                <h3 class="feature-card-title" data-i18n="landing.feature5Title">Bank-Level Security</h3>
                <p class="feature-card-desc" data-i18n="landing.feature5Desc">End-to-end encryption ensures your conversations remain private and protected at all times.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                </div>
                <h3 class="feature-card-title" data-i18n="landing.feature6Title">24/7 Availability</h3>
                <p class="feature-card-desc" data-i18n="landing.feature6Desc">Mental health support doesn't wait. Mentta is here for you anytime, day or night, holidays included.</p>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials">
        <div class="testimonials-header">
            <p class="testimonials-label" data-i18n="landing.testimonialsLabel">Stories</p>
            <h2 class="testimonials-title" data-i18n="landing.testimonialsTitle">What Our Users Say</h2>
        </div>
        <div class="testimonials-grid">
            <div class="testimonial-card">
                <div class="testimonial-quote">"</div>
                <p class="testimonial-text" data-i18n="landing.testimonial1">Mentta helped me through my darkest moments. Having someone to talk to at 3 AM when anxiety hits is invaluable.</p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">A</div>
                    <div class="testimonial-info">
                        <h4 data-i18n="landing.testimonial1Author">Anonymous User</h4>
                        <p data-i18n="landing.testimonial1Meta">Member since 2025</p>
                    </div>
                </div>
            </div>
            <div class="testimonial-card">
                <div class="testimonial-quote">"</div>
                <p class="testimonial-text" data-i18n="landing.testimonial2">I was skeptical about AI therapy, but Mentta truly understands context. It remembers our conversations and builds on them.</p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">M</div>
                    <div class="testimonial-info">
                        <h4 data-i18n="landing.testimonial2Author">M. García</h4>
                        <p data-i18n="landing.testimonial2Meta">3 months with Mentta</p>
                    </div>
                </div>
            </div>
            <div class="testimonial-card">
                <div class="testimonial-quote">"</div>
                <p class="testimonial-text" data-i18n="landing.testimonial3">The breathing exercises and grounding techniques have become part of my daily routine. Simple but life-changing.</p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">C</div>
                    <div class="testimonial-info">
                        <h4 data-i18n="landing.testimonial3Author">Carlos R.</h4>
                        <p data-i18n="landing.testimonial3Meta">Active user</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq">
        <div class="faq-header">
            <p class="faq-label" data-i18n="landing.faqLabel">Questions</p>
            <h2 class="faq-title" data-i18n="landing.faqTitle">Frequently Asked</h2>
        </div>
        <div class="faq-container">
            <div class="faq-item">
                <button class="faq-question" onclick="toggleFaq(this)">
                    <span data-i18n="landing.q1">Is my information secure and private?</span>
                    <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                </button>
                <div class="faq-answer">
                    <p data-i18n="landing.a1">Absolutely. We use bank-level encryption for all data. Your conversations are never shared, sold, or used for advertising. You can delete your data at any time from your profile settings.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question" onclick="toggleFaq(this)">
                    <span data-i18n="landing.q2">How does the AI understand my emotions?</span>
                    <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                </button>
                <div class="faq-answer">
                    <p data-i18n="landing.a2">Our AI is trained on clinical psychology protocols and uses advanced natural language processing to detect emotional cues, context, and patterns in your messages. It continuously learns from our conversations to better support you.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question" onclick="toggleFaq(this)">
                    <span data-i18n="landing.q3">What happens if I'm in a crisis?</span>
                    <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                </button>
                <div class="faq-answer">
                    <p data-i18n="landing.a3">Mentta has built-in crisis detection. If we identify you may be in danger, we'll provide immediate access to emergency hotlines (like 113), calming exercises, and—if you've set it up—notify your emergency contacts or linked psychologist.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question" onclick="toggleFaq(this)">
                    <span data-i18n="landing.q4">Is Mentta free to use?</span>
                    <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                </button>
                <div class="faq-answer">
                    <p data-i18n="landing.a4">Yes, Mentta offers a free tier with full access to our AI companion, chat history, and wellness resources. Premium features like Mentta Live video sessions and professional psychologist connections may have associated costs.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Final CTA Section -->
    <section class="final-cta">
        <div class="cta-content">
            <p class="cta-label" data-i18n="landing.ctaLabel">Ready to Begin?</p>
            <h2 class="cta-title" data-i18n="landing.ctaTitle">Your Journey to Clarity Starts Now</h2>
            <p class="cta-desc" data-i18n="landing.ctaDesc">Join thousands who have found solace, understanding, and growth with Mentta. Your mental well-being deserves attention.</p>
            <div class="cta-buttons">
                <a href="register.php" class="btn-cta" data-i18n="landing.createAccount">Create Free Account</a>
                <a href="login.php" class="btn-cta btn-cta-outline" data-i18n="landing.signIn">Sign In</a>
            </div>
            <p class="cta-emergency" data-i18n="landing.emergencyText">In case of emergency, call <strong>113</strong> or <strong>106</strong> immediately.</p>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-left">
            <div class="footer-logo">MENTTA</div>
            <div class="footer-address">
                <span data-i18n="landing.footer.hq">GLOBAL HEADQUARTERS</span><br>
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
                <a href="#" data-i18n="landing.footer.legal">LEGAL</a>
                <a href="#" data-i18n="landing.footer.security">SECURITY</a>
                <a href="#" data-i18n="landing.footer.privacy">PRIVACY POLICY</a>
            </div>
        </div>
    </footer>

    <script src="assets/js/utils.js?v=<?= time() ?>"></script>
    <script src="assets/js/translations.js?v=<?= time() ?>"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Initialize i18n
            if (typeof i18n !== 'undefined') {
                const savedLang = localStorage.getItem('mentta_language') || 'es';
                i18n.setLanguage(savedLang);
                
                // Update selector
                const selector = document.getElementById('languageSelector');
                if (selector) selector.value = savedLang;
                
                i18n.applyTranslations();
            }
        });

        function changeLanguage(lang) {
            if (typeof i18n !== 'undefined') {
                i18n.setLanguage(lang);
                // i18n.applyTranslations() is called inside setLanguage via notify listeners
                // But force apply to be sure
                i18n.applyTranslations();
            }
        }

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

        document.querySelectorAll('.experience, .curation, .team-card, .feature, .statistics, .how-it-works, .features-section, .testimonials, .faq, .step-card, .feature-card, .testimonial-card, .stat-item').forEach(el => {
            el.style.opacity = "0";
            el.style.transform = "translateY(40px)";
            el.style.transition = "all 0.8s cubic-bezier(0.165, 0.84, 0.44, 1)";
            observer.observe(el);
        });

        // FAQ Toggle Function
        function toggleFaq(button) {
            const faqItem = button.parentElement;
            const isActive = faqItem.classList.contains('active');
            
            // Close all FAQ items
            document.querySelectorAll('.faq-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // Toggle current item
            if (!isActive) {
                faqItem.classList.add('active');
            }
        }
    </script>
</body>

</html>