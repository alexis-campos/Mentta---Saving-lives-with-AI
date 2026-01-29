<?php
/**
 * MENTTA - API: Detalle del Paciente
 * Retorna información completa, métricas y evolución emocional
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

// Obtener patient_id
$patientId = $_GET['patient_id'] ?? null;
if (!$patientId) {
    jsonResponse(false, null, 'ID de paciente requerido');
}

try {
    $db = getDB();
    
    // Verificar que el paciente está vinculado a este psicólogo
    $stmt = $db->prepare("
        SELECT 1 FROM patient_psychologist_link
        WHERE patient_id = :patient_id
        AND psychologist_id = :psychologist_id
        AND status = 'active'
    ");
    $stmt->execute([
        'patient_id' => $patientId,
        'psychologist_id' => $user['id']
    ]);
    
    if (!$stmt->fetch()) {
        jsonResponse(false, null, 'Paciente no autorizado', 403);
    }
    
    // 1. Datos del paciente
    $stmt = $db->prepare("
        SELECT 
            u.id, u.name, u.age, u.email, u.created_at,
            l.linked_at
        FROM users u
        JOIN patient_psychologist_link l ON u.id = l.patient_id
        WHERE u.id = :patient_id
    ");
    $stmt->execute(['patient_id' => $patientId]);
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$patient) {
        jsonResponse(false, null, 'Paciente no encontrado', 404);
    }
    
    // Calcular status
    $patient['status'] = calculatePatientStatus($patientId);
    $patient['linked_since'] = $patient['linked_at'];
    
    // 2. Historial emocional (últimos 30 días)
    $emotionHistory = getEmotionHistory($patientId);
    
    // 3. Métricas
    $metrics = getPatientMetrics($patientId);
    
    // 4. Alertas recientes
    $recentAlerts = getRecentAlerts($patientId);
    
    // 5. Temas principales
    $topTopics = getTopTopics($patientId);
    
    jsonResponse(true, [
        'patient' => $patient,
        'emotion_history' => $emotionHistory,
        'metrics' => $metrics,
        'recent_alerts' => $recentAlerts,
        'top_topics' => $topTopics
    ]);
    
} catch (Exception $e) {
    logError('Error en get-patient-detail.php', ['error' => $e->getMessage()]);
    jsonResponse(false, null, 'Error al obtener detalles del paciente');
}

/**
 * Calcular estado del paciente
 */
