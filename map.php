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
<html lang="es" data-theme="<?= htmlspecialchars($theme) ?>">

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
           MOBILE BOTTOM SHEET - Complete Rewrite
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
            
            #map {
                height: 100% !important;
                width: 100% !important;
            }
            
            /* Bottom Sheet Panel */
            #centers-panel {
                position: absolute !important;
                bottom: 0 !important;
                left: 0 !important;
                right: 0 !important;
                top: auto !important;
                height: 40vh;
                max-height: 85vh;
                min-height: 120px;
                z-index: 100 !important;
                background: white !important;
                border-radius: 24px 24px 0 0 !important;
                box-shadow: 0 -8px 30px rgba(0, 0, 0, 0.12) !important;
                display: flex;
                flex-direction: column;
                transition: height 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                overflow: hidden;
            }
            
            /* Panel expanded state */
            #centers-panel.expanded {
                height: 75vh;
            }
            
            /* Panel collapsed state */
            #centers-panel.collapsed {
                height: 140px;
            }
            
            /* Drag handle area */
            #swipe-area {
                flex-shrink: 0;
                padding: 12px 0 8px 0;
                cursor: grab;
                touch-action: none;
            }
            
            #swipe-area:active {
                cursor: grabbing;
            }
            
            /* Drag handle bar */
            #swipe-area > div {
                width: 40px;
                height: 4px;
                background: #D1D5DB;
                border-radius: 2px;
            }
            
            /* Location header - more compact */
            #centers-panel > div:nth-child(2) {
                padding: 12px 16px !important;
            }
            
            #centers-panel > div:nth-child(2) h3 {
                font-size: 1.125rem !important;
            }
            
            /* Filters - horizontal scroll */
            #centers-panel > div:nth-child(3) {
                padding: 8px 12px !important;
                flex-shrink: 0;
            }
            
            /* Centers list - scrollable */
            #centers-panel > div:nth-child(4) {
                flex: 1;
                overflow-y: auto;
                padding: 12px !important;
                -webkit-overflow-scrolling: touch;
            }
            
            /* Center cards - more compact */
            .center-card {
                padding: 12px !important;
                margin-bottom: 8px !important;
                border-radius: 16px !important;
            }
            
            .center-card h4 {
                font-size: 0.9375rem !important;
                white-space: normal;
                line-height: 1.3;
            }
            
            /* Ensure white background fills entire panel */
            #centers-list {
                background: white !important;
                min-height: 100%;
                padding-bottom: 20px !important;
            }
            
            /* Panel inner sections all white */
            #centers-panel > div {
                background: white !important;
            }
            
            /* Keep location section black */
            #centers-panel > div:nth-child(2) {
                background: #111 !important;
            }
            
            /* Recenter button - above panel */
            #recenter-btn {
                bottom: calc(40vh + 16px) !important;
                right: 16px !important;
                z-index: 90;
                width: 44px !important;
                height: 44px !important;
                transition: bottom 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            
            /* Header floating buttons */
            .floating-action {
                width: 40px !important;
                height: 40px !important;
                min-width: 44px;
                min-height: 44px;
            }
            
            /* Touch device - disable hover */
            .center-card:hover {
                transform: none !important;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.01) !important;
            }
            
            .center-card:active {
                transform: scale(0.98) !important;
                background: #F9FAFB !important;
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
        <p class="text-gray-600 font-medium">Cargando mapa...</p>
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
                    class="text-[7px] md:text-[9px] font-bold text-black/40 uppercase tracking-[0.2em] md:tracking-[0.4em]">Localizador</span>
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
                <input type="text" id="search-input" placeholder="Busca por nombre o distrito..."
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
                <h2 class="text-2xl font-serif font-bold text-[#111]">Buscar</h2>
                <button onclick="toggleMobileSearch()" class="p-2 rounded-full bg-gray-100">
                    <svg class="w-6 h-6 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <input type="text" id="mobile-search-input" placeholder="Nombre, distrito o especialidad..."
                class="w-full bg-gray-50 px-6 py-5 rounded-2xl border-none focus:ring-2 focus:ring-black text-lg mb-4"
                oninput="searchCenters(this.value)" autocomplete="off" autofocus>
            <p class="text-xs text-gray-400 font-bold uppercase tracking-widest text-center">Resultados en tiempo real
            </p>
        </div>
    </header>

    <!-- Main Container -->
    <div class="flex flex-col-reverse md:flex-row h-screen pt-[72px] overflow-hidden">

        <!-- Side Panel (Desktop: Left | Mobile: Bottom Sheet) -->
        <aside id="centers-panel"
            class="panel-sidebar md:w-[400px] w-full h-[35vh] md:h-full flex flex-col shadow-[0_-10px_40px_-5px_rgba(0,0,0,0.15)] md:shadow-none bg-white z-40 relative rounded-t-[32px] md:rounded-none overflow-hidden shrink-0 initial-reveal-container transform transition-all duration-300 order-2 md:order-1"
            style="transition-delay: 0.1s;">
            <!-- Swipe Handle (Mobile only - Top of panel) -->
            <div class="w-full flex justify-center py-2 md:hidden bg-white cursor-pointer z-20 hover:bg-gray-50 transition-colors"
                id="swipe-area" onclick="togglePanelHeight()">
                <div class="w-12 h-1 bg-gray-300 rounded-full"></div>
            </div>

            <!-- Location Aura Section (Compacted) -->
            <div class="px-5 py-4 md:p-8 bg-black text-white relative overflow-hidden shrink-0">
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-[#AF8A6B]/20 blur-[60px] rounded-full"></div>
                <div class="relative z-10 flex flex-col md:block">
                    <div class="flex items-center justify-between mb-1">
                        <p class="text-[9px] font-bold text-white/40 uppercase tracking-[0.2em]">Entorno Actual</p>
                        <div class="flex items-center gap-2 md:hidden">
                            <span class="w-1.5 h-1.5 bg-[#AF8A6B] rounded-full animate-pulse"></span>
                            <p class="text-[9px] text-white/50 font-medium" id="user-city-text-mobile">En línea</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <h3 class="font-serif italic text-xl md:text-2xl" id="user-location-text">Detectando...</h3>
                        <div class="hidden md:flex items-center gap-2 mt-2">
                            <span class="w-1.5 h-1.5 bg-[#AF8A6B] rounded-full animate-pulse"></span>
                            <p class="text-[10px] text-white/50 font-medium whitespace-nowrap" id="user-city-text">
                                Optimizado para ti</p>
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
                            class="px-5 py-2 md:px-6 md:py-3.5 rounded-full border border-black/5 bg-white text-[11px] md:text-[11px] font-bold text-black/60 peer-checked:bg-[#111111] peer-checked:text-white peer-checked:border-[#111111] transition-all whitespace-nowrap shadow-sm">
                            Todos</div>
                    </label>
                    <label class="cursor-pointer group">
                        <input type="radio" name="filter" value="mentta" onchange="applyFilter(this.value)"
                            class="hidden peer">
                        <div
                            class="px-5 py-2 md:px-6 md:py-3.5 rounded-full border border-black/5 bg-white text-[11px] md:text-[11px] font-bold text-black/60 peer-checked:bg-[#111111] peer-checked:text-white peer-checked:border-[#111111] transition-all whitespace-nowrap shadow-sm">
                            Red Mentta</div>
                    </label>
                    <label class="cursor-pointer group">
                        <input type="radio" name="filter" value="emergency" onchange="applyFilter(this.value)"
                            class="hidden peer">
                        <div
                            class="px-5 py-2 md:px-6 md:py-3.5 rounded-full border border-black/5 bg-white text-[11px] md:text-[11px] font-bold text-black/60 peer-checked:bg-[#111111] peer-checked:text-white peer-checked:border-[#111111] transition-all whitespace-nowrap shadow-sm">
                            24h</div>
                    </label>
                </div>
            </div>

            <!-- Centers List with Elite Card Style -->
            <div class="flex-1 overflow-y-auto px-4 py-5 md:px-6 md:py-6" style="background: white;">
                <p class="text-[9px] font-bold text-black/30 uppercase tracking-[0.2em] mb-5 px-1">Resultados Cercanos
                </p>
                <div id="centers-list" class="space-y-4">
                    <!-- Populated by JS -->
                </div>
            </div>


        </aside>

        <!-- Map Container -->
        <main class="flex-1 relative initial-reveal-container" style="transition-delay: 0.2s;">
            <div id="map" class="w-full h-full"></div>

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
                <div id="leaflet-map" class="w-full h-full z-10"></div>
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
            // Leaflet fallback map
            document.getElementById('loading-overlay').style.display = 'none';

            window.map = L.map('leaflet-map').setView([-12.0464, -77.0428], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map);

            // Sample mental health centers in Lima
            const centers = [
                { name: 'Hospital Larco Herrera', lat: -12.0970, lng: -77.0486, type: 'hospital', emergency: true },
                { name: 'Instituto Nacional de Salud Mental Noguchi', lat: -12.0389, lng: -77.0581, type: 'hospital', mentta: true },
                { name: 'Centro de Salud Mental Comunitario Jesús María', lat: -12.0757, lng: -77.0454, type: 'csmc', mentta: true },
                { name: 'Centro de Salud Mental Lince', lat: -12.0842, lng: -77.0336, type: 'csmc' },
                { name: 'Hospital Hermilio Valdízan', lat: -12.0559, lng: -76.9644, type: 'hospital', emergency: true }
            ];

            // Custom icons
            const createIcon = (color) => L.divIcon({
                className: 'custom-marker',
                html: `<div style="background:${color};width:24px;height:24px;border-radius:50%;border:3px solid white;box-shadow:0 2px 8px rgba(0,0,0,0.3);"></div>`,
                iconSize: [24, 24],
                iconAnchor: [12, 12]
            });

            // Add markers
            centers.forEach(center => {
                let color = '#E69F8B'; // Soft Terracotta (Antigravity default)
                if (center.mentta) color = '#AF8A6B'; // Luxury Beige for Mentta
                else if (center.emergency) color = '#C8553D'; // Darker Terracotta for Emergency

                const marker = L.marker([center.lat, center.lng], { icon: createIcon(color) }).addTo(map);
                marker.bindPopup(`
                <div style="min-width:200px">
                        <h3 style="font-family:'Playfair Display',serif;font-weight:700;margin-bottom:4px;color:#2d3a2d;">${center.name}</h3>
                        <p style="color:#8b9d8b;font-size:11px;margin-bottom:12px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;">
                            ${center.emergency ? '🏥 Emergencias 24h' : ''}
                            ${center.mentta ? '✅ Sistema Mentta' : ''}
                        </p>
                        <a href="https://www.google.com/maps/dir/?api=1&destination=${center.lat},${center.lng}" 
                           target="_blank" 
                           style="display:block;text-align:center;padding:10px;background:#2d3a2d;color:white;border-radius:12px;text-decoration:none;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;">
                            Cómo llegar
                        </a>
                </div>
            `);
            });

            // Update location text
            document.getElementById('user-location-text').textContent = 'Lima, Perú';
            const cityTextEl = document.getElementById('user-city-text');
            if (cityTextEl) cityTextEl.textContent = 'Usando ubicación aproximada';
            const countsEl = document.getElementById('centers-count');
            if (countsEl) countsEl.textContent = centers.length;

            document.getElementById('centers-list').innerHTML = centers.map((c, index) => {
                let indicatorColor = "#AF8A6B";
                if (c.emergency) indicatorColor = "#C8553D";
                if (c.mentta) indicatorColor = "#111";

                return `
                <div class="center-card group mb-4" onclick="window.map.panTo([${c.lat}, ${c.lng}]); window.map.setZoom(16);">
                    <div class="flex items-center gap-5">
                        <div class="w-12 h-12 rounded-[20px] bg-[#FDFDFB] border border-black/5 flex items-center justify-center transition-all group-hover:border-[#C8553D]/20">
                            <span class="font-serif italic text-lg text-[#111]">${index + 1}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-serif italic text-[16px] text-[#111] font-bold truncate">${c.name}</h4>
                            <div class="flex items-center gap-2 mt-1.5">
                                <span class="capsule-label ${c.mentta ? 'elite' : c.emergency ? 'urgency' : ''}">
                                    ${c.mentta ? 'Red Elite' : c.emergency ? 'Urgencia 24h' : 'Centro Salud'}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            }).join('');

            // Functions
            window.goBack = () => history.back() || (location.href = 'chat.php');
            window.recenterMap = () => window.map.setView([-12.0464, -77.0428], 13);
            window.toggleSearch = () => document.getElementById('search-bar').classList.toggle('hidden');
            window.applyFilter = (filter) => console.log('Filter:', filter);
            window.searchCenters = (q) => console.log('Search:', q);
            window.searchCenters = (q) => console.log('Search:', q);
            window.clearSearch = () => { document.getElementById('search-input').value = ''; };
            window.toggleMobileSearch = () => {
                const el = document.getElementById('mobile-search-overlay');
                el.classList.toggle('hidden');
                el.classList.toggle('flex');
                if (!el.classList.contains('hidden')) {
                    setTimeout(() => document.getElementById('mobile-search-input').focus(), 100);
                }
            };

            // Simple mobile panel interactions
            let isPanelExpanded = false;
            window.togglePanelHeight = () => {
                const panel = document.getElementById('centers-panel');
                if (isPanelExpanded) {
                    panel.style.height = '55vh';
                    panel.classList.remove('h-[85vh]');
                } else {
                    panel.style.height = '85vh';
                    panel.classList.add('h-[85vh]');
                }
                isPanelExpanded = !isPanelExpanded;
            };
        </script>
    <?php endif; ?>

    <!-- Utility Scripts -->
    <script src="assets/js/utils.js?v=<?= time() ?>"></script>
    <script src="assets/js/map.js?v=<?= time() ?>"></script>
</body>

</html>