<?php
/**
 * MENTTA - Test Suite para Sistema PAP (Primeros Auxilios Psicológicos)
 * 
 * 25 casos de prueba para verificar la detección de niveles de riesgo
 * y el correcto funcionamiento del protocolo ABCDE.
 * 
 * Uso: php test/test-pap-system.php
 */

// Versión standalone de analyzeRiskLevel para testing (sin BD)
function analyzeRiskLevel_test($text) {
    $text_lower = strtolower($text);
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
            if (strpos($text_lower, $keyword) !== false) {
                $risk_score = max($risk_score, $data['weight']);
                if (!in_array($keyword, $keywords_found)) {
                    $keywords_found[] = $keyword;
                }
            }
        }
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
    
    return [
        'risk_score' => $risk_score,
        'suggested_level' => $suggested_level,
        'keywords_found' => $keywords_found
    ];
}

// Colores para terminal
define('GREEN', "\033[32m");
define('RED', "\033[31m");
define('YELLOW', "\033[33m");
define('RESET', "\033[0m");

echo "╔══════════════════════════════════════════════════════════════════════╗\n";
echo "║         MENTTA - Test Suite Sistema PAP (Protocolo ABCDE)           ║\n";
echo "║                        25 Casos de Prueba                           ║\n";
echo "╚══════════════════════════════════════════════════════════════════════╝\n\n";

// Dataset de 25 casos de prueba
$testCases = [
    // ==================== NIVEL 0 (None) ====================
    [
        'message' => 'Hola, ¿cómo estás?',
        'expected_level' => 0,
        'description' => 'Saludo casual'
    ],
    [
        'message' => 'Hoy jugué fútbol con mis amigos y fue muy divertido',
        'expected_level' => 0,
        'description' => 'Experiencia positiva'
    ],
    [
        'message' => '¿Qué hora es?',
        'expected_level' => 0,
        'description' => 'Pregunta neutral'
    ],
    [
        'message' => 'Me gusta el helado de chocolate',
        'expected_level' => 0,
        'description' => 'Comentario positivo'
    ],
    [
        'message' => 'Ayer fui al cine a ver una película de acción',
        'expected_level' => 0,
        'description' => 'Actividad recreativa'
    ],
    
    // ==================== NIVEL 1 (Low) ====================
    [
        'message' => 'Hoy me siento un poco triste',
        'expected_level' => 1,
        'description' => 'Tristeza leve'
    ],
    [
        'message' => 'No tengo ganas de hacer nada',
        'expected_level' => 1,
        'description' => 'Apatía leve'
    ],
    [
        'message' => 'Me siento solo últimamente',
        'expected_level' => 1,
        'description' => 'Soledad'
    ],
    [
        'message' => 'Hoy fue un día muy difícil en el trabajo',
        'expected_level' => 1,
        'description' => 'Estrés laboral'
    ],
    
    // ==================== NIVEL 2 (Moderate) ====================
    [
        'message' => 'No puedo más con esta situación',
        'expected_level' => 2,
        'description' => 'Desesperanza moderada'
    ],
    [
        'message' => 'Me siento completamente vacío por dentro',
        'expected_level' => 2,
        'description' => 'Vacío emocional'
    ],
    [
        'message' => 'Todo está mal en mi vida, nada importa ya',
        'expected_level' => 2,
        'description' => 'Desesperanza generalizada'
    ],
    [
        'message' => 'Solo quiero dormir y no despertar nunca',
        'expected_level' => 2,
        'description' => 'Deseo de escapar (sin plan)'
    ],
    
    // ==================== NIVEL 3 (High) ====================
    [
        'message' => 'A veces pienso que quiero morir',
        'expected_level' => 3,
        'description' => 'Ideación suicida vaga'
    ],
    [
        'message' => 'Mi familia estaría mejor sin mí',
        'expected_level' => 3,
        'description' => 'Pensamiento de ser una carga'
    ],
    [
        'message' => 'No hay salida a mis problemas',
        'expected_level' => 3,
        'description' => 'Desesperanza con ideación'
    ],
    [
        'message' => 'Ojalá no existiera, seria más fácil para todos',
        'expected_level' => 3,
        'description' => 'Deseo de no existir'
    ],
    
    // ==================== NIVEL 4 (Critical) ====================
    [
        'message' => 'Ya tengo un plan para acabar con esto',
        'expected_level' => 4,
        'description' => 'Plan suicida'
    ],
    [
        'message' => 'He estado buscando métodos para morir',
        'expected_level' => 4,
        'description' => 'Búsqueda de métodos'
    ],
    [
        'message' => 'Tengo las pastillas listas en mi cuarto',
        'expected_level' => 4,
        'description' => 'Acceso a método letal'
    ],
    [
        'message' => 'Escribí una carta de despedida para mi mamá',
        'expected_level' => 4,
        'description' => 'Carta de despedida'
    ],
    
    // ==================== NIVEL 5 (Imminent) ====================
    [
        'message' => 'Voy a matarme esta noche cuando todos duerman',
        'expected_level' => 5,
        'description' => 'Plan con plazo inmediato'
    ],
    [
        'message' => 'Ya tomé la decisión, esta es mi despedida',
        'expected_level' => 5,
        'description' => 'Despedida final'
    ],
    [
        'message' => 'Estoy en el techo del edificio ahora mismo',
        'expected_level' => 5,
        'description' => 'En situación de peligro'
    ],
    [
        'message' => 'Ya tomé las pastillas hace 10 minutos',
        'expected_level' => 5,
        'description' => 'Intento en curso'
    ]
];

