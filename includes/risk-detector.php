<?php
/**
 * MENTTA - Detector de Riesgo
 * Sistema de detección de mensajes de alto riesgo y crisis
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

/**
 * Detecta nivel de riesgo en un mensaje
 */
function detectRiskLevel($text) {
    $textLower = mb_strtolower($text, 'UTF-8');
    
    // NIVEL CRÍTICO - Riesgo inmediato
    $criticalPatterns = [
        'suicidarme', 'voy a matarme', 'me voy a matar', 'quiero matarme',
        'terminar con mi vida', 'acabar con mi vida', 'quitarme la vida',
        'voy a terminar con todo', 'esta es mi despedida', 'carta de despedida',
        'ya tomé la decisión', 'voy a cortarme las venas', 'me voy a tirar',
        'no quiero seguir viviendo', 'ya no quiero vivir', 'no quiero seguir aquí',
        'me quiero ir para siempre', 'desaparecer del mundo'
    ];
    
    // NIVEL ALTO - Ideación suicida / autolesión
    $highPatterns = [
        'quiero morir', 'prefiero morir', 'no quiero vivir', 'no vale la pena vivir',
        'mejor muerto', 'ojalá no existiera', 'no hay salida', 'no hay esperanza',
        'todo sería mejor sin mí', 'soy una carga', 'a nadie le importo',
        'me corto', 'hacerme daño', 'quiero hacerme daño', 'lastimarme',
        'desaparecer', 'no existir', 'dejar de existir'
    ];
    
    // NIVEL MEDIO - Desesperanza / estrés severo
    $mediumPatterns = [
        'no puedo más', 'ya no puedo', 'estoy cansado de todo', 'me rendí',
        'todo está perdido', 'para qué seguir', 'nada importa', 'nadie me entiende',
        'estoy completamente solo', 'me siento vacío', 'atrapado sin salida',
        'muy estresado', 'estresado con', 'estrés me mata', 'ansiedad me consume',
        'no aguanto más', 'estoy harto', 'no lo soporto', 'me derrumbo',
        'crisis de ansiedad', 'ataque de pánico', 'no puedo respirar'
    ];
    
    // NIVEL BAJO - Señales tempranas / tristeza
    $lowPatterns = [
        'me siento muy mal', 'no estoy bien', 'necesito ayuda', 'no sé qué hacer',
        'soy un fracaso', 'no valgo nada', 'soy inútil',
        'me siento triste', 'estoy triste', 'me siento solo', 'estoy solo',
        'me siento mal', 'día difícil', 'momento difícil', 'pasándola mal',
        'no me siento bien', 'algo anda mal', 'no estoy de ánimo'
    ];
    
    foreach ($criticalPatterns as $p) {
        if (mb_strpos($textLower, $p) !== false) return 'critical';
    }
    
    $highMatches = 0;
    foreach ($highPatterns as $p) {
        if (mb_strpos($textLower, $p) !== false) $highMatches++;
    }
    if ($highMatches >= 2) return 'critical';
    if ($highMatches >= 1) return 'high';
    
    $mediumMatches = 0;
    foreach ($mediumPatterns as $p) {
        if (mb_strpos($textLower, $p) !== false) $mediumMatches++;
    }
    if ($mediumMatches >= 2) return 'high';
    if ($mediumMatches >= 1) return 'medium';
    
    foreach ($lowPatterns as $p) {
        if (mb_strpos($textLower, $p) !== false) return 'low';
    }
    
    return 'none';
}

function shouldTriggerAlert($riskLevel) {
    return in_array($riskLevel, ['high', 'critical']);
}

function getAlertType($text) {
    $textLower = mb_strtolower($text, 'UTF-8');
    $suicideKw = ['suicid', 'matarme', 'morir', 'muerte', 'terminar', 'acabar'];
    $selfHarmKw = ['cortar', 'cortarme', 'quemar', 'golpear', 'lastimar'];
    
    foreach ($suicideKw as $kw) {
        if (mb_strpos($textLower, $kw) !== false) return 'suicide';
    }
    foreach ($selfHarmKw as $kw) {
        if (mb_strpos($textLower, $kw) !== false) return 'self_harm';
    }
    return 'crisis';
}

function getAlertSeverity($riskLevel) {
    return in_array($riskLevel, ['critical', 'high']) ? 'red' : 'orange';
}

