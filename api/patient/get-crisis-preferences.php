<?php
/**
 * MENTTA - API: Obtener Preferencias de Crisis
 * 
 * Retorna las preferencias de escalamiento de crisis del usuario actual.
 */

require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');
setSecurityHeaders();

// Solo GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, null, 'Método no permitido', 405);
}

// Verificar autenticación
$user = checkAuth();
if (!$user || $user['role'] !== 'patient') {
    jsonResponse(false, null, 'No autorizado', 401);
}

try {
    $preferences = dbFetchOne(
        "SELECT 
            notify_psychologist,
            notify_emergency_contacts,
            auto_call_emergency_line,
            emergency_line_preference,
            auto_call_threshold,
            consent_given_at IS NOT NULL as consent_given
         FROM crisis_preferences 
         WHERE user_id = ?",
        [$user['id']]
    );
    
    if (!$preferences) {
        // Retornar defaults si no hay preferencias guardadas
        $preferences = [
            'notify_psychologist' => true,
            'notify_emergency_contacts' => true,
            'auto_call_emergency_line' => false,
            'emergency_line_preference' => '113',
            'auto_call_threshold' => 'imminent',
            'consent_given' => false
        ];
    } else {
        // Convertir valores a booleanos
        $preferences['notify_psychologist'] = (bool)$preferences['notify_psychologist'];
        $preferences['notify_emergency_contacts'] = (bool)$preferences['notify_emergency_contacts'];
        $preferences['auto_call_emergency_line'] = (bool)$preferences['auto_call_emergency_line'];
        $preferences['consent_given'] = (bool)$preferences['consent_given'];
    }
    
    jsonResponse(true, $preferences);
    
} catch (Exception $e) {
    logError('Error en get-crisis-preferences', [
        'error' => $e->getMessage(),
        'user_id' => $user['id']
    ]);
    jsonResponse(false, null, 'Error al obtener preferencias');
}
