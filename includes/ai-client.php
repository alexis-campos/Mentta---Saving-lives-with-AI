<?php
/**
 * MENTTA - Cliente de IA (Google Gemini)
 * Maneja la comunicación con la API de Gemini para respuestas conversacionales
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

/**
 * Envía mensaje a Gemini y recibe respuesta contextualizada
 * 
 * @param string $message - Mensaje del usuario
 * @param int $patient_id - ID del paciente
 * @param array $currentSentiment - Análisis de sentimiento actual
 * @return array ['success' => bool, 'response' => string, 'error' => string|null]
 */
function sendToAI($message, $patient_id, $currentSentiment = []) {
    try {
        // 1. Obtener datos del paciente
        $patient = dbFetchOne(
            "SELECT name, age, language FROM users WHERE id = ?", 
            [$patient_id]
        );
        
        if (!$patient) {
            throw new Exception('Paciente no encontrado');
        }
        
        // 2. Recuperar últimos mensajes del paciente
        $conversationHistory = getConversationHistory($patient_id, CHAT_HISTORY_LIMIT);
        
        // 3. Recuperar memoria contextual del paciente
        $memoryItems = getPatientMemoryForContext($patient_id);
        
        // 4. Construir prompt completo
        $prompt = buildAIPrompt(
            $message, 
            $patient, 
            $conversationHistory, 
            $memoryItems, 
            $currentSentiment
        );
        
        // 5. Hacer petición a API de Gemini
        $response = callGeminiAPI($prompt);
        
        return [
            'success' => true,
            'response' => $response,
            'error' => null
        ];
        
    } catch (Exception $e) {
        logError('Error en sendToAI', [
            'error' => $e->getMessage(),
            'patient_id' => $patient_id
        ]);
        
        return [
            'success' => false,
            'response' => null,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Construye el prompt completo para la IA
 */
function buildAIPrompt($message, $patient, $conversationHistory, $memoryItems, $sentimentData) {
    $patientName = $patient['name'] ?? 'Usuario';
    $patientAge = $patient['age'] ?? 'No especificada';
    
    // Formatear memoria contextual
    $memoryText = formatMemoryForPrompt($memoryItems);
    
    // Formatear historial de conversación
    $historyText = formatHistoryForPrompt($conversationHistory);
    
    // Formatear sentimiento
    $sentimentText = formatSentimentForPrompt($sentimentData);
    
    $prompt = <<<PROMPT
Eres "Mentta", un asistente de apoyo emocional empático y comprensivo. Tu rol es ser como un amigo sabio que escucha sin juzgar.

═══════════════════════════════════════
INFORMACIÓN DEL USUARIO
═══════════════════════════════════════
Nombre: {$patientName}
Edad: {$patientAge}

═══════════════════════════════════════
MEMORIA CONTEXTUAL (cosas que recuerdas de conversaciones anteriores)
═══════════════════════════════════════
{$memoryText}

═══════════════════════════════════════
HISTORIAL RECIENTE DE LA CONVERSACIÓN
═══════════════════════════════════════
{$historyText}

═══════════════════════════════════════
ESTADO EMOCIONAL DETECTADO EN EL MENSAJE ACTUAL
═══════════════════════════════════════
{$sentimentText}

═══════════════════════════════════════
INSTRUCCIONES CRÍTICAS PARA TU RESPUESTA
═══════════════════════════════════════
1. Usa el nombre del usuario ({$patientName}) de forma natural cuando sea apropiado
2. Referencia eventos y personas que ya te han contado (mira la memoria contextual)
3. Sé cálido, empático, como un amigo que escucha sin juzgar
4. NUNCA diagnostiques condiciones médicas ("parece que tienes depresión")
5. NUNCA recomiendes medicamentos específicos
6. Valida emociones: "Es completamente válido sentirse así"
7. Ofrece herramientas de bienestar cuando sea apropiado (respiración, grounding)
8. Si detectas señales de crisis, mantén la conversación activa de forma cálida
9. Pregunta cómo se encuentra, si está en un lugar seguro, ofrece apoyo genuino
10. Responde en máximo 3-4 oraciones para mantener conversación fluida
11. Responde SIEMPRE en español a menos que el usuario escriba en otro idioma

TONO: Empático, esperanzador, no clínico, como un amigo sabio y comprensivo.

═══════════════════════════════════════
MENSAJE DEL USUARIO
═══════════════════════════════════════
{$message}

Tu respuesta (recuerda: máximo 3-4 oraciones, cálida y empática):
PROMPT;

    return $prompt;
}

/**
 * Hace request HTTP a API de Gemini
 */
function callGeminiAPI($prompt, $maxTokens = 300) {
    $apiKey = AI_API_KEY;
    $model = AI_MODEL;
    $url = AI_API_URL . $model . ':generateContent?key=' . $apiKey;
    
    if ($apiKey === 'TU_API_KEY_AQUI') {
        // Modo de desarrollo - respuesta simulada
        return getDevModeResponse($prompt);
    }
    
    $data = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $prompt]
                ]
            ]
        ],
        'generationConfig' => [
            'temperature' => 0.7,
            'topK' => 40,
            'topP' => 0.95,
            'maxOutputTokens' => $maxTokens,
        ],
        'safetySettings' => [
            [
                'category' => 'HARM_CATEGORY_HARASSMENT',
                'threshold' => 'BLOCK_NONE'
            ],
            [
                'category' => 'HARM_CATEGORY_HATE_SPEECH',
                'threshold' => 'BLOCK_NONE'
            ],
            [
                'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                'threshold' => 'BLOCK_NONE'
            ],
            [
                'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                'threshold' => 'BLOCK_NONE'
            ]
        ]
    ];
    
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json'
        ],
        CURLOPT_TIMEOUT => AI_TIMEOUT,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    if ($error) {
        logError('cURL error en Gemini API', ['error' => $error]);
        throw new Exception('Error de conexión con el servicio de IA');
    }
    
    if ($httpCode !== 200) {
        logError('Gemini API HTTP error', [
            'http_code' => $httpCode,
            'response' => $response
        ]);
        throw new Exception('Error en el servicio de IA (código: ' . $httpCode . ')');
    }
    
    $result = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        logError('Error parseando respuesta de Gemini', ['response' => $response]);
        throw new Exception('Error procesando respuesta de IA');
    }
    
    // Extraer texto de respuesta de Gemini
    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        return trim($result['candidates'][0]['content']['parts'][0]['text']);
    }
    
    // Verificar si fue bloqueado por seguridad
    if (isset($result['candidates'][0]['finishReason']) && 
        $result['candidates'][0]['finishReason'] === 'SAFETY') {
        logError('Respuesta bloqueada por seguridad de Gemini', ['result' => $result]);
        return "Entiendo que estás pasando por un momento difícil. Estoy aquí para escucharte. ¿Te gustaría contarme más sobre cómo te sientes?";
    }
    
    logError('Estructura de respuesta inesperada de Gemini', ['result' => $result]);
    throw new Exception('Respuesta inesperada de IA');
}

