<?php
/**
 * MENTTA - Test Chat System
 * Prueba del sistema de chat sin UI
 */

require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/sentiment-analyzer.php';
require_once '../includes/risk-detector.php';
require_once '../includes/memory-parser.php';
require_once '../includes/ai-client.php';

echo "<h1>🧪 Test del Sistema de Chat Mentta</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; max-width: 800px; margin: 40px auto; padding: 20px; }
    .test { background: #f5f5f5; padding: 15px; margin: 10px 0; border-radius: 8px; }
    .pass { border-left: 4px solid #10B981; }
    .fail { border-left: 4px solid #EF4444; }
    .warning { border-left: 4px solid #F59E0B; }
    h2 { color: #374151; margin-top: 30px; }
    pre { background: #1f2937; color: #e5e7eb; padding: 10px; border-radius: 4px; overflow-x: auto; }
    code { font-size: 13px; }
</style>";

// ============================================
// TEST 1: Análisis de Sentimientos
// ============================================
echo "<h2>1️⃣ Análisis de Sentimientos</h2>";

$sentimentTests = [
    "Hoy me siento muy feliz y lleno de energía" => 'positive',
    "Estoy muy triste, todo me sale mal" => 'sadness',
    "Tengo mucho miedo y ansiedad" => 'anxiety',
    "Estoy furioso, no puedo más con esto" => 'anger',
    "El clima está nublado hoy" => 'neutral'
];

foreach ($sentimentTests as $text => $expectedDominant) {
    $result = analyzeSentiment($text);
    $dominant = getDominantEmotion($result);
    $class = $dominant === $expectedDominant ? 'pass' : 'warning';
    
    echo "<div class='test {$class}'>";
    echo "<strong>Texto:</strong> \"{$text}\"<br>";
    echo "<strong>Esperado:</strong> {$expectedDominant} | <strong>Obtenido:</strong> {$dominant}<br>";
    echo "<pre><code>" . json_encode($result, JSON_PRETTY_PRINT) . "</code></pre>";
    echo "</div>";
}

// ============================================
// TEST 2: Detección de Riesgo
// ============================================
echo "<h2>2️⃣ Detección de Riesgo</h2>";

$riskTests = [
    "Hoy tuve un buen día en el trabajo" => 'none',
    "Me siento un poco triste" => 'low',
    "No puedo más con esta situación" => 'medium',
    "Quiero desaparecer, a nadie le importo" => 'high',
    "Quiero matarme, ya tomé la decisión" => 'critical'
];

foreach ($riskTests as $text => $expectedRisk) {
    $result = detectRiskLevel($text);
    $class = $result === $expectedRisk ? 'pass' : 'fail';
    
    echo "<div class='test {$class}'>";
    echo "<strong>Texto:</strong> \"{$text}\"<br>";
    echo "<strong>Esperado:</strong> {$expectedRisk} | <strong>Obtenido:</strong> {$result}";
    
    if (shouldTriggerAlert($result)) {
        echo " <strong style='color: #EF4444;'>⚠️ ALERTA</strong>";
    }
    echo "</div>";
}

// ============================================
// TEST 3: Extracción de Memoria
// ============================================
echo "<h2>3️⃣ Extracción de Memoria</h2>";

$memoryTests = [
    "Mi hermana María me ayudó mucho hoy",
    "Ayer falleció mi abuelo, estoy muy triste",
    "Mi novia Ana terminó conmigo",
    "Me despidieron del trabajo la semana pasada"
];

echo "<div class='test warning'>";
echo "<em>Nota: Las siguientes pruebas solo muestran patrones detectados, no guardan en BD:</em><br><br>";

foreach ($memoryTests as $text) {
    echo "<strong>Texto:</strong> \"{$text}\"<br>";
    
    // Detectar relaciones
    $relPatterns = [
        'madre', 'mamá', 'padre', 'papá', 'hermano', 'hermana',
        'esposo', 'esposa', 'novio', 'novia', 'abuelo', 'abuela'
    ];
    $pattern = '/mi\s+(' . implode('|', $relPatterns) . ')\s+([A-ZÁÉÍÓÚÑ][a-záéíóúñ]+)/u';
    
    if (preg_match_all($pattern, $text, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            echo "→ Relación detectada: <code>{$match[1]}</code> = <code>{$match[2]}</code><br>";
        }
    }
    
    // Detectar eventos
    $eventKeywords = ['falleci', 'murió', 'despidieron', 'terminó'];
    foreach ($eventKeywords as $kw) {
        if (mb_stripos($text, $kw) !== false) {
            echo "→ Evento detectado: <code>{$kw}</code><br>";
        }
    }
    echo "<br>";
}
echo "</div>";

// ============================================
// TEST 4: Cliente de IA
// ============================================
echo "<h2>4️⃣ Cliente de IA (Modo Dev)</h2>";

if (AI_API_KEY === 'TU_API_KEY_AQUI') {
    echo "<div class='test warning'>";
    echo "<strong>⚠️ API Key no configurada</strong> - Usando modo desarrollo<br><br>";
    
    // Simular respuesta
    $testMessage = "Hola, hoy me siento un poco ansioso";
    $sentiment = analyzeSentiment($testMessage);
    
    echo "<strong>Mensaje de prueba:</strong> \"{$testMessage}\"<br>";
    echo "<strong>Sentimiento:</strong> " . json_encode($sentiment) . "<br><br>";
    
    // Probar construcción de prompt
    $patient = ['name' => 'Usuario Test', 'age' => 25];
    $history = [
        ['message' => 'Hola', 'sender' => 'user'],
        ['message' => 'Hola! ¿Cómo estás?', 'sender' => 'ai']
    ];
    $memory = [
        ['memory_type' => 'relationship', 'key_name' => 'hermana', 'value' => 'María']
    ];
    
    $prompt = buildAIPrompt($testMessage, $patient, $history, $memory, $sentiment);
    
    echo "<strong>Prompt generado (primeras 500 caracteres):</strong><br>";
    echo "<pre><code>" . htmlspecialchars(mb_substr($prompt, 0, 500)) . "...</code></pre>";
    
    // Probar respuesta dev
    $devResponse = getDevModeResponse($prompt);
    echo "<strong>Respuesta dev:</strong> \"{$devResponse}\"";
    echo "</div>";
} else {
    echo "<div class='test pass'>";
    echo "<strong>✅ API Key configurada</strong> - El sistema usará Gemini API";
    echo "</div>";
}

// ============================================
// TEST 5: Base de Datos
// ============================================
echo "<h2>5️⃣ Conexión a Base de Datos</h2>";

try {
    $db = getDB();
    
    // Verificar tablas
    $tables = ['users', 'conversations', 'patient_memory', 'alerts', 'rate_limits'];
    $existingTables = [];
    
    foreach ($tables as $table) {
        $stmt = $db->query("SHOW TABLES LIKE '{$table}'");
        if ($stmt->rowCount() > 0) {
            $existingTables[] = $table;
        }
    }
    
    echo "<div class='test pass'>";
    echo "<strong>✅ Conexión exitosa</strong><br>";
    echo "Tablas encontradas: " . implode(', ', $existingTables);
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='test fail'>";
    echo "<strong>❌ Error de conexión:</strong> " . htmlspecialchars($e->getMessage());
    echo "</div>";
}

echo "<hr>";
echo "<p>✅ Pruebas completadas - " . date('Y-m-d H:i:s') . "</p>";
echo "<p><a href='../chat.php' style='color: #6366F1;'>→ Ir al Chat</a></p>";
