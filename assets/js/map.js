/**
 * MENTTA - Interactive Map JavaScript Module
 * Handles Google Maps integration, geolocation, and center management
 * 
 * @version 0.5.4
 */

// ============================================
// GLOBAL STATE
// ============================================
let map = null;
let userMarker = null;
let userCircle = null;
let centerMarkers = [];
let userLocation = null;
let currentFilter = 'all';
let infoWindow = null;
let isSearching = false;
let panelExpanded = false;
let geocoder = null;

// Get base URL from current page location
const BASE_URL = window.location.pathname.replace(/\/[^\/]*$/, '');

// ============================================
// MAP INITIALIZATION
// ============================================

/**
 * Initialize the map - called by Google Maps API callback
 */
function initMap() {
    console.log('🗺️ Initializing Mentta Map...');

    // Hide loading overlay
    const loadingOverlay = document.getElementById('loading-overlay');

    // Initialize geocoder for reverse geocoding
    geocoder = new google.maps.Geocoder();

    // Request user's location
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            // Success callback
            (position) => {
                userLocation = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };

                console.log('📍 User location detected:', userLocation);
                updateLocationText('Tu ubicación actual');

                // Get city name from coordinates (reverse geocoding)
                reverseGeocode(userLocation);

                createMap(userLocation);
                addUserMarker(userLocation);
                loadNearbyCenters(userLocation.lat, userLocation.lng);

                if (loadingOverlay) loadingOverlay.style.display = 'none';
            },
            // Error callback
            (error) => {
                console.warn('Geolocation error:', error.message);
                handleGeolocationError();
                if (loadingOverlay) loadingOverlay.style.display = 'none';
            },
            // Options
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 60000
            }
        );
    } else {
        console.warn('Geolocation not supported');
        handleGeolocationError();
        if (loadingOverlay) loadingOverlay.style.display = 'none';
    }

    // Initialize swipe handler for mobile
    initSwipeHandler();
}

/**
 * Reverse geocode coordinates to get city/locality name
 */
function reverseGeocode(location) {
    // Set a fallback with coordinates in case geocoding fails or times out
    const coordsFallback = `${Math.abs(location.lat).toFixed(2)}°${location.lat >= 0 ? 'N' : 'S'}, ${Math.abs(location.lng).toFixed(2)}°${location.lng >= 0 ? 'E' : 'W'}`;

    // Set timeout - if geocoding doesn't respond in 3 seconds, use fallback
    const timeoutId = setTimeout(() => {
        console.warn('⏰ Geocoding timeout, using coordinates');
        updateCityText(coordsFallback);
    }, 3000);

    if (!geocoder) {
        clearTimeout(timeoutId);
        updateCityText(coordsFallback);
        return;
    }

    try {
        geocoder.geocode({ location: location }, (results, status) => {
            clearTimeout(timeoutId);

            if (status === 'OK' && results && results[0]) {
                // Extract city, locality, or administrative area
                let city = '';
                let region = '';
                let country = '';

                for (const component of results[0].address_components) {
                    if (component.types.includes('locality')) {
                        city = component.long_name;
                    } else if (component.types.includes('administrative_area_level_2')) {
                        if (!city) city = component.long_name;
                    } else if (component.types.includes('administrative_area_level_1')) {
                        region = component.long_name;
                    } else if (component.types.includes('country')) {
                        country = component.long_name;
                    }
                }

                // Build location text
                let locationText = city || region || coordsFallback;
                if (country && country !== city && city) {
                    locationText += ', ' + country;
                }

                console.log('📍 Reverse geocoded:', locationText);
                updateCityText(locationText);
            } else {
                console.warn('Geocoder response:', status);
                // Use coordinates as fallback
                updateCityText(coordsFallback);
            }
        });
    } catch (error) {
        clearTimeout(timeoutId);
        console.error('Geocoder error:', error);
        updateCityText(coordsFallback);
    }
}

