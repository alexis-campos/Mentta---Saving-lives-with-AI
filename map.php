<?php
/**
 * MENTTA - Interactive Map of Mental Health Centers
 * Fullscreen map view with Google Maps integration
 * 
 * @version 0.5.2
 */

require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Require patient authentication
$user = requireAuth('patient');

// Get user theme preference
$theme = $user['theme_preference'] ?? 'light';

// Get Google Maps API Key
$mapsApiKey = env('GOOGLE_MAPS_API_KEY', '');
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($user['language'] ?? 'es') ?>" data-theme="<?= htmlspecialchars($theme) ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#6366F1">
    <meta name="description" content="Encuentra centros de salud mental cercanos a ti">
    <title>Mapa de Centros - Mentta</title>

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

    <!-- Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,700;1,700&display=swap"
        rel="stylesheet">

    <!-- Theme & Map Styles -->
    <link rel="stylesheet" href="assets/css/theme.css">
    <link rel="stylesheet" href="assets/css/map.css">

    <!-- DEV-004: Leaflet CSS (fallback when no Google Maps API) -->
    <?php if (empty($mapsApiKey)): ?>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
            integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <?php endif; ?>

    <style>
        :root {
            --bg-antigravity: #F9F9F7;
            --text-main: #111111;
            --accent-lux: #AF8A6B;
        }

        body {
            margin: 0;
            padding: 0;
            overflow: hidden;
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-antigravity);
            color: var(--text-main);
        }

        .loading-overlay {
            position: fixed;
            inset: 0;
            background: var(--bg-antigravity);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 2rem;
            z-index: 9999;
        }

        .spinner {
            width: 56px;
            height: 56px;
            border: 2px solid rgba(0, 0, 0, 0.03);
            border-top-color: #111;
            border-radius: 50%;
            animation: spin 1s cubic-bezier(0.16, 1, 0.3, 1) infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .panel-sidebar {
            background-color: #FDFDFB;
            /* Light cream */
            border-right: none;
            z-index: 20;
            transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .center-card {
            background: #FFFFFF;
            border: 1px solid rgba(0, 0, 0, 0.02);
            border-radius: 24px;
            padding: 1.5rem;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.01);
        }

        .center-card:hover,
        .center-card.active {
            transform: translateY(-4px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.05);
            border-color: rgba(0, 0, 0, 0.05);
        }

        .center-card.active {
            border-left: 4px solid #C8553D;
            /* Soft Terracotta */
        }

        /* ============================================
           MOBILE BOTTOM SHEET - Professional 3-State UX
           States: peek (15vh), half (45vh), full (85vh)
           ============================================ */
        @media (max-width: 767px) {
            /* Hide desktop order classes on mobile */
            .order-2.md\:order-1 {
                order: 2 !important;
            }
            
            /* Main container - stack vertically */
            .flex.flex-col-reverse.md\:flex-row {
                display: block !important;
                position: relative;
                height: calc(100vh - 72px);
            }
            
            /* Map container - full screen behind panel */
            main.flex-1 {
                position: absolute !important;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                height: 100% !important;
                z-index: 1;
            }
            
            #map, #leaflet-map {
                height: 100% !important;
                width: 100% !important;
            }
            
            /* ============================================
               Bottom Sheet Panel - iOS-style transitions
               ============================================ */
            #centers-panel {
                position: absolute !important;
                bottom: 0 !important;
                left: 0 !important;
                right: 0 !important;
                top: auto !important;
                height: 45vh; /* Default: half state */
                max-height: 90vh;
                min-height: 100px;
                z-index: 100 !important;
                background: white !important;
                border-radius: 20px 20px 0 0 !important;
                box-shadow: 0 -4px 30px rgba(0, 0, 0, 0.1), 
                            0 -1px 0 rgba(0, 0, 0, 0.05) !important;
                display: flex;
                flex-direction: column;
                overflow: hidden;
                /* Smooth iOS-like spring animation */
                transition: height 0.4s cubic-bezier(0.32, 0.72, 0, 1),
                            transform 0.4s cubic-bezier(0.32, 0.72, 0, 1);
                will-change: height, transform;
            }
            
            /* State: Peek (collapsed) - shows just handle + location */
            #centers-panel.state-peek {
                height: 140px;
            }
            
            /* State: Half - shows filters + some centers */
            #centers-panel.state-half {
                height: 45vh;
            }
            
            /* State: Full - shows all content */
            #centers-panel.state-full {
                height: 85vh;
            }
            
            /* Dragging state - disable transitions for smooth drag */
            #centers-panel.dragging {
                transition: none !important;
            }
            
            /* ============================================
               Drag Handle - Professional touch target
               ============================================ */
            #swipe-area {
                flex-shrink: 0;
                padding: 12px 0 8px 0;
                cursor: grab;
                touch-action: none;
                -webkit-user-select: none;
                user-select: none;
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 8px;
            }
            
            #swipe-area:active {
                cursor: grabbing;
            }
            
            /* Drag handle pill */
            .drag-handle {
                width: 36px;
                height: 5px;
                background: #E0E0E0;
                border-radius: 3px;
                transition: all 0.2s ease;
            }
            
            #centers-panel.dragging .drag-handle,
            #swipe-area:active .drag-handle {
                width: 44px;
                background: #BDBDBD;
            }
            
            /* State indicator dots */
            .state-dots {
                display: flex;
                gap: 6px;
                opacity: 0;
                transition: opacity 0.3s ease;
            }
            
            #centers-panel.dragging .state-dots {
                opacity: 1;
            }
            
            .state-dot {
                width: 6px;
                height: 6px;
                border-radius: 50%;
                background: #E0E0E0;
                transition: all 0.2s ease;
            }
            
            .state-dot.active {
                background: #111;
                transform: scale(1.2);
            }
            
            /* ============================================
               Location Header - Compact on mobile
               ============================================ */
            .location-header {
                padding: 12px 16px !important;
                flex-shrink: 0;
            }
            
            .location-header h3 {
                font-size: 1.125rem !important;
            }
            
            /* ============================================
               Filters - Horizontal scrollable
               ============================================ */
            .filters-section {
                padding: 8px 12px !important;
                flex-shrink: 0;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            /* Hide scrollbar but keep functionality */
            .filters-section::-webkit-scrollbar {
                display: none;
            }
            
            /* ============================================
               Centers List - Scrollable content
               ============================================ */
            .centers-content {
                flex: 1;
                overflow-y: auto;
                padding: 12px !important;
                -webkit-overflow-scrolling: touch;
                overscroll-behavior: contain;
            }
            
            /* Fade effect when in peek state */
            #centers-panel.state-peek .centers-content {
                opacity: 0.3;
                pointer-events: none;
            }
            
            /* Center cards - touch optimized */
            .center-card {
                padding: 14px !important;
                margin-bottom: 10px !important;
                border-radius: 16px !important;
                border: 1px solid rgba(0,0,0,0.04) !important;
                background: #FAFAFA !important;
                transition: transform 0.15s ease, background 0.15s ease !important;
            }
            
            .center-card:active {
                transform: scale(0.98) !important;
                background: #F0F0F0 !important;
            }
            
            .center-card h4 {
                font-size: 0.9375rem !important;
                white-space: normal;
                line-height: 1.3;
            }
            
            /* Centers list background */
            #centers-list {
                background: white !important;
                min-height: 100%;
                padding-bottom: 40px !important;
            }
            
            /* Panel sections styling */
            #centers-panel > div {
                background: white !important;
            }
            
            /* Keep location section dark */
            #centers-panel > div:nth-child(2) {
                background: #111 !important;
            }
            
            /* ============================================
               Recenter Button - Follows panel state
               ============================================ */
            #recenter-btn {
                z-index: 90;
                width: 48px !important;
                height: 48px !important;
                transition: bottom 0.4s cubic-bezier(0.32, 0.72, 0, 1),
                            transform 0.2s ease,
                            opacity 0.2s ease;
            }
            
            /* Position based on panel state */
            #recenter-btn.state-peek { bottom: 156px !important; }
            #recenter-btn.state-half { bottom: calc(45vh + 16px) !important; }
            #recenter-btn.state-full { bottom: calc(85vh + 16px) !important; opacity: 0.5; }
            
            /* Header floating buttons */
            .floating-action {
                width: 44px !important;
                height: 44px !important;
                min-width: 44px;
                min-height: 44px;
            }
        }

        .capsule-label {
            display: inline-flex;
            padding: 4px 12px;
            border-radius: 100px;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            background: rgba(0, 0, 0, 0.03);
            color: rgba(0, 0, 0, 0.4);
        }

        .capsule-label.urgency {
            background: rgba(200, 85, 61, 0.08);
            color: #C8553D;
        }

        .capsule-label.elite {
            background: #111111;
            color: white;
        }

        header {
            background: rgba(249, 249, 247, 0.95) !important;
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05) !important;
        }

        /* Ambient Glow for Urgency markers */
        .urgency-pulse {
            position: relative;
        }

        .urgency-pulse::after {
            content: '';
            position: absolute;
            inset: -4px;
            background: #C8553D;
            border-radius: 50%;
            opacity: 0.2;
            animation: pulse-glow 2s infinite;
        }

        @keyframes pulse-glow {
            0% {
                transform: scale(1);
                opacity: 0.2;
            }

            50% {
                transform: scale(1.5);
                opacity: 0;
            }

            100% {
                transform: scale(1);
                opacity: 0.2;
            }
        }

        .floating-action {
            background: white;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(0, 0, 0, 0.02);
            transition: all 0.3s ease;
        }
    </style>
