<?php
/**
 * MENTTA - Circuit Breaker Pattern
 * Protege el sistema de fallos en servicios externos (IA API)
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

class CircuitBreaker {
    private $db;
    private $state; // 'closed', 'open', 'half-open'
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->loadState();
    }
    
    /**
     * Verifica si podemos intentar usar la IA
     */
    public function canAttempt() {
        if ($this->state === 'closed') {
            return true; // Todo bien, API funcionando
        }
        
        if ($this->state === 'open') {
            // API caída, verificar si ya pasó tiempo de recuperación
            $lastFailure = $this->getLastFailureTime();
            $timeSinceFailure = time() - $lastFailure;
            
            if ($timeSinceFailure >= AI_RECOVERY_TIME) {
                $this->setState('half-open'); // Probar de nuevo (1 request pasa)
                logError('CIRCUIT BREAKER: Estado Half-Open - Probando recuperación');
                return true;
            }
            
            // Todavía en enfriamiento
            return false;
        }
        
        if ($this->state === 'half-open') {
            // En half-open, idealmente solo dejamos pasar 1 request
            // Por simplicidad, asumimos que "el que pregunte primero pasa"
            return true; 
        }
        
        return false;
    }
    
    /**
     * Registra éxito de llamada a IA
     */
    public function recordSuccess() {
        if ($this->state !== 'closed') {
            $this->setState('closed');
            $this->resetFailureCount();
            logError('CIRCUIT BREAKER: Recuperado - Circuito Cerrado');
        }
    }
    
    /**
     * Registra fallo de llamada a IA
     */
    public function recordFailure($error_message) {
        $count = $this->incrementFailureCount();
        $this->updateLastFailureTime();
        
        logError('CIRCUIT BREAKER: Fallo registrado', [
            'consecutive_failures' => $count,
            'limit' => AI_MAX_FAILURES,
            'error' => $error_message
        ]);
        
        if ($count >= AI_MAX_FAILURES || $this->state === 'half-open') {
            if ($this->state !== 'open') {
                $this->setState('open'); // Abrir circuito
                
                logError('🚨 CIRCUIT BREAKER: ACTIVADO (OPEN) - IA DESACTIVADA', [
                    'reason' => 'Límite de fallos alcanzado',
                    'recovery_in' => AI_RECOVERY_TIME . 's'
                ]);
            }
        }
    }
    
    /**
     * Obtener estado actual para debugging/logs
     */
    public function getStatus() {
        return [
            'state' => $this->state,
            'failures' => $this->getFailureCount(),
            'last_failure_ago' => time() - $this->getLastFailureTime()
        ];
    }
    
    // ============ Métodos privados de persistencia ============
    
    private function loadState() {
        $stmt = $this->db->prepare("SELECT config_value FROM system_config WHERE config_key = 'circuit_breaker_state'");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->state = $result ? $result['config_value'] : 'closed';
    }
    
    private function setState($newState) {
        $this->state = $newState;
        $this->setConfig('circuit_breaker_state', $newState);
    }
    
    private function getFailureCount() {
        $stmt = $this->db->prepare("SELECT config_value FROM system_config WHERE config_key = 'ai_failure_count'");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? intval($result['config_value']) : 0;
    }
    
    private function incrementFailureCount() {
        $count = $this->getFailureCount() + 1;
        $this->setConfig('ai_failure_count', strval($count));
        return $count;
    }
    
    private function resetFailureCount() {
        $this->setConfig('ai_failure_count', '0');
    }
    
    private function getLastFailureTime() {
        $stmt = $this->db->prepare("SELECT config_value FROM system_config WHERE config_key = 'ai_last_failure'");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? intval($result['config_value']) : 0;
    }
    
    private function updateLastFailureTime() {
        $this->setConfig('ai_last_failure', strval(time()));
    }
    
    private function setConfig($key, $value) {
        $stmt = $this->db->prepare("
            INSERT INTO system_config (config_key, config_value, updated_at) 
            VALUES (:key, :val, NOW()) 
            ON DUPLICATE KEY UPDATE config_value = :val_update, updated_at = NOW()
        ");
        $stmt->execute(['key' => $key, 'val' => $value, 'val_update' => $value]);
    }
}