/**
 * Handle geolocation error - use default Lima location
 */
function handleGeolocationError() {
    const defaultLocation = window.MENTTA_MAP_CONFIG?.defaultLocation || { lat: -12.0464, lng: -77.0428 };
    userLocation = defaultLocation;

    console.log('📍 Using default location (Lima):', defaultLocation);
    updateLocationText('Lima (ubicación predeterminada)');
    updateCityText('Lima, Perú');
    createMap(defaultLocation);
    addUserMarker(defaultLocation);
    loadNearbyCenters(defaultLocation.lat, defaultLocation.lng);
}

/**
 * Create the Google Map instance
 */
function createMap(location) {
    const mapElement = document.getElementById('map');
    if (!mapElement) return;

    map = new google.maps.Map(mapElement, {
        center: location,
        zoom: window.MENTTA_MAP_CONFIG?.defaultZoom || 13,
        mapTypeControl: false,
        streetViewControl: false,
        fullscreenControl: false,
        zoomControl: true,
        zoomControlOptions: {
            position: google.maps.ControlPosition.RIGHT_TOP
        },
        gestureHandling: 'greedy',
        styles: getMapStyles()
    });

    // Create reusable InfoWindow
    infoWindow = new google.maps.InfoWindow({
        maxWidth: 320
    });

    // Close info window when clicking on map
    map.addListener('click', () => {
        infoWindow.close();
    });

    console.log('✅ Map created successfully');
}

/**
 * Get custom map styles (cleaner look)
 */
function getMapStyles() {
    return [
        {
            featureType: "poi",
            elementType: "labels",
            stylers: [{ visibility: "off" }]
        },
        {
            featureType: "poi.medical",
            elementType: "all",
            stylers: [{ visibility: "on" }]
        },
        {
            featureType: "transit",
            elementType: "labels.icon",
            stylers: [{ visibility: "off" }]
        }
    ];
}

/**
 * Add user location marker
 */
function addUserMarker(location) {
    if (!map) return;

    // Remove existing user marker and circle
    if (userMarker) userMarker.setMap(null);
    if (userCircle) userCircle.setMap(null);

    // Create user marker (simple circle)
    userMarker = new google.maps.Marker({
        position: location,
        map: map,
        title: "Tu ubicación",
        icon: {
            path: google.maps.SymbolPath.CIRCLE,
            scale: 10,
            fillColor: "#3B82F6",
            fillOpacity: 1,
            strokeColor: "#FFFFFF",
            strokeWeight: 3
        },
        zIndex: 1000
    });

    // Add pulsing effect using a circle overlay
    userCircle = new google.maps.Circle({
        strokeColor: "#3B82F6",
        strokeOpacity: 0.4,
        strokeWeight: 2,
        fillColor: "#3B82F6",
        fillOpacity: 0.15,
        map: map,
        center: location,
        radius: 150
    });
}

// ============================================
// CENTERS MANAGEMENT
// ============================================

/**
 * Load nearby mental health centers from API
 */
async function loadNearbyCenters(lat, lng) {
    const apiUrl = `${BASE_URL}/api/map/get-nearby-centers.php?lat=${lat}&lng=${lng}&radius=100&filter=${currentFilter}`;
    console.log('🔍 Fetching nearby centers from:', apiUrl);

    try {
        const response = await fetch(apiUrl);

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        console.log('📊 API Response:', data);

        if (data.success) {
            if (data.data.centers && data.data.centers.length > 0) {
                renderCentersOnMap(data.data.centers);
                renderCentersList(data.data.centers);
                updateCentersCount(data.data.count);
                console.log(`✅ Loaded ${data.data.count} centers`);
            } else {
                // No centers found in area - show helpful message
                showNoCentersMessage('No hay centros registrados cerca de tu ubicación. Los centros disponibles están en Lima, Perú.');
                centerMarkers.forEach(marker => marker.setMap(null));
                centerMarkers = [];
            }
        } else {
            console.error('API Error:', data.error);
            showNoCentersMessage('Error: ' + (data.error || 'Error al cargar centros'));
        }
    } catch (error) {
        console.error('Network error loading centers:', error);
        showNoCentersMessage('Error de conexión');
    }
}

