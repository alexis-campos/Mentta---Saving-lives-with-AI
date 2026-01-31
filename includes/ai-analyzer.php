<?php
/**
 * MENTTA - AI Analyzer (Robust Version)
 * Integra Circuit Breaker Pattern + Graceful Degradation
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/circuit-breaker.php';
require_once __DIR__ . '/ai-client.php';
require_once __DIR__ . '/sentiment-analyzer.php';
require_once __DIR__ . '/risk-detector.php';

/**
 * Función principal: Analizar mensaje con IA (Protegida por Circuit Breaker)
 * 
 * @param string $message Mensaje del usuario
 * @param int $patient_id ID del paciente
 * @param int $risk_level Nivel de riesgo preliminar (para priorizar timeout)
 * @param array $conversation_history Historial para contexto
 * @return array Respuesta estructurada {success, response, ...}
 */
function analyzeWithAI($message, $patient_id, $risk_level, $conversation_history = []) {
    $circuitBreaker = new CircuitBreaker();
    
    // 1. Verificar estado del circuito
    if (!$circuitBreaker->canAttempt()) {
        logError('Circuit Breaker: Abierto - Usando Modo Seguro', $circuitBreaker->getStatus());
        return useSafeMode($message, $patient_id, $risk_level);
    }
    
    try {
        // 2. Configurar timeout dinámico
        // Si el riesgo es alto, damos más tiempo (45s). Si es bajo, estándar (30s).
        $timeout = ($risk_level >= 4) ? AI_TIMEOUT_CRITICAL : AI_TIMEOUT;
        
        // 3. Preparar prompt
        $patient = dbFetchOne("SELECT name, age FROM users WHERE id = ?", [$patient_id]);
        
        // Análisis de sentimiento local para enriquecer prompt
        $sentiment = analyzeSentiment($message);
        
        // Construir prompt usando la función existente en ai-client.php
        // NOTA: buildAIPrompt devuelve string. Enviamos eso a Gemini.
        $prompt = buildAIPrompt(
            $message, 
            $patient, 
            $conversation_history, 
            getMemorySnapshot($patient_id), // Snapshot memoria
            $sentiment, 
            $risk_level
        );
        
        // 4. Llamada a la API
        $aiResponseText = callGeminiAPIRaw($prompt, $timeout);
        
        // LOG TEMPORAL: Ver qué demonios responde la IA
        logError('RAW AI DEBUG', ['response_text' => $aiResponseText, 'risk' => $risk_level]);

        // 🛡️ VALIDACIÓN ROBUSTA DE RESPUESTA
        $cleanResponse = trim($aiResponseText);
        $cleanName = trim($patient['name'] ?? '');
        
        // Validaciones de calidad de respuesta
        $isGarbage = false;
        $garbageReason = '';
        
        // 1. Respuesta muy corta (< 50 chars sin tags)
        $responseWithoutTags = preg_replace('/\[RISK_LEVEL:\s*\d+\]\s*\[PAP_PHASE:\s*[A-E]\]/', '', $cleanResponse);
        if (mb_strlen(trim($responseWithoutTags)) < 50) {
            $isGarbage = true;
            $garbageReason = 'Response too short: ' . mb_strlen(trim($responseWithoutTags)) . ' chars';
        }
        
        // 2. Repite el nombre del usuario (alucinación)
        if (!$isGarbage && mb_strlen($cleanName) > 2) {
            if (strip_tags($responseWithoutTags) === strip_tags($cleanName) ||
                levenshtein(trim($responseWithoutTags), $cleanName) < 5) {
                $isGarbage = true;
                $garbageReason = 'Name repetition detected';
            }
        }
        
        // 3. No contiene palabras reales (solo símbolos/espacios)
        if (!$isGarbage && !preg_match('/[a-záéíóúñü]{3,}/iu', $responseWithoutTags)) {
            $isGarbage = true;
            $garbageReason = 'No real words detected';
        }
        
        if ($isGarbage) {
            logError('AI Garbage Response Detected', [
                'reason' => $garbageReason,
                'raw' => mb_substr($cleanResponse, 0, 100)
            ]);
            throw new Exception("AI Invalid Response: $garbageReason");
        }
        
        // 5. Éxito
        $circuitBreaker->recordSuccess();
        
        return [
            'success' => true,
            'response' => $aiResponseText,
            'source' => 'ai'
        ];
        
    } catch (Exception $e) {
        // 6. Fallo
        $circuitBreaker->recordFailure($e->getMessage());
        
        logError('Fallo de IA en analyzeWithAI', [
            'error' => $e->getMessage(),
            'patient_id' => $patient_id
        ]);
        
        // Fallback a Modo Seguro
        return useSafeMode($message, $patient_id, $risk_level);
    }
}

/**
 * Modo Seguro (Graceful Degradation)
 * Respuesta determinística basada en reglas cuando la IA falla
 */
