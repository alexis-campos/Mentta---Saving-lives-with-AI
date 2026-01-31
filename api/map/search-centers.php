<?php
/**
 * MENTTA - API: Search Mental Health Centers by Text
 * Searches by name, district, address, or services
 * 
 * @param string q - Search query (min 2 chars)
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
    
    // Get search query
    $query = trim($_GET['q'] ?? '');
    
    if (strlen($query) < 2) {
        echo json_encode(['success' => false, 'error' => 'Búsqueda muy corta (mínimo 2 caracteres)']);
        exit;
    }
    
    // Sanitize query for LIKE - remove special chars
    $query = preg_replace('/[%_]/', '', $query);
    $searchTerm = '%' . $query . '%';
    
    $db = getDB();
    
    // Use positional parameters (?) instead of named parameters
    // because PDO with MySQL doesn't allow reusing the same named parameter
    $sql = "
        SELECT 
            id, name, address, district, city,
            latitude, longitude, phone, email, website,
            services, has_mentta, emergency_24h, rating
        FROM mental_health_centers
        WHERE verified = TRUE
        AND (
            name LIKE ?
            OR district LIKE ?
            OR address LIKE ?
            OR city LIKE ?
            OR services LIKE ?
        )
        ORDER BY 
            CASE WHEN has_mentta = TRUE THEN 0 ELSE 1 END,
            CASE WHEN emergency_24h = TRUE THEN 0 ELSE 1 END,
            name ASC
        LIMIT 20
    ";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([
        $searchTerm, 
        $searchTerm, 
        $searchTerm, 
        $searchTerm, 
        $searchTerm
    ]);
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format results
    foreach ($results as &$center) {
        $center['services_array'] = !empty($center['services']) 
            ? array_map('trim', explode(',', $center['services'])) 
            : [];
        $center['has_mentta'] = (bool)$center['has_mentta'];
        $center['emergency_24h'] = (bool)$center['emergency_24h'];
        $center['rating'] = (float)$center['rating'];
        $center['latitude'] = (float)$center['latitude'];
        $center['longitude'] = (float)$center['longitude'];
        
        // For search results, distance is not calculated
        $center['distance'] = null;
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'results' => $results,
            'count' => count($results),
            'query' => $query
        ]
    ]);
    
} catch (Exception $e) {
    // Log error if function exists
    if (function_exists('logError')) {
        logError('Error en search-centers', ['error' => $e->getMessage()]);
    }
    echo json_encode(['success' => false, 'error' => 'Error en búsqueda: ' . $e->getMessage()]);
}