/**
 * Render center markers on the map
 */
function renderCentersOnMap(centers) {
    // Clear existing markers
    centerMarkers.forEach(marker => marker.setMap(null));
    centerMarkers = [];

    if (!map || !centers || !centers.length) {
        console.log('⚠️ No centers to render on map');
        return;
    }

    // Create bounds to fit all markers
    const bounds = new google.maps.LatLngBounds();

    if (userLocation) {
        bounds.extend(userLocation);
    }

    centers.forEach((center, index) => {
        const position = {
            lat: parseFloat(center.latitude),
            lng: parseFloat(center.longitude)
        };

        bounds.extend(position);

        // Determine marker color based on type
        let pinColor = "#EF4444"; // Default: Red

        if (center.has_mentta) {
            pinColor = "#10B981"; // Green for Mentta centers
        } else if (center.emergency_24h) {
            pinColor = "#F59E0B"; // Orange for emergency
        }

        const marker = new google.maps.Marker({
            position: position,
            map: map,
            title: center.name,
            label: {
                text: String(index + 1),
                color: "#FFFFFF",
                fontSize: "11px",
                fontWeight: "bold"
            },
            icon: {
                path: google.maps.SymbolPath.CIRCLE,
                scale: 14,
                fillColor: pinColor,
                fillOpacity: 1,
                strokeColor: "#FFFFFF",
                strokeWeight: 2
            },
            animation: center.emergency_24h ? google.maps.Animation.BOUNCE : null
        });

        // Stop bounce animation after 2 seconds for emergency centers
        if (center.emergency_24h) {
            setTimeout(() => marker.setAnimation(null), 2000);
        }

        // Click handler
        marker.addListener('click', () => {
            showCenterInfo(center, marker);
            highlightListItem(center.id);
        });

        centerMarkers.push(marker);
    });

    // Fit map to show all markers (with padding)
    if (centers.length > 0) {
        map.fitBounds(bounds, {
            top: 80,
            right: 20,
            bottom: 20,
            left: window.innerWidth >= 768 ? 340 : 20
        });

        // Don't zoom in too much
        const listener = google.maps.event.addListener(map, 'idle', () => {
            if (map.getZoom() > 15) map.setZoom(15);
            google.maps.event.removeListener(listener);
        });
    }
}

/**
 * Show center info in InfoWindow
 */
