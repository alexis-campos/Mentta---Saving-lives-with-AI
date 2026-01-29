<?php
/**
 * MENTTA - Analizador de Sentimientos
 * Análisis de emociones basado en keywords para mensajes en español
 */

require_once __DIR__ . '/config.php';

/**
 * Analiza sentimiento de un mensaje
 * 
 * @param string $text Mensaje a analizar
 * @return array Scores de cada emoción (0-1)
 */
function analyzeSentiment($text) {
    $textLower = mb_strtolower($text, 'UTF-8');
    
    // Keywords para cada emoción (español)
    $emotions = [
        'positive' => [
            'keywords' => [
                'feliz', 'contento', 'contenta', 'alegre', 'alegría', 'bien', 'mejor', 
                'gracias', 'genial', 'excelente', 'increíble', 'maravilloso', 'fantástico',
                'amor', 'amo', 'quiero', 'cariño', 'esperanza', 'optimista', 'motivado',
                'motivada', 'entusiasmado', 'entusiasmada', 'agradecido', 'agradecida',
                'tranquilo', 'tranquila', 'paz', 'sereno', 'serena', 'satisfecho',
                'satisfecha', 'logré', 'conseguí', 'éxito', 'victoria', 'celebrar',
                'divertido', 'divertida', 'risa', 'reír', 'sonrisa', 'sonreír',
                'hermoso', 'hermosa', 'bonito', 'bonita', 'lindo', 'linda'
            ],
            'weight' => 1.0
        ],
        'negative' => [
            'keywords' => [
                'mal', 'triste', 'deprimido', 'deprimida', 'horrible', 'terrible', 
                'peor', 'fatal', 'pésimo', 'pésima', 'odio', 'detesto', 'asco',
                'fracaso', 'fracasé', 'fallo', 'fallé', 'error', 'culpa', 'culpable',
                'inútil', 'incompetente', 'incapaz', 'débil', 'perdedor', 'perdedora',
                'miserable', 'desgraciado', 'desgraciada', 'desastre', 'catástrofe',
                'sufrimiento', 'sufro', 'dolor', 'duele', 'herido', 'herida',
                'abandonado', 'abandonada', 'rechazado', 'rechazada', 'ignorado', 'ignorada'
            ],
            'weight' => 1.0
        ],
        'anxiety' => [
            'keywords' => [
                'ansioso', 'ansiosa', 'ansiedad', 'nervioso', 'nerviosa', 'nervios',
                'preocupado', 'preocupada', 'preocupación', 'miedo', 'pánico', 'terror',
                'estrés', 'estresado', 'estresada', 'agobiado', 'agobiada', 'abrumado',
                'abrumada', 'presión', 'tensión', 'tenso', 'tensa', 'inquieto', 'inquieta',
                'intranquilo', 'intranquila', 'inseguro', 'insegura', 'aterrado', 'aterrada',
                'asustado', 'asustada', 'temblando', 'temblar', 'palpitaciones', 'ahogo',
                'no puedo respirar', 'me falta el aire', 'ataque de pánico'
            ],
            'weight' => 1.2 // Mayor peso para ansiedad
        ],
        'sadness' => [
            'keywords' => [
                'triste', 'tristeza', 'llorar', 'lloro', 'lágrimas', 'solo', 'sola',
                'soledad', 'vacío', 'vacía', 'deprimido', 'deprimida', 'depresión',
                'melancolía', 'melancólico', 'melancólica', 'desanimado', 'desanimada',
                'desesperado', 'desesperada', 'desesperanza', 'sin esperanza', 'perdido',
                'perdida', 'desolado', 'desolada', 'abatido', 'abatida', 'desconsolado',
                'desconsolada', 'afligido', 'afligida', 'pena', 'lamento', 'extraño',
                'nostalgia', 'añoranza', 'echo de menos', 'murió', 'falleció', 'muerte'
            ],
            'weight' => 1.2
        ],
        'anger' => [
            'keywords' => [
                'enojado', 'enojada', 'enojo', 'furioso', 'furiosa', 'furia', 'rabia',
                'frustrado', 'frustrada', 'frustración', 'odio', 'odiar', 'molesto',
                'molesta', 'irritado', 'irritada', 'harto', 'harta', 'cansado de',
                'indignado', 'indignada', 'injusto', 'injusticia', 'impotencia',
                'coraje', 'bronca', 'cabrear', 'enfurecido', 'enfurecida', 'iracundo',
                'iracunda', 'maldición', 'maldito', 'maldita', 'estúpido', 'estúpida',
                'idiota', 'imbécil', 'desgraciado'
            ],
            'weight' => 1.0
        ]
    ];
    
    $scores = [];
    $wordCount = str_word_count($text);
    $wordCount = max($wordCount, 1); // Evitar división por cero
    
    foreach ($emotions as $emotion => $data) {
        $matchCount = 0;
        
        foreach ($data['keywords'] as $keyword) {
            // Contar ocurrencias de cada keyword
            $matchCount += mb_substr_count($textLower, $keyword);
        }
        
        // Calcular score normalizado (0-1)
        // Fórmula: (matches * weight) / (wordCount * factor_normalización)
        $rawScore = ($matchCount * $data['weight']) / ($wordCount * 0.5);
        $scores[$emotion] = min(1.0, max(0.0, $rawScore)); // Clampar a 0-1
    }
    
    // Aplicar intensificadores y modificadores
    $scores = applyIntensifiers($textLower, $scores);
    
    // Redondear a 2 decimales
    foreach ($scores as $emotion => $score) {
        $scores[$emotion] = round($score, 2);
    }
    
    return $scores;
}