function useSafeMode($message, $patient_id, $risk_level) {
    // Análisis de sentimiento local (muy rápido, sin API)
    $sentiment = analyzeSentiment($message);
    $dominantEmotion = getDominantEmotion($sentiment);
    
    // Respuestas predefinidas por Nivel de Riesgo (Protocolo ABCDE simplificado)
    $responses = [
        0 => [ // Riesgo Nulo
            'sadness' => "Siento mucho que estés triste. A veces hablar ayuda a desahogarse. Estoy aquí para escucharte.",
            'anxiety' => "Noto que estás inquieto. Tomemos un momento. Respira conmigo... ¿Mejor?",
            'default' => "Te escucho. Cuéntame un poco más sobre eso que mencionas."
        ],
        1 => "Entiendo que estás pasando por un momento difícil. Es válido sentirse así.",
        2 => "Lo que sientes es importante. A veces las emociones son intensas, pero estoy aquí para acompañarte.",
        3 => "Me preocupa cómo te sientes. ¿Hay alguien de confianza cerca con quien puedas hablar? Recuerda que no tienes que pasar por esto solo.",
        4 => "⚠️ Tu seguridad es lo más importante. Por favor, considera llamar al 113 (opción 5) o al 106 ahora mismo. ¿Estás en un lugar seguro?",
        5 => "🆘 NECESITAS AYUDA INMEDIATA. Por favor, llama al 106 AHORA. He activado una alerta para que recibas apoyo. NO ESTÁS SOLO."
    ];
    
    // Seleccionar respuesta
    if ($risk_level == 0) {
        $responseText = $responses[0][$dominantEmotion] ?? $responses[0]['default'];
    } else {
        $responseText = $responses[$risk_level] ?? $responses[0]['default'];
    }
    
    // IMPORTANTE: Añadir tags simulados para que send-message.php no falle
    // El sistema espera [RISK_LEVEL] y [PAP_PHASE]
    $papPhase = ($risk_level >= 4) ? 'D' : 'A'; // D=Contención, A=Escucha
    
    $responseText .= "\n\n[RISK_LEVEL: {$risk_level}] [PAP_PHASE: {$papPhase}]";
    
    logError('🛑 MODO SEGURO ACTIVADO', [
        'patient_id' => $patient_id,
        'risk_level' => $risk_level,
        'emotion' => $dominantEmotion
    ]);
    
    return [
        'success' => true, // Para el frontend es un éxito (hay respuesta)
        'response' => $responseText,
        'source' => 'safe_mode',
        'safe_mode' => true
    ];
}

/**
 * Wrapper para Gemini API con Timeout y 1 Retry
 * Implementa retry con backoff exponencial para errores transitorios
 */
function callGeminiAPIRaw($prompt, $timeout) {
    $apiKey = AI_API_KEY;
    $url = AI_API_URL . AI_MODEL . ':generateContent?key=' . $apiKey;
    
    $data = [
        'contents' => [['parts' => [['text' => $prompt]]]],
        'generationConfig' => [
            'temperature' => 0.3,
            'maxOutputTokens' => 2048  // Aumentado para evitar truncamiento
        ],
        'safetySettings' => [
            ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_NONE'],
            ['category' => 'HARM_CATEGORY_HARASSMENT', 'threshold' => 'BLOCK_NONE'],
            ['category' => 'HARM_CATEGORY_HATE_SPEECH', 'threshold' => 'BLOCK_NONE'],
            ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_NONE']
        ]
    ];
    
    $maxRetries = 1; // 1 retry = 2 intentos total
    $retryableCodes = [429, 500, 502, 503, 504];
    
    for ($attempt = 0; $attempt <= $maxRetries; $attempt++) {
        if ($attempt > 0) {
            $delay = pow(2, $attempt) * 1000000; // 2^attempt segundos en microsegundos
            usleep($delay);
        }
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json']
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        // Si es error de conexión y podemos reintentar
        if ($curlError && $attempt < $maxRetries) {
            logError('Gemini API cURL error, retrying', ['error' => $curlError, 'attempt' => $attempt]);
            continue;
        }
        
        if ($curlError) throw new Exception("Error de Conexión (cURL): $curlError");
        
        // Si es código reintentable
        if (in_array($httpCode, $retryableCodes) && $attempt < $maxRetries) {
            logError('Gemini API retryable error, retrying', ['code' => $httpCode, 'attempt' => $attempt]);
            continue;
        }
        
        if ($httpCode !== 200) {
            $errorDetails = json_decode($response, true);
            $msg = $errorDetails['error']['message'] ?? 'Error desconocido';
            throw new Exception("API Error {$httpCode}: {$msg}");
        }
        
        $decoded = json_decode($response, true);
        if (!isset($decoded['candidates'][0]['content']['parts'][0]['text'])) {
            throw new Exception("Formato de respuesta IA inválido");
        }
        
        return $decoded['candidates'][0]['content']['parts'][0]['text'];
    }
    
    throw new Exception("Gemini API falló después de {$maxRetries} reintentos");
}

/**
 * Helper para obtener snapshot de memoria (simplificado)
 */
function getMemorySnapshot($patient_id) {
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT key_name, value, memory_type FROM patient_memory WHERE patient_id = ? ORDER BY last_mentioned_at DESC LIMIT 5");
        $stmt->execute([$patient_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // Si la columna memory_type no existe, usar query sin ella
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT key_name, value FROM patient_memory WHERE patient_id = ? ORDER BY last_mentioned_at DESC LIMIT 5");
        $stmt->execute([$patient_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