function showCenterInfo(center, marker) {
    const services = center.services_array?.join(', ') || center.services || 'No especificado';
    const distanceText = center.distance !== null && center.distance !== undefined
        ? `${center.distance} km`
        : '';

    // Build badges
    let badges = '';
    if (center.has_mentta) {
        badges += '<span style="display:inline-block;background:#D1FAE5;color:#065F46;padding:2px 8px;border-radius:4px;font-size:11px;margin-right:4px;">⭐ Mentta</span>';
    }
    if (center.emergency_24h) {
        badges += '<span style="display:inline-block;background:#FEF3C7;color:#92400E;padding:2px 8px;border-radius:4px;font-size:11px;">🚨 24h</span>';
    }

    // Build rating stars
    let ratingStars = '';
    if (center.rating > 0) {
        const fullStars = Math.floor(center.rating);
        const hasHalfStar = center.rating % 1 >= 0.5;
        for (let i = 0; i < fullStars; i++) ratingStars += '⭐';
        if (hasHalfStar) ratingStars += '½';
        ratingStars = `<span style="font-size:12px;">${ratingStars} (${center.rating})</span>`;
    }

    const content = `
        <div style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;max-width:300px;">
            <h3 style="font-weight:700;font-size:15px;margin:0 0 6px 0;color:#1F2937;line-height:1.3;">
                ${escapeHtml(center.name)}
            </h3>
            
            ${badges ? `<div style="margin-bottom:8px;">${badges}</div>` : ''}
            
            <p style="margin:0 0 6px 0;font-size:13px;color:#4B5563;line-height:1.4;">
                📍 ${escapeHtml(center.address)}<br>
                ${center.district ? escapeHtml(center.district) + ', ' : ''}${center.city || 'Lima'}
            </p>
            
            ${center.phone ? `<p style="margin:0 0 6px 0;font-size:13px;color:#4B5563;">📞 ${escapeHtml(center.phone)}</p>` : ''}
            
            <p style="margin:0 0 6px 0;font-size:12px;color:#6B7280;">
                🏥 ${escapeHtml(services)}
            </p>
            
            ${ratingStars ? `<p style="margin:0 0 6px 0;">${ratingStars}</p>` : ''}
            
            ${distanceText ? `<p style="margin:0 0 12px 0;font-size:14px;color:#6366F1;font-weight:600;">📏 ${distanceText}</p>` : ''}
            
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                ${center.phone ? `
                    <a href="tel:${center.phone}" 
                       style="display:inline-flex;align-items:center;gap:4px;background:#3B82F6;color:white;padding:8px 12px;border-radius:6px;text-decoration:none;font-size:12px;font-weight:500;">
                        📞 Llamar
                    </a>
                ` : ''}
                <a href="https://www.google.com/maps/dir/?api=1&destination=${center.latitude},${center.longitude}" 
                   target="_blank"
                   style="display:inline-flex;align-items:center;gap:4px;background:#6B7280;color:white;padding:8px 12px;border-radius:6px;text-decoration:none;font-size:12px;font-weight:500;">
                    🚗 Cómo llegar
                </a>
            </div>
        </div>
    `;

    infoWindow.setContent(content);
    infoWindow.open(map, marker);
}

/**
 * Render centers list in sidebar
 */
function renderCentersList(centers) {
    const container = document.getElementById('centers-list');
    if (!container) return;

    container.innerHTML = '';

    if (!centers || !centers.length) {
        showNoCentersMessage('No se encontraron centros');
        return;
    }

    centers.forEach((center, index) => {
        const item = document.createElement('div');
        item.className = 'center-card';
        item.dataset.centerId = center.id;
        item.onclick = () => focusOnCenter(center, index);

        // Determine badge type
        let badgeHtml = '';
        if (center.has_mentta) {
            badgeHtml = '<span class="badge badge-mentta">⭐ Mentta</span>';
        } else if (center.emergency_24h) {
            badgeHtml = '<span class="badge badge-emergency">🚨 24h</span>';
        }

        const distanceText = center.distance !== null && center.distance !== undefined
            ? `${center.distance} km`
            : '';

        item.innerHTML = `
            <div class="flex items-start gap-3">
                <div class="center-number">${index + 1}</div>
                <div class="flex-1 min-w-0">
                    <h4 class="center-name">${escapeHtml(center.name)}</h4>
                    <p class="center-district">${escapeHtml(center.district || center.city || '')}</p>
                    ${distanceText ? `<p class="center-distance">📍 ${distanceText}</p>` : ''}
                    ${badgeHtml ? `<div class="mt-2">${badgeHtml}</div>` : ''}
                </div>
            </div>
        `;

        container.appendChild(item);
    });
}

/**
 * Focus on a center (from list click)
 */
function focusOnCenter(center, index) {
    if (!map) return;

    // Center map on the selected center
    map.setCenter({
        lat: parseFloat(center.latitude),
        lng: parseFloat(center.longitude)
    });
    map.setZoom(16);

    // Trigger marker click to show info window
    if (centerMarkers[index]) {
        google.maps.event.trigger(centerMarkers[index], 'click');
    }

    // Highlight the list item
    highlightListItem(center.id);

    // On mobile, collapse the panel
    if (window.innerWidth < 768 && panelExpanded) {
        togglePanel(false);
    }
}

