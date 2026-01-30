<?php
/**
 * MENTTA - AI Analyzer (Unified)
 * Análisis potenciado por IA para:
 * - Detección de riesgo contextual
 * - Análisis de sentimiento profundo
 * - Extracción de memoria semántica
 * 
 * Reemplaza los sistemas basados en keywords con comprensión real del contexto
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

/**
 * Análisis completo de un mensaje usando IA
 * 
 * @param string $message - Mensaje actual del usuario
 * @param int $patientId - ID del paciente
 * @param array $conversationHistory - Últimos mensajes para contexto
 * @return array Análisis completo
 */
function analyzeMessageWithAI($message, $patientId, $conversationHistory = []) {
    // Obtener datos del paciente
    $patient = dbFetchOne(
        "SELECT name, age FROM users WHERE id = ?",
        [$patientId]
    );
    
    // Formatear historial para contexto
    $historyText = formatConversationForAnalysis($conversationHistory);
    
    // Construir prompt de análisis
    $prompt = buildAnalysisPrompt($message, $patient, $historyText);
    
    // Llamar a Gemini para análisis
    $analysis = callGeminiForAnalysis($prompt);
    
    // Si falla la IA, usar fallback de keywords
    if (!$analysis) {
        return getFallbackAnalysis($message);
    }
    
    // Guardar análisis en BD para tracking
    saveAnalysisLog($patientId, $message, $analysis);
    
    return $analysis;
}

/**
 * Construye el prompt para análisis integral
 */
function buildAnalysisPrompt($message, $patient, $historyText) {
    $patientName = $patient['name'] ?? 'Usuario';
    $patientAge = $patient['age'] ?? 'desconocida';
    
    return <<<PROMPT
Eres un sistema de análisis de salud mental para la aplicación Mentta. Tu trabajo es analizar mensajes de pacientes para detectar su estado emocional y nivel de riesgo.

═══════════════════════════════════════
CONTEXTO DEL PACIENTE
═══════════════════════════════════════
Nombre: {$patientName}
Edad: {$patientAge}

═══════════════════════════════════════
HISTORIAL RECIENTE DE CONVERSACIÓN
═══════════════════════════════════════
{$historyText}

═══════════════════════════════════════
MENSAJE A ANALIZAR
═══════════════════════════════════════
"{$message}"

═══════════════════════════════════════
INSTRUCCIONES CRÍTICAS
═══════════════════════════════════════
Analiza el mensaje EN CONTEXTO. No solo busques palabras clave, entiende la INTENCIÓN real.

Ejemplos de análisis contextual:
- "me quiero morir de risa" → NO es riesgo, es expresión coloquial
- "todo está gris, no le encuentro sentido" → SÍ es riesgo (desesperanza implícita)
- "mi amigo se cortó" → NO es riesgo del paciente, pero es tema sensible
- "estoy cansado de todo esto" → DEPENDE del contexto histórico

═══════════════════════════════════════
RESPONDE SOLO EN FORMATO JSON VÁLIDO
═══════════════════════════════════════
{
    "risk_assessment": {
        "level": "none|low|medium|high|critical",
        "is_real_risk": true/false,
        "trigger_alert": true/false,
        "reasoning": "explicación breve de por qué determinaste este nivel"
    },
    "sentiment": {
        "positive": 0.0-1.0,
        "negative": 0.0-1.0,
        "anxiety": 0.0-1.0,
        "sadness": 0.0-1.0,
        "anger": 0.0-1.0,
        "dominant_emotion": "nombre de la emoción principal"
    },
    "memory_extraction": {
        "people": ["nombre de personas mencionadas"],
        "relationships": [{"name": "Ana", "relation": "hermana"}],
        "events": ["eventos importantes mencionados"],
        "places": ["lugares mencionados"],
        "topics": ["temas detectados: trabajo, familia, salud, etc"]
    },
    "emotional_state": {
        "current_mood": "descripción breve del estado actual",
        "trend": "improving|stable|declining|unknown",
        "needs_attention": true/false
    },
    "safe_life_mode": {
        "activate": true/false,
        "reason": "razón si se activa"
    }
}

IMPORTANTE: Responde ÚNICAMENTE con el JSON, sin texto adicional.
PROMPT;
}

/**
 * Llama a Gemini específicamente para análisis
 */
