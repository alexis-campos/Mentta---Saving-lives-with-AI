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
        'ya tomé la decisión', 'voy a cortarme las venas', 'me voy a tirar'
    ];
    
    // NIVEL ALTO - Ideación suicida
    $highPatterns = [
        'quiero morir', 'prefiero morir', 'no quiero vivir', 'no vale la pena vivir',
        'mejor muerto', 'ojalá no existiera', 'no hay salida', 'no hay esperanza',
        'todo sería mejor sin mí', 'soy una carga', 'a nadie le importo',
        'me corto', 'hacerme daño', 'quiero hacerme daño'
    ];
    
    // NIVEL MEDIO - Desesperanza
    $mediumPatterns = [
        'no puedo más', 'ya no puedo', 'estoy cansado de todo', 'me rendí',
        'todo está perdido', 'para qué seguir', 'nada importa', 'nadie me entiende',
        'estoy completamente solo', 'me siento vacío', 'atrapado sin salida'
    ];
    
    // NIVEL BAJO - Señales tempranas
    $lowPatterns = [
        'me siento muy mal', 'no estoy bien', 'necesito ayuda', 'no sé qué hacer',
        'soy un fracaso', 'no valgo nada', 'soy inútil'
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
