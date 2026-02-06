<?php
/**
 * MENTTA - Test de Conexión a Base de Datos
 * Ejecutar desde navegador o CLI: php test/test-db.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>\n";
echo "===========================================\n";
echo "   MENTTA - Test de Base de Datos\n";
echo "===========================================\n\n";

// Cargar dependencias
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

try {
    // Test 1: Conexión
    echo "1. Probando conexión a base de datos...\n";
    $db = getDB();
    echo "   ✅ Conexión exitosa a MySQL\n\n";
    
    // Test 2: Listar tablas
    echo "2. Tablas en la base de datos 'mentta':\n";
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "   ⚠️ No hay tablas. ¿Ejecutaste schema.sql?\n\n";
    } else {
        foreach ($tables as $table) {
            $count = $db->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
            echo "   - {$table}: {$count} registros\n";
        }
        echo "\n";
    }
    
    // Test 3: Contar usuarios
    echo "3. Verificando datos de prueba:\n";
    
    $psych = $db->query("SELECT COUNT(*) FROM users WHERE role = 'psychologist'")->fetchColumn();
    $patients = $db->query("SELECT COUNT(*) FROM users WHERE role = 'patient'")->fetchColumn();
    $conversations = $db->query("SELECT COUNT(*) FROM conversations")->fetchColumn();
    $alerts = $db->query("SELECT COUNT(*) FROM alerts")->fetchColumn();
    $memories = $db->query("SELECT COUNT(*) FROM patient_memory")->fetchColumn();
    
    echo "   - Psicólogos: {$psych}\n";
    echo "   - Pacientes: {$patients}\n";
    echo "   - Conversaciones: {$conversations}\n";
    echo "   - Memorias: {$memories}\n";
    echo "   - Alertas: {$alerts}\n\n";
    
    if ($psych >= 2 && $patients >= 5) {
        echo "   ✅ Datos de prueba cargados correctamente\n\n";
    } else {
        echo "   ⚠️ Datos incompletos. ¿Ejecutaste seed.sql?\n\n";
    }
    
    // Test 4: Verificar relaciones
    echo "4. Verificando relaciones (FK):\n";
    
    $links = $db->query("SELECT COUNT(*) FROM patient_psychologist_link")->fetchColumn();
    $contacts = $db->query("SELECT COUNT(*) FROM emergency_contacts")->fetchColumn();
    
    echo "   - Enlaces paciente-psicólogo: {$links}\n";
    echo "   - Contactos de emergencia: {$contacts}\n\n";
    
    // Test 5: Probar funciones helper
    echo "5. Probando funciones helper de DB:\n";
    
    $user = dbFetchOne("SELECT id, name, email FROM users WHERE role = 'patient' LIMIT 1");
    if ($user) {
        echo "   ✅ dbFetchOne() funciona: {$user['name']}\n";
    }
    
    $users = dbFetchAll("SELECT id, name FROM users LIMIT 3");
    if (count($users) > 0) {
        echo "   ✅ dbFetchAll() funciona: " . count($users) . " registros\n";
    }
    
    echo "\n===========================================\n";
    echo "   ✅ TODOS LOS TESTS PASARON\n";
    echo "===========================================\n";
    
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n\n";
    echo "Posibles soluciones:\n";
    echo "1. Verificar que MySQL esté corriendo\n";
    echo "2. Verificar credenciales en includes/config.php\n";
    echo "3. Ejecutar database/schema.sql\n";
    echo "4. Ejecutar database/seed.sql\n";
}

echo "</pre>";
