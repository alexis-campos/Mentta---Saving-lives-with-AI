<?php
/**
 * MENTTA - API: Historial de Sentimientos
 * Retorna datos de sentimiento para gráficos
 */

require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');
setSecurityHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, null, 'Método no permitido', 405);
}

$user = checkAuth();
if (!$user) {
    jsonResponse(false, null, 'No autenticado', 401);
}

$days = isset($_GET['days']) ? min(90, max(1, (int)$_GET['days'])) : 7;

try {
    $patientId = $user['id'];
    
    // Si es psicólogo, puede ver datos de sus pacientes
    if ($user['role'] === 'psychologist' && isset($_GET['patient_id'])) {
        $patientId = (int)$_GET['patient_id'];
        
        // Verificar que el paciente está vinculado
        $link = dbFetchOne(
            "SELECT id FROM patient_psychologist_link 
             WHERE psychologist_id = ? AND patient_id = ? AND status = 'active'",
            [$user['id'], $patientId]
        );
        
        if (!$link) {
            jsonResponse(false, null, 'No tienes acceso a este paciente', 403);
        }
    }
    
    // Obtener promedios diarios de sentimiento
    $data = dbFetchAll(
        "SELECT 
            DATE(created_at) as date,
            COUNT(*) as message_count,
            AVG(JSON_EXTRACT(sentiment_score, '$.positive')) as avg_positive,
            AVG(JSON_EXTRACT(sentiment_score, '$.negative')) as avg_negative,
            AVG(JSON_EXTRACT(sentiment_score, '$.anxiety')) as avg_anxiety,
            AVG(JSON_EXTRACT(sentiment_score, '$.sadness')) as avg_sadness,
            AVG(JSON_EXTRACT(sentiment_score, '$.anger')) as avg_anger
         FROM conversations 
         WHERE patient_id = ? 
         AND sender = 'user' 
         AND sentiment_score IS NOT NULL 
         AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
         GROUP BY DATE(created_at)
         ORDER BY date ASC",
        [$patientId, $days]
    );
    
    // Formatear datos
    $formatted = array_map(function($row) {
        return [
            'date' => $row['date'],
            'message_count' => (int)$row['message_count'],
            'positive' => round((float)$row['avg_positive'], 2),
            'negative' => round((float)$row['avg_negative'], 2),
            'anxiety' => round((float)$row['avg_anxiety'], 2),
            'sadness' => round((float)$row['avg_sadness'], 2),
            'anger' => round((float)$row['avg_anger'], 2)
        ];
    }, $data);
    
    jsonResponse(true, [
        'patient_id' => $patientId,
        'days' => $days,
        'data' => $formatted
    ]);
    
} catch (Exception $e) {
    logError('Error en get-sentiment-history.php', ['error' => $e->getMessage()]);
    jsonResponse(false, null, 'Error al cargar historial de sentimientos');
}
