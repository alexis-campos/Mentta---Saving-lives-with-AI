<?php
/**
 * MENTTA - API: Get Chat List
 * Returns list of chat sessions with metadata
 */

require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');
setSecurityHeaders();

$user = checkAuth();
if (!$user || $user['role'] !== 'patient') {
    jsonResponse(false, null, 'No autorizado', 401);
}

try {
    $db = getDB();
    
    // Get sessions with aggregated data
    // Group by session_id, or by date if no session_id
    $stmt = $db->prepare("
        SELECT 
            COALESCE(session_id, DATE(created_at)) as session_id,
            MIN(created_at) as first_message,
            MAX(created_at) as last_message,
            COUNT(*) as message_count,
            AVG(JSON_EXTRACT(sentiment_score, '$.positive')) as avg_mood,
            SUBSTRING_INDEX(GROUP_CONCAT(
                CASE WHEN sender = 'user' THEN message END 
                ORDER BY created_at ASC SEPARATOR '|||'
            ), '|||', 1) as first_user_message
        FROM conversations 
        WHERE patient_id = ?
        GROUP BY COALESCE(session_id, DATE(created_at))
        ORDER BY MAX(created_at) DESC
        LIMIT 10
    ");
    $stmt->execute([$user['id']]);
    $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format for frontend
    $formatted = array_map(function($s) {
        // Generate title from first message
        $title = 'Sin título';
        if (!empty($s['first_user_message'])) {
            $title = mb_substr($s['first_user_message'], 0, 40);
            if (mb_strlen($s['first_user_message']) > 40) {
                $title .= '...';
            }
        }
        
        return [
            'session_id' => $s['session_id'],
            'title' => $title,
            'date' => $s['last_message'],
            'message_count' => intval($s['message_count']),
            'mood' => $s['avg_mood'] !== null ? floatval($s['avg_mood']) : 0.5
        ];
    }, $sessions);
    
    jsonResponse(true, ['sessions' => $formatted]);
    
} catch (Exception $e) {
    logError('Error en get-chat-list.php', ['error' => $e->getMessage()]);
    jsonResponse(false, null, 'Error al obtener historial');
}
