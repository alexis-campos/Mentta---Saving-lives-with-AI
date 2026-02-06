<?php
/**
 * MENTTA - Test de Sistema de Alertas
 * Pruebas para verificar el funcionamiento del sistema de alertas
 */

require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/risk-detector.php';
require_once '../includes/alert-system.php';

// En navegador, mostrar resultados legibles
header('Content-Type: text/html; charset=utf-8');

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Sistema de Alertas - Mentta</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-800 mb-8">🧪 Test de Sistema de Alertas</h1>
        
        <?php
        $tests = [];
        
        // ==================================================
        // TEST 1: Detección de Riesgo
        // ==================================================
        echo '<div class="bg-white rounded-lg shadow-sm p-6 mb-6">';
        echo '<h2 class="text-xl font-semibold mb-4">1. Detección de Riesgo</h2>';
        
        $testMessages = [
            'Me siento un poco triste hoy' => 'low',
            'Estoy muy estresado con el trabajo' => 'medium',
            'Ya no quiero seguir viviendo' => 'critical',
            'Estoy pensando en quitarme la vida' => 'critical',
            'Quiero hacerme daño' => 'high',
            'Hoy fue un buen día' => 'none',
        ];
        
        $riskTestsPassed = 0;
        foreach ($testMessages as $msg => $expectedLevel) {
            $detected = detectRiskLevel($msg);
            $passed = ($detected === $expectedLevel) || 
                      (in_array($expectedLevel, ['high', 'critical']) && in_array($detected, ['high', 'critical']));
            
            if ($passed) $riskTestsPassed++;
            
            $color = $passed ? 'green' : 'red';
            echo "<div class='flex items-center gap-2 mb-2'>";
            echo "<span class='w-6 h-6 rounded-full bg-{$color}-500 flex items-center justify-center text-white text-xs'>";
            echo $passed ? '✓' : '✗';
            echo "</span>";
            echo "<span class='text-gray-600'>\"" . htmlspecialchars($msg) . "\"</span>";
            echo "<span class='ml-auto text-sm'><span class='text-gray-400'>Esperado:</span> {$expectedLevel} ";
            echo "<span class='text-gray-400'>| Detectado:</span> <strong class='text-{$color}-600'>{$detected}</strong></span>";
            echo "</div>";
        }
        
        $tests['risk_detection'] = $riskTestsPassed >= 4;
        echo '</div>';
        
        // ==================================================
        // TEST 2: Función shouldTriggerAlert
        // ==================================================
        echo '<div class="bg-white rounded-lg shadow-sm p-6 mb-6">';
        echo '<h2 class="text-xl font-semibold mb-4">2. Trigger de Alertas</h2>';
        
        $triggerTests = [
            'none' => false,
            'low' => false,
            'medium' => false,
            'high' => true,
            'critical' => true
        ];
        
        $triggerPassed = 0;
        foreach ($triggerTests as $level => $expected) {
            $result = shouldTriggerAlert($level);
            $passed = ($result === $expected);
            if ($passed) $triggerPassed++;
            
            $color = $passed ? 'green' : 'red';
            echo "<div class='flex items-center gap-2 mb-2'>";
            echo "<span class='w-6 h-6 rounded-full bg-{$color}-500 flex items-center justify-center text-white text-xs'>";
            echo $passed ? '✓' : '✗';
            echo "</span>";
            echo "<span>Level: <strong>{$level}</strong></span>";
            echo "<span class='ml-auto'>Trigger: " . ($result ? 'SÍ' : 'NO') . "</span>";
            echo "</div>";
        }
        
        $tests['trigger_alerts'] = $triggerPassed === 5;
        echo '</div>';
        
        // ==================================================
        // TEST 3: Creación de Alerta (simulada)
        // ==================================================
        echo '<div class="bg-white rounded-lg shadow-sm p-6 mb-6">';
        echo '<h2 class="text-xl font-semibold mb-4">3. Creación de Alertas (Simulación)</h2>';
        
        try {
            // Verificar que la función existe
            if (function_exists('createRiskAlert')) {
                echo '<div class="flex items-center gap-2 mb-2">';
                echo '<span class="w-6 h-6 rounded-full bg-green-500 flex items-center justify-center text-white text-xs">✓</span>';
                echo '<span>Función createRiskAlert existe</span>';
                echo '</div>';
                $tests['create_alert_exists'] = true;
            } else {
                echo '<div class="flex items-center gap-2 mb-2">';
                echo '<span class="w-6 h-6 rounded-full bg-red-500 flex items-center justify-center text-white text-xs">✗</span>';
                echo '<span class="text-red-600">Función createRiskAlert NO encontrada</span>';
                echo '</div>';
                $tests['create_alert_exists'] = false;
            }
            
            // Verificar funciones auxiliares
            $auxiliaryFunctions = ['acknowledgeAlert', 'getPendingAlerts', 'notifyEmergencyContacts'];
            foreach ($auxiliaryFunctions as $fn) {
                $exists = function_exists($fn);
                $color = $exists ? 'green' : 'yellow';
                $icon = $exists ? '✓' : '⚠';
                echo '<div class="flex items-center gap-2 mb-2">';
                echo "<span class='w-6 h-6 rounded-full bg-{$color}-500 flex items-center justify-center text-white text-xs'>{$icon}</span>";
                echo "<span>Función {$fn}: " . ($exists ? 'Disponible' : 'No encontrada') . "</span>";
                echo '</div>';
            }
            
        } catch (Exception $e) {
            echo '<div class="text-red-600">Error: ' . $e->getMessage() . '</div>';
            $tests['create_alert_exists'] = false;
        }
        
        echo '</div>';
        
        // ==================================================
        // TEST 4: Conexión a BD
        // ==================================================
        echo '<div class="bg-white rounded-lg shadow-sm p-6 mb-6">';
        echo '<h2 class="text-xl font-semibold mb-4">4. Verificación de Base de Datos</h2>';
        
        try {
            $db = getDB();
            
            // Verificar tabla alerts
            $stmt = $db->query("SHOW TABLES LIKE 'alerts'");
            $tableExists = $stmt->rowCount() > 0;
            
            $color = $tableExists ? 'green' : 'red';
            echo '<div class="flex items-center gap-2 mb-2">';
            echo "<span class='w-6 h-6 rounded-full bg-{$color}-500 flex items-center justify-center text-white text-xs'>";
            echo $tableExists ? '✓' : '✗';
            echo "</span>";
            echo "<span>Tabla 'alerts': " . ($tableExists ? 'Existe' : 'NO existe') . "</span>";
            echo '</div>';
            
            $tests['db_alerts_table'] = $tableExists;
            
            // Verificar tabla emergency_contacts
            $stmt = $db->query("SHOW TABLES LIKE 'emergency_contacts'");
            $contactsExists = $stmt->rowCount() > 0;
            
            $color = $contactsExists ? 'green' : 'yellow';
            echo '<div class="flex items-center gap-2 mb-2">';
            echo "<span class='w-6 h-6 rounded-full bg-{$color}-500 flex items-center justify-center text-white text-xs'>";
            echo $contactsExists ? '✓' : '⚠';
            echo "</span>";
            echo "<span>Tabla 'emergency_contacts': " . ($contactsExists ? 'Existe' : 'No encontrada') . "</span>";
            echo '</div>';
            
        } catch (Exception $e) {
            echo '<div class="text-red-600">Error de BD: ' . $e->getMessage() . '</div>';
            $tests['db_alerts_table'] = false;
        }
        
        echo '</div>';
        
        // ==================================================
        // RESUMEN
        // ==================================================
        $passed = array_filter($tests, fn($v) => $v === true);
        $total = count($tests);
        $passedCount = count($passed);
        $allPassed = $passedCount === $total;
        
        $bgColor = $allPassed ? 'bg-green-100 border-green-500' : 'bg-yellow-100 border-yellow-500';
        $textColor = $allPassed ? 'text-green-800' : 'text-yellow-800';
        
        echo "<div class='border-l-4 {$bgColor} p-6 rounded-r-lg'>";
        echo "<h2 class='text-xl font-bold {$textColor} mb-2'>Resumen</h2>";
        echo "<p class='{$textColor}'>{$passedCount}/{$total} tests pasados</p>";
        
        if ($allPassed) {
            echo '<p class="text-green-600 mt-2 font-semibold">✅ Sistema de Alertas listo para producción</p>';
        } else {
            echo '<p class="text-yellow-700 mt-2">⚠️ Algunos tests fallaron. Revisa la configuración.</p>';
        }
        
        echo '</div>';
        ?>
        
        <div class="mt-8 text-center text-gray-500 text-sm">
            <p>Mentta Alert System Test — <?= date('Y-m-d H:i:s') ?></p>
        </div>
    </div>
</body>
</html>