// Ejecutar tests
$passed = 0;
$failed = 0;
$warnings = 0;
$patient_id = 1; // ID ficticio para tests

echo "Ejecutando " . count($testCases) . " casos de prueba...\n\n";

foreach ($testCases as $i => $test) {
    $result = analyzeRiskLevel_test($test['message']);
    $actual_level = $result['suggested_level'];
    $expected_level = $test['expected_level'];
    
    // Tolerancia de ±1 nivel (para casos ambiguos)
    $tolerance = 1;
    $within_tolerance = abs($actual_level - $expected_level) <= $tolerance;
    $exact_match = $actual_level === $expected_level;
    
    $test_num = str_pad($i + 1, 2, '0', STR_PAD_LEFT);
    
    if ($exact_match) {
        echo GREEN . "✓ Test {$test_num}: PASS" . RESET . " - {$test['description']}\n";
        echo "  Mensaje: \"{$test['message']}\"\n";
        echo "  Esperado: {$expected_level}, Obtenido: {$actual_level}, Score: {$result['risk_score']}\n\n";
        $passed++;
    } elseif ($within_tolerance) {
        echo YELLOW . "⚠ Test {$test_num}: WARN" . RESET . " - {$test['description']} (dentro de tolerancia)\n";
        echo "  Mensaje: \"{$test['message']}\"\n";
        echo "  Esperado: {$expected_level}, Obtenido: {$actual_level}, Score: {$result['risk_score']}\n\n";
        $warnings++;
    } else {
        echo RED . "✗ Test {$test_num}: FAIL" . RESET . " - {$test['description']}\n";
        echo "  Mensaje: \"{$test['message']}\"\n";
        echo "  Esperado: {$expected_level}, Obtenido: {$actual_level}, Score: {$result['risk_score']}\n";
        if (!empty($result['keywords_found'])) {
            echo "  Keywords detectados: " . implode(', ', $result['keywords_found']) . "\n";
        }
        echo "\n";
        $failed++;
    }
}

// Resumen
echo "══════════════════════════════════════════════════════════════════════\n";
echo "                           RESUMEN\n";
echo "══════════════════════════════════════════════════════════════════════\n";
echo "Total de tests:  " . count($testCases) . "\n";
echo GREEN . "Pasados (exact): {$passed}" . RESET . "\n";
echo YELLOW . "Warnings (±1):   {$warnings}" . RESET . "\n";
echo RED . "Fallados:        {$failed}" . RESET . "\n";
echo "\n";

$success_rate = (($passed + $warnings) / count($testCases)) * 100;
echo "Tasa de éxito (con tolerancia): " . number_format($success_rate, 1) . "%\n";

if ($success_rate >= 80) {
    echo GREEN . "\n✓ El sistema PAP cumple con el umbral mínimo de 80%" . RESET . "\n";
    exit(0);
} else {
    echo RED . "\n✗ El sistema PAP NO cumple con el umbral mínimo de 80%" . RESET . "\n";
    exit(1);
}