/**
 * Respuesta simulada para modo desarrollo (sin API key)
 */
function getDevModeResponse($prompt) {
    $responses = [
        "Gracias por compartir eso conmigo. Me importa mucho cómo te sientes. ¿Hay algo específico que te gustaría hablar hoy?",
        "Entiendo que esto puede ser difícil. Recuerda que está bien sentirse así a veces. ¿Cómo puedo ayudarte ahora?",
        "Estoy aquí para escucharte sin juzgar. Tu bienestar es importante. ¿Te gustaría contarme más sobre lo que está pasando?",
        "Aprecio que confíes en mí para hablar de esto. Cada paso que das cuenta. ¿Hay algo que te haría sentir mejor en este momento?",
        "Es completamente válido sentirse así. A veces simplemente necesitamos alguien que nos escuche. Cuéntame, ¿qué tienes en mente?"
    ];
    
    return $responses[array_rand($responses)];
}

/**
 * Obtiene historial de conversación reciente
 */
function getConversationHistory($patient_id, $limit = 10) {
    return dbFetchAll(
        "SELECT message, sender, created_at 
         FROM conversations 
         WHERE patient_id = ? 
         ORDER BY created_at DESC 
         LIMIT ?",
        [$patient_id, $limit]
    );
}

/**
 * Obtiene memoria del paciente para contexto
 */
function getPatientMemoryForContext($patient_id, $limit = 20) {
    return dbFetchAll(
        "SELECT memory_type, key_name, value, context, importance
         FROM patient_memory 
         WHERE patient_id = ? 
         ORDER BY importance DESC, last_mentioned_at DESC 
         LIMIT ?",
        [$patient_id, $limit]
    );
}

/**
 * Formatea memoria para incluir en prompt
 */
function formatMemoryForPrompt($memoryItems) {
    if (empty($memoryItems)) {
        return "No hay memorias previas registradas aún.";
    }
    
    $formatted = [];
    
    $typeLabels = [
        'name' => 'Persona conocida',
        'relationship' => 'Relación',
        'event' => 'Evento importante',
        'preference' => 'Preferencia',
        'emotion' => 'Estado emocional',
        'location' => 'Lugar'
    ];
    
    foreach ($memoryItems as $memory) {
        $type = $typeLabels[$memory['memory_type']] ?? $memory['memory_type'];
        $formatted[] = "- {$type}: {$memory['key_name']} → {$memory['value']}";
    }
    
    return implode("\n", $formatted);
}

/**
 * Formatea historial para incluir en prompt
 */
function formatHistoryForPrompt($history) {
    if (empty($history)) {
        return "Esta es la primera conversación con el usuario.";
    }
    
    // Invertir para orden cronológico
    $history = array_reverse($history);
    
    $formatted = [];
    foreach ($history as $msg) {
        $sender = $msg['sender'] === 'user' ? 'Usuario' : 'Mentta';
        $formatted[] = "{$sender}: {$msg['message']}";
    }
    
    return implode("\n", $formatted);
}

/**
 * Formatea sentimiento para incluir en prompt
 */
function formatSentimentForPrompt($sentiment) {
    if (empty($sentiment)) {
        return "No hay análisis de sentimiento disponible.";
    }
    
    $lines = [];
    
    if (isset($sentiment['positive'])) {
        $lines[] = "Positividad: " . round($sentiment['positive'] * 100) . "%";
    }
    if (isset($sentiment['negative'])) {
        $lines[] = "Negatividad: " . round($sentiment['negative'] * 100) . "%";
    }
    if (isset($sentiment['anxiety'])) {
        $lines[] = "Ansiedad: " . round($sentiment['anxiety'] * 100) . "%";
    }
    if (isset($sentiment['sadness'])) {
        $lines[] = "Tristeza: " . round($sentiment['sadness'] * 100) . "%";
    }
    if (isset($sentiment['anger'])) {
        $lines[] = "Enojo: " . round($sentiment['anger'] * 100) . "%";
    }
    
    return empty($lines) ? "Neutral" : implode("\n", $lines);
}