function callGeminiForAnalysis($prompt) {
    $apiKey = AI_API_KEY;
    $model = AI_MODEL;
    // API key goes in header, not URL (per latest Google API docs)
    $url = AI_API_URL . $model . ':generateContent';
    
    // En desarrollo sin API key
    if ($apiKey === 'TU_API_KEY_AQUI' || $apiKey === 'YOUR_API_KEY_HERE' || empty($apiKey)) {
        return getDevModeAnalysis();
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
            'temperature' => 0.1, // Baja temperatura para análisis consistente
            'topK' => 1,
            'topP' => 0.8,
            'maxOutputTokens' => 3000,
        ],
        // CRÍTICO: Desactivar filtros de seguridad para análisis de salud mental
        // Esto permite que la IA analice contenido sobre suicidio/autolesión
        // sin ser bloqueada por los filtros automáticos de Google
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
            'Content-Type: application/json',
            'x-goog-api-key: ' . $apiKey  // API key in header per latest docs
        ],
        CURLOPT_TIMEOUT => 15,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    if ($error || $httpCode !== 200) {
        logError('Error en análisis IA', [
            'error' => $error,
            'http_code' => $httpCode
        ]);
        return null;
    }
    
    $result = json_decode($response, true);
    
    // Extraer texto de respuesta
    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        $jsonText = trim($result['candidates'][0]['content']['parts'][0]['text']);
        
        // Limpiar posibles caracteres extra
        $jsonText = preg_replace('/^```json\s*/', '', $jsonText);
        $jsonText = preg_replace('/\s*```$/', '', $jsonText);
        
        $analysis = json_decode($jsonText, true);
        
        if (json_last_error() === JSON_ERROR_NONE) {
            return $analysis;
        } else {
            logError('Error parseando JSON de análisis', [
                'json_error' => json_last_error_msg(),
                'raw_response' => $jsonText
            ]);
        }
    }
    
    return null;
}

/**
 * Formatea conversación para el análisis
 */
function formatConversationForAnalysis($history) {
    if (empty($history)) {
        return "Esta es la primera interacción con el usuario.";
    }
    
    // Invertir para orden cronológico
    $history = array_reverse($history);
    
    $formatted = [];
    foreach ($history as $msg) {
        $sender = $msg['sender'] === 'user' ? 'Paciente' : 'Mentta';
        $formatted[] = "[{$sender}]: {$msg['message']}";
    }
    
    return implode("\n", $formatted);
}

/**
 * Análisis fallback usando keywords cuando la IA no está disponible
 */
function getFallbackAnalysis($message) {
    // Importar funciones de los sistemas legacy
    require_once __DIR__ . '/sentiment-analyzer.php';
    require_once __DIR__ . '/risk-detector.php';
    
    $sentiment = analyzeSentiment($message);
    $riskLevel = detectRiskLevel($message);
    
    return [
        'risk_assessment' => [
            'level' => $riskLevel,
            'is_real_risk' => in_array($riskLevel, ['high', 'critical']),
            'trigger_alert' => shouldTriggerAlert($riskLevel),
            'reasoning' => 'Análisis basado en keywords (fallback)'
        ],
        'sentiment' => array_merge($sentiment, [
            'dominant_emotion' => getDominantEmotion($sentiment)
        ]),
        'memory_extraction' => [
            'people' => [],
            'relationships' => [],
            'events' => [],
            'places' => [],
            'topics' => []
        ],
        'emotional_state' => [
            'current_mood' => 'No determinado (fallback)',
            'trend' => 'unknown',
            'needs_attention' => in_array($riskLevel, ['medium', 'high', 'critical'])
        ],
        'safe_life_mode' => [
            'activate' => in_array($riskLevel, ['high', 'critical']),
            'reason' => $riskLevel !== 'none' ? 'Detectado por sistema de keywords' : null
        ],
        '_fallback' => true
    ];
}

/**
 * Análisis simulado para modo desarrollo
 */
function getDevModeAnalysis() {
    return [
        'risk_assessment' => [
            'level' => 'none',
            'is_real_risk' => false,
            'trigger_alert' => false,
            'reasoning' => 'Modo desarrollo - análisis simulado'
        ],
        'sentiment' => [
            'positive' => 0.3,
            'negative' => 0.2,
            'anxiety' => 0.1,
            'sadness' => 0.2,
            'anger' => 0.1,
            'dominant_emotion' => 'neutral'
        ],
        'memory_extraction' => [
            'people' => [],
            'relationships' => [],
            'events' => [],
            'places' => [],
            'topics' => ['conversación general']
        ],
        'emotional_state' => [
            'current_mood' => 'Estable (simulado)',
            'trend' => 'stable',
            'needs_attention' => false
        ],
        'safe_life_mode' => [
            'activate' => false,
            'reason' => null
        ],
        '_dev_mode' => true
    ];
}

/**
 * Guarda el análisis en BD para tracking y mejora continua
 */
