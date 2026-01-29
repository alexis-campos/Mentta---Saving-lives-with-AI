<?php
/**
 * MENTTA - API: Enviar Mensaje (v0.3.1 - AI Powered)
 * Endpoint principal del chat con análisis completo por IA
 */

require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';
require_once '../../includes/ai-client.php';
require_once '../../includes/ai-analyzer.php';  // Nuevo analizador unificado
require_once '../../includes/alert-system.php';

header('Content-Type: application/json; charset=utf-8');
setSecurityHeaders();

// Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, null, 'Método no permitido', 405);
}

// 1. Verificar autenticación
$user = checkAuth();
if (!$user) {
    jsonResponse(false, null, 'No autenticado', 401);
}

if ($user['role'] !== 'patient') {
    jsonResponse(false, null, 'Solo pacientes pueden usar el chat', 403);
}

// 2. Validar input
$message = trim($_POST['message'] ?? '');

if (empty($message)) {
    jsonResponse(false, null, 'El mensaje no puede estar vacío');
}

if (mb_strlen($message) > CHAT_MAX_MESSAGE_LENGTH) {
    jsonResponse(false, null, 'El mensaje es demasiado largo (máximo ' . CHAT_MAX_MESSAGE_LENGTH . ' caracteres)');
}

// 3. Rate limiting
if (!checkRateLimit($user['id'], 'send_message', RATE_LIMIT_MESSAGES, RATE_LIMIT_WINDOW)) {
    jsonResponse(false, null, 'Has enviado demasiados mensajes. Por favor, espera un momento.');
}

try {
    $db = getDB();
    
    // 4. Obtener historial reciente para contexto
    $conversationHistory = dbFetchAll(
        "SELECT message, sender, created_at 
         FROM conversations 
         WHERE patient_id = ? 
         ORDER BY created_at DESC 
         LIMIT ?",
        [$user['id'], CHAT_HISTORY_LIMIT]
    );
    
    // 5. 🤖 ANÁLISIS COMPLETO CON IA
    // La IA analiza: riesgo contextual, sentimiento profundo, extracción de memoria
    $analysis = analyzeMessageWithAI($message, $user['id'], $conversationHistory);
    
    // Extraer datos del análisis
    $riskLevel = $analysis['risk_assessment']['level'] ?? 'none';
    $triggerAlert = $analysis['risk_assessment']['trigger_alert'] ?? false;
    $sentiment = $analysis['sentiment'] ?? [];
    $safeLifeMode = $analysis['safe_life_mode']['activate'] ?? false;
    
    // 6. Guardar mensaje del usuario con análisis de IA
    $stmt = $db->prepare("
        INSERT INTO conversations (patient_id, message, sender, sentiment_score, risk_level, created_at)
        VALUES (:patient_id, :message, 'user', :sentiment, :risk_level, NOW())
    ");
    $stmt->execute([
        'patient_id' => $user['id'],
        'message' => $message,
        'sentiment' => json_encode($sentiment),
        'risk_level' => $riskLevel
    ]);
    
    $userMessageId = $db->lastInsertId();
    
    // 7. Si la IA detecta riesgo real, crear alerta silenciosa
    $alertTriggered = false;
    if ($triggerAlert && $analysis['risk_assessment']['is_real_risk']) {
        require_once '../../includes/risk-detector.php';
        createRiskAlert($user['id'], $message, $riskLevel);
        $alertTriggered = true;
        
        logError('🚨 Alerta disparada por análisis de IA', [
            'patient_id' => $user['id'],
            'risk_level' => $riskLevel,
            'reasoning' => $analysis['risk_assessment']['reasoning'] ?? 'No especificado'
        ]);
    }
    
    // 8. Procesar y guardar memoria extraída por IA
    if (!empty($analysis['memory_extraction'])) {
        processExtractedMemory($user['id'], $analysis['memory_extraction']);
    }
    
    // 9. Enviar a IA para respuesta (con Safe Life Mode si aplica)
    $aiResponse = sendToAI($message, $user['id'], $sentiment, $safeLifeMode ? 'high' : $riskLevel);
    
    if (!$aiResponse['success']) {
        // Si falla la IA, dar respuesta fallback
        $fallbackResponse = "Entiendo que quieres hablar. Estoy aquí para escucharte. ¿Podrías contarme un poco más sobre lo que sientes?";
        $aiMessage = $fallbackResponse;
        logError('Fallback AI response usado', ['error' => $aiResponse['error']]);
    } else {
        $aiMessage = $aiResponse['response'];
    }
    
    // 10. Guardar respuesta de IA
    $stmt = $db->prepare("
        INSERT INTO conversations (patient_id, message, sender, created_at)
        VALUES (:patient_id, :message, 'ai', NOW())
    ");
    $stmt->execute([
        'patient_id' => $user['id'],
        'message' => $aiMessage
    ]);
    
    // 11. Retornar respuesta exitosa
    // Nota: Incluimos más datos del análisis (sin exponer que hubo alerta)
    jsonResponse(true, [
        'message' => $aiMessage,
        'sentiment' => [
            'positive' => $sentiment['positive'] ?? 0,
            'negative' => $sentiment['negative'] ?? 0,
            'anxiety' => $sentiment['anxiety'] ?? 0,
            'sadness' => $sentiment['sadness'] ?? 0,
            'anger' => $sentiment['anger'] ?? 0,
            'dominant' => $sentiment['dominant_emotion'] ?? 'neutral'
        ],
        'emotional_state' => $analysis['emotional_state']['current_mood'] ?? null,
        'message_id' => $userMessageId,
        // Datos para UI (no sensibles)
        'topics' => $analysis['memory_extraction']['topics'] ?? []
    ]);
    
} catch (Exception $e) {
    logError('Error en send-message.php', [
        'error' => $e->getMessage(),
        'user_id' => $user['id']
    ]);
    jsonResponse(false, null, 'Error al procesar el mensaje. Por favor, intenta de nuevo.');
}
