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
        body {
            margin: 0;
            padding: 0;
            overflow: hidden;
            font-family: 'Inter', sans-serif;
            background-color: #f5f5f0;
        }

        .loading-overlay {
            position: fixed;
            inset: 0;
            background: #fbfbf9;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 1.5rem;
            z-index: 9999;
        }

        .spinner {
            width: 48px;
            height: 48px;
            border: 3px solid #eef2ee;
            border-top-color: #2d3a2d;
            border-radius: 50%;
            animation: spin 0.8s cubic-bezier(0.4, 0, 0.2, 1) infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .panel-sidebar {
            background-color: #fbfbf9;
            border-right: 1px solid rgba(45, 58, 45, 0.08);
            box-shadow: 10px 0 30px rgba(45, 58, 45, 0.03);
            z-index: 20;
        }

        .center-card {
            background: white;
            border: 1px solid rgba(45, 58, 45, 0.05);
            border-radius: 1.25rem;
            padding: 1rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .center-card:hover {
            transform: translateY(-2px);
            border-color: #cbaa8e;
            box-shadow: 0 10px 20px rgba(45, 58, 45, 0.05);
        }

        header {
            background: rgba(255, 255, 255, 0.85) !important;
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(45, 58, 45, 0.05) !important;
        }
    </style>
</head>

<body class="antialiased bg-gray-100">

    <!-- Loading Overlay -->
    <div id="loading-overlay" class="loading-overlay">
        <div class="spinner"></div>
        <p class="text-gray-600 font-medium">Cargando mapa...</p>
    </div>

    <!-- Header -->
    <header class="fixed top-0 left-0 right-0 z-30 shadow-sm transition-all duration-300">
        <div class="px-6 py-4 flex items-center justify-between max-w-[1440px] mx-auto">
            <!-- Back Button -->
            <button onclick="goBack()"
                class="flex items-center gap-2 text-mentta-primary hover:text-black transition-all group">
                <div
                    class="w-8 h-8 rounded-full flex items-center justify-center bg-mentta-50 group-hover:bg-mentta-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </div>
                <span class="font-bold text-[10px] uppercase tracking-widest hidden sm:inline">Volver</span>
            </button>

            <!-- Title -->
            <div class="flex items-center gap-3">
                <span class="font-serif italic text-xl text-mentta-primary"
                    style="font-family: 'Playfair Display', serif; font-weight: 700;">Mentta</span>
                <span class="w-1 h-1 bg-mentta-secondary rounded-full"></span>
                <span
                    class="text-[10px] font-black text-mentta-primary uppercase tracking-[0.25em] hidden sm:inline">Localizador
                    de Centros</span>
            </div>

            <!-- Search Toggle -->
            <button id="search-toggle-btn" onclick="toggleSearch()"
                class="w-10 h-10 flex items-center justify-center bg-mentta-primary text-white rounded-xl shadow-lg shadow-mentta-900/10 hover:scale-105 active:scale-95 transition-all"
                title="Buscar">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </button>
        </div>

        <!-- Search Bar (hidden by default) -->
        <div id="search-bar" class="hidden px-4 pb-3 animate-slideDown">
            <div class="relative">
                <input type="text" id="search-input" placeholder="Buscar por nombre, distrito o servicio..."
                    class="w-full px-4 py-2.5 pl-10 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-gray-800"
                    oninput="searchCenters(this.value)" autocomplete="off">
                <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <button onclick="clearSearch()"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 hidden"
                    id="clear-search-btn">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <div class="flex flex-col md:flex-row h-screen pt-[60px]">

        <!-- Side Panel (Desktop: Left | Mobile: Bottom) -->
        <aside id="centers-panel" class="panel-sidebar">
            <!-- Swipe Handle (Mobile only) -->
            <div class="swipe-handle md:hidden" id="swipe-handle">
                <div class="handle-bar"></div>
            </div>

            <!-- Location Info -->
            <div class="p-6 bg-[#2d3a2d] text-white border-b border-white/10">
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 bg-white/10 rounded-2xl flex items-center justify-center border border-white/20">
                        <svg class="w-6 h-6 text-mentta-secondary" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-white/50 uppercase tracking-widest mb-0.5">Tu área actual</p>
                        <p class="text-sm font-bold" id="user-location-text">Detectando...</p>
                        <p class="text-[10px] text-white/60 font-medium" id="user-city-text">Actualizando base de
                            datos...</p>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="p-6 border-b bg-white">
                <p class="text-[10px] font-black text-mentta-accent uppercase tracking-[0.2em] mb-4">Filtrar por
                    categoría</p>
                <div class="space-y-3">
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input type="radio" name="filter" value="all" checked onchange="applyFilter(this.value)"
                            class="w-4 h-4 text-mentta-primary focus:ring-mentta-primary border-mentta-accent/30">
                        <span
                            class="text-xs font-bold text-mentta-primary/70 group-hover:text-black transition-all">Todos
                            los centros</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input type="radio" name="filter" value="mentta" onchange="applyFilter(this.value)"
                            class="w-4 h-4 text-mentta-primary focus:ring-mentta-primary border-mentta-accent/30">
                        <span
                            class="text-xs font-bold text-mentta-primary/70 group-hover:text-black transition-all flex items-center gap-2">
                            <span class="w-1.5 h-1.5 bg-mentta-secondary rounded-full"></span>
                            Red Mentta
                        </span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input type="radio" name="filter" value="emergency" onchange="applyFilter(this.value)"
                            class="w-4 h-4 text-mentta-primary focus:ring-mentta-primary border-mentta-accent/30">
                        <span
                            class="text-xs font-bold text-mentta-primary/70 group-hover:text-black transition-all flex items-center gap-2">
                            <span class="w-1.5 h-1.5 bg-red-500 rounded-full animate-pulse"></span>
                            Emergencias 24h
                        </span>
                    </label>
                </div>
            </div>

            <!-- Centers List -->
            <div class="flex-1 overflow-y-auto bg-gray-50">
                <div class="p-4">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">
                        <span id="centers-count">0</span> centros cercanos
                    </p>
                    <div id="centers-list" class="space-y-3">
                        <!-- Centers will be populated by JavaScript -->
                        <div class="text-center py-8 text-gray-400">
                            <div class="spinner mx-auto mb-3" style="width:32px;height:32px;border-width:3px;"></div>
                            <p class="text-sm">Buscando centros...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Legend -->
            <div class="p-3 bg-white border-t text-xs">
                <div class="flex flex-wrap gap-3 justify-center text-gray-600">
                    <span class="flex items-center gap-1">
                        <span class="w-3 h-3 bg-blue-500 rounded-full"></span> Tu ubicación
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="w-3 h-3 bg-green-500 rounded-full"></span> Mentta
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="w-3 h-3 bg-orange-500 rounded-full"></span> 24h
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="w-3 h-3 bg-red-500 rounded-full"></span> Otros
                    </span>
                </div>
            </div>
        </aside>

        <!-- Map Container -->
        <main class="flex-1 relative">
            <div id="map" class="w-full h-full"></div>

            <!-- Re-center Button -->
            <button id="recenter-btn" onclick="recenterMap()"
                class="absolute bottom-24 right-3 bg-[#2d3a2d] p-4 rounded-2xl shadow-2xl hover:scale-110 active:scale-95 transition-all z-10 border border-white/20 overflow-hidden group"
                title="Volver a mi ubicación">
                <div
                    class="absolute inset-0 bg-gradient-to-tr from-white/0 to-white/10 opacity-0 group-hover:opacity-100 transition-opacity">
                </div>
                <svg class="w-6 h-6 text-mentta-secondary relative z-10" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
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

            const map = L.map('leaflet-map').setView([-12.0464, -77.0428], 13);

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
                let color = '#EF4444'; // Red default
                if (center.mentta) color = '#10B981'; // Green for Mentta
                else if (center.emergency) color = '#F97316'; // Orange for 24h

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
            document.getElementById('user-city-text').textContent = 'Usando ubicación aproximada';
            document.getElementById('centers-count').textContent = centers.length;

            document.getElementById('centers-list').innerHTML = centers.map(c => `
            <div class="center-card group" onclick="map.setView([${c.lat}, ${c.lng}], 16)">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-2.5 h-2.5 rounded-full ring-4 ring-opacity-20" style="background:${c.mentta ? '#cbaa8e' : c.emergency ? '#ef4444' : '#8b9d8b'}; ring-color:${c.mentta ? '#cbaa8e33' : c.emergency ? '#ef444433' : '#8b9d8b33'}"></div>
                        <span class="font-bold text-sm text-mentta-primary group-hover:text-black transition-colors">${c.name}</span>
                    </div>
                    <svg class="w-4 h-4 text-mentta-accent opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
            </div>
        `).join('');

            // Functions
            window.goBack = () => history.back() || (location.href = 'chat.php');
            window.recenterMap = () => map.setView([-12.0464, -77.0428], 13);
            window.toggleSearch = () => document.getElementById('search-bar').classList.toggle('hidden');
            window.applyFilter = (filter) => console.log('Filter:', filter);
            window.searchCenters = (q) => console.log('Search:', q);
            window.clearSearch = () => { document.getElementById('search-input').value = ''; };
        </script>
    <?php endif; ?>

    <!-- Utility Scripts -->
    <script src="assets/js/utils.js"></script>
    <script src="assets/js/map.js"></script>
</body>

</html>