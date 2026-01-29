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

// Parámetros de paginación
$limit = isset($_GET['limit']) ? min(100, max(1, (int)$_GET['limit'])) : 50;
$offset = isset($_GET['offset']) ? max(0, (int)$_GET['offset']) : 0;

try {
    $db = getDB();
    
    // Obtener mensajes
    $stmt = $db->prepare("
        SELECT 
            id,
            message,
            sender,
            sentiment_score,
            created_at
        FROM conversations 
        WHERE patient_id = :patient_id 
        ORDER BY created_at ASC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':patient_id', $user['id'], PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear mensajes
    $formattedMessages = array_map(function($msg) {
        return [
            'id' => (int)$msg['id'],
            'message' => $msg['message'],
            'sender' => $msg['sender'],
            'sentiment' => $msg['sentiment_score'] ? json_decode($msg['sentiment_score'], true) : null,
            'created_at' => $msg['created_at'],
            'time_ago' => timeAgo($msg['created_at'])
        ];
    }, $messages);
    
    // Obtener total de mensajes
    $stmt = $db->prepare("SELECT COUNT(*) FROM conversations WHERE patient_id = ?");
    $stmt->execute([$user['id']]);
    $totalMessages = (int)$stmt->fetchColumn();
    
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
