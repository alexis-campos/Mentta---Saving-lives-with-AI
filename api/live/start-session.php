<?php
/**
 * MENTTA - API: Iniciar Sesión Live
 * Crea una nueva sesión de videollamada y devuelve token + API key
 * 
 * Método: POST
 * Respuesta: { success: true, sessionToken: string, apiKey: string }
 */

require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Preflight CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Solo POST permitido
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

try {
    // Verificar autenticación
    $userId = getCurrentUserId();

    if (!$userId) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'No autenticado']);
        exit;
    }

    // Verificar que es paciente
    $db = getDB();
    $stmt = $db->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || $user['role'] !== 'patient') {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Solo pacientes pueden iniciar sesiones']);
        exit;
    }

    // Generar token único para esta sesión
    $sessionToken = bin2hex(random_bytes(32));

    // Crear registro en BD
    $stmt = $db->prepare("
        INSERT INTO live_sessions (patient_id, session_token, status) 
        VALUES (?, ?, 'active')
    ");
    $stmt->execute([$userId, $sessionToken]);
    $sessionId = $db->lastInsertId();

    // Obtener API key de Gemini (desde config)
    $apiKey = defined('AI_API_KEY') ? AI_API_KEY : getenv('AI_API_KEY');

    if (!$apiKey) {
        // Si no hay API key, lanzar error
        throw new Exception('API key de Gemini no configurada');
    }

    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'sessionId' => $sessionId,
        'sessionToken' => $sessionToken,
        'apiKey' => $apiKey, // En producción, usar ephemeral tokens
        'model' => 'gemini-2.5-flash-native-audio-preview-12-2025',
        'message' => 'Sesión iniciada correctamente'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error al iniciar sesión: ' . $e->getMessage()
    ]);
}
