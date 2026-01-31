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
    
    <!-- Theme & Map Styles -->
    <link rel="stylesheet" href="assets/css/theme.css">
    <link rel="stylesheet" href="assets/css/map.css">
    
    <style>
        /* Critical CSS inline for fast render */
        body {
            margin: 0;
            padding: 0;
            overflow: hidden;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .loading-overlay {
            position: fixed;
            inset: 0;
            background: rgba(255,255,255,0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 1rem;
            z-index: 9999;
        }
        
        .spinner {
            width: 48px;
            height: 48px;
            border: 4px solid #E5E7EB;
            border-top-color: #6366F1;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
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
    <header class="fixed top-0 left-0 right-0 z-30 bg-white border-b shadow-sm">
        <div class="px-4 py-3 flex items-center justify-between">
            <!-- Back Button -->
            <button onclick="goBack()" class="flex items-center gap-2 text-gray-700 hover:text-indigo-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                <span class="font-medium hidden sm:inline">Volver</span>
            </button>
            
            <!-- Title -->
            <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                <span>🗺️</span>
                <span class="hidden sm:inline">Centros de Salud Mental</span>
                <span class="sm:hidden">Centros</span>
            </h1>
            
            <!-- Search Toggle -->
            <button id="search-toggle-btn" onclick="toggleSearch()" class="p-2 hover:bg-gray-100 rounded-full transition" title="Buscar">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </button>
        </div>
        
        <!-- Search Bar (hidden by default) -->
        <div id="search-bar" class="hidden px-4 pb-3 animate-slideDown">
            <div class="relative">
                <input 
                    type="text" 
                    id="search-input" 
                    placeholder="Buscar por nombre, distrito o servicio..." 
                    class="w-full px-4 py-2.5 pl-10 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-gray-800"
                    oninput="searchCenters(this.value)"
                    autocomplete="off"
                >
                <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <button onclick="clearSearch()" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 hidden" id="clear-search-btn">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
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
            <div class="p-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-b">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-indigo-900" id="user-location-text">Detectando ubicación...</p>
                        <p class="text-xs text-indigo-700" id="user-city-text">Obteniendo ciudad...</p>
                    </div>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="p-4 border-b bg-white">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Filtrar por</p>
                <div class="space-y-2">
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input type="radio" name="filter" value="all" checked onchange="applyFilter(this.value)" 
                               class="w-4 h-4 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm text-gray-700 group-hover:text-indigo-600 transition">Todos los centros</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input type="radio" name="filter" value="mentta" onchange="applyFilter(this.value)" 
                               class="w-4 h-4 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm text-gray-700 group-hover:text-indigo-600 transition">
                            <span class="inline-flex items-center gap-1">
                                <span class="w-2.5 h-2.5 bg-green-500 rounded-full"></span>
                                Centros con Mentta
                            </span>
                        </span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input type="radio" name="filter" value="emergency" onchange="applyFilter(this.value)" 
                               class="w-4 h-4 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm text-gray-700 group-hover:text-indigo-600 transition">
                            <span class="inline-flex items-center gap-1">
                                <span class="w-2.5 h-2.5 bg-orange-500 rounded-full animate-pulse"></span>
                                Emergencias 24h
                            </span>
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
            
            <!-- Re-center Button (positioned above Google Maps controls) -->
            <button 
                id="recenter-btn" 
                onclick="recenterMap()" 
                class="absolute bottom-24 right-3 bg-white p-3 rounded-full shadow-lg hover:shadow-xl transition-all hover:scale-105 z-10"
                title="Volver a mi ubicación"
            >
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
            </button>
            
            <!-- No API Key Warning -->
            <?php if (empty($mapsApiKey)): ?>
            <div class="absolute inset-0 flex items-center justify-center bg-gray-100 z-20">
                <div class="text-center p-8 max-w-md">
                    <div class="text-6xl mb-4">🗺️</div>
                    <h2 class="text-xl font-bold text-gray-800 mb-2">API Key no configurada</h2>
                    <p class="text-gray-600 mb-4">
                        Para usar el mapa, necesitas configurar una API Key de Google Maps en el archivo <code class="bg-gray-200 px-1 rounded">.env</code>
                    </p>
                    <code class="block bg-gray-800 text-green-400 p-3 rounded text-sm text-left">
                        GOOGLE_MAPS_API_KEY=tu_api_key_aqui
                    </code>
                    <a href="chat.php" class="inline-block mt-6 px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                        ← Volver al Chat
                    </a>
                </div>
            </div>
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
        async 
        defer
    ></script>
    <?php else: ?>
    <script>
        // Hide loading overlay if no API key
        document.getElementById('loading-overlay').style.display = 'none';
    </script>
    <?php endif; ?>
    
    <!-- Utility Scripts -->
    <script src="assets/js/utils.js"></script>
    <script src="assets/js/map.js"></script>
</body>
</html>
