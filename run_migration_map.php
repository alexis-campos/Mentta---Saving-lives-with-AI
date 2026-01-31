<?php
/**
 * MENTTA - Migration Runner for Map Feature (Fixed)
 * Execute: http://localhost/Mentta - Salvando vidas con la IA/run_migration_map.php
 */

echo "<h1>Mentta - Database Migration (Map Feature)</h1>";
echo "<pre>";

require_once 'includes/config.php';
require_once 'includes/db.php';

try {
    $db = getDB();
    
    echo "Connecting to database... OK\n\n";
    
    // Step 2: Clean the table - TRUNCATE to remove all existing data
    echo "Step 2: Cleaning existing data...\n";
    $db->exec("TRUNCATE TABLE mental_health_centers");
    echo "✅ Table cleaned successfully!\n\n";
    
    // Step 3: Insert new data
    echo "Step 3: Inserting mental health centers (Lima & Huánuco)...\n";
    
    // Format: [name, address, district, city, lat, lng, phone, email, website, services, has_mentta, emergency_24h, rating]
    $centers = [
        // ═══════════════════════════════════════════════════════════════════════
        // LIMA - Hospitales principales (emergencias 24h)
        // ═══════════════════════════════════════════════════════════════════════
        ['Instituto Nacional de Salud Mental Honorio Delgado-Hideyo Noguchi', 'Jr. Eloy Espinoza 709', 'San Martín de Porres', 'Lima', -12.0234, -77.0856, '01-614-9200', 'informes@insm.gob.pe', 'https://www.insm.gob.pe', 'psiquiatría, psicología, emergencias, hospitalización', false, true, 4.2],
        ['Hospital Hermilio Valdizán', 'Carretera Central Km 3.5', 'Santa Anita', 'Lima', -12.0456, -76.9734, '01-362-0902', 'informes@hhv.gob.pe', 'https://www.hhv.gob.pe', 'psiquiatría, emergencias, hospitalización, rehabilitación', false, true, 4.0],
        ['Hospital Víctor Larco Herrera', 'Av. Del Río 601', 'Magdalena del Mar', 'Lima', -12.0912, -77.0789, '01-261-5516', null, null, 'psiquiatría, hospitalización, emergencias', false, true, 3.8],
        ['Clínica Ricardo Palma - Salud Mental', 'Av. Javier Prado Este 1066', 'San Isidro', 'Lima', -12.0899, -77.0234, '01-224-2224', null, 'https://www.crp.com.pe', 'psiquiatría, psicología, emergencias', false, true, 4.4],
        ['Clínica Psiquiátrica Delgado', 'Av. Arequipa 3456', 'San Isidro', 'Lima', -12.0989, -77.0345, '01-421-7654', 'citas@clinicadelgado.pe', null, 'psiquiatría, hospitalización, tratamiento adicciones', false, true, 4.3],
        ['ESSALUD - Hospital Rebagliati - Salud Mental', 'Av. Rebagliati 490', 'Jesús María', 'Lima', -12.0789, -77.0412, '01-265-4901', null, null, 'psiquiatría, psicología, emergencias', false, true, 3.9],
        
        // LIMA - Clínicas privadas (sin emergencias 24h)
        ['Clínica San Felipe - Salud Mental', 'Av. Gregorio Escobedo 650', 'Jesús María', 'Lima', -12.0723, -77.0503, '01-219-0000', 'citas@sanfelipe.com.pe', 'https://www.sanfelipe.com.pe', 'psicología, psiquiatría, terapia familiar', false, false, 4.5],
        ['Clínica Montesur - Área de Psiquiatría', 'Av. Tomás Marsano 1280', 'Surquillo', 'Lima', -12.1123, -77.0056, '01-207-4000', 'citas@montesur.com.pe', 'https://www.montesur.com.pe', 'psicología, psiquiatría, neuropsicología', true, false, 4.6],
        
        // LIMA - Centros comunitarios con Mentta
        ['Centro de Salud Mental Comunitario San Juan de Miraflores', 'Av. Guillermo Billinghurst 1069', 'San Juan de Miraflores', 'Lima', -12.1567, -76.9745, '01-276-5641', null, null, 'psicología, terapia grupal, atención comunitaria', true, false, 4.1],
        ['Centro de Salud Mental Comunitario Villa El Salvador', 'Av. César Vallejo s/n Sector 2', 'Villa El Salvador', 'Lima', -12.2134, -76.9456, '01-287-3421', null, null, 'psicología, terapia familiar, atención infantil', true, false, 4.0],
        ['Centro de Salud Mental Comunitario Comas', 'Av. Túpac Amaru Km 11', 'Comas', 'Lima', -11.9456, -77.0567, '01-537-2890', null, null, 'psicología, psiquiatría, terapia grupal', true, false, 3.9],
        
        // LIMA - Centros especializados
        ['Centro Ann Sullivan del Perú', 'Av. Petronila Alvarez 180', 'San Miguel', 'Lima', -12.0776, -77.0877, '01-263-3644', 'info@annsullivanperu.org', 'https://www.annsullivanperu.org', 'psicología, terapia conductual, atención TEA', false, false, 4.7],
        ['Instituto Gestalt de Lima', 'Calle Monterosa 371, Chacarilla', 'Santiago de Surco', 'Lima', -12.0945, -76.9934, '01-372-0531', 'contacto@gestaltlima.com', 'https://www.gestaltlima.com', 'psicología, terapia gestalt, coaching', true, false, 4.5],
        ['Centro Psicológico Miraflores', 'Av. Benavides 1555', 'Miraflores', 'Lima', -12.1234, -77.0289, '01-445-6789', 'citas@psicologiamiraflores.pe', null, 'psicología, terapia individual, terapia de pareja', true, false, 4.3],
        ['Centro de Salud Mental La Molina', 'Av. La Molina 1234', 'La Molina', 'Lima', -12.0678, -76.9456, '01-348-9012', null, null, 'psicología, psiquiatría, neurología', false, false, 4.2],
        ['Centro de Atención Psicosocial MINSA', 'Av. Salaverry 801', 'Jesús María', 'Lima', -12.0834, -77.0512, '01-315-6600', null, null, 'psicología, terapia familiar, atención crisis', false, false, 4.0],
        ['Consultorio Psicológico San Borja', 'Av. San Borja Sur 678', 'San Borja', 'Lima', -12.1012, -77.0034, '01-226-7890', 'consultas@psicosanborja.pe', null, 'psicología, coaching, mindfulness', true, false, 4.4],
        ['Centro Terapéutico Barranco', 'Jr. Domeyer 326', 'Barranco', 'Lima', -12.1456, -77.0234, '01-247-5678', 'info@terapiabarranco.com', 'https://www.terapiabarranco.com', 'psicología, arteterapia, musicoterapia', false, false, 4.6],
        ['Centro de Bienestar Mental Surco', 'Av. Caminos del Inca 2345', 'Santiago de Surco', 'Lima', -12.1234, -76.9789, '01-344-5678', 'bienestar@mentalsurco.pe', null, 'psicología, meditación, terapia holística', true, false, 4.5],
        ['Centro de Salud Mental Ate', 'Av. Los Quechuas 789', 'Ate', 'Lima', -12.0256, -76.9123, '01-350-4567', null, null, 'psicología, atención comunitaria', false, false, 3.8],
        
        // ═══════════════════════════════════════════════════════════════════════
        // HUÁNUCO - Hospitales y Emergencias (24h)
        // ═══════════════════════════════════════════════════════════════════════
        ['Hospital II EsSalud Huánuco', 'Carretera Central 262, Amarilis', 'Amarilis', 'Huánuco', -9.937255, -76.239402, '062-591000', null, 'http://www.essalud.gob.pe', 'psiquiatría, emergencias, psicología', false, true, 3.5],
        ['Hospital Materno Infantil Carlos Showing Ferrari', 'Jr. Micaela Bastidas 207', 'Amarilis', 'Huánuco', -9.943173, -76.241987, '062-518899', null, null, 'psicología, atención crisis, violencia familiar', false, true, 4.0],
        ['Hospital Regional Hermilio Valdizán (Contingencia)', 'La Esperanza, Huánuco', 'Amarilis', 'Huánuco', -9.897524, -76.223044, '062-512400', 'informes@hrhvm.gob.pe', 'http://www.hrhvm.gob.pe', 'psiquiatría, emergencias, hospitalización', false, true, 3.8],
        
        // HUÁNUCO - Centros de Salud Mental Comunitarios (Minsa)
        ['CSMC Pakkarin', 'Jr. Lope de Vega s/n', 'Amarilis', 'Huánuco', -9.939108, -76.240335, '962-345-678', null, null, 'psicología, psiquiatría, rehabilitación', true, false, 4.3],
        ['CSMC Universitario Virgilio López Calderón (Pillco Mozo)', 'Av. Universitaria (Campus UNHEVAL)', 'Pillco Marca', 'Huánuco', -9.947812, -76.244803, '989-647-625', null, null, 'psicología juvenil, terapia de pareja, ansiedad', true, false, 4.6],
        ['Puesto de Salud La Esperanza', 'Av. Circunvalación 565', 'Amarilis', 'Huánuco', -9.896289, -76.219591, '062-514567', null, null, 'psicología, terapia ocupacional', true, false, 4.1],
        
        // HUÁNUCO - Clínicas y Privados
        ['Clínica Huánuco', 'Jr. Constitución 980', 'Huánuco', 'Huánuco', -9.929155, -76.235181, '062-514026', 'citas@clinicahuanuco.pe', 'https://clinicahuanuco.pe', 'psicología clínica, evaluaciones', false, false, 4.1],
        ['Consultorio Psicológico UNHEVAL', 'Campus Universitario Cayhuayna', 'Pillco Marca', 'Huánuco', -9.948980, -76.250711, '062-591060', 'bienestar@unheval.edu.pe', 'https://www.unheval.edu.pe', 'psicopedagogía, orientación vocacional', true, false, 4.7],
        ['Policlínico Parroquial San Francisco', 'Jr. Damaso Beraun (Iglesia San Francisco)', 'Huánuco', 'Huánuco', -9.927115, -76.242525, '062-512345', null, null, 'consejería psicológica, apoyo social', false, false, 4.2],
        ['Centro Psicoterapéutico Serenia', 'Jr. Dámaso Beraún 516', 'Huánuco', 'Huánuco', -9.927737, -76.241598, '913-994-367', null, null, 'psicoterapia, terapia familiar', false, false, 4.8],
        ['Centro Psicológico Illa Psychology', 'Av. Esteban Pavletich lote 11', 'Amarilis', 'Huánuco', -9.947180, -76.243284, '970-089-580', null, 'https://www.psicoilla.com', 'psicología clínica, niños y adultos', false, false, 4.5],
    ];
    
    $insertSQL = "INSERT INTO mental_health_centers 
        (name, address, district, city, latitude, longitude, phone, email, website, services, has_mentta, emergency_24h, rating) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $db->prepare($insertSQL);
    $inserted = 0;
    
    foreach ($centers as $center) {
        try {
            $stmt->execute($center);
            $inserted++;
        } catch (PDOException $e) {
            echo "⚠️ Error inserting: " . $center[0] . " - " . $e->getMessage() . "\n";
        }
    }
    
    echo "✅ Inserted $inserted centers successfully!\n\n";
    
    // Final count
    $finalCount = $db->query("SELECT COUNT(*) FROM mental_health_centers")->fetchColumn();
    
    // Count by city
    $limaCount = $db->query("SELECT COUNT(*) FROM mental_health_centers WHERE city = 'Lima'")->fetchColumn();
    $huanucoCount = $db->query("SELECT COUNT(*) FROM mental_health_centers WHERE city = 'Huánuco'")->fetchColumn();
    
    echo str_repeat('-', 50) . "\n";
    echo "📍 Total mental health centers in database: $finalCount\n";
    echo "   - Lima: $limaCount centers\n";
    echo "   - Huánuco: $huanucoCount centers\n";
    echo "✅ Migration completed successfully!\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "</pre>";
echo "<p><a href='map.php' style='display:inline-block;padding:10px 20px;background:#6366F1;color:white;text-decoration:none;border-radius:8px;margin-top:20px;'>🗺️ Go to Map →</a></p>";
