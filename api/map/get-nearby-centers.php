<?php
/**
 * MENTTA - API: Get Nearby Mental Health Centers
 * Uses Haversine formula to find centers within a radius
 * 
 * @param float lat - User latitude
 * @param float lng - User longitude
 * @param int radius - Search radius in km (default 50)
 * @param string filter - 'all', 'mentta', or 'emergency'
 */

require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Suppress PHP errors from showing in output
error_reporting(0);
ini_set('display_errors', 0);

try {
    // Verify authentication
    $user = checkAuth();
    if (!$user || $user['role'] !== 'patient') {
        echo json_encode(['success' => false, 'error' => 'No autorizado']);
        exit;
    }
    
    // Get parameters
    $lat = floatval($_GET['lat'] ?? 0);
    $lng = floatval($_GET['lng'] ?? 0);
    $radius = min(intval($_GET['radius'] ?? 50), 100); // Max 100km
    $filter = $_GET['filter'] ?? 'all';
    
    // Validate coordinates
    if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
        echo json_encode(['success' => false, 'error' => 'Coordenadas inválidas']);
        exit;
    }
    
    // Default to Lima if no coordinates provided
    if ($lat == 0 && $lng == 0) {
        $lat = -12.0464;
        $lng = -77.0428;
    }
    
    $db = getDB();
    
    // Build query with Haversine formula for distance calculation
    // Using positional parameters (?) for compatibility
    // 6371 = Earth's radius in kilometers
    $sql = "
        SELECT 
            id, name, address, district, city,
            latitude, longitude, 
            phone, email, website, 
            services, accepts_insurance, insurance_providers,
            has_mentta, emergency_24h, schedule, rating,
            (
                6371 * acos(
                    LEAST(1.0, GREATEST(-1.0,
                        cos(radians(?)) 
                        * cos(radians(latitude)) 
                        * cos(radians(longitude) - radians(?)) 
                        + sin(radians(?)) 
                        * sin(radians(latitude))
                    ))
                )
            ) AS distance
        FROM mental_health_centers
        WHERE verified = TRUE
    ";
    
    // Build parameters array
    $params = [$lat, $lng, $lat];
    
    // Apply filters
    if ($filter === 'mentta') {
        $sql .= " AND has_mentta = TRUE";
    } elseif ($filter === 'emergency') {
        $sql .= " AND emergency_24h = TRUE";
    }
    
    $sql .= "
        HAVING distance <= ?
        ORDER BY 
            CASE WHEN emergency_24h = TRUE THEN 0 ELSE 1 END,
            CASE WHEN has_mentta = TRUE THEN 0 ELSE 1 END,
            distance ASC
        LIMIT 20
    ";
    
    $params[] = $radius;
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    
    $centers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format data for frontend
    foreach ($centers as &$center) {
        // Round distance to 1 decimal
        $center['distance'] = round((float)$center['distance'], 1);
        
        // Parse services into array
        $center['services_array'] = !empty($center['services']) 
            ? array_map('trim', explode(',', $center['services'])) 
            : [];
        
        // Parse schedule JSON
        if (!empty($center['schedule'])) {
            $center['schedule'] = json_decode($center['schedule'], true);
        }
        
        // Convert to proper types for JSON
        $center['has_mentta'] = (bool)$center['has_mentta'];
        $center['emergency_24h'] = (bool)$center['emergency_24h'];
        $center['accepts_insurance'] = (bool)($center['accepts_insurance'] ?? false);
        $center['verified'] = true;
        $center['latitude'] = (float)$center['latitude'];
        $center['longitude'] = (float)$center['longitude'];
        $center['rating'] = (float)$center['rating'];
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'centers' => $centers,
            'count' => count($centers),
            'user_location' => ['lat' => $lat, 'lng' => $lng],
            'filter_applied' => $filter,
            'radius_km' => $radius
        ]
    ]);
    
} catch (Exception $e) {
    // Log error if function exists
    if (function_exists('logError')) {
        logError('Error en get-nearby-centers', ['error' => $e->getMessage()]);
    }
    echo json_encode(['success' => false, 'error' => 'Error al buscar centros: ' . $e->getMessage()]);
}
