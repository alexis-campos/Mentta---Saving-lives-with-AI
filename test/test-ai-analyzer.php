<?php
/**
 * MENTTA - Test del Analizador de IA
 * Pruebas para verificar el funcionamiento del análisis contextual
 */

require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/ai-analyzer.php';

header('Content-Type: text/html; charset=utf-8');

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Analizador IA - Mentta</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">🤖 Test de Analizador IA</h1>
        <p class="text-gray-600 mb-8">Prueba del análisis contextual potenciado por IA</p>
        
        <?php
        // Mensajes de prueba con contexto esperado
        $testCases = [
            [
                'message' => 'Me quiero morir de risa con este meme',
                'expected_risk' => 'none',
                'description' => 'Expresión coloquial - NO debería ser riesgo'
            ],
            [
                'message' => 'Últimamente siento que todo es gris, no le encuentro sentido a nada',
                'expected_risk' => 'high',
                'description' => 'Desesperanza implícita - DEBERÍA ser riesgo'
            ],
            [
                'message' => 'Hoy fue un buen día en el trabajo',
                'expected_risk' => 'none',
                'description' => 'Mensaje positivo - Sin riesgo'
            ],
            [
                'message' => 'Estoy pensando en quitarme la vida',
                'expected_risk' => 'critical',
                'description' => 'Ideación suicida directa - CRÍTICO'
            ],
            [
                'message' => 'Mi hermana Ana me contó que tuvo un mal día',
                'expected_risk' => 'none',
                'description' => 'Habla de terceros - Sin riesgo del paciente'
            ]
        ];
        
        echo '<div class="space-y-6">';
        
        foreach ($testCases as $index => $test) {
            echo '<div class="bg-white rounded-lg shadow-sm p-6">';
            echo '<div class="flex items-start gap-4">';
            echo '<span class="bg-indigo-100 text-indigo-800 rounded-full w-8 h-8 flex items-center justify-center font-bold">' . ($index + 1) . '</span>';
            echo '<div class="flex-1">';
            
            echo '<p class="text-gray-400 text-sm mb-1">' . htmlspecialchars($test['description']) . '</p>';
            echo '<p class="text-gray-800 font-medium mb-3">"' . htmlspecialchars($test['message']) . '"</p>';
            
            // Realizar análisis
            $startTime = microtime(true);
            $analysis = analyzeMessageWithAI($test['message'], 1, []); // Patient ID 1 para test
            $duration = round((microtime(true) - $startTime) * 1000);
            
            $actualRisk = $analysis['risk_assessment']['level'] ?? 'error';
            $isRealRisk = $analysis['risk_assessment']['is_real_risk'] ?? false;
            $reasoning = $analysis['risk_assessment']['reasoning'] ?? 'No disponible';
            
            // Determinar si pasó
            $passed = false;
            if ($test['expected_risk'] === 'none' && !$isRealRisk) {
                $passed = true;
            } elseif ($test['expected_risk'] !== 'none' && $isRealRisk) {
                $passed = true;
            } elseif ($actualRisk === $test['expected_risk']) {
                $passed = true;
            }
            
            // Badge de resultado
            $badgeColor = $passed ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
            $badgeText = $passed ? '✓ Correcto' : '✗ Revisar';
            
            echo '<div class="grid grid-cols-2 gap-4 text-sm">';
            
            // Columna izquierda - Resultados
            echo '<div class="space-y-2">';
            echo '<div><span class="text-gray-500">Esperado:</span> <span class="font-mono">' . $test['expected_risk'] . '</span></div>';
            echo '<div><span class="text-gray-500">Detectado:</span> <span class="font-mono font-bold">' . $actualRisk . '</span></div>';
            echo '<div><span class="text-gray-500">¿Riesgo real?:</span> ' . ($isRealRisk ? '🔴 Sí' : '🟢 No') . '</div>';
            echo '<div><span class="text-gray-500">Tiempo:</span> ' . $duration . 'ms</div>';
            echo '</div>';
            
            // Columna derecha - Análisis detallado
            echo '<div class="space-y-2">';
            if (isset($analysis['sentiment'])) {
                $dominant = $analysis['sentiment']['dominant_emotion'] ?? 'neutral';
                echo '<div><span class="text-gray-500">Emoción:</span> ' . ucfirst($dominant) . '</div>';
            }
            if (isset($analysis['safe_life_mode'])) {
                $slm = $analysis['safe_life_mode']['activate'] ? '🛡️ Activado' : 'No';
                echo '<div><span class="text-gray-500">Safe Life Mode:</span> ' . $slm . '</div>';
            }
            echo '</div>';
            
            echo '</div>';
            
            // Razonamiento de la IA
            echo '<div class="mt-3 p-3 bg-gray-50 rounded text-sm text-gray-600">';
            echo '<span class="font-medium">Razonamiento IA:</span> ' . htmlspecialchars($reasoning);
            echo '</div>';
            
            // Badge de resultado
            echo '<div class="mt-3">';
            echo '<span class="' . $badgeColor . ' px-3 py-1 rounded-full text-sm font-medium">' . $badgeText . '</span>';
            
            // Indicar si es modo dev/fallback
            if (isset($analysis['_dev_mode'])) {
                echo '<span class="ml-2 bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">Modo Dev</span>';
            }
            if (isset($analysis['_fallback'])) {
                echo '<span class="ml-2 bg-orange-100 text-orange-800 px-2 py-1 rounded text-xs">Fallback</span>';
            }
            echo '</div>';
            
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
        
        echo '</div>';
        ?>
        
        <!-- Navigation -->
        <div class="mt-8 flex justify-between items-center">
            <a href="test-api.php" class="text-indigo-600 hover:text-indigo-800 flex items-center gap-2">
                ← Test de Conexión API
            </a>
            <a href="test-ai-memory.php" class="text-indigo-600 hover:text-indigo-800 flex items-center gap-2">
                Test de Memoria →
            </a>
        </div>
        
        <!-- Info del API -->
        <div class="mt-8 bg-indigo-50 border border-indigo-200 rounded-lg p-4">
            <h3 class="font-medium text-indigo-800 mb-2">ℹ️ Información</h3>
            <ul class="text-sm text-indigo-700 space-y-1">
                <li>• Si ves "Modo Dev", significa que no hay API key configurada y se usan respuestas simuladas</li>
                <li>• Si ves "Fallback", la IA falló y se usó el sistema de keywords</li>
                <li>• Los tiempos de respuesta reales con API son 500-2000ms</li>
                <li>• El análisis contextual puede detectar riesgos que los keywords NO detectan</li>
            </ul>
        </div>
        
        <div class="mt-8 text-center text-gray-500 text-sm">
            <p>Mentta AI Analyzer Test — v0.3.1 — <?= date('Y-m-d H:i:s') ?></p>
        </div>
    </div>
</body>
</html>
