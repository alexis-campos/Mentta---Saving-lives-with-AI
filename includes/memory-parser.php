<?php
/**
 * MENTTA - Parser de Memoria Contextual
 * Extrae y almacena información relevante de las conversaciones
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

/**
 * Extrae y guarda memorias de un mensaje
 */
function extractAndSaveMemory($message, $patientId) {
    $memoriesSaved = [];
    
    $memoriesSaved = array_merge($memoriesSaved, extractRelationships($message, $patientId));
    $memoriesSaved = array_merge($memoriesSaved, extractEvents($message, $patientId));
    $memoriesSaved = array_merge($memoriesSaved, extractNames($message, $patientId));
    
    return $memoriesSaved;
}

/**
 * Extrae relaciones familiares/sociales
 */
function extractRelationships($message, $patientId) {
    $saved = [];
    
    $relations = [
        'madre', 'mamá', 'mama', 'mami',
        'padre', 'papá', 'papa', 'papi',
        'hermano', 'hermana', 'hermanito', 'hermanita',
        'esposo', 'esposa', 'marido', 'mujer',
        'novio', 'novia', 'pareja', 'ex',
        'hijo', 'hija', 'bebé',
        'abuelo', 'abuela', 'abuelito', 'abuelita',
        'tío', 'tía', 'primo', 'prima',
        'amigo', 'amiga', 'mejor amigo', 'mejor amiga',
        'jefe', 'jefa', 'compañero', 'compañera'
    ];
    
    $pattern = '/mi\s+(' . implode('|', $relations) . ')\s+([A-ZÁÉÍÓÚÑ][a-záéíóúñ]+)/u';
    
    if (preg_match_all($pattern, $message, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $relation = mb_strtolower($match[1]);
            $name = $match[2];
            
            $saved[] = saveMemory($patientId, 'relationship', $relation, $name, $match[0], 4);
        }
    }
    
    return array_filter($saved);
}

/**
 * Extrae eventos importantes
 */
function extractEvents($message, $patientId) {
    $saved = [];
    $textLower = mb_strtolower($message);
    
    $eventPatterns = [
        'falleci' => 'fallecimiento',
        'murió' => 'fallecimiento',
        'muerte de' => 'fallecimiento',
        'despidieron' => 'pérdida de trabajo',
        'perdí mi trabajo' => 'pérdida de trabajo',
        'me echaron' => 'pérdida de trabajo',
        'hospital' => 'hospitalización',
        'operación' => 'cirugía',
        'cirugía' => 'cirugía',
        'divorcio' => 'divorcio',
        'separación' => 'separación',
        'terminé con' => 'ruptura',
        'me dejó' => 'ruptura',
        'accidente' => 'accidente',
        'embarazada' => 'embarazo',
        'bebé' => 'nacimiento',
        'casé' => 'matrimonio',
        'boda' => 'matrimonio',
        'graduación' => 'graduación',
        'graduarme' => 'graduación',
        'nuevo trabajo' => 'nuevo trabajo',
        'me contrataron' => 'nuevo trabajo',
        'mudé' => 'mudanza',
        'mudanza' => 'mudanza'
    ];
    
    foreach ($eventPatterns as $keyword => $eventType) {
        if (mb_strpos($textLower, $keyword) !== false) {
            $context = extractContext($message, $keyword, 100);
            $saved[] = saveMemory($patientId, 'event', $eventType, $context, $message, 5);
        }
    }
    
    return array_filter($saved);
}

/**
 * Extrae nombres propios
 */
function extractNames($message, $patientId) {
    $saved = [];
    
    if (preg_match_all('/(?:^|\.\s+)([A-ZÁÉÍÓÚÑ][a-záéíóúñ]+)(?:\s|,|\.|$)/u', $message, $matches)) {
        $commonWords = [
            'El', 'La', 'Los', 'Las', 'Un', 'Una', 'Unos', 'Unas',
            'Yo', 'Tu', 'Mi', 'Su', 'Que', 'Como', 'Cuando', 'Donde',
            'Hoy', 'Ayer', 'Mañana', 'Ahora', 'Nunca', 'Siempre',
            'Si', 'No', 'Pero', 'Porque', 'Aunque', 'Entonces',
            'Bien', 'Mal', 'Muy', 'Mucho', 'Poco', 'Todo', 'Nada',
            'Gracias', 'Hola', 'Adiós', 'Por', 'Para', 'Con', 'Sin'
        ];
        
        foreach ($matches[1] as $name) {
            if (!in_array($name, $commonWords) && mb_strlen($name) > 2) {
                $saved[] = saveMemory($patientId, 'name', $name, $name, $message, 2);
            }
        }
    }
    
    return array_filter($saved);
}

/**
 * Guarda una memoria en la BD
 */
function saveMemory($patientId, $type, $keyName, $value, $context, $importance = 3) {
    try {
        $existing = dbFetchOne(
            "SELECT id FROM patient_memory WHERE patient_id = ? AND memory_type = ? AND key_name = ?",
            [$patientId, $type, $keyName]
        );
        
        if ($existing) {
            dbQuery(
                "UPDATE patient_memory SET value = ?, context = ?, importance = GREATEST(importance, ?), 
                 last_mentioned_at = NOW() WHERE id = ?",
                [$value, mb_substr($context, 0, 500), $importance, $existing['id']]
            );
            return ['type' => $type, 'key' => $keyName, 'value' => $value, 'updated' => true];
        }
        
        $id = dbInsert('patient_memory', [
            'patient_id' => $patientId,
            'memory_type' => $type,
            'key_name' => $keyName,
            'value' => $value,
            'context' => mb_substr($context, 0, 500),
            'importance' => $importance,
            'last_mentioned_at' => date('Y-m-d H:i:s')
        ]);
        
        return $id ? ['type' => $type, 'key' => $keyName, 'value' => $value, 'created' => true] : null;
    } catch (Exception $e) {
        logError('Error guardando memoria', ['error' => $e->getMessage()]);
        return null;
    }
}

/**
 * Extrae contexto alrededor de un keyword
 */
function extractContext($text, $keyword, $length = 100) {
    $pos = mb_stripos($text, $keyword);
    if ($pos === false) return $text;
    
    $start = max(0, $pos - $length / 2);
    $extractedLength = min(mb_strlen($text) - $start, $length);
    
    $context = mb_substr($text, $start, $extractedLength);
    if ($start > 0) $context = '...' . $context;
    if ($start + $extractedLength < mb_strlen($text)) $context .= '...';
    
    return $context;
}

/**
 * Obtiene memorias del paciente
 */
function getPatientMemory($patientId, $limit = 20) {
    return dbFetchAll(
        "SELECT memory_type, key_name, value, context, importance, created_at
         FROM patient_memory WHERE patient_id = ? 
         ORDER BY importance DESC, last_mentioned_at DESC LIMIT ?",
        [$patientId, $limit]
    );
}

/**
 * Formatea memoria para mostrar
 */
function formatMemoryForDisplay($memory) {
    $typeLabels = [
        'name' => '👤 Persona',
        'relationship' => '👨‍👩‍👧 Relación',
        'event' => '📅 Evento',
        'preference' => '⭐ Preferencia',
        'emotion' => '💭 Emoción',
        'location' => '📍 Lugar'
    ];
    
    $type = $typeLabels[$memory['memory_type']] ?? $memory['memory_type'];
    return "{$type}: {$memory['key_name']} - {$memory['value']}";
}