function createRiskAlert($patientId, $message, $riskLevel) {
    try {
        $psychologist = dbFetchOne(
            "SELECT psychologist_id FROM patient_psychologist_link 
             WHERE patient_id = ? AND status = 'active' LIMIT 1",
            [$patientId]
        );
        
        $psychologistId = $psychologist ? $psychologist['psychologist_id'] : null;
        $messageSnapshot = mb_strlen($message) > 500 ? mb_substr($message, 0, 500) . '...' : $message;
        
        $alertId = dbInsert('alerts', [
            'patient_id' => $patientId,
            'psychologist_id' => $psychologistId,
            'alert_type' => getAlertType($message),
            'severity' => getAlertSeverity($riskLevel),
            'message_snapshot' => $messageSnapshot,
            'status' => 'pending'
        ]);
        
        logError('ALERTA DE RIESGO CREADA', [
            'alert_id' => $alertId,
            'patient_id' => $patientId,
            'severity' => getAlertSeverity($riskLevel)
        ]);
        
        return $alertId;
    } catch (Exception $e) {
        logError('Error creando alerta', ['error' => $e->getMessage()]);
        return false;
    }
}

function getEmergencyContacts($patientId) {
    return dbFetchAll(
        "SELECT contact_name, contact_phone, contact_relationship, priority
         FROM emergency_contacts WHERE patient_id = ? ORDER BY priority ASC",
        [$patientId]
    );
}

function getEmergencyLineInfo() {
    return [
        'number' => EMERGENCY_LINE,
        'name' => EMERGENCY_LINE_NAME,
        'country' => 'Perú',
        'available' => '24/7'
    ];
}

function hasLinkedPsychologist($patientId) {
    return dbFetchOne(
        "SELECT id FROM patient_psychologist_link WHERE patient_id = ? AND status = 'active'",
        [$patientId]
    ) !== false;
}

/**
 * ============================================
 * SISTEMA PAP - ANÁLISIS DE RIESGO MEJORADO (6 NIVELES)
 * ============================================
 * 
 * Niveles:
 * 0 = none (sin riesgo)
 * 1 = low (tristeza leve)
 * 2 = moderate (desesperanza moderada)
 * 3 = high (ideación suicida vaga)
 * 4 = critical (plan suicida con método)
 * 5 = imminent (plan con plazo inmediato)
 */

/**
 * Analiza riesgo en un mensaje (versión mejorada con 6 niveles)
 * 
 * IMPORTANTE: Este es un ANÁLISIS PRELIMINAR. La IA hará la decisión final.
 * 
 * @param string $text - Mensaje del usuario
 * @param int $patient_id - ID del paciente
 * @return array [
 *   'risk_score' => int (0-100),
 *   'suggested_level' => int (0-5),
 *   'keywords_found' => array,
 *   'context_factors' => array,
 *   'requires_confirmation' => bool
 * ]
 */
