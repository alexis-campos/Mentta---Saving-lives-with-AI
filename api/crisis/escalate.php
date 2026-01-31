<?php
/**
 * MENTTA - Sistema de Escalamiento Automático en Crisis
 * 
 * Se activa cuando risk_level >= 4 (critical/imminent)
 * 
 * Flujo:
 * 1. Notificar psicólogo (si vinculado)
 * 2. Notificar contactos de emergencia (si permitido)
 * 3. Preparar botón de pánico para usuario
 * 4. Registrar todo en logs para auditoría
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

/**
 * Función principal de escalamiento de crisis
 * 
 * @param int $patient_id - ID del paciente en crisis
 * @param int $risk_level - Nivel de riesgo (4=critical, 5=imminent)
 * @param string $message_snapshot - Mensaje que disparó la crisis
 * @return array - Resultado del escalamiento
 */
function escalateCrisis($patient_id, $risk_level, $message_snapshot) {
    $escalation_log = [];
    
    try {
        // 1. Obtener datos del paciente
        $patient = dbFetchOne(
            "SELECT name, age, email FROM users WHERE id = ?",
            [$patient_id]
        );
        
        if (!$patient) {
            throw new Exception('Paciente no encontrado');
        }
        
        // 2. Verificar preferencias de crisis
        $preferences = getCrisisPreferences($patient_id);
        
        // 3. Crear alerta en BD
        $alert_id = createCrisisAlert($patient_id, $risk_level, $message_snapshot);
        $escalation_log[] = "Alerta #{$alert_id} creada";
        
        // 4. Notificar psicólogo (si permitido y vinculado)
        if ($preferences['notify_psychologist']) {
            $psychologist_notified = notifyPsychologist($patient_id, $alert_id, $risk_level);
            $escalation_log[] = $psychologist_notified 
                ? "Psicólogo notificado" 
                : "Sin psicólogo vinculado";
        }
        
        // 5. Notificar contactos de emergencia (si permitido)
        $contacts_notified = 0;
        if ($preferences['notify_emergency_contacts']) {
            $contacts_notified = notifyEmergencyContacts(
                $patient_id, 
                $patient['name'], 
                $risk_level, 
                $preferences['auto_call_emergency_line']
            );
            $escalation_log[] = "Contactos notificados: {$contacts_notified}";
        }
        
        // 6. Preparar botón de pánico en UI (SIEMPRE para niveles 4-5)
        $panic_button_data = [
            'show_panic_button' => true,
            'primary_line' => '113',
            'secondary_line' => '106',
            'message' => '🆘 Detectamos que necesitas ayuda inmediata. Por favor, considera llamar a la línea de crisis.'
        ];
        
        // 7. Log del escalamiento completo
        logError('ESCALAMIENTO DE CRISIS', [
            'patient_id' => $patient_id,
            'patient_name' => $patient['name'],
            'risk_level' => $risk_level,
            'alert_id' => $alert_id,
            'actions' => $escalation_log,
            'preferences' => $preferences,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
        return [
            'success' => true,
            'alert_id' => $alert_id,
            'escalation_log' => $escalation_log,
            'panic_button' => $panic_button_data,
            'contacts_notified' => $contacts_notified
        ];
        
    } catch (Exception $e) {
        logError('Error en escalateCrisis', [
            'error' => $e->getMessage(),
            'patient_id' => $patient_id,
            'risk_level' => $risk_level
        ]);
        
        return [
            'success' => false,
            'error' => $e->getMessage(),
            'panic_button' => [
                'show_panic_button' => true,
                'primary_line' => '113',
                'secondary_line' => '106',
                'message' => '🆘 Si necesitas ayuda inmediata, llama al 113 o 106.'
            ]
        ];
    }
}

/**
 * Obtiene preferencias de crisis del usuario
 * 
 * @param int $patient_id - ID del paciente
 * @return array - Preferencias (con defaults si no existen)
 */
function getCrisisPreferences($patient_id) {
    $preferences = dbFetchOne(
        "SELECT * FROM crisis_preferences WHERE user_id = ?",
        [$patient_id]
    );
    
    // Si no tiene preferencias configuradas, usar defaults seguros
    if (!$preferences) {
        return [
            'notify_psychologist' => true,
            'notify_emergency_contacts' => true,
            'auto_call_emergency_line' => false,
            'emergency_line_preference' => '113',
            'auto_call_threshold' => 'imminent'
        ];
    }
    
    return $preferences;
}

/**
 * Crea alerta de crisis en BD
 * 
 * @param int $patient_id - ID del paciente
 * @param int $risk_level - Nivel de riesgo (4-5)
 * @param string $message_snapshot - Mensaje que disparó la alerta
 * @return int - ID de la alerta creada
 */
function createCrisisAlert($patient_id, $risk_level, $message_snapshot) {
    // Buscar psicólogo vinculado
    $link = dbFetchOne(
        "SELECT psychologist_id 
         FROM patient_psychologist_link 
         WHERE patient_id = ? AND status = 'active'
         LIMIT 1",
        [$patient_id]
    );
    
    $psychologist_id = $link ? $link['psychologist_id'] : null;
    
    // Determinar tipo y severidad
    $alert_type = $risk_level >= 5 ? 'suicide' : 'crisis';
    $severity = 'red'; // Siempre rojo para niveles 4-5
    
    // Insertar alerta
    $alert_id = dbInsert('alerts', [
        'patient_id' => $patient_id,
        'psychologist_id' => $psychologist_id,
        'alert_type' => $alert_type,
        'severity' => $severity,
        'message_snapshot' => mb_substr($message_snapshot, 0, 500),
        'status' => 'pending'
    ]);
    
    return $alert_id;
}

/**
 * Notifica a psicólogo vinculado
 * 
 * @param int $patient_id - ID del paciente
 * @param int $alert_id - ID de la alerta
 * @param int $risk_level - Nivel de riesgo
 * @return bool - True si se notificó, False si no hay psicólogo
 */
function notifyPsychologist($patient_id, $alert_id, $risk_level) {
    $psychologist = dbFetchOne(
        "SELECT u.id, u.name, u.email
         FROM patient_psychologist_link l
         JOIN users u ON l.psychologist_id = u.id
         WHERE l.patient_id = ? AND l.status = 'active'
         LIMIT 1",
        [$patient_id]
    );
    
    if (!$psychologist) {
        return false;
    }
    
    // Obtener nombre del paciente
    $patient = dbFetchOne("SELECT name FROM users WHERE id = ?", [$patient_id]);
    
    // Crear notificación en BD (para dashboard del psicólogo)
    $urgency = $risk_level >= 5 ? 'INMEDIATA' : 'CRÍTICA';
    $notification_message = "🚨 ALERTA {$urgency}: {$patient['name']} necesita atención inmediata.";
    
    // Insertar en tabla de notificaciones si existe
    try {
        dbInsert('notifications', [
            'user_id' => $psychologist['id'],
            'type' => 'crisis_alert',
            'title' => "Alerta de Crisis - {$patient['name']}",
            'message' => $notification_message,
            'reference_id' => $alert_id,
            'reference_type' => 'alert',
            'is_read' => false
        ]);
    } catch (Exception $e) {
        // Tabla notifications podría no existir, solo log
        logError('No se pudo crear notificación para psicólogo', [
            'error' => $e->getMessage()
        ]);
    }
    
    // TODO: Implementar notificación real en tiempo real
    // - Email urgente
    // - SMS (si configurado)
    // - Push notification
    
    logError('NOTIFICACIÓN A PSICÓLOGO', [
        'psychologist_id' => $psychologist['id'],
        'psychologist_name' => $psychologist['name'],
        'patient_id' => $patient_id,
        'alert_id' => $alert_id,
        'risk_level' => $risk_level
    ]);
    
    return true;
}

/**
 * Notifica a contactos de emergencia
 * 
 * @param int $patient_id - ID del paciente
 * @param string $patient_name - Nombre del paciente
 * @param int $risk_level - Nivel de riesgo
 * @param bool $include_call_link - Si incluir link directo para llamar
 * @return int - Número de contactos notificados
 */
function notifyEmergencyContacts($patient_id, $patient_name, $risk_level, $include_call_link) {
    $contacts = dbFetchAll(
        "SELECT contact_name, contact_phone, contact_relationship
         FROM emergency_contacts
         WHERE patient_id = ?
         ORDER BY priority ASC
         LIMIT 3",
        [$patient_id]
    );
    
    if (empty($contacts)) {
        logError('SIN CONTACTOS DE EMERGENCIA', [
            'patient_id' => $patient_id,
            'patient_name' => $patient_name
        ]);
        return 0;
    }
    
    $count = 0;
    foreach ($contacts as $contact) {
        // Construir mensaje
        $urgency = $risk_level >= 5 ? '🆘 URGENTE' : '⚠️ ALERTA';
        $message = "{$urgency} MENTTA: {$patient_name} podría necesitar apoyo ahora. ";
        
        if ($include_call_link && $risk_level >= 5) {
            $message .= "Por favor llama al 113 o 106 si no puedes contactarlo/a. ";
        }
        
        $message .= "Este es un mensaje automático del sistema de apoyo emocional.";
        
        // TODO: Implementar envío real de SMS
        // Servicios sugeridos: Twilio, Infobip, Vonage
        // Por ahora solo registramos en log
        
        logError('SMS A CONTACTO DE EMERGENCIA', [
            'patient_id' => $patient_id,
            'contact_name' => $contact['contact_name'],
            'contact_phone' => $contact['contact_phone'],
            'contact_relationship' => $contact['contact_relationship'],
            'message' => $message,
            'risk_level' => $risk_level
        ]);
        
        $count++;
    }
    
    return $count;
}

/**
 * Obtiene recursos de crisis apropiados para el nivel de riesgo
 * 
 * @param int $risk_level - Nivel de riesgo actual
 * @return array - Lista de recursos de crisis
 */
function getCrisisResources($risk_level) {
    $level_filter = 'all';
    if ($risk_level >= 5) {
        $level_filter = 'imminent';
    } elseif ($risk_level >= 4) {
        $level_filter = 'critical';
    } elseif ($risk_level >= 3) {
        $level_filter = 'high';
    }
    
    return dbFetchAll(
        "SELECT name, description, contact, availability, resource_type
         FROM crisis_resources
         WHERE active = TRUE 
         AND (for_crisis_level = 'all' OR for_crisis_level = ?)
         ORDER BY priority ASC
         LIMIT 5",
        [$level_filter]
    );
}
