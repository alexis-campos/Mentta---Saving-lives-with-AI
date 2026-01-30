<?php
/**
 * MENTTA - API: Load Session
 * Loads messages for a specific chat session
 */

require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');
setSecurityHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, null, 'Método no permitido', 405);
}

$user = checkAuth();
if (!$user || $user['role'] !== 'patient') {
    jsonResponse(false, null, 'No autorizado', 401);
}

$sessionId = $_POST['session_id'] ?? '';

if (empty($sessionId)) {
    jsonResponse(false, null, 'ID de sesión requerido');
}

try {
    $db = getDB();
    
    // Check if session_id is a date or an actual session ID
    $isDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', $sessionId);
    
    if ($isDate) {
        // Load by date (for old conversations without session_id)
        $stmt = $db->prepare("
            SELECT id, message, sender, sentiment_score, risk_level, created_at
            FROM conversations 
            WHERE patient_id = ? 
              AND session_id IS NULL
              AND DATE(created_at) = ?
            ORDER BY created_at ASC
        ");
        $stmt->execute([$user['id'], $sessionId]);
    } else {
        // Load by session_id
        $stmt = $db->prepare("
            SELECT id, message, sender, sentiment_score, risk_level, created_at
            FROM conversations 
            WHERE patient_id = ? AND session_id = ?
            ORDER BY created_at ASC
        ");
        $stmt->execute([$user['id'], $sessionId]);
    }
    
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format for frontend
    $formatted = array_map(function($m) {
        return [
            'id' => intval($m['id']),
            'message' => $m['message'],
            'sender' => $m['sender'],
            'sentiment' => $m['sentiment_score'] ? json_decode($m['sentiment_score'], true) : null,
            'created_at' => $m['created_at']
        ];
    }, $messages);
    
    jsonResponse(true, ['messages' => $formatted]);
    
} catch (Exception $e) {
    logError('Error en load-session.php', ['error' => $e->getMessage()]);
    jsonResponse(false, null, 'Error al cargar conversación');
}
