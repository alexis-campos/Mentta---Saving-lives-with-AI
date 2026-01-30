<?php
/**
 * MENTTA - Test directo de API de Gemini
 * Prueba la conexión con la API para diagnosticar problemas
 */

require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

$apiKey = AI_API_KEY;
$model = AI_MODEL;
$url = AI_API_URL . $model . ':generateContent';

echo "=== DIAGNÓSTICO DE API GEMINI ===\n\n";
echo "URL: $url\n";
echo "API Key (primeros 10 chars): " . substr($apiKey, 0, 10) . "...\n";
echo "Modelo: $model\n\n";

$data = [
    'contents' => [
        [
            'parts' => [
                ['text' => 'Di "hola, funciono correctamente" en español']
            ]
        ]
    ],
    'generationConfig' => [
        'temperature' => 0.7,
        'maxOutputTokens' => 100,
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
        'x-goog-api-key: ' . $apiKey
    ],
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => true
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);

curl_close($ch);

echo "=== RESULTADO ===\n\n";
echo "HTTP Code: $httpCode\n";

if ($curlError) {
    echo "cURL Error: $curlError\n";
} else {
    echo "Response:\n";
    $decoded = json_decode($response, true);
    echo json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
