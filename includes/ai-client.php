<?php
/**
 * MENTTA - Cliente de IA (Google Gemini)
 * Maneja la comunicación con la API de Gemini para respuestas conversacionales
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/risk-detector.php';

// ============================================================
// NOTA: La función sendToAI() fue removida (código zombie).
// El flujo actual usa analyzeWithAI() en ai-analyzer.php
// ============================================================

/**
 * Construye prompt con personalidad "AMIGO ADAPTATIVO"
 * Adapta tono según contexto: celebra, empodera, escucha, aconseja
 * Automatically detects language from user message and responds in same language
 */
function buildAIPrompt($message, $patient, $conversationHistory, $memoryItems, $sentimentData, $riskData = 'none') {
    $patientName = $patient['name'] ?? 'Usuario';
    $patientAge = $patient['age'] ?? '';
    
    // Formatear datos contextuales
    $historyText = formatHistoryForPrompt($conversationHistory);
    $memoryText = formatMemoryForPrompt($memoryItems);
    $sentimentText = formatSentimentForPrompt($sentimentData);
    
    // Procesar riesgo
    $risk_level = is_array($riskData) ? $riskData['suggested_level'] : riskLevelToInt($riskData);
    $risk_score = is_array($riskData) ? $riskData['risk_score'] : 0;
    $keywords = is_array($riskData) && !empty($riskData['keywords_found']) 
        ? implode(', ', $riskData['keywords_found']) : 'none detected';
    
    // === BILINGUAL PROMPT - AUTO LANGUAGE DETECTION ===
    $prompt = <<<PROMPT
ROLE: You are Mentta, a close FRIEND who knows about mental health. You are NOT a therapist, you do NOT feel pity.
You are like that wise friend who listens, advises, celebrates achievements, and knows when to say "this is serious, seek professional help."

🌐 CRITICAL LANGUAGE RULE:
- Detect the language of the user's message below
- If the user writes in ENGLISH → respond ENTIRELY in English
- If the user writes in SPANISH → respond ENTIRELY in Spanish
- NEVER mix languages in a single response
- This rule applies to EVERY response, no exceptions

ADAPTIVE PERSONALITY (adjust based on context):
- If user shares ACHIEVEMENT → Celebrate genuinely ("That's great! How did you do it?" / "¡Genial! ¿Cómo lo lograste?")
- If user is DISCOURAGED → Empower ("I know you can do this. What do you need?" / "Sé que puedes con esto. ¿Qué necesitas?")
- If user wants to VENT → Listen without judgment, ask open questions
- If user is CONFUSED → Orient, help them think through options
- If user in MILD CRISIS → Active listening, advice based on PAP protocol
- If user in SEVERE CRISIS (level 4-5) → Take seriously, recommend professional help

HOW A FRIEND SPEAKS (vs how NOT to speak):
❌ "I understand your pain" (pity)     → ✅ "What exactly happened?"
❌ "It's valid to feel that way" (validate everything) → ✅ "Sounds tough. What options do you see?"
❌ "Everything will be fine" (empty promise) → ✅ "I'm here with you in this"
❌ Always agree → ✅ "Have you thought that maybe...?"

PERU RESOURCES (only when appropriate):
- Line 113 (option 5): Mental health, 24/7
- SAMU 106: Emergencies
- Map of help centers in the app

RULES:
1. DETECT language from user's message and respond in THAT SAME language
2. Maximum 4-5 sentences, be concise but warm
3. For level 4-5: "This worries me. I think you should talk to a professional. Do you know about line 113?"
4. DO NOT diagnose, DO NOT prescribe medications
5. DO NOT repeat the user's name at the beginning

FORMAT (MANDATORY):
[RISK_LEVEL: X] [PAP_PHASE: Y]
Your response here...

LEVELS: 0=calm, 1=mild, 2=moderate, 3=high, 4=critical, 5=imminent
PAP PHASES: A=Listen, B=Regulation, C=Needs, D=Networks, E=Psychoeducation

CURRENT CONTEXT:
- User: {$patientName} ({$patientAge} years old)
- Backend analysis: level {$risk_level}/5, score {$risk_score}/100
- Keywords detected: {$keywords}
- Detected emotion: {$sentimentText}
- Memory: {$memoryText}

RECENT HISTORY:
{$historyText}

USER MESSAGE (detect language from this):
"{$message}"

YOUR RESPONSE AS A FRIEND (in the SAME language as the user's message above):
PROMPT;
    
    return $prompt;
}

/**
 * Sugiere recursos de psicoeducación según mensaje y nivel de riesgo
 * 
 * @param string $message - Mensaje del usuario
 * @param int $risk_level - Nivel de riesgo sugerido (0-5)
 * @return array - Lista de recursos sugeridos
 */
function getSuggestedResources($message, $risk_level) {
    // Si nivel es crítico, no sugerir ejercicios (prioridad es seguridad)
    if ($risk_level >= 4) {
        return [];
    }
    
    $message_lower = mb_strtolower($message, 'UTF-8');
    $resources = [];
    
    // Keywords para sugerir recursos de ansiedad
    if (mb_strpos($message_lower, 'ansi') !== false || 
        mb_strpos($message_lower, 'nervios') !== false ||
        mb_strpos($message_lower, 'pánico') !== false ||
        mb_strpos($message_lower, 'panico') !== false) {
        $resources[] = [
            'title' => 'Técnica 5-4-3-2-1 para ansiedad',
            'description' => 'Nombra 5 cosas que ves, 4 que tocas, 3 que escuchas, 2 que hueles, 1 que saboreas'
        ];
        $resources[] = [
            'title' => 'Respiración 4-7-8',
            'description' => 'Inhala 4 seg, mantén 7 seg, exhala 8 seg. Repite 4 veces'
        ];
    }
    
    // Keywords para problemas de sueño
    if (mb_strpos($message_lower, 'dormir') !== false || 
        mb_strpos($message_lower, 'insomnio') !== false ||
        mb_strpos($message_lower, 'no puedo dormir') !== false) {
        $resources[] = [
            'title' => 'Higiene del sueño',
            'description' => 'Evita pantallas 1h antes, ambiente oscuro, temperatura fresca'
        ];
    }
    
    // Keywords para soledad
    if (mb_strpos($message_lower, 'solo') !== false || 
        mb_strpos($message_lower, 'soledad') !== false ||
        mb_strpos($message_lower, 'nadie me entiende') !== false) {
        $resources[] = [
            'title' => 'Conexión social gradual',
            'description' => 'Empieza con un mensaje a un amigo, no necesitas ver a nadie si no quieres'
        ];
    }
    
    // Keywords para estrés
    if (mb_strpos($message_lower, 'estrés') !== false || 
        mb_strpos($message_lower, 'estres') !== false ||
        mb_strpos($message_lower, 'abrumado') !== false ||
        mb_strpos($message_lower, 'presión') !== false) {
        $resources[] = [
            'title' => 'Técnica de la pausa de 60 segundos',
            'description' => 'Para, cierra los ojos, respira 3 veces, pregúntate qué necesitas AHORA MISMO'
        ];
    }
    
    // Keywords para tristeza/depresión
    if (mb_strpos($message_lower, 'triste') !== false || 
        mb_strpos($message_lower, 'deprimid') !== false ||
        mb_strpos($message_lower, 'vacío') !== false) {
        $resources[] = [
            'title' => 'Activación conductual mínima',
            'description' => 'Haz UNA cosa pequeña: levántate, toma agua, abre la ventana. No tienes que hacer más'
        ];
    }
    
    return $resources;
}

/**
 * Hace request HTTP a API de Gemini con reintentos exponenciales
 * 
 * Implementa Exponential Backoff para manejar errores transitorios (503, 429, etc.)
 * Crítico para apps de salud mental donde el silencio de la IA no es aceptable.
 * 
 * @param string $prompt - Prompt a enviar
 * @param int $maxTokens - Máximo de tokens en respuesta
 * @param int $maxRetries - Número máximo de reintentos (default 3)
 * @return string - Respuesta de la IA
 * @throws Exception - Si todos los reintentos fallan
 */
function callGeminiAPI($prompt, $maxTokens = 4000, $maxRetries = 3) {
    $apiKey = AI_API_KEY;
    $model = AI_MODEL;
    $url = AI_API_URL . $model . ':generateContent';
    
    if ($apiKey === 'TU_API_KEY_AQUI' || $apiKey === 'YOUR_API_KEY_HERE') {
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
    
    $lastError = null;
    $lastHttpCode = 0;
    
    // Códigos HTTP que ameritan reintento
    $retryableCodes = [429, 500, 502, 503, 504];
    
    for ($attempt = 0; $attempt <= $maxRetries; $attempt++) {
        // Espera exponencial antes de reintentar (excepto primer intento)
        if ($attempt > 0) {
            $waitSeconds = pow(2, $attempt - 1); // 1s, 2s, 4s...
            logError("Gemini API - Reintento #{$attempt} después de {$waitSeconds}s", [
                'previous_error' => $lastError,
                'previous_http_code' => $lastHttpCode
            ]);
            sleep($waitSeconds);
        }
        
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-goog-api-key: ' . $apiKey
            ],
            CURLOPT_TIMEOUT => AI_TIMEOUT,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        
        curl_close($ch);
        
        // Error de conexión - reintentar
        if ($curlError) {
            $lastError = $curlError;
            $lastHttpCode = 0;
            continue; // Reintentar
        }
        
        // Éxito
        if ($httpCode === 200) {
            $result = json_decode($response, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                logError('Error parseando respuesta de Gemini', ['response' => $response]);
                throw new Exception('Error procesando respuesta de IA');
            }
            
            // Extraer texto de respuesta
            if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                // Log éxito si hubo reintentos
                if ($attempt > 0) {
                    logError("Gemini API - Éxito después de {$attempt} reintentos", []);
                }
                return trim($result['candidates'][0]['content']['parts'][0]['text']);
            }
            
            // Bloqueado por seguridad
            if (isset($result['candidates'][0]['finishReason']) && 
                $result['candidates'][0]['finishReason'] === 'SAFETY') {
                logError('Respuesta bloqueada por seguridad de Gemini', ['result' => $result]);
                return "Entiendo que estás pasando por un momento difícil. Estoy aquí para escucharte. ¿Te gustaría contarme más sobre cómo te sientes?";
            }
            
            logError('Estructura de respuesta inesperada de Gemini', ['result' => $result]);
            throw new Exception('Respuesta inesperada de IA');
        }
        
        // Error que amerita reintento
        if (in_array($httpCode, $retryableCodes)) {
            $lastError = "HTTP {$httpCode}";
            $lastHttpCode = $httpCode;
            
            // Log solo en primer intento fallido
            if ($attempt === 0) {
                logError('Gemini API temporalmente no disponible, iniciando reintentos', [
                    'http_code' => $httpCode,
                    'max_retries' => $maxRetries
                ]);
            }
            continue; // Reintentar
        }
        
        // Error no recuperable (400, 401, 403, etc.)
        logError('Gemini API error no recuperable', [
            'http_code' => $httpCode,
            'response' => $response
        ]);
        throw new Exception('Error en el servicio de IA (código: ' . $httpCode . ')');
    }
    
    // Todos los reintentos fallaron
    logError('Gemini API - Todos los reintentos fallaron', [
        'attempts' => $maxRetries + 1,
        'last_error' => $lastError,
        'last_http_code' => $lastHttpCode
    ]);
    throw new Exception('El servicio de IA no está disponible. Por favor intenta en unos minutos.');
}