/**
 * Aplica intensificadores que aumentan o reducen scores
 */
function applyIntensifiers($text, $scores) {
    // Intensificadores (aumentan emociones)
    $intensifiers = [
        'muy', 'mucho', 'muchísimo', 'demasiado', 'extremadamente',
        'totalmente', 'completamente', 'absolutamente', 'increíblemente',
        'terriblemente', 'tanto', 'tanta'
    ];
    
    // Negadores (pueden invertir o reducir)
    $negators = [
        'no', 'nunca', 'jamás', 'nada', 'ningún', 'ninguno', 'ninguna',
        'tampoco', 'sin'
    ];
    
    // Contar intensificadores
    $intensifierCount = 0;
    foreach ($intensifiers as $intensifier) {
        $intensifierCount += mb_substr_count($text, $intensifier);
    }
    
    // Si hay intensificadores, aumentar scores existentes
    if ($intensifierCount > 0) {
        $boost = min(0.3, $intensifierCount * 0.1);
        foreach ($scores as $emotion => $score) {
            if ($score > 0.1) {
                $scores[$emotion] = min(1.0, $score + $boost);
            }
        }
    }
    
    // Detectar negaciones simples que podrían afectar positive
    $negatorCount = 0;
    foreach ($negators as $negator) {
        $negatorCount += mb_substr_count($text, $negator);
    }
    
    // Si hay muchas negaciones, reducir positive y aumentar negative
    if ($negatorCount >= 2) {
        $scores['positive'] = max(0, $scores['positive'] - 0.2);
        $scores['negative'] = min(1.0, $scores['negative'] + 0.1);
    }
    
    return $scores;
}

/**
 * Obtiene la emoción dominante de un análisis
 * 
 * @param array $scores Scores de sentimiento
 * @return string Nombre de la emoción dominante
 */
function getDominantEmotion($scores) {
    $dominantEmotion = 'neutral';
    $maxScore = 0.2; // Umbral mínimo para considerar una emoción dominante
    
    foreach ($scores as $emotion => $score) {
        if ($score > $maxScore) {
            $maxScore = $score;
            $dominantEmotion = $emotion;
        }
    }
    
    return $dominantEmotion;
}

/**
 * Genera un resumen textual del estado emocional
 * 
 * @param array $scores Scores de sentimiento
 * @return string Resumen en español
 */
function getSentimentSummary($scores) {
    $dominant = getDominantEmotion($scores);
    
    $summaries = [
        'positive' => 'Estado de ánimo positivo',
        'negative' => 'Estado de ánimo bajo',
        'anxiety' => 'Señales de ansiedad',
        'sadness' => 'Señales de tristeza',
        'anger' => 'Señales de frustración/enojo',
        'neutral' => 'Estado emocional neutral'
    ];
    
    $summary = $summaries[$dominant] ?? 'Estado no determinado';
    
    // Agregar nivel de intensidad
    $dominantScore = $scores[$dominant] ?? 0;
    if ($dominantScore > 0.7) {
        $summary .= ' (intenso)';
    } elseif ($dominantScore > 0.4) {
        $summary .= ' (moderado)';
    } elseif ($dominantScore > 0.2) {
        $summary .= ' (leve)';
    }
    
    return $summary;
}

/**
 * Calcula score de bienestar general (inverso de emociones negativas)
 * 
 * @param array $scores Scores de sentimiento
 * @return float Score de bienestar (0-1)
 */
function getWellbeingScore($scores) {
    $positiveWeight = ($scores['positive'] ?? 0) * 1.5;
    $negativeWeight = ($scores['negative'] ?? 0) + 
                      ($scores['anxiety'] ?? 0) + 
                      ($scores['sadness'] ?? 0) + 
                      ($scores['anger'] ?? 0);
    
    $negativeWeight = $negativeWeight / 4; // Promedio de negativos
    
    // Fórmula: positivo - negativo, normalizado
    $wellbeing = ($positiveWeight - $negativeWeight + 1) / 2;
    
    return round(min(1.0, max(0.0, $wellbeing)), 2);
}
