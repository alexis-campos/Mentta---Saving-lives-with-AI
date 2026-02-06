<?php
/**
 * MENTTA - Alert System (Extended Functions)
 * Funciones adicionales para gestión de alertas
 * 
 * NOTA: La función createRiskAlert() está en risk-detector.php
 * Este archivo añade funciones complementarias para:
 * - Notificaciones a contactos de emergencia
 * - Long polling para psicólogos
 * - Gestión de estados de alertas
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

/**
 * Notifica a contactos de emergencia del paciente
 * Se llama cuando no hay psicólogo vinculado
 */
function notifyEmergencyContacts($patient_id, $message_snapshot) {
    $db = getDB();
    
    // Obtener contactos ordenados por prioridad
    $stmt = $db->prepare("
        SELECT contact_name, contact_phone, contact_relationship
        FROM emergency_contacts
        WHERE patient_id = :patient_id
        ORDER BY priority ASC
        LIMIT 3
    ");
    $stmt->execute(['patient_id' => $patient_id]);
    $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($contacts)) {
        // No hay contactos, preparar datos para línea nacional
        prepareNationalEmergencyLine($patient_id, $message_snapshot);
        return false;
    }
    
    // Log de contactos (en producción, aquí se enviarían SMS o llamadas)
    foreach ($contacts as $contact) {
        logError('📞 CONTACTO DE EMERGENCIA NOTIFICADO', [
            'patient_id' => $patient_id,
            'contact' => $contact['contact_name'],
            'phone' => $contact['contact_phone'],
            'relationship' => $contact['contact_relationship'],
            'message' => 'ALERTA: Persona en riesgo necesita apoyo inmediato'
        ]);
    }
    
    return true;
}

/**
 * Prepara datos para línea nacional de emergencia (113 en Perú)
 */
function prepareNationalEmergencyLine($patient_id, $message_snapshot) {
    $db = getDB();
    
    // Obtener datos del paciente
    $stmt = $db->prepare("SELECT name, age, email FROM users WHERE id = :id");
    $stmt->execute(['id' => $patient_id]);
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Log crítico para seguimiento manual
    logError('🆘 ALERTA SIN CONTACTOS - LÍNEA NACIONAL REQUERIDA', [
        'patient_id' => $patient_id,
        'patient_name' => $patient['name'] ?? 'Desconocido',
        'patient_age' => $patient['age'] ?? 'Desconocida',
        'message_snapshot' => mb_substr($message_snapshot, 0, 100),
        'emergency_line' => EMERGENCY_LINE . ' - ' . EMERGENCY_LINE_NAME,
        'action_required' => 'Contacto manual inmediato',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
    // En producción: API de emergencias o sistema de dispatch
}

/**
 * Obtener alertas pendientes de un psicólogo (para long polling)
 */
function getPendingAlerts($psychologist_id, $since_timestamp = null) {
    $db = getDB();
    
    $sql = "
        SELECT 
            a.id, a.patient_id, a.alert_type, a.severity,
            a.message_snapshot, a.created_at,
            u.name as patient_name, u.age as patient_age
        FROM alerts a
        JOIN users u ON a.patient_id = u.id
        WHERE a.psychologist_id = :psychologist_id
        AND a.status = 'pending'
    ";
    
    if ($since_timestamp) {
        $sql .= " AND a.created_at > :since";
    }
    
    $sql .= " ORDER BY a.severity DESC, a.created_at DESC LIMIT 10";
    
    $stmt = $db->prepare($sql);
    $params = ['psychologist_id' => $psychologist_id];
    if ($since_timestamp) {
        $params['since'] = date('Y-m-d H:i:s', $since_timestamp);
    }
    $stmt->execute($params);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Obtener conteo de alertas por estado
 */
function getAlertCounts($psychologist_id) {
    $db = getDB();
    
    $stmt = $db->prepare("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'acknowledged' THEN 1 ELSE 0 END) as acknowledged,
            SUM(CASE WHEN severity = 'red' AND status = 'pending' THEN 1 ELSE 0 END) as critical_pending
        FROM alerts
        WHERE psychologist_id = :psychologist_id
    ");
    $stmt->execute(['psychologist_id' => $psychologist_id]);
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Marca alerta como reconocida
 */
function acknowledgeAlert($alert_id, $psychologist_id) {
    $db = getDB();
    
    // Verificar que la alerta pertenece al psicólogo
    $stmt = $db->prepare("
        UPDATE alerts 
        SET status = 'acknowledged', acknowledged_at = NOW()
        WHERE id = :id AND psychologist_id = :psychologist_id AND status = 'pending'
    ");
    
    $result = $stmt->execute([
        'id' => $alert_id,
        'psychologist_id' => $psychologist_id
    ]);
    
    if ($result && $stmt->rowCount() > 0) {
        logError('✅ Alerta reconocida', [
            'alert_id' => $alert_id,
            'psychologist_id' => $psychologist_id
        ]);
        return true;
    }
    
    return false;
}

/**
 * Resolver alerta con notas
 */
function resolveAlert($alert_id, $psychologist_id, $notes = '') {
    $db = getDB();
    
    $stmt = $db->prepare("
        UPDATE alerts 
        SET status = 'resolved', 
            resolved_at = NOW(),
            resolution_notes = :notes
        WHERE id = :id AND psychologist_id = :psychologist_id
    ");
    
    return $stmt->execute([
        'id' => $alert_id,
        'psychologist_id' => $psychologist_id,
        'notes' => $notes
    ]);
}

/**
 * Obtener historial de alertas de un paciente
 */
function getPatientAlertHistory($patient_id, $limit = 20) {
    $db = getDB();
    
    $stmt = $db->prepare("
        SELECT a.*, u.name as psychologist_name
        FROM alerts a
        LEFT JOIN users u ON a.psychologist_id = u.id
        WHERE a.patient_id = :patient_id
        ORDER BY a.created_at DESC
        LIMIT :limit
    ");
    $stmt->bindValue(':patient_id', $patient_id, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
