<?php
/**
 * MENTTA - API: Enviar Mensaje
 * Endpoint principal del chat que procesa mensajes del paciente
 */

require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';
require_once '../../includes/ai-client.php';
require_once '../../includes/sentiment-analyzer.php';
require_once '../../includes/risk-detector.php';
require_once '../../includes/memory-parser.php';
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
    
    // 4. Analizar sentimiento
    $sentiment = analyzeSentiment($message);
    
    // 5. Detectar riesgo
    $riskLevel = detectRiskLevel($message);
    
    // 6. Guardar mensaje del usuario
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
    
    // 7. Si hay riesgo alto/crítico, crear alerta silenciosa
    $alertTriggered = false;
    if (shouldTriggerAlert($riskLevel)) {
        createRiskAlert($user['id'], $message, $riskLevel);
        $alertTriggered = true;
    }
    
    // 8. Extraer y guardar memoria contextual
    extractAndSaveMemory($message, $user['id']);
    
    // 9. Enviar a IA y obtener respuesta (con Safe Life Mode si hay riesgo)
    $aiResponse = sendToAI($message, $user['id'], $sentiment, $riskLevel);
    
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
    jsonResponse(true, [
        'message' => $aiMessage,
        'sentiment' => $sentiment,
        'risk_level' => $riskLevel,
        'message_id' => $userMessageId,
        // NOTE: alert_triggered NO se envía al paciente visiblemente
        // Solo se usa internamente para logging
    ]);
    
} catch (Exception $e) {
    logError('Error en send-message.php', [
        'error' => $e->getMessage(),
        'user_id' => $user['id']
    ]);
    jsonResponse(false, null, 'Error al procesar el mensaje. Por favor, intenta de nuevo.');
}
