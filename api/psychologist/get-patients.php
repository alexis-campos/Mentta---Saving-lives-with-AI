<?php
/**
 * MENTTA - API: Obtener Pacientes del Psicólogo
 * Retorna lista de pacientes vinculados con su estado actual
 */

// Suppress HTML error output for API
ini_set('display_errors', 0);
error_reporting(0);

require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');
setSecurityHeaders();

// Verificar autenticación
$user = checkAuth();
if (!$user || $user['role'] !== 'psychologist') {
    jsonResponse(false, null, 'No autorizado', 401);
}


try {
    $db = getDB();
    
    // Obtener pacientes vinculados
    $stmt = $db->prepare("
        SELECT 
            u.id, 
            u.name, 
            u.age, 
            u.email,
            l.linked_at,
            (SELECT COUNT(*) FROM alerts WHERE patient_id = u.id AND status = 'pending') as unread_alerts,
            (SELECT MAX(created_at) FROM conversations WHERE patient_id = u.id) as last_activity
        FROM users u
        JOIN patient_psychologist_link l ON u.id = l.patient_id
        WHERE l.psychologist_id = :psychologist_id
        AND l.status = 'active'
        ORDER BY last_activity DESC
    ");
    
    $stmt->execute(['psychologist_id' => $user['id']]);
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Para cada paciente, calcular status
    foreach ($patients as &$patient) {
        $patient['status'] = calculatePatientStatus($patient['id']);
        $patient['last_activity_formatted'] = timeAgo($patient['last_activity']);
        $patient['age'] = (int)$patient['age'];
        $patient['unread_alerts'] = (int)$patient['unread_alerts'];
    }
    
    jsonResponse(true, $patients);
    
} catch (Exception $e) {
    logError('Error en get-patients.php', ['error' => $e->getMessage()]);
    jsonResponse(false, null, 'Error al obtener pacientes');
}

/**
 * Calcula el status de un paciente (stable/monitor/risk)
 */
function calculatePatientStatus($patient_id) {
    $db = getDB();
    
    // 1. Revisar alertas recientes (últimos 7 días)
    $stmt = $db->prepare("
        SELECT severity FROM alerts
        WHERE patient_id = :patient_id
        AND created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
        ORDER BY severity DESC
        LIMIT 1
    ");
    $stmt->execute(['patient_id' => $patient_id]);
    $alert = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($alert && $alert['severity'] === 'red') {
        return 'risk';
    }
    
    if ($alert && $alert['severity'] === 'orange') {
        return 'monitor';
    }
    
    // 2. Revisar sentimiento promedio últimos 3 días
    $stmt = $db->prepare("
        SELECT 
            AVG(CAST(JSON_UNQUOTE(JSON_EXTRACT(sentiment_score, '$.negative')) AS DECIMAL(3,2))) as avg_negative,
            AVG(CAST(JSON_UNQUOTE(JSON_EXTRACT(sentiment_score, '$.anxiety')) AS DECIMAL(3,2))) as avg_anxiety
        FROM conversations
        WHERE patient_id = :patient_id
        AND sender = 'user'
        AND sentiment_score IS NOT NULL
        AND created_at > DATE_SUB(NOW(), INTERVAL 3 DAY)
    ");
    $stmt->execute(['patient_id' => $patient_id]);
    $sentiment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($sentiment['avg_negative'] > 0.7 || $sentiment['avg_anxiety'] > 0.8) {
        return 'monitor';
    }
    
    return 'stable';
}