function saveAnalysisLog($patientId, $message, $analysis) {
    try {
        $db = getDB();
        
        // Verificar si existe la tabla de logs de análisis
        $stmt = $db->query("SHOW TABLES LIKE 'ai_analysis_logs'");
        if ($stmt->rowCount() === 0) {
            // Crear tabla si no existe
            $db->exec("
                CREATE TABLE ai_analysis_logs (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    patient_id INT NOT NULL,
                    message_hash VARCHAR(64) NOT NULL,
                    risk_level VARCHAR(20),
                    trigger_alert BOOLEAN DEFAULT FALSE,
                    sentiment_data JSON,
                    memory_data JSON,
                    safe_life_activated BOOLEAN DEFAULT FALSE,
                    analysis_source ENUM('ai', 'fallback', 'dev') DEFAULT 'ai',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_patient (patient_id),
                    INDEX idx_risk (risk_level),
                    INDEX idx_created (created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        }
        
        // Determinar fuente del análisis
        $source = 'ai';
        if (isset($analysis['_fallback'])) $source = 'fallback';
        if (isset($analysis['_dev_mode'])) $source = 'dev';
        
        $stmt = $db->prepare("
            INSERT INTO ai_analysis_logs 
            (patient_id, message_hash, risk_level, trigger_alert, sentiment_data, memory_data, safe_life_activated, analysis_source)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $patientId,
            hash('sha256', $message),
            $analysis['risk_assessment']['level'] ?? 'none',
            $analysis['risk_assessment']['trigger_alert'] ?? false,
            json_encode($analysis['sentiment'] ?? []),
            json_encode($analysis['memory_extraction'] ?? []),
            $analysis['safe_life_mode']['activate'] ?? false,
            $source
        ]);
        
    } catch (Exception $e) {
        logError('Error guardando log de análisis', ['error' => $e->getMessage()]);
    }
}

/**
 * Procesa la memoria extraída y la guarda en la BD
 */
function processExtractedMemory($patientId, $memoryData) {
    if (empty($memoryData)) return;
    
    $db = getDB();
    
    // Procesar relaciones
    if (!empty($memoryData['relationships'])) {
        foreach ($memoryData['relationships'] as $rel) {
            saveMemoryItem($patientId, 'relationship', $rel['name'], $rel['relation']);
        }
    }
    
    // Procesar personas mencionadas
    if (!empty($memoryData['people'])) {
        foreach ($memoryData['people'] as $person) {
            saveMemoryItem($patientId, 'name', $person, $person);
        }
    }
    
    // Procesar eventos
    if (!empty($memoryData['events'])) {
        foreach ($memoryData['events'] as $event) {
            saveMemoryItem($patientId, 'event', $event, $event);
        }
    }
}

/**
 * Guarda un ítem de memoria
 */
function saveMemoryItem($patientId, $type, $key, $value) {
    try {
        $db = getDB();
        
        // Verificar si ya existe
        $existing = dbFetchOne(
            "SELECT id FROM patient_memory WHERE patient_id = ? AND memory_type = ? AND key_name = ?",
            [$patientId, $type, $key]
        );
        
        if ($existing) {
            // Actualizar timestamp
            $db->prepare("UPDATE patient_memory SET last_mentioned_at = NOW() WHERE id = ?")
               ->execute([$existing['id']]);
        } else {
            // Insertar nuevo
            $db->prepare("
                INSERT INTO patient_memory (patient_id, memory_type, key_name, value, importance, last_mentioned_at)
                VALUES (?, ?, ?, ?, 3, NOW())
            ")->execute([$patientId, $type, $key, $value]);
        }
    } catch (Exception $e) {
        logError('Error guardando memoria', ['error' => $e->getMessage()]);
    }
}

/**
 * Obtiene tendencia emocional del paciente (últimos N análisis)
 */
function getEmotionalTrend($patientId, $days = 7) {
    try {
        $db = getDB();
        
        $stmt = $db->prepare("
            SELECT 
                DATE(created_at) as date,
                AVG(JSON_EXTRACT(sentiment_data, '$.positive')) as avg_positive,
                AVG(JSON_EXTRACT(sentiment_data, '$.negative')) as avg_negative,
                AVG(JSON_EXTRACT(sentiment_data, '$.anxiety')) as avg_anxiety,
                AVG(JSON_EXTRACT(sentiment_data, '$.sadness')) as avg_sadness,
                COUNT(*) as message_count,
                SUM(CASE WHEN risk_level IN ('high', 'critical') THEN 1 ELSE 0 END) as high_risk_count
            FROM ai_analysis_logs
            WHERE patient_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ");
        
        $stmt->execute([$patientId, $days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        return [];
    }
}
