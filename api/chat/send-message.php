<?php
/**
 * MENTTA - API: Enviar Mensaje (v0.3.1 - AI Powered)
 * Endpoint principal del chat con arquitectura Circuit Breaker
 */

require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';
require_once '../../includes/ai-client.php';
require_once '../../includes/ai-analyzer.php';
require_once '../../includes/risk-detector.php';
require_once '../../includes/circuit-breaker.php';

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
$sessionId = trim($_POST['session_id'] ?? '');

if (empty($message)) {
    jsonResponse(false, null, 'El mensaje no puede estar vacío');
}

if (mb_strlen($message) > CHAT_MAX_MESSAGE_LENGTH) {
    jsonResponse(false, null, 'El mensaje es demasiado largo');
}

// Validar session_id
if (!empty($sessionId) && !preg_match('/^session_\d+_[a-z0-9]+$/', $sessionId)) {
    $sessionId = ''; 
}

// 3. Rate limiting
if (!checkRateLimit($user['id'], 'send_message', RATE_LIMIT_MESSAGES, RATE_LIMIT_WINDOW)) {
    jsonResponse(false, null, 'Has enviado demasiados mensajes. Por favor, espera un momento.');
}

try {
    $db = getDB();
    
    // 4. Obtener historial reciente SOLO DE LA SESIÓN ACTUAL (evita contaminación)
    $historyQuery = "SELECT message, sender, created_at FROM conversations 
         WHERE patient_id = ?";
    $historyParams = [$user['id']];
    
    if (!empty($sessionId)) {
        $historyQuery .= " AND session_id = ?";
        $historyParams[] = $sessionId;
    }
    $historyQuery .= " ORDER BY created_at DESC LIMIT 6"; // 6 mensajes de sesión actual
    
    $conversationHistory = dbFetchAll($historyQuery, $historyParams);
    
    // 5. Análisis PAP Híbrido (Keyword + Contexto) - Siempre se ejecuta
    $papRiskAnalysis = analyzeRiskLevel($message, $user['id']);
    $suggestedRiskLevel = $papRiskAnalysis['suggested_level']; // 0-5
    $messageHasRiskKeywords = $papRiskAnalysis['risk_score'] > 0; // CLAVE: ¿El mensaje actual tiene keywords?
    
    // 6. Usar IA con Circuit Breaker y Modo Seguro
    $aiResult = analyzeWithAI(
        $message, 
        $user['id'], 
        $suggestedRiskLevel, 
        $conversationHistory
    );
    
    if (!$aiResult['success']) {
        throw new Exception('Error crítico en AI Analysis');
    }
    
    // Extraer respuesta
    $aiMessage = $aiResult['response'];
    $source = $aiResult['source']; // 'ai' o 'safe_mode'
    
    // Variables para parsing
    $finalRiskLevel = 0;
    $papPhase = null;
    $panicButton = null;
    
    // 7. Parsear Tags (Vienen tanto de IA como de Modo Seguro)
    if (preg_match('/\[RISK_LEVEL:\s*(\d+)\]/', $aiMessage, $matches)) {
        $finalRiskLevel = intval($matches[1]);
        $aiMessage = preg_replace('/\[RISK_LEVEL:\s*\d+\]/', '', $aiMessage);
    } else {
        $finalRiskLevel = $suggestedRiskLevel; // Fallback al análisis local
    }
    
    if (preg_match('/\[PAP_PHASE:\s*([A-E])\]/', $aiMessage, $matches)) {
        $papPhase = $matches[1];
        $aiMessage = preg_replace('/\[PAP_PHASE:\s*[A-E]\]/', '', $aiMessage);
    } else {
        $papPhase = 'A'; // Default
    }
    
    $aiMessage = trim($aiMessage);
    
    // 8. Escalamiento con VALIDACIÓN MULTI-CAPA (evita falsos positivos)
    // Capa 1: ¿El mensaje actual tiene keywords de riesgo?
    // Capa 2: ¿La IA confirmó nivel 4-5?
    // Solo escala si AMBAS condiciones se cumplen
    $shouldEscalate = false;
    
    if ($finalRiskLevel >= 4) {
        if ($messageHasRiskKeywords) {
            // El mensaje actual SÍ tiene keywords de crisis → Escalar
            $shouldEscalate = true;
        } else {
            // El mensaje actual NO tiene keywords → Posible falso positivo
            // Solo escalar si IA dijo nivel 5 (inmediato)
            if ($finalRiskLevel >= 5) {
                $shouldEscalate = true;
            } else {
                logError('ESCALAMIENTO BLOQUEADO (Posible Falso Positivo)', [
                    'message' => mb_substr($message, 0, 50),
                    'ai_level' => $finalRiskLevel,
                    'keyword_score' => $papRiskAnalysis['risk_score']
                ]);
            }
        }
    }
    
    if ($shouldEscalate) {
        require_once __DIR__ . '/../crisis/escalate.php';
        $escalationResult = escalateCrisis($user['id'], $finalRiskLevel, $message);
        
        if ($escalationResult['success']) {
            $panicButton = $escalationResult['panic_button'];
            logError('ALERTA DE CRISIS ACTIVADA', [
                 'level' => $finalRiskLevel,
                 'source' => $source
            ]);
        }
    }
    
    // 9. Guardar mensaje de usuario
    $stmt = $db->prepare("
        INSERT INTO conversations (patient_id, session_id, message, sender, risk_level, created_at)
        VALUES (:patient_id, :session_id, :message, 'user', :risk_level, NOW())
    ");
    $stmt->execute([
        'patient_id' => $user['id'],
        'session_id' => !empty($sessionId) ? $sessionId : null,
        'message' => $message,
        'risk_level' => $suggestedRiskLevel
    ]);
    
    $userMessageId = $db->lastInsertId();
    
    // 10. Guardar respuesta de Sistema (IA o Safe Mode)
    $stmt = $db->prepare("
        INSERT INTO conversations (patient_id, session_id, message, sender, final_risk_level, pap_phase, created_at)
        VALUES (:patient_id, :session_id, :message, 'ai', :final_risk_level, :pap_phase, NOW())
    ");
    $stmt->execute([
        'patient_id' => $user['id'],
        'session_id' => !empty($sessionId) ? $sessionId : null,
        'message' => $aiMessage,
        'final_risk_level' => $finalRiskLevel,
        'pap_phase' => $papPhase
    ]);
    
    // 11. Preparar respuesta JSON
    $responseData = [
        'message' => $aiMessage,
        'message_id' => $userMessageId,
        'final_risk_level' => $finalRiskLevel,
        'pap_phase' => $papPhase,
        'response_source' => $source // Para debugging (ai vs safe_mode)
    ];
    
    if ($panicButton) {
        $responseData['panic_button'] = $panicButton;
    }
    
    jsonResponse(true, $responseData);
    
} catch (Exception $e) {
    // CAPTURA FINAL DE ERRORES: Evita HTML leaks
    logError('CRITICAL ERROR en Chat', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    
    // Respuesta de emergencia extrema (si incluso el Circuit Breaker falló)
    jsonResponse(true, [
        'message' => "Lo siento, estoy teniendo dificultades técnicas momentáneas. Por favor, si necesitas ayuda urgente, llama al 113.",
        'final_risk_level' => 0,
        'pap_phase' => 'A'
    ]);
}
