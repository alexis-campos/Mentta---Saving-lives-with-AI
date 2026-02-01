<?php
/**
 * MENTTA - API: Obtener Historial de Chat
 * Retorna el historial de conversación del paciente
 */

require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');
setSecurityHeaders();

// Solo GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, null, 'Método no permitido', 405);
}

// Verificar autenticación
$user = checkAuth();
if (!$user) {
    jsonResponse(false, null, 'No autenticado', 401);
}

if ($user['role'] !== 'patient') {
    jsonResponse(false, null, 'Acceso no autorizado', 403);
}

// Parámetros de paginación y sesión
$limit = isset($_GET['limit']) ? min(100, max(1, (int) $_GET['limit'])) : 50;
$offset = isset($_GET['offset']) ? max(0, (int) $_GET['offset']) : 0;
$sessionId = isset($_GET['session_id']) ? trim($_GET['session_id']) : '';

// Validar formato de session_id
if (!empty($sessionId) && !preg_match('/^session_\d+_[a-z0-9]+$/', $sessionId)) {
    $sessionId = '';
}

try {
    $db = getDB();

    // Si no hay session_id, retornar vacío (nuevo chat)
    if (empty($sessionId)) {
        jsonResponse(true, [
            'messages' => [],
            'pagination' => [
                'limit' => $limit,
                'offset' => 0,
                'total' => 0,
                'has_more' => false
            ]
        ]);
    }

    // Obtener mensajes SOLO de la sesión específica
    $stmt = $db->prepare("
        SELECT 
            id,
            message,
            sender,
            sentiment_score,
            created_at
        FROM conversations 
        WHERE patient_id = :patient_id AND session_id = :session_id
        ORDER BY created_at ASC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':patient_id', $user['id'], PDO::PARAM_INT);
    $stmt->bindValue(':session_id', $sessionId, PDO::PARAM_STR);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formatear mensajes
    $formattedMessages = array_map(function ($msg) {
        return [
            'id' => (int) $msg['id'],
            'message' => $msg['message'],
            'sender' => $msg['sender'],
            'sentiment' => $msg['sentiment_score'] ? json_decode($msg['sentiment_score'], true) : null,
            'created_at' => $msg['created_at'],
            'time_ago' => timeAgo($msg['created_at'])
        ];
    }, $messages);

    // Obtener total de mensajes de esta sesión
    $stmt = $db->prepare("SELECT COUNT(*) FROM conversations WHERE patient_id = ? AND session_id = ?");
    $stmt->execute([$user['id'], $sessionId]);
    $totalMessages = (int) $stmt->fetchColumn();

    jsonResponse(true, [
        'messages' => $formattedMessages,
        'pagination' => [
            'limit' => $limit,
            'offset' => $offset,
            'total' => $totalMessages,
            'has_more' => ($offset + $limit) < $totalMessages
        ]
    ]);

} catch (Exception $e) {
    logError('Error en get-history.php', ['error' => $e->getMessage()]);
    jsonResponse(false, null, 'Error al cargar el historial');
}