/**
 * Highlight list item when marker is clicked
 */
function highlightListItem(centerId) {
    // Remove previous highlight
    document.querySelectorAll('.center-card.active').forEach(el => {
        el.classList.remove('active');
    });

    // Add highlight to selected
    const item = document.querySelector(`[data-center-id="${centerId}"]`);
    if (item) {
        item.classList.add('active');
        item.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

// ============================================
// FILTERS & SEARCH
// ============================================

/**
 * Apply filter and reload centers
 */
function applyFilter(filter) {
    console.log('🔧 Applying filter:', filter);
    currentFilter = filter;
    isSearching = false;

    // Clear search input
    const searchInput = document.getElementById('search-input');
    if (searchInput) searchInput.value = '';

    if (userLocation) {
        loadNearbyCenters(userLocation.lat, userLocation.lng);
    }
}

/**
 * Toggle search bar visibility
 */
function toggleSearch() {
    const searchBar = document.getElementById('search-bar');
    const searchInput = document.getElementById('search-input');

    if (searchBar) {
        searchBar.classList.toggle('hidden');

        if (!searchBar.classList.contains('hidden') && searchInput) {
            searchInput.focus();
        }
    }
}

/**
 * Search centers by text (debounced)
 */
let searchTimeout = null;
function searchCenters(query) {
    console.log('🔍 Search input:', query);

    const clearBtn = document.getElementById('clear-search-btn');
    if (clearBtn) {
        clearBtn.classList.toggle('hidden', !query);
    }

    clearTimeout(searchTimeout);

    if (query.length < 2) {
        // If query is too short, reload normal results
        if (isSearching && userLocation) {
            isSearching = false;
            loadNearbyCenters(userLocation.lat, userLocation.lng);
        }
        return;
    }

    searchTimeout = setTimeout(async () => {
        isSearching = true;
        const apiUrl = `${BASE_URL}/api/map/search-centers.php?q=${encodeURIComponent(query)}`;
        console.log('🔍 Searching centers:', apiUrl);

        try {
            const response = await fetch(apiUrl);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const text = await response.text();
            console.log('📄 Raw API Response:', text);

            // Try to parse JSON
            let data;
            try {
                data = JSON.parse(text);
            } catch (parseError) {
                console.error('JSON Parse error:', parseError);
                console.error('Response was:', text);
                showNoCentersMessage('Error en respuesta del servidor');
                return;
            }

            console.log('📊 Search results:', data);

            if (data.success) {
                const results = data.data.results || [];
                renderCentersOnMap(results);
                renderCentersList(results);
                updateCentersCount(data.data.count || results.length);
                console.log(`✅ Found ${results.length} results`);
            } else {
                console.error('Search API error:', data.error);
                showNoCentersMessage(data.error || 'Error en búsqueda');
            }
        } catch (error) {
            console.error('Search error:', error);
            showNoCentersMessage('Error al buscar');
        }
    }, 400); // 400ms debounce
}

/**
 * Clear search and reload location-based results
 */
function clearSearch() {
    const searchInput = document.getElementById('search-input');
    const clearBtn = document.getElementById('clear-search-btn');

    if (searchInput) searchInput.value = '';
    if (clearBtn) clearBtn.classList.add('hidden');

    isSearching = false;
    if (userLocation) {
        loadNearbyCenters(userLocation.lat, userLocation.lng);
    }
}

// ============================================
// UI HELPERS
// ============================================

/**
 * Update location text in panel (first line)
 */
function updateLocationText(text) {
    const el = document.getElementById('user-location-text');
    if (el) el.textContent = text;
}

/**
 * Update city text in panel (second line - formerly hardcoded)
 */
function updateCityText(text) {
    const el = document.getElementById('user-city-text');
    if (el) el.textContent = text;
}

/**
 * Update centers count display
 */
function updateCentersCount(count) {
    const el = document.getElementById('centers-count');
    if (el) el.textContent = count;
}

/**
 * Show "no centers" message
 */
function showNoCentersMessage(message) {
    const container = document.getElementById('centers-list');
    if (container) {
        container.innerHTML = `
            <div class="text-center py-8 text-gray-400">
                <div class="text-4xl mb-2">🏥</div>
                <p class="text-sm">${escapeHtml(message)}</p>
            </div>
        `;
    }
    updateCentersCount(0);
}

/**
 * Recenter map on user location
 */
function recenterMap() {
    if (userLocation && map) {
        map.setCenter(userLocation);
        map.setZoom(13);

        // Reset filter
        currentFilter = 'all';
        const filterRadio = document.querySelector('input[name="filter"][value="all"]');
        if (filterRadio) filterRadio.checked = true;

        // Also reload nearby centers
        isSearching = false;
        loadNearbyCenters(userLocation.lat, userLocation.lng);
    }
}

/**
 * Go back to chat
 */
function goBack() {
    window.location.href = 'chat.php';
}

/**
 * Escape HTML for safe rendering
 */
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ============================================
// MOBILE SWIPE PANEL
// ============================================

/**
 * Initialize swipe handler for mobile panel
 */
function initSwipeHandler() {
    const panel = document.getElementById('centers-panel');
    const handle = document.getElementById('swipe-handle');

    if (!panel || !handle || window.innerWidth >= 768) return;

    let startY = 0;
    let startHeight = 0;

    handle.addEventListener('touchstart', (e) => {
        startY = e.touches[0].clientY;
        startHeight = panel.offsetHeight;
        panel.style.transition = 'none';
    });

    handle.addEventListener('touchmove', (e) => {
        const deltaY = startY - e.touches[0].clientY;
        const newHeight = Math.min(Math.max(startHeight + deltaY, 150), window.innerHeight * 0.8);
        panel.style.maxHeight = newHeight + 'px';
    });

    handle.addEventListener('touchend', () => {
        panel.style.transition = 'max-height 0.3s ease';
        const currentHeight = panel.offsetHeight;
        const threshold = window.innerHeight * 0.5;

        if (currentHeight > threshold) {
            panel.style.maxHeight = '80vh';
            panelExpanded = true;
        } else {
            panel.style.maxHeight = '40vh';
            panelExpanded = false;
        }
    });

    // Click on handle to toggle
    handle.addEventListener('click', () => {
        togglePanel(!panelExpanded);
    });
}

/**
 * Toggle panel expanded/collapsed
 */
function togglePanel(expand) {
    const panel = document.getElementById('centers-panel');
    if (!panel) return;

    panel.style.transition = 'max-height 0.3s ease';

    if (expand) {
        panel.style.maxHeight = '80vh';
        panelExpanded = true;
    } else {
        panel.style.maxHeight = '40vh';
        panelExpanded = false;
    }
}

// ============================================
// INITIALIZATION CHECK
// ============================================

// If Google Maps fails to load, show error
window.gm_authFailure = function () {
    console.error('❌ Google Maps authentication failed');
    const mapElement = document.getElementById('map');
    if (mapElement) {
        mapElement.innerHTML = `
            <div style="display:flex;align-items:center;justify-content:center;height:100%;background:#F3F4F6;flex-direction:column;padding:2rem;text-align:center;">
                <div style="font-size:48px;margin-bottom:16px;">⚠️</div>
                <h2 style="font-size:18px;font-weight:600;color:#1F2937;margin-bottom:8px;">Error de autenticación</h2>
                <p style="color:#6B7280;max-width:300px;">La API Key de Google Maps no es válida o no tiene los permisos necesarios.</p>
            </div>
        `;
    }

    const loadingOverlay = document.getElementById('loading-overlay');
    if (loadingOverlay) loadingOverlay.style.display = 'none';
};

// Log when script is loaded
console.log('📦 map.js v0.5.4 loaded successfully');