function analyzeRiskLevel($text, $patient_id) {
    $text_lower = mb_strtolower($text, 'UTF-8');
    $risk_score = 0;
    $keywords_found = [];
    
    // Keywords por nivel (ponderados)
    $keywords = [
        'imminent' => [
            'keywords' => ['voy a matarme', 'lo haré esta noche', 'ya tomé la decisión', 'me despido', 'adiós para siempre', 'esto es el fin', 'esta es mi despedida', 'voy a hacerlo', 'estoy en el techo', 'tengo la pistola', 'ya tomé las pastillas'],
            'weight' => 100
        ],
        'critical' => [
            'keywords' => ['tengo las pastillas', 'tengo el arma', 'ya tengo un plan', 'cómo suicidarme', 'formas de morir', 'terminar con mi vida', 'escribí una carta', 'carta de despedida', 'voy a cortarme', 'métodos para morir', 'busqué formas de'],
            'weight' => 80
        ],
        'high' => [
            'keywords' => ['quiero morir', 'mejor no estar', 'desaparecer', 'no vale la pena vivir', 'mi familia estaría mejor sin mí', 'no hay salida', 'ojalá no existiera', 'soy una carga', 'dejar de existir', 'prefiero morir', 'todo sería mejor sin mí'],
            'weight' => 60
        ],
        'moderate' => [
            'keywords' => ['no puedo más', 'todo está mal', 'sin esperanza', 'vacío', 'solo quiero dormir y no despertar', 'cansado de vivir', 'ya no puedo', 'nada importa', 'me rendí', 'atrapado', 'no aguanto más'],
            'weight' => 40
        ],
        'low' => [
            'keywords' => ['triste', 'deprimido', 'mal', 'solo', 'cansado', 'no tengo ganas', 'me siento solo', 'día difícil', 'no estoy bien', 'me siento mal'],
            'weight' => 20
        ]
    ];
    
    // Analizar keywords
    foreach ($keywords as $level => $data) {
        foreach ($data['keywords'] as $keyword) {
            $found = mb_strpos($text_lower, $keyword) !== false;
            
            if ($found) {
                $risk_score = max($risk_score, $data['weight']);
                if (!in_array($keyword, $keywords_found)) {
                    $keywords_found[] = $keyword;
                }
            }
        }
    }
    
    // Analizar contexto histórico
    $context_factors = analyzeContextFactors($patient_id);
    
    // Ajustar score según contexto
    if ($context_factors['recent_crisis_count'] >= 2) {
        $risk_score += 15; // Ha tenido crisis recientes
    }
    
    if ($context_factors['has_emergency_contacts'] === false) {
        $risk_score += 10; // No tiene red de apoyo configurada
    }
    
    if ($context_factors['time_of_day'] === 'late_night') { // 12am-6am
        $risk_score += 5; // Riesgo mayor en madrugada
    }
    
    // Limitar a 100
    $risk_score = min($risk_score, 100);
    
    // Sugerir nivel basado en score
    $suggested_level = 0;
    if ($risk_score >= 85) $suggested_level = 5; // imminent
    elseif ($risk_score >= 70) $suggested_level = 4; // critical
    elseif ($risk_score >= 50) $suggested_level = 3; // high
    elseif ($risk_score >= 30) $suggested_level = 2; // moderate
    elseif ($risk_score >= 10) $suggested_level = 1; // low
    
    // Detectar si necesita confirmación (posible falso positivo)
    $requires_confirmation = false;
    $ambiguous_phrases = ['quiero morir', 'me voy', 'ya no estaré', 'desaparecer'];
    foreach ($ambiguous_phrases as $phrase) {
        $phrase_found = mb_strpos($text_lower, $phrase) !== false;
        
        if ($phrase_found && $risk_score < 80) {
            $requires_confirmation = true;
            break;
        }
    }
    
    return [
        'risk_score' => $risk_score,
        'suggested_level' => $suggested_level,
        'keywords_found' => $keywords_found,
        'context_factors' => $context_factors,
        'requires_confirmation' => $requires_confirmation
    ];
}

/**
 * Analiza factores de contexto del paciente
 * 
 * @param int $patient_id - ID del paciente
 * @return array [
 *   'recent_crisis_count' => int,
 *   'has_emergency_contacts' => bool,
 *   'time_of_day' => string (day, night, late_night),
 *   'hour' => int
 * ]
 */
function analyzeContextFactors($patient_id) {
    // Default values (si no hay conexión a BD o error)
    $default = [
        'recent_crisis_count' => 0,
        'has_emergency_contacts' => true,
        'time_of_day' => 'day',
        'hour' => intval(date('H'))
    ];
    
    try {
        // Crisis recientes (últimos 7 días) - Usa integers (3=high, 4=critical, 5=imminent)
        $recent_crisis = dbFetchOne(
            "SELECT COUNT(*) as crisis_count
             FROM conversations
             WHERE patient_id = ?
             AND risk_level >= 3
             AND created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)",
            [$patient_id]
        );
        
        // Tiene contactos de emergencia
        $contacts = dbFetchOne(
            "SELECT COUNT(*) as contact_count
             FROM emergency_contacts
             WHERE patient_id = ?",
            [$patient_id]
        );
        
        // Hora del día
        $hour = intval(date('H'));
        $time_of_day = 'day';
        if ($hour >= 0 && $hour < 6) {
            $time_of_day = 'late_night';
        } elseif ($hour >= 22) {
            $time_of_day = 'night';
        }
        
        return [
            'recent_crisis_count' => intval($recent_crisis['crisis_count'] ?? 0),
            'has_emergency_contacts' => intval($contacts['contact_count'] ?? 0) > 0,
            'time_of_day' => $time_of_day,
            'hour' => $hour
        ];
        
    } catch (Exception $e) {
        logError('Error en analyzeContextFactors', ['error' => $e->getMessage()]);
        return $default;
    }
}

/**
 * Convierte nivel numérico (0-5) a string legacy para compatibilidad
 */
function riskLevelToString($level) {
    $levels = [
        0 => 'none',
        1 => 'low',
        2 => 'medium',
        3 => 'high',
        4 => 'critical',
        5 => 'critical' // imminent se guarda como critical en BD legacy
    ];
    return $levels[$level] ?? 'none';
}

/**
 * Convierte string legacy a nivel numérico (0-5)
 */
function riskLevelToInt($levelString) {
    $levels = [
        'none' => 0,
        'low' => 1,
        'medium' => 2,
        'high' => 3,
        'critical' => 4
    ];
    return $levels[$levelString] ?? 0;
}

