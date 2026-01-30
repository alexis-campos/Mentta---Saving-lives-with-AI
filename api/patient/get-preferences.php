<?php
/**
 * MENTTA - API: Get User Preferences
 * Returns user's theme, analysis pause status, and other preferences
 */

require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');
setSecurityHeaders();

// Check authentication
$user = checkAuth();
if (!$user) {
    jsonResponse(false, null, 'No autenticado', 401);
}

try {
    $db = getDB();
    
    // Get user preferences from user_preferences table
    $stmt = $db->prepare("
        SELECT analysis_paused, analysis_paused_until, notifications_enabled, daily_reminder_time
        FROM user_preferences 
        WHERE user_id = ?
    ");
    $stmt->execute([$user['id']]);
    $prefs = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Check if analysis pause has expired
    $analysisPaused = false;
    $pausedUntil = null;
    
    if ($prefs && $prefs['analysis_paused']) {
        if ($prefs['analysis_paused_until'] && strtotime($prefs['analysis_paused_until']) > time()) {
            $analysisPaused = true;
            $pausedUntil = $prefs['analysis_paused_until'];
        } else {
            // Expired, reset
            $stmt = $db->prepare("
                UPDATE user_preferences 
                SET analysis_paused = FALSE, analysis_paused_until = NULL
                WHERE user_id = ?
            ");
            $stmt->execute([$user['id']]);
        }
    }
    
    jsonResponse(true, [
        'theme' => $user['theme_preference'] ?? 'light',
        'analysis_paused' => $analysisPaused,
        'analysis_paused_until' => $pausedUntil,
        'notifications_enabled' => $prefs['notifications_enabled'] ?? true,
        'daily_reminder_time' => $prefs['daily_reminder_time'] ?? null
    ]);
    
} catch (Exception $e) {
    logError('Error en get-preferences.php', ['error' => $e->getMessage()]);
    jsonResponse(false, null, 'Error al obtener preferencias');
}
