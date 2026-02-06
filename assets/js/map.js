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

// Get base URL from current page location (trimmed and decoded)
const BASE_URL = decodeURIComponent(window.location.pathname.replace(/\/[^\/]*$/, '')).trim();

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
        { "featureType": "all", "elementType": "labels.text.fill", "stylers": [{ "color": "#7c7c7c" }] },
        { "featureType": "all", "elementType": "labels.text.stroke", "stylers": [{ "visibility": "on" }, { "color": "#F9F9F7" }, { "weight": 2 }] },
        { "featureType": "all", "elementType": "labels.icon", "stylers": [{ "visibility": "off" }] },
        { "featureType": "landscape", "elementType": "geometry", "stylers": [{ "color": "#F9F9F7" }] },
        { "featureType": "poi", "elementType": "geometry", "stylers": [{ "visibility": "off" }] },
        { "featureType": "road", "elementType": "geometry.fill", "stylers": [{ "color": "#FFFFFF" }] },
        { "featureType": "road", "elementType": "geometry.stroke", "stylers": [{ "visibility": "off" }] },
        { "featureType": "road.highway", "elementType": "geometry.fill", "stylers": [{ "color": "#EFEFE9" }] },
        { "featureType": "transit", "elementType": "geometry", "stylers": [{ "visibility": "off" }] },
        { "featureType": "water", "elementType": "geometry", "stylers": [{ "color": "#E3E3DE" }] }
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

    // Create user marker (Google Maps Blue for easy identification)
    userMarker = new google.maps.Marker({
        position: location,
        map: map,
        title: "Tu ubicación",
        icon: {
            path: google.maps.SymbolPath.CIRCLE,
            scale: 12,
            fillColor: "#4285F4",
            fillOpacity: 1,
            strokeColor: "#FFFFFF",
            strokeWeight: 4
        },
        zIndex: 1000
    });

    // Add pulsing effect (blue)
    userCircle = new google.maps.Circle({
        strokeColor: "#4285F4",
        strokeOpacity: 0.4,
        strokeWeight: 2,
        fillColor: "#4285F4",
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

        // Determine marker style based on brand palette
        let pinColor = "#AF8A6B"; // Default: Sophisticated Sand
        let pinScale = 10;

        if (center.has_mentta) {
            pinColor = "#111111"; // Elite Black
            pinScale = 12;
        } else if (center.emergency_24h) {
            pinColor = "#C8553D"; // Soft Terracotta
        }

        const marker = new google.maps.Marker({
            position: position,
            map: map,
            title: center.name,
            icon: {
                path: google.maps.SymbolPath.CIRCLE,
                scale: pinScale,
                fillColor: pinColor,
                fillOpacity: 1,
                strokeColor: "#FFFFFF",
                strokeWeight: 4
            },
            optimized: false // Required for some animations
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

    // Build badges with brand colors
    let badges = '';
    if (center.has_mentta) {
        badges += '<span style="display:inline-block;background:#2d3a2d;color:#cbaa8e;padding:4px 10px;border-radius:20px;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:0.05em;margin-right:6px;">✨ Sistema Mentta</span>';
    }
    if (center.emergency_24h) {
        badges += '<span style="display:inline-block;background:#fef3c7;color:#92400E;padding:4px 10px;border-radius:20px;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:0.05em;">🚨 Emergencias 24h</span>';
    }

    // Build rating stars
    let ratingStars = '';
    if (center.rating > 0) {
        const fullStars = Math.floor(center.rating);
        const hasHalfStar = center.rating % 1 >= 0.5;
        let stars = '';
        for (let i = 0; i < 5; i++) {
            if (i < fullStars) stars += '★';
            else if (i === fullStars && hasHalfStar) stars += '½';
            else stars += '☆';
        }
        ratingStars = `<span style="color:#cbaa8e;font-size:14px;letter-spacing:2px;">${stars}</span> <span style="font-size:11px;font-weight:700;color:#8b9d8b;margin-left:4px;">${center.rating}</span>`;
    }

    const content = `
        <div style="font-family:'Inter',sans-serif; padding:12px; max-width:300px;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px;">
                <h3 style="font-family:'Playfair Display',serif; font-weight:700; font-size:20px; margin:0; color:#111; line-height:1.2; flex:1;">
                    ${escapeHtml(center.name)}
                </h3>
            </div>
            
            <div style="display:flex; gap:6px; flex-wrap:wrap; margin-bottom:16px;">
                ${center.has_mentta ? `<span style="background:#111; color:white; padding:4px 10px; border-radius:20px; font-size:9px; font-weight:800; text-transform:uppercase; letter-spacing:0.1em;">✨ Mentta Elite</span>` : ''}
                ${center.emergency_24h ? `<span style="background:#C8553D; color:white; padding:4px 10px; border-radius:20px; font-size:9px; font-weight:800; text-transform:uppercase; letter-spacing:0.1em;">🚨 Urgencias 24h</span>` : ''}
            </div>
            
            <div style="margin-bottom:20px; display:flex; flex-direction:column; gap:10px;">
                <div style="display:flex; gap:10px; font-size:13px; color:#555; line-height:1.5;">
                    <span style="color:#AF8A6B; font-size:16px;">❂</span>
                    <span>${escapeHtml(center.address)}, ${center.district || center.city || 'Lima'}</span>
                </div>
                
                ${center.phone ? `
                <div style="display:flex; gap:10px; font-size:13px; color:#555;">
                    <span style="color:#AF8A6B; font-size:16px;">✆</span>
                    <span>${escapeHtml(center.phone)}</span>
                </div>` : ''}
            </div>
            
            <div style="display:flex; gap:10px;">
                ${center.phone ? `
                    <a href="tel:${center.phone}" 
                       style="flex:1; display:flex; align-items:center; justify-content:center; background:#111; color:white; padding:14px; border-radius:16px; text-decoration:none; font-size:10px; font-weight:800; text-transform:uppercase; letter-spacing:0.1em; transition:all 0.3s ease;">
                        Llamar
                    </a>
                ` : ''}
                <a href="https://www.google.com/maps/dir/?api=1&destination=${center.latitude},${center.longitude}" 
                   target="_blank"
                   style="flex:1; display:flex; align-items:center; justify-content:center; background:#F9F9F7; color:#111; padding:14px; border-radius:16px; text-decoration:none; font-size:10px; font-weight:800; text-transform:uppercase; letter-spacing:0.1em; border:1px solid rgba(0,0,0,0.05);">
                    Ruta
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
        item.className = 'center-card group';
        item.dataset.centerId = center.id;
        item.onclick = () => focusOnCenter(center, index);

        // Custom indicator color
        let indicatorColor = "#AF8A6B"; // Lux Sand
        if (center.emergency_24h) indicatorColor = "#C8553D"; // Deep Terracotta
        if (center.has_mentta) indicatorColor = "#111"; // Elite Black

        const distanceText = center.distance !== null && center.distance !== undefined
            ? `${center.distance} km`
            : '';

        item.innerHTML = `
            <div class="flex items-center gap-5">
                <div class="w-10 h-10 rounded-2xl flex items-center justify-center text-[10px] font-black transition-all duration-500 group-hover:scale-110" 
                     style="background: ${indicatorColor}; color: white; box-shadow: 0 10px 20px -5px ${indicatorColor}44;">
                    ${String(index + 1).padStart(2, '0')}
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="font-serif italic text-[15px] text-[#111] font-bold truncate">${escapeHtml(center.name)}</h4>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span class="text-[9px] font-bold text-black/30 uppercase tracking-widest">${escapeHtml(center.district || center.city || '')}</span>
                        ${distanceText ? `
                            <div class="w-1 h-1 rounded-full bg-black/10"></div>
                            <span class="text-[9px] font-black text-[#AF8A6B] uppercase tracking-widest">${distanceText}</span>
                        ` : ''}
                    </div>
                </div>
                <div class="w-8 h-8 rounded-full bg-black/[0.02] flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all">
                    <svg class="w-3 h-3 text-[#111]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
                    </svg>
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

    // Center map with smooth zoom
    map.panTo({
        lat: parseFloat(center.latitude),
        lng: parseFloat(center.longitude)
    });

    // Gradual zoom for better transition
    let currentZoom = map.getZoom();
    let targetZoom = 16;
    if (currentZoom < targetZoom) {
        let zoomInterval = setInterval(() => {
            currentZoom++;
            map.setZoom(currentZoom);
            if (currentZoom >= targetZoom) clearInterval(zoomInterval);
        }, 100);
    } else {
        map.setZoom(targetZoom);
    }

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
 * Toggle mobile search overlay visibility
 */
function toggleMobileSearch() {
    const overlay = document.getElementById('mobile-search-overlay');
    if (overlay) {
        overlay.classList.toggle('hidden');
        overlay.classList.toggle('flex');

        if (!overlay.classList.contains('hidden')) {
            const mobileSearchInput = document.getElementById('mobile-search-input');
            if (mobileSearchInput) {
                setTimeout(() => mobileSearchInput.focus(), 100);
            }
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
                throw new Error(`HTTP error! status: ${response.status} `);
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
        < div class="text-center py-8 text-gray-400" >
                <div class="text-4xl mb-2">🏥</div>
                <p class="text-sm">${escapeHtml(message)}</p>
            </div >
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
    const recenterBtn = document.getElementById('recenter-btn');
    if (!panel) return;

    if (expand) {
        panel.classList.remove('collapsed');
        panel.classList.add('expanded');
        panelExpanded = true;
        // Adjust recenter button position
        if (recenterBtn && window.innerWidth < 768) {
            recenterBtn.style.bottom = 'calc(75vh + 16px)';
        }
    } else {
        panel.classList.remove('expanded');
        panel.classList.add('collapsed');
        panelExpanded = false;
        // Reset recenter button position
        if (recenterBtn && window.innerWidth < 768) {
            recenterBtn.style.bottom = 'calc(140px + 16px)';
        }
    }
}

/**
 * ============================================
 * PROFESSIONAL 3-STATE BOTTOM SHEET
 * States: peek (140px), half (45vh), full (85vh)
 * ============================================
 */
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

// Initialize on mobile only
if (window.innerWidth < 768) {
    document.addEventListener('DOMContentLoaded', () => BottomSheet.init());
}

/**
 * Toggle panel height - Global function for compatibility
 * Cycles through: peek -> half -> full -> peek
 */
function togglePanelHeight() {
    BottomSheet.cycleState();
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
        < div style = "display:flex;align-items:center;justify-content:center;height:100%;background:#F3F4F6;flex-direction:column;padding:2rem;text-align:center;" >
                <div style="font-size:48px;margin-bottom:16px;">⚠️</div>
                <h2 style="font-size:18px;font-weight:600;color:#1F2937;margin-bottom:8px;">Error de autenticación</h2>
                <p style="color:#6B7280;max-width:300px;">La API Key de Google Maps no es válida o no tiene los permisos necesarios.</p>
            </div >
        `;
    }

    const loadingOverlay = document.getElementById('loading-overlay');
    if (loadingOverlay) loadingOverlay.style.display = 'none';
};

// Log when script is loaded
console.log('📦 map.js v0.5.4 loaded successfully');
