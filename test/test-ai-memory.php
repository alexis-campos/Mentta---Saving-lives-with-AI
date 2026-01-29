<?php
/**
 * MENTTA - Test de Extracción de Memoria IA
 * Pruebas individuales para evitar rate limiting
 */

require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/ai-analyzer.php';

header('Content-Type: text/html; charset=utf-8');

// Mensajes de prueba
$memoryTests = [
    1 => [
        'message' => 'Mi hermana Ana me contó que perdió su trabajo ayer. Estoy preocupado por ella porque vive en Lima.',
        'description' => 'Familia, trabajo y lugares'
    ],
    2 => [
        'message' => 'Ayer fui al parque con mi mejor amigo Carlos. Nos encontramos con su novia María cerca de la Plaza de Armas.',
        'description' => 'Amigos y lugares públicos'
    ],
    3 => [
        'message' => 'Mi mamá cumple años el próximo lunes y quiero organizarle una fiesta sorpresa en el restaurante donde trabaja mi papá.',
        'description' => 'Eventos familiares y celebraciones'
    ]
];

$selectedTest = isset($_GET['test']) ? (int)$_GET['test'] : null;
$analysis = null;
$duration = 0;

if ($selectedTest && isset($memoryTests[$selectedTest])) {
    $startTime = microtime(true);
    $analysis = analyzeMessageWithAI($memoryTests[$selectedTest]['message'], 1, []);
    $duration = round((microtime(true) - $startTime) * 1000);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Memoria IA - Mentta</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">🧠 Test de Extracción de Memoria</h1>
        <p class="text-gray-600 mb-8">Haz clic en un test para ejecutarlo (uno a la vez para evitar rate limit)</p>
        
        <!-- Botones de selección -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <?php foreach ($memoryTests as $id => $test): ?>
                <a href="?test=<?= $id ?>" 
                   class="block p-4 rounded-lg border-2 transition-all <?= $selectedTest === $id ? 'bg-purple-100 border-purple-500' : 'bg-white border-gray-200 hover:border-purple-300' ?>">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="bg-purple-100 text-purple-800 rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold"><?= $id ?></span>
                        <span class="font-medium text-gray-700"><?= htmlspecialchars($test['description']) ?></span>
                    </div>
                    <p class="text-sm text-gray-500 line-clamp-2">"<?= htmlspecialchars(substr($test['message'], 0, 60)) ?>..."</p>
                </a>
            <?php endforeach; ?>
        </div>
        
        <?php if ($selectedTest && $analysis): ?>
            <?php $memory = $analysis['memory_extraction'] ?? []; ?>
            <?php $isFallback = isset($analysis['_fallback']); ?>
            
            <!-- Resultado del test -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">Resultado Test #<?= $selectedTest ?></h2>
                    <div class="flex items-center gap-2">
                        <span class="text-gray-500 text-sm"><?= $duration ?>ms</span>
                        <?php if ($isFallback): ?>
                            <span class="bg-orange-100 text-orange-800 px-2 py-1 rounded text-xs">Fallback</span>
                        <?php else: ?>
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">✓ IA</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <p class="text-gray-600 mb-6 p-3 bg-gray-50 rounded-lg italic">
                    "<?= htmlspecialchars($memoryTests[$selectedTest]['message']) ?>"
                </p>
                
                <!-- Grid de resultados -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <!-- Personas -->
                    <div class="bg-green-50 rounded-lg p-4">
                        <h4 class="font-medium text-green-800 mb-3 flex items-center gap-2">
                            <span>👥</span> Personas
                        </h4>
                        <ul class="text-sm text-green-700 space-y-1">
                            <?php if (!empty($memory['people'])): ?>
                                <?php foreach ($memory['people'] as $person): ?>
                                    <li class="flex items-center gap-2">
                                        <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                                        <?= htmlspecialchars($person) ?>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="text-gray-400 italic">Ninguna</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    
                    <!-- Relaciones -->
                    <div class="bg-blue-50 rounded-lg p-4">
                        <h4 class="font-medium text-blue-800 mb-3 flex items-center gap-2">
                            <span>💑</span> Relaciones
                        </h4>
                        <ul class="text-sm text-blue-700 space-y-1">
                            <?php if (!empty($memory['relationships'])): ?>
                                <?php foreach ($memory['relationships'] as $rel): ?>
                                    <li class="flex items-center gap-2">
                                        <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                                        <?php if (is_array($rel)): ?>
                                            <?= htmlspecialchars($rel['name'] . ' → ' . $rel['relation']) ?>
                                        <?php else: ?>
                                            <?= htmlspecialchars($rel) ?>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="text-gray-400 italic">Ninguna</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    
                    <!-- Eventos -->
                    <div class="bg-yellow-50 rounded-lg p-4">
                        <h4 class="font-medium text-yellow-800 mb-3 flex items-center gap-2">
                            <span>📅</span> Eventos
                        </h4>
                        <ul class="text-sm text-yellow-700 space-y-1">
                            <?php if (!empty($memory['events'])): ?>
                                <?php foreach ($memory['events'] as $event): ?>
                                    <li class="flex items-center gap-2">
                                        <span class="w-2 h-2 bg-yellow-500 rounded-full"></span>
                                        <?= htmlspecialchars($event) ?>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="text-gray-400 italic">Ninguno</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    
                    <!-- Lugares -->
                    <div class="bg-purple-50 rounded-lg p-4">
                        <h4 class="font-medium text-purple-800 mb-3 flex items-center gap-2">
                            <span>📍</span> Lugares
                        </h4>
                        <ul class="text-sm text-purple-700 space-y-1">
                            <?php if (!empty($memory['places'])): ?>
                                <?php foreach ($memory['places'] as $place): ?>
                                    <li class="flex items-center gap-2">
                                        <span class="w-2 h-2 bg-purple-500 rounded-full"></span>
                                        <?= htmlspecialchars($place) ?>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="text-gray-400 italic">Ninguno</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
                
                <!-- Temas -->
                <?php if (!empty($memory['topics'])): ?>
                    <div class="mt-6 pt-4 border-t">
                        <span class="text-gray-500 text-sm mr-2">Temas detectados:</span>
                        <?php foreach ($memory['topics'] as $topic): ?>
                            <span class="inline-block bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-sm mr-2 mb-2">
                                <?= htmlspecialchars($topic) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php elseif (!$selectedTest): ?>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
                <p class="text-blue-700">👆 Selecciona un test arriba para ejecutarlo</p>
                <p class="text-blue-600 text-sm mt-2">Espera ~10 segundos entre tests para evitar rate limiting</p>
            </div>
        <?php endif; ?>
        
        <!-- Navigation -->
        <div class="mt-8 flex justify-between items-center">
            <a href="test-ai-analyzer.php" class="text-indigo-600 hover:text-indigo-800 flex items-center gap-2">
                ← Test de Análisis de Mensajes
            </a>
            <a href="test-api.php" class="text-indigo-600 hover:text-indigo-800 flex items-center gap-2">
                Test de Conexión API →
            </a>
        </div>
        
        <div class="mt-8 text-center text-gray-500 text-sm">
            <p>Mentta Memory Extraction Test — v0.3.1 — <?= date('Y-m-d H:i:s') ?></p>
        </div>
    </div>
</body>
</html>
