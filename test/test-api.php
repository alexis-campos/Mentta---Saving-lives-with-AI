<?php
/**
 * MENTTA - Test de Conexión API de Gemini
 * Diagnóstico directo de la API (actualizado con x-goog-api-key header)
 */

require_once '../includes/config.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Test API Gemini - Diagnóstico</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-3xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">🔧 Diagnóstico de API Gemini</h1>
        
        <?php
        // Mostrar configuración actual
        echo '<div class="bg-white rounded-lg shadow p-6 mb-6">';
        echo '<h2 class="font-semibold text-lg mb-4">📋 Configuración Actual</h2>';
        echo '<div class="grid grid-cols-2 gap-4 text-sm">';
        echo '<div><strong>AI_API_URL:</strong></div><div class="font-mono text-xs break-all">' . AI_API_URL . '</div>';
        echo '<div><strong>AI_MODEL:</strong></div><div class="font-mono">' . AI_MODEL . '</div>';
        echo '<div><strong>API Key (primeros 10 chars):</strong></div><div class="font-mono">' . substr(AI_API_KEY, 0, 10) . '***</div>';
        echo '<div><strong>URL Completa:</strong></div><div class="font-mono text-xs break-all">' . AI_API_URL . AI_MODEL . ':generateContent</div>';
        echo '<div><strong>Método de Auth:</strong></div><div class="font-mono text-green-600">x-goog-api-key (header)</div>';
        echo '</div>';
        echo '</div>';
        
        // Hacer test de conexión real
        echo '<div class="bg-white rounded-lg shadow p-6 mb-6">';
        echo '<h2 class="font-semibold text-lg mb-4">🧪 Test de Conexión</h2>';
        
        // URL sin API key (va en header)
        $url = AI_API_URL . AI_MODEL . ':generateContent';
        
        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => 'Responde solo con: "OK"']
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.1,
                'maxOutputTokens' => 10,
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
                'x-goog-api-key: ' . AI_API_KEY  // API key en header
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        
        $startTime = microtime(true);
        $response = curl_exec($ch);
        $duration = round((microtime(true) - $startTime) * 1000);
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        
        curl_close($ch);
        
        // Mostrar resultados
        echo '<div class="space-y-3">';
        
        // HTTP Code
        $codeColor = $httpCode === 200 ? 'green' : 'red';
        echo "<div class='flex items-center gap-2'>";
        echo "<span class='w-4 h-4 rounded-full bg-{$codeColor}-500'></span>";
        echo "<span>HTTP Code: <strong class='text-{$codeColor}-600'>{$httpCode}</strong></span>";
        echo "<span class='text-gray-500 text-sm'>({$duration}ms)</span>";
        echo "</div>";
        
        // Curl Error
        if ($curlError) {
            echo "<div class='text-red-600'>❌ cURL Error: {$curlError}</div>";
        }
        
        // Response
        echo '<div class="mt-4"><strong>Respuesta del servidor:</strong></div>';
        echo '<pre class="bg-gray-800 text-green-400 p-4 rounded text-xs overflow-x-auto max-h-64 overflow-y-auto">';
        
        $responseData = json_decode($response, true);
        if ($responseData) {
            echo htmlspecialchars(json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } else {
            echo htmlspecialchars($response);
        }
        echo '</pre>';
        
        // Si funciona, mostrar éxito
        if ($httpCode === 200) {
            echo '<div class="mt-4 p-4 bg-green-50 border border-green-200 rounded">';
            echo '<h3 class="font-semibold text-green-800">✅ ¡API Funcionando Correctamente!</h3>';
            echo '<p class="text-sm text-green-700 mt-2">La conexión con Gemini API está establecida.</p>';
            echo '</div>';
        }
        
        // Si hay error, mostrar diagnóstico
        if ($httpCode !== 200) {
            echo '<div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded">';
            echo '<h3 class="font-semibold text-yellow-800">💡 Diagnóstico:</h3>';
            echo '<ul class="mt-2 text-sm text-yellow-700 space-y-1">';
            
            if ($httpCode === 404) {
                echo '<li>• <strong>404</strong>: El modelo no existe. Verificar nombre del modelo.</li>';
            } elseif ($httpCode === 429) {
                echo '<li>• <strong>429</strong>: Límite de requests alcanzado. Espera 1-2 minutos.</li>';
            } elseif ($httpCode === 400) {
                echo '<li>• <strong>400</strong>: Error en el formato de la petición.</li>';
            } elseif ($httpCode === 401 || $httpCode === 403) {
                echo '<li>• <strong>' . $httpCode . '</strong>: API key inválida o sin permisos.</li>';
            }
            
            echo '</ul>';
            echo '</div>';
        }
        
        echo '</div>';
        echo '</div>';
        
        // Info adicional
        echo '<div class="bg-blue-50 border border-blue-200 rounded-lg p-4">';
        echo '<h3 class="font-semibold text-blue-800">ℹ️ Información</h3>';
        echo '<ul class="mt-2 text-sm text-blue-700 space-y-1">';
        echo '<li>• Si ves HTTP 200, la API funciona correctamente</li>';
        echo '<li>• Si ves HTTP 404, el modelo no existe</li>';
        echo '<li>• Si ves HTTP 429, espera 1-2 minutos (rate limit)</li>';
        echo '<li>• Auth method: <code>x-goog-api-key</code> header (per latest Google docs)</li>';
        echo '</ul>';
        echo '</div>';
        ?>
        
        <div class="mt-6 text-center text-gray-500 text-sm">
            Test ejecutado: <?= date('Y-m-d H:i:s') ?>
        </div>
    </div>
</body>
</html>