/**
 * Respuesta simulada para modo desarrollo (sin API key)
 * Includes bilingual responses
 */
function getDevModeResponse($prompt) {
    // Spanish responses
    $responsesES = [
        "[RISK_LEVEL: 0] [PAP_PHASE: A]\nGracias por compartir eso conmigo. Me importa mucho cómo te sientes. ¿Hay algo específico que te gustaría hablar hoy?",
        "[RISK_LEVEL: 0] [PAP_PHASE: A]\nEntiendo que esto puede ser difícil. Recuerda que está bien sentirse así a veces. ¿Cómo puedo ayudarte ahora?",
        "[RISK_LEVEL: 0] [PAP_PHASE: A]\nEstoy aquí para escucharte sin juzgar. Tu bienestar es importante. ¿Te gustaría contarme más sobre lo que está pasando?"
    ];
    
    // English responses
    $responsesEN = [
        "[RISK_LEVEL: 0] [PAP_PHASE: A]\nThanks for sharing that with me. I really care about how you're feeling. Is there something specific you'd like to talk about today?",
        "[RISK_LEVEL: 0] [PAP_PHASE: A]\nI understand this can be difficult. Remember it's okay to feel this way sometimes. How can I help you now?",
        "[RISK_LEVEL: 0] [PAP_PHASE: A]\nI'm here to listen without judgment. Your well-being is important. Would you like to tell me more about what's going on?"
    ];
    
    // Simple language detection from prompt (check for common English words)
    $englishIndicators = ['the ', 'and ', 'is ', 'are ', 'you ', 'how ', 'what ', 'hello', 'hi ', "i'm ", "i am", 'feel'];
    $promptLower = strtolower($prompt);
    $isEnglish = false;
    
    foreach ($englishIndicators as $word) {
        if (strpos($promptLower, $word) !== false) {
            $isEnglish = true;
            break;
        }
    }
    
    $responses = $isEnglish ? $responsesEN : $responsesES;
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
        $typeKey = $memory['memory_type'] ?? 'unknown';
        $type = $typeLabels[$typeKey] ?? $typeKey;
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