</head>

<body class="antialiased bg-gray-100">
    <script>
        // Page reveal logic
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                document.body.classList.add('loaded');
                document.querySelectorAll('.initial-reveal-container').forEach(el => el.classList.add('active'));
            }, 100);
        });
    </script>


    <!-- Loading Overlay -->
    <div id="loading-overlay" class="loading-overlay">
        <div class="spinner"></div>
        <p class="text-gray-600 font-medium" data-i18n="map.loading">Loading map...</p>
    </div>

    <!-- Header -->
    <header class="fixed top-0 left-0 right-0 z-30 transition-all duration-500 h-[72px] initial-reveal-container">
        <div class="px-4 md:px-8 py-4 flex items-center justify-between max-w-full mx-auto h-full">
            <!-- Luxury Back Button -->
            <button onclick="goBack()"
                class="floating-action flex items-center justify-center w-10 h-10 md:w-12 md:h-12 rounded-[14px] md:rounded-[18px] text-[#111] hover:scale-105 active:scale-95 transition-all btn-bloom">
                <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>

            <!-- Sophisticated Title (Visible on mobile now) -->
            <div class="flex flex-col md:flex-row items-center gap-1 md:gap-4">
                <h1 class="font-serif italic text-lg md:text-2xl text-[#111] font-bold tracking-tight">Mentta</h1>
                <div class="hidden md:block w-px h-6 bg-black/10"></div>
                <span
                    class="text-[7px] md:text-[9px] font-bold text-black/40 uppercase tracking-[0.2em] md:tracking-[0.4em]" data-i18n="map.locator">Locator</span>
            </div>

            <!-- Search Trigger -->
            <button id="search-toggle-btn" onclick="window.innerWidth < 768 ? toggleMobileSearch() : toggleSearch()"
                class="floating-action w-10 h-10 md:w-12 md:h-12 flex items-center justify-center text-[#111] rounded-[14px] md:rounded-[18px] hover:bg-black hover:text-white transition-all btn-bloom">
                <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </button>
        </div>

        <!-- Search Bar Expansion -->
        <div id="search-bar" class="hidden px-8 pb-6 animate-fade">
            <div class="relative max-w-2xl mx-auto">
                <input type="text" id="search-input" data-i18n-placeholder="map.searchInputPlaceholder" placeholder="Search by name or district..."
                    class="w-full bg-white/50 backdrop-blur-md px-12 py-4 rounded-[2rem] border border-black/5 focus:outline-none focus:ring-4 focus:ring-black/5 transition-all text-sm font-medium"
                    oninput="searchCenters(this.value)" autocomplete="off">
                <svg class="w-5 h-5 text-black/30 absolute left-5 top-1/2 -translate-y-1/2" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>

        <!-- Mobile Search Overlay (Full Screen) -->
        <div id="mobile-search-overlay"
            class="fixed inset-0 bg-white z-[60] hidden flex-col p-6 animate-fade md:hidden">
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-2xl font-serif font-bold text-[#111]" data-i18n="map.search">Search</h2>
                <button onclick="toggleMobileSearch()" class="p-2 rounded-full bg-gray-100">
                    <svg class="w-6 h-6 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <input type="text" id="mobile-search-input" data-i18n-placeholder="map.mobileSearchInputPlaceholder" placeholder="Name, district or specialty..."
                class="w-full bg-gray-50 px-6 py-5 rounded-2xl border-none focus:ring-2 focus:ring-black text-lg mb-4"
                oninput="searchCenters(this.value)" autocomplete="off" autofocus>
            <p class="text-xs text-gray-400 font-bold uppercase tracking-widest text-center" data-i18n="map.realTimeResults">Real-time results
            </p>
        </div>
    </header>

    <!-- Main Container -->
    <div class="flex flex-col-reverse md:flex-row h-screen pt-[72px] overflow-hidden">

        <!-- Side Panel (Desktop: Left | Mobile: Bottom Sheet) -->
        <aside id="centers-panel"
            class="panel-sidebar md:w-[400px] w-full h-[35vh] md:h-full flex flex-col shadow-[0_-10px_40px_-5px_rgba(0,0,0,0.15)] md:shadow-none bg-white z-40 relative rounded-t-[32px] md:rounded-none overflow-hidden shrink-0 initial-reveal-container transform transition-all duration-300 order-2 md:order-1"
            style="transition-delay: 0.1s;">
            <!-- Swipe Handle (Mobile only - Professional drag handle) -->
            <div class="w-full flex flex-col items-center py-3 md:hidden bg-white cursor-grab z-20 select-none"
                id="swipe-area">
                <div class="drag-handle"></div>
                <div class="state-dots mt-2">
                    <div class="state-dot" data-state="peek"></div>
                    <div class="state-dot active" data-state="half"></div>
                    <div class="state-dot" data-state="full"></div>
                </div>
            </div>

            <!-- Location Aura Section (Compacted) -->
            <div class="px-5 py-4 md:p-8 bg-black text-white relative overflow-hidden shrink-0">
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-[#AF8A6B]/20 blur-[60px] rounded-full"></div>
                <div class="relative z-10 flex flex-col md:block">
                    <div class="flex items-center justify-between mb-1">
                        <p class="text-[9px] font-bold text-white/40 uppercase tracking-[0.2em]" data-i18n="map.currentEnvironment">Current Environment</p>
                        <div class="flex items-center gap-2 md:hidden">
                            <span class="w-1.5 h-1.5 bg-[#AF8A6B] rounded-full animate-pulse"></span>
                            <p class="text-[9px] text-white/50 font-medium" id="user-city-text-mobile" data-i18n="map.online">Online</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <h3 class="font-serif italic text-xl md:text-2xl" id="user-location-text" data-i18n="map.detecting">Detecting...</h3>
                        <div class="hidden md:flex items-center gap-2 mt-2">
                            <span class="w-1.5 h-1.5 bg-[#AF8A6B] rounded-full animate-pulse"></span>
                            <p class="text-[10px] text-white/50 font-medium whitespace-nowrap" id="user-city-text" data-i18n="map.optimizedForYou">
                                Optimized for you</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters: Antigravity Selection Style (Compacted) -->
            <div class="py-2.5 px-4 md:p-8 border-b border-black/5 shrink-0 overflow-x-auto no-scrollbar">
                <div class="flex flex-nowrap md:flex-wrap gap-3 min-w-max px-2 md:px-0">
                    <label class="cursor-pointer group">
                        <input type="radio" name="filter" value="all" checked onchange="applyFilter(this.value)"
                            class="hidden peer">
                        <div
                            class="px-5 py-2 md:px-6 md:py-3.5 rounded-full border border-black/5 bg-white text-[11px] md:text-[11px] font-bold text-black/60 peer-checked:bg-[#111111] peer-checked:text-white peer-checked:border-[#111111] transition-all whitespace-nowrap shadow-sm" data-i18n="map.filters.all">
                            All</div>
                    </label>
                    <label class="cursor-pointer group">
                        <input type="radio" name="filter" value="mentta" onchange="applyFilter(this.value)"
                            class="hidden peer">
                        <div
                            class="px-5 py-2 md:px-6 md:py-3.5 rounded-full border border-black/5 bg-white text-[11px] md:text-[11px] font-bold text-black/60 peer-checked:bg-[#111111] peer-checked:text-white peer-checked:border-[#111111] transition-all whitespace-nowrap shadow-sm" data-i18n="map.filters.menttaNetwork">
                            Mentta Network</div>
                    </label>
                    <label class="cursor-pointer group">
                        <input type="radio" name="filter" value="emergency" onchange="applyFilter(this.value)"
                            class="hidden peer">
                        <div
                            class="px-5 py-2 md:px-6 md:py-3.5 rounded-full border border-black/5 bg-white text-[11px] md:text-[11px] font-bold text-black/60 peer-checked:bg-[#111111] peer-checked:text-white peer-checked:border-[#111111] transition-all whitespace-nowrap shadow-sm" data-i18n="map.filters.emergency">
                            24h</div>
                    </label>
                </div>
            </div>

            <!-- Centers List with Elite Card Style -->
            <div class="flex-1 overflow-y-auto px-4 py-5 md:px-6 md:py-6" style="background: white;">
                <p class="text-[9px] font-bold text-black/30 uppercase tracking-[0.2em] mb-5 px-1" data-i18n="map.nearbyResults">Nearby Results
                </p>
                <div id="centers-list" class="space-y-4">
                    <!-- Populated by JS -->
                </div>
            </div>


        </aside>

        <!-- Map Container -->
        <main class="flex-1 relative initial-reveal-container" style="transition-delay: 0.2s;">
            <?php if (!empty($mapsApiKey)): ?>
                <div id="map" class="w-full h-full"></div>
            <?php endif; ?>

            <!-- Re-center Button: Floating Aesthetic - Smaller & Lower for easier reach -->
            <button id="recenter-btn" onclick="recenterMap()"
                class="absolute bottom-6 right-4 md:bottom-10 md:right-8 w-10 h-10 md:w-16 md:h-16 bg-white text-[#555] rounded-xl md:rounded-[1.8rem] shadow-[0_10px_30px_rgba(0,0,0,0.1)] hover:scale-110 active:scale-95 transition-all z-10 flex items-center justify-center border border-black/5 group btn-bloom"
                title="Volver a mi ubicación">
                <svg class="w-5 h-5 md:w-6 md:h-6 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 2v2m0 16v2m10-10h-2M4 12H2m15.364-7.364l-1.414 1.414M7.05 16.95l-1.414 1.414m12.728 0l-1.414-1.414M7.05 7.05L5.636 5.636M12 12a3 3 0 100-6 3 3 0 000 6z" />
                </svg>
            </button>

            <!-- DEV-004 FIXED: Leaflet fallback when no Google Maps API key -->
            <?php if (empty($mapsApiKey)): ?>
                <div id="leaflet-map" class="absolute inset-0 w-full h-full"></div>
            <?php endif; ?>
        </main>
    </div>

    <!-- Google Maps API -->
    <?php if (!empty($mapsApiKey)): ?>
        <script>
            // Pass PHP variables to JavaScript
            window.MENTTA_MAP_CONFIG = {
                apiKey: '<?= htmlspecialchars($mapsApiKey) ?>',
                defaultLocation: { lat: -12.0464, lng: -77.0428 }, // Lima
                defaultZoom: 13
            };
        </script>
        <script
            src="https://maps.googleapis.com/maps/api/js?key=<?= htmlspecialchars($mapsApiKey) ?>&callback=initMap&loading=async"
            async defer></script>
    <?php else: ?>
        <!-- DEV-004: Leaflet fallback script -->
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
            // ============================================
            // LEAFLET FALLBACK - Premium Map Experience
            // ============================================
            
            // Global state
            let userLocation = null;
            let centerMarkers = [];
            let currentFilter = 'all';
            const BASE_URL = decodeURIComponent(window.location.pathname.replace(/\/[^\/]*$/, '')).trim();
            
            // Initialize map with Carto Positron (luxury white style)
            window.map = L.map('leaflet-map', {
                zoomControl: true
            }).setView([-12.0464, -77.0428], 13);
            
            // CartoDB Positron - Clean, luxury white style (similar to Google Maps custom style)
            L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>',
                subdomains: 'abcd',
                maxZoom: 19
            }).addTo(window.map);
            
            // Custom icon creator
            const createIcon = (color, size = 24) => L.divIcon({
                className: 'custom-marker',
                html: `<div style="background:${color};width:${size}px;height:${size}px;border-radius:50%;border:3px solid white;box-shadow:0 4px 12px rgba(0,0,0,0.15);"></div>`,
                iconSize: [size, size],
                iconAnchor: [size/2, size/2]
            });
            
            // User location marker
            let userMarker = null;
            let userCircle = null;
            
            function addUserMarker(location) {
                if (userMarker) window.map.removeLayer(userMarker);
                if (userCircle) window.map.removeLayer(userCircle);
                
                userMarker = L.marker([location.lat, location.lng], {
                    icon: L.divIcon({
                        className: 'user-marker',
                        html: `<div style="background:#4285F4;width:16px;height:16px;border-radius:50%;border:3px solid white;box-shadow:0 2px 8px rgba(66,133,244,0.5);"></div>`,
                        iconSize: [16, 16],
                        iconAnchor: [8, 8]
                    }),
                    zIndexOffset: 1000
                }).addTo(window.map);
                
                userCircle = L.circle([location.lat, location.lng], {
                    color: '#4285F4',
                    fillColor: '#4285F4',
                    fillOpacity: 0.15,
                    radius: 150,
                    weight: 2,
                    opacity: 0.4
                }).addTo(window.map);
            }
            
            // Geolocation
            function initGeolocation() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            userLocation = {
                                lat: position.coords.latitude,
                                lng: position.coords.longitude
                            };
                            console.log('📍 User location detected:', userLocation);
                            window.map.setView([userLocation.lat, userLocation.lng], 13);
                            addUserMarker(userLocation);
                            updateLocationText('Tu ubicación actual');
                            loadNearbyCenters(userLocation.lat, userLocation.lng);
                            document.getElementById('loading-overlay').style.display = 'none';
                        },
                        (error) => {
                            console.warn('Geolocation error:', error.message);
                            handleGeolocationError();
                        },
                        { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 }
                    );
                } else {
                    handleGeolocationError();
                }
            }
            
            function handleGeolocationError() {
                userLocation = { lat: -12.0464, lng: -77.0428 };
                window.map.setView([userLocation.lat, userLocation.lng], 13);
                addUserMarker(userLocation);
                updateLocationText('Lima, Perú');
                updateCityText('Usando ubicación aproximada');
                loadNearbyCenters(userLocation.lat, userLocation.lng);
                document.getElementById('loading-overlay').style.display = 'none';
            }
            
            // Load centers from API
            async function loadNearbyCenters(lat, lng) {
                const apiUrl = `${BASE_URL}/api/map/get-nearby-centers.php?lat=${lat}&lng=${lng}&radius=100&filter=${currentFilter}`;
                console.log('🔍 Fetching nearby centers from:', apiUrl);
                
                try {
                    const response = await fetch(apiUrl);
                    const data = await response.json();
                    
                    if (data.success && data.data.centers && data.data.centers.length > 0) {
                        renderCentersOnMap(data.data.centers);
                        renderCentersList(data.data.centers);
                        console.log(`✅ Loaded ${data.data.count} centers`);
                    } else {
                        showNoCentersMessage('No hay centros registrados cerca de tu ubicación.');
                    }
                } catch (error) {
                    console.error('Error loading centers:', error);
                    showNoCentersMessage('Error al cargar centros');
                }
            }
            
            // Render markers on map
            function renderCentersOnMap(centers) {
                centerMarkers.forEach(m => window.map.removeLayer(m));
                centerMarkers = [];
                
                const bounds = L.latLngBounds();
                if (userLocation) bounds.extend([userLocation.lat, userLocation.lng]);
                
                centers.forEach((center, index) => {
                    const lat = parseFloat(center.latitude);
                    const lng = parseFloat(center.longitude);
                    bounds.extend([lat, lng]);
                    
                    let color = '#AF8A6B'; // Luxury Sand
                    let size = 22;
                    if (center.has_mentta) { color = '#111111'; size = 26; }
                    else if (center.emergency_24h) { color = '#C8553D'; }
                    
                    const marker = L.marker([lat, lng], { icon: createIcon(color, size) }).addTo(window.map);
                    
                    const distanceText = center.distance ? `${center.distance} km` : '';
                    marker.bindPopup(`
                        <div style="min-width:220px;font-family:'Inter',sans-serif;">
                            <h3 style="font-family:'Playfair Display',serif;font-weight:700;font-size:16px;margin:0 0 8px 0;color:#111;">${escapeHtml(center.name)}</h3>
                            <div style="margin-bottom:12px;">
                                ${center.has_mentta ? '<span style="display:inline-block;background:#111;color:white;padding:3px 8px;border-radius:12px;font-size:9px;font-weight:700;margin-right:4px;">✨ MENTTA</span>' : ''}
                                ${center.emergency_24h ? '<span style="display:inline-block;background:#C8553D;color:white;padding:3px 8px;border-radius:12px;font-size:9px;font-weight:700;">🚨 24H</span>' : ''}
                            </div>
                            <p style="color:#666;font-size:12px;margin:0 0 12px 0;">${escapeHtml(center.address || '')}</p>
                            ${center.phone ? `<a href="tel:${center.phone}" style="display:block;text-align:center;padding:10px;background:#111;color:white;border-radius:10px;text-decoration:none;font-size:11px;font-weight:700;margin-bottom:8px;">📞 Llamar</a>` : ''}
                            <a href="https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}" target="_blank" style="display:block;text-align:center;padding:10px;background:#F5F5F0;color:#111;border-radius:10px;text-decoration:none;font-size:11px;font-weight:700;">🧭 Cómo llegar</a>
                        </div>
                    `);
                    
                    marker.on('click', () => highlightListItem(center.id));
                    marker.centerData = center;
                    marker.centerIndex = index;
                    centerMarkers.push(marker);
                });
                
                if (centers.length > 0) {
                    window.map.fitBounds(bounds, { padding: [50, 50] });
                }
            }
            
            // Render centers list
            function renderCentersList(centers) {
                const container = document.getElementById('centers-list');
                if (!container) return;
                
                container.innerHTML = centers.map((center, index) => {
                    let indicatorColor = '#AF8A6B';
                    if (center.emergency_24h) indicatorColor = '#C8553D';
                    if (center.has_mentta) indicatorColor = '#111';
                    
                    const distanceText = center.distance ? `${center.distance} km` : '';
                    
                    return `
                        <div class="center-card group" data-center-id="${center.id}" onclick="focusOnCenter(${index})">
                            <div class="flex items-center gap-5">
                                <div class="w-10 h-10 rounded-2xl flex items-center justify-center text-[10px] font-black transition-all duration-500 group-hover:scale-110" 
                                     style="background: ${indicatorColor}; color: white; box-shadow: 0 10px 20px -5px ${indicatorColor}44;">
                                    ${String(index + 1).padStart(2, '0')}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-serif italic text-[15px] text-[#111] font-bold truncate">${escapeHtml(center.name)}</h4>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="text-[9px] font-bold text-black/30 uppercase tracking-widest">${escapeHtml(center.district || center.city || '')}</span>
                                        ${distanceText ? `<div class="w-1 h-1 rounded-full bg-black/10"></div><span class="text-[9px] font-black text-[#AF8A6B] uppercase tracking-widest">${distanceText}</span>` : ''}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                }).join('');
            }
            
            function focusOnCenter(index) {
                if (centerMarkers[index]) {
                    const marker = centerMarkers[index];
                    window.map.setView(marker.getLatLng(), 16);
                    marker.openPopup();
                    highlightListItem(marker.centerData.id);
                }
            }
            
            function highlightListItem(centerId) {
                document.querySelectorAll('.center-card.active').forEach(el => el.classList.remove('active'));
                const item = document.querySelector(`[data-center-id="${centerId}"]`);
                if (item) {
                    item.classList.add('active');
                    item.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            }
            
            // UI helpers
            function updateLocationText(text) {
                const el = document.getElementById('user-location-text');
                if (el) el.textContent = text;
            }
            
            function updateCityText(text) {
                const el = document.getElementById('user-city-text');
                if (el) el.textContent = text;
            }
            
            function showNoCentersMessage(message) {
                const container = document.getElementById('centers-list');
                if (container) {
                    container.innerHTML = `<div class="text-center py-8 text-gray-400"><div class="text-4xl mb-2">🏥</div><p class="text-sm">${escapeHtml(message)}</p></div>`;
                }
            }
            
            function escapeHtml(text) {
                if (!text) return '';
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
            
            // Global functions
            window.goBack = () => window.location.href = 'chat.php';
            window.recenterMap = () => {
                if (userLocation) {
                    window.map.setView([userLocation.lat, userLocation.lng], 13);
                    loadNearbyCenters(userLocation.lat, userLocation.lng);
                }
            };
            window.toggleSearch = () => document.getElementById('search-bar')?.classList.toggle('hidden');
            window.applyFilter = (filter) => {
                console.log('Applying filter:', filter);
                currentFilter = filter;
                if (userLocation) loadNearbyCenters(userLocation.lat, userLocation.lng);
            };
            
            let searchTimeout = null;
            window.searchCenters = async (query) => {
                clearTimeout(searchTimeout);
                if (query.length < 2) {
                    if (userLocation) loadNearbyCenters(userLocation.lat, userLocation.lng);
                    return;
                }
                searchTimeout = setTimeout(async () => {
                    try {
                        const response = await fetch(`${BASE_URL}/api/map/search-centers.php?q=${encodeURIComponent(query)}`);
                        const data = await response.json();
                        if (data.success) {
                            renderCentersOnMap(data.data.results || []);
                            renderCentersList(data.data.results || []);
                        }
                    } catch (error) {
                        console.error('Search error:', error);
                    }
                }, 400);
            };
            
            window.clearSearch = () => {
                const input = document.getElementById('search-input');
                if (input) input.value = '';
                if (userLocation) loadNearbyCenters(userLocation.lat, userLocation.lng);
            };
            
            window.toggleMobileSearch = () => {
                const el = document.getElementById('mobile-search-overlay');
                if (el) {
                    el.classList.toggle('hidden');
                    el.classList.toggle('flex');
                    if (!el.classList.contains('hidden')) {
                        setTimeout(() => document.getElementById('mobile-search-input')?.focus(), 100);
                    }
                }
            };
            
            // ============================================
            // PROFESSIONAL 3-STATE BOTTOM SHEET
            // States: peek (140px), half (45vh), full (85vh)
            // ============================================
            const BottomSheet = {
                panel: null,
                swipeArea: null,
                recenterBtn: null,
                
                // State definitions
                states: {
                    peek: { height: 140, name: 'peek' },
                    half: { height: window.innerHeight * 0.45, name: 'half' },
                    full: { height: window.innerHeight * 0.85, name: 'full' }
                },
                
                currentState: 'half',
                startY: 0,
                startHeight: 0,
                isDragging: false,
                
                init() {
                    this.panel = document.getElementById('centers-panel');
                    this.swipeArea = document.getElementById('swipe-area');
                    this.recenterBtn = document.getElementById('recenter-btn');
                    
                    if (!this.panel || !this.swipeArea) return;
                    
                    // Update state heights on resize
                    window.addEventListener('resize', () => {
                        this.states.half.height = window.innerHeight * 0.45;
                        this.states.full.height = window.innerHeight * 0.85;
                    });
                    
                    // Touch events
                    this.swipeArea.addEventListener('touchstart', (e) => this.onTouchStart(e), { passive: true });
                    this.swipeArea.addEventListener('touchmove', (e) => this.onTouchMove(e), { passive: false });
                    this.swipeArea.addEventListener('touchend', (e) => this.onTouchEnd(e));
                    
                    // Mouse events for desktop testing
                    this.swipeArea.addEventListener('mousedown', (e) => this.onMouseDown(e));
                    
                    // Tap to toggle
                    this.swipeArea.addEventListener('click', (e) => {
                        if (!this.isDragging) this.cycleState();
                    });
                    
                    // Initialize state
                    this.setState('half', false);
                },
                
                onTouchStart(e) {
                    this.startDrag(e.touches[0].clientY);
                },
                
                onTouchMove(e) {
                    if (!this.isDragging) return;
                    e.preventDefault();
                    this.onDrag(e.touches[0].clientY);
                },
                
                onTouchEnd(e) {
                    this.endDrag();
                },
                
                onMouseDown(e) {
                    this.startDrag(e.clientY);
                    
                    const onMouseMove = (e) => this.onDrag(e.clientY);
                    const onMouseUp = () => {
                        this.endDrag();
                        document.removeEventListener('mousemove', onMouseMove);
                        document.removeEventListener('mouseup', onMouseUp);
                    };
                    
                    document.addEventListener('mousemove', onMouseMove);
                    document.addEventListener('mouseup', onMouseUp);
                },
                
                startDrag(y) {
                    this.isDragging = true;
                    this.startY = y;
                    this.startHeight = this.panel.offsetHeight;
                    this.panel.classList.add('dragging');
                },
                
                onDrag(y) {
                    if (!this.isDragging) return;
                    
                    const deltaY = this.startY - y;
                    const newHeight = Math.max(100, Math.min(this.startHeight + deltaY, window.innerHeight * 0.9));
                    
                    this.panel.style.height = newHeight + 'px';
                    
                    // Update state indicator dots during drag
                    this.updateStateDots(newHeight);
                },
                
                endDrag() {
                    if (!this.isDragging) return;
                    
                    this.isDragging = false;
                    this.panel.classList.remove('dragging');
                    
                    const currentHeight = this.panel.offsetHeight;
                    const thresholds = [
                        { state: 'peek', height: this.states.peek.height },
                        { state: 'half', height: this.states.half.height },
                        { state: 'full', height: this.states.full.height }
                    ];
                    
                    // Find closest state
                    let closestState = 'half';
                    let minDiff = Infinity;
                    
                    thresholds.forEach(t => {
                        const diff = Math.abs(currentHeight - t.height);
                        if (diff < minDiff) {
                            minDiff = diff;
                            closestState = t.state;
                        }
                    });
                    
                    // Velocity-based adjustment (if dragged quickly, go to next state)
                    const dragDistance = this.startHeight - currentHeight;
                    if (Math.abs(dragDistance) > 50) {
                        if (dragDistance > 0 && closestState !== 'peek') {
                            closestState = closestState === 'full' ? 'half' : 'peek';
                        } else if (dragDistance < 0 && closestState !== 'full') {
                            closestState = closestState === 'peek' ? 'half' : 'full';
                        }
                    }
                    
                    this.setState(closestState);
                },
                
                setState(state, animate = true) {
                    this.currentState = state;
                    const height = this.states[state].height;
                    
                    // Remove inline height and let CSS take over
                    this.panel.classList.remove('state-peek', 'state-half', 'state-full');
                    this.panel.classList.add(`state-${state}`);
                    this.panel.style.height = '';
                    
                    // Update recenter button position
                    if (this.recenterBtn) {
                        this.recenterBtn.classList.remove('state-peek', 'state-half', 'state-full');
                        this.recenterBtn.classList.add(`state-${state}`);
                    }
                    
                    // Update state dots
                    this.updateStateDots(height);
                    
                    console.log(`📱 Panel state: ${state}`);
                },
                
                updateStateDots(height) {
                    const dots = document.querySelectorAll('.state-dot');
                    let activeState = 'half';
                    
                    if (height <= 200) activeState = 'peek';
                    else if (height >= window.innerHeight * 0.65) activeState = 'full';
                    
                    dots.forEach(dot => {
                        dot.classList.toggle('active', dot.dataset.state === activeState);
                    });
                },
                
                cycleState() {
                    const order = ['peek', 'half', 'full'];
                    const currentIndex = order.indexOf(this.currentState);
                    const nextIndex = (currentIndex + 1) % order.length;
                    this.setState(order[nextIndex]);
                }
            };
            
            // Initialize on load
            if (window.innerWidth < 768) {
                BottomSheet.init();
            }
            
            // Global function for compatibility
            window.togglePanelHeight = () => BottomSheet.cycleState();
            
            // Initialize
            initGeolocation();
        </script>
    <?php endif; ?>

    <!-- Utility Scripts -->
    <script src="assets/js/utils.js?v=<?= time() ?>"></script>
    <script src="assets/js/translations.js?v=<?= time() ?>"></script>
    <script src="assets/js/map.js?v=<?= time() ?>"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof i18n !== 'undefined') {
                i18n.applyTranslations();
                
                // Subscribe to language changes to reload map markers if needed
                i18n.onLanguageChange((lang) => {
                    // Refresh page to re-render map with new language
                    window.location.reload();
                });
            }
        });
    </script>
</body>

</html>