function calculatePatientStatus($patient_id) {
    $db = getDB();
    
    $stmt = $db->prepare("
        SELECT severity FROM alerts
        WHERE patient_id = :patient_id
        AND created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
        ORDER BY FIELD(severity, 'red', 'orange', 'yellow') 
        LIMIT 1
    ");
    $stmt->execute(['patient_id' => $patient_id]);
    $alert = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($alert && $alert['severity'] === 'red') return 'risk';
    if ($alert && $alert['severity'] === 'orange') return 'monitor';
    
    return 'stable';
}

/**
 * Historial emocional de 30 días
 */
function getEmotionHistory($patientId) {
    $db = getDB();
    
    $stmt = $db->prepare("
        SELECT 
            DATE(created_at) as date,
            AVG(CAST(JSON_UNQUOTE(JSON_EXTRACT(sentiment_score, '$.positive')) AS DECIMAL(3,2))) as positive,
            AVG(CAST(JSON_UNQUOTE(JSON_EXTRACT(sentiment_score, '$.negative')) AS DECIMAL(3,2))) as negative,
            AVG(CAST(JSON_UNQUOTE(JSON_EXTRACT(sentiment_score, '$.anxiety')) AS DECIMAL(3,2))) as anxiety,
            AVG(CAST(JSON_UNQUOTE(JSON_EXTRACT(sentiment_score, '$.sadness')) AS DECIMAL(3,2))) as sadness
        FROM conversations
        WHERE patient_id = :patient_id
        AND sender = 'user'
        AND sentiment_score IS NOT NULL
        AND created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date ASC
    ");
    $stmt->execute(['patient_id' => $patientId]);
    
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convertir a números
    foreach ($history as &$day) {
        $day['positive'] = (float)($day['positive'] ?? 0);
        $day['negative'] = (float)($day['negative'] ?? 0);
        $day['anxiety'] = (float)($day['anxiety'] ?? 0);
        $day['sadness'] = (float)($day['sadness'] ?? 0);
    }
    
    return $history;
}

/**
 * Métricas del paciente
 */
function getPatientMetrics($patientId) {
    $db = getDB();
    
    // Total de conversaciones
    $stmt = $db->prepare("
        SELECT COUNT(*) as total FROM conversations
        WHERE patient_id = :patient_id AND sender = 'user'
    ");
    $stmt->execute(['patient_id' => $patientId]);
    $totalConversations = $stmt->fetchColumn();
    
    // Promedio de mensajes por día
    $stmt = $db->prepare("
        SELECT COUNT(*) / GREATEST(DATEDIFF(NOW(), MIN(created_at)), 1) as avg_per_day
        FROM conversations
        WHERE patient_id = :patient_id AND sender = 'user'
    ");
    $stmt->execute(['patient_id' => $patientId]);
    $avgPerDay = $stmt->fetchColumn();
    
    // Última actividad
    $stmt = $db->prepare("
        SELECT MAX(created_at) as last_active
        FROM conversations
        WHERE patient_id = :patient_id
    ");
    $stmt->execute(['patient_id' => $patientId]);
    $lastActive = $stmt->fetchColumn();
    
    // Racha de días consecutivos
    $streak = calculateStreak($patientId);
    
    return [
        'total_conversations' => (int)$totalConversations,
        'avg_messages_per_day' => round((float)$avgPerDay, 1),
        'last_active' => $lastActive,
        'last_active_formatted' => $lastActive ? timeAgo($lastActive) : 'Nunca',
        'streak_days' => $streak
    ];
}

/**
 * Calcular racha de días consecutivos
 */
function calculateStreak($patientId) {
    $db = getDB();
    
    $stmt = $db->prepare("
        SELECT DISTINCT DATE(created_at) as activity_date
        FROM conversations
        WHERE patient_id = :patient_id
        ORDER BY activity_date DESC
        LIMIT 30
    ");
    $stmt->execute(['patient_id' => $patientId]);
    $dates = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($dates)) return 0;
    
    $streak = 0;
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    
    // Si no hubo actividad hoy ni ayer, racha es 0
    if ($dates[0] !== $today && $dates[0] !== $yesterday) {
        return 0;
    }
    
    $expectedDate = $dates[0];
    foreach ($dates as $date) {
        if ($date === $expectedDate) {
            $streak++;
            $expectedDate = date('Y-m-d', strtotime($expectedDate . ' -1 day'));
        } else {
            break;
        }
    }
    
    return $streak;
}

/**
 * Alertas recientes
 */
function getRecentAlerts($patientId) {
    $db = getDB();
    
    $stmt = $db->prepare("
        SELECT 
            id, severity, status, message_snapshot, created_at, acknowledged_at
        FROM alerts
        WHERE patient_id = :patient_id
        ORDER BY created_at DESC
        LIMIT 10
    ");
    $stmt->execute(['patient_id' => $patientId]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Temas principales (análisis de palabras frecuentes)
 */
function getTopTopics($patientId) {
    $db = getDB();
    
    // Obtener mensajes recientes
    $stmt = $db->prepare("
        SELECT message FROM conversations
        WHERE patient_id = :patient_id
        AND sender = 'user'
        AND created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
        LIMIT 100
    ");
    $stmt->execute(['patient_id' => $patientId]);
    $messages = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Stopwords en español
    $stopwords = ['de', 'la', 'que', 'el', 'en', 'y', 'a', 'los', 'se', 'del', 'las', 
                  'un', 'por', 'con', 'no', 'una', 'su', 'para', 'es', 'al', 'lo', 
                  'como', 'más', 'pero', 'sus', 'le', 'ya', 'o', 'este', 'sí', 'porque',
                  'esta', 'entre', 'cuando', 'muy', 'sin', 'sobre', 'también', 'me',
                  'hasta', 'hay', 'donde', 'quien', 'desde', 'todo', 'nos', 'mi', 'yo',
                  'he', 'eso', 'era', 'son', 'uno', 'bien', 'hoy', 'fue', 'está', 'estoy',
                  'tengo', 'siento', 'sentí', 'mucho', 'poco', 'algo', 'nada', 'puede',
                  'hacer', 'sido', 'tiene', 'voy', 'creo', 'día', 'días', 'vez', 'tan'];
    
    $wordCount = [];
    
    foreach ($messages as $message) {
        // Limpiar y dividir en palabras
        $words = preg_split('/\s+/', mb_strtolower(preg_replace('/[^\p{L}\s]/u', '', $message)));
        
        foreach ($words as $word) {
            if (mb_strlen($word) < 4) continue; // Ignorar palabras cortas
            if (in_array($word, $stopwords)) continue;
            
            if (!isset($wordCount[$word])) {
                $wordCount[$word] = 0;
            }
            $wordCount[$word]++;
        }
    }
    
    // Ordenar por frecuencia y tomar top 10
    arsort($wordCount);
    $topWords = array_slice($wordCount, 0, 10, true);
    
    $result = [];
    foreach ($topWords as $word => $count) {
        $result[] = [
            'word' => $word,
            'frequency' => $count
        ];
    }
    
    return $result;
}

