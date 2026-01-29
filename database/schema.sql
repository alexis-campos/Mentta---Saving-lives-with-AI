-- ============================================
-- MENTTA - Sistema de Apoyo Emocional con IA
-- Esquema de Base de Datos v1.0
-- ============================================
-- Autor: Mentta Team
-- Fecha: 2026-01-29
-- Descripción: Esquema completo de la base de datos para la plataforma Mentta
-- ============================================

-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS mentta 
    CHARACTER SET utf8mb4 
    COLLATE utf8mb4_unicode_ci;

USE mentta;

-- ============================================
-- TABLA: users
-- Almacena todos los usuarios del sistema (pacientes y psicólogos)
-- El campo role determina el tipo de usuario y sus permisos
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    age INT NULL,
    role ENUM('patient', 'psychologist') NOT NULL DEFAULT 'patient',
    language ENUM('es', 'en') NOT NULL DEFAULT 'es',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Constraints
    UNIQUE KEY unique_email (email),
    
    -- Índices para búsquedas frecuentes
    INDEX idx_role (role),
    INDEX idx_is_active (is_active),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Usuarios del sistema - pacientes y psicólogos';

-- ============================================
-- TABLA: sessions
-- Maneja las sesiones de autenticación de usuarios
-- Permite invalidación de sesiones y control de expiración
-- ============================================
CREATE TABLE IF NOT EXISTS sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(64) NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    CONSTRAINT fk_sessions_user 
        FOREIGN KEY (user_id) REFERENCES users(id) 
        ON DELETE CASCADE,
    
    -- Constraints
    UNIQUE KEY unique_session_token (session_token),
    
    -- Índices para búsquedas frecuentes
    INDEX idx_user_id (user_id),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Sesiones de autenticación de usuarios';

-- ============================================
-- TABLA: conversations
-- Almacena todos los mensajes del chat entre pacientes y la IA
-- Incluye análisis de sentimientos y nivel de riesgo por mensaje
-- ============================================
CREATE TABLE IF NOT EXISTS conversations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    message TEXT NOT NULL,
    sender ENUM('user', 'ai') NOT NULL,
    sentiment_score JSON NULL COMMENT 'Scores: {positive, negative, anxiety, sadness, anger} valores 0-1',
    risk_level ENUM('none', 'low', 'medium', 'high', 'critical') NOT NULL DEFAULT 'none',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    CONSTRAINT fk_conversations_patient 
        FOREIGN KEY (patient_id) REFERENCES users(id) 
        ON DELETE CASCADE,
    
    -- Índices para búsquedas y análisis frecuentes
    INDEX idx_patient_id (patient_id),
    INDEX idx_created_at (created_at),
    INDEX idx_sender (sender),
    INDEX idx_risk_level (risk_level),
    INDEX idx_patient_created (patient_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Historial de conversaciones entre pacientes y la IA';

-- ============================================
-- TABLA: patient_memory
-- Almacena memorias contextuales extraídas de las conversaciones
-- Permite a la IA recordar nombres, eventos, relaciones del paciente
-- ============================================
CREATE TABLE IF NOT EXISTS patient_memory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    memory_type ENUM('name', 'relationship', 'event', 'preference', 'emotion', 'location') NOT NULL,
    key_name VARCHAR(100) NOT NULL COMMENT 'Identificador de la memoria (ej: "hermana", "trabajo")',
    value TEXT NOT NULL COMMENT 'Valor de la memoria (ej: "Ana", "Despedido en Enero")',
    context TEXT NULL COMMENT 'Frase original donde se detectó esta memoria',
    importance INT DEFAULT 1 COMMENT 'Nivel de importancia 1-5 para priorizar en contexto',
    last_mentioned_at TIMESTAMP NULL COMMENT 'Última vez que el paciente mencionó esto',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    CONSTRAINT fk_memory_patient 
        FOREIGN KEY (patient_id) REFERENCES users(id) 
        ON DELETE CASCADE,
    
    -- Constraint de unicidad por paciente, tipo y clave
    UNIQUE KEY unique_patient_memory (patient_id, memory_type, key_name),
    
    -- Índices
    INDEX idx_patient_id (patient_id),
    INDEX idx_memory_type (memory_type),
    INDEX idx_importance (importance)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Memorias contextuales de los pacientes para personalización de IA';

-- ============================================
-- TABLA: patient_psychologist_link
-- Vincula pacientes con sus psicólogos asignados
-- Un paciente puede tener múltiples psicólogos (histórico)
-- pero solo uno activo a la vez
-- ============================================
CREATE TABLE IF NOT EXISTS patient_psychologist_link (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    psychologist_id INT NOT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    notes TEXT NULL COMMENT 'Notas del psicólogo sobre la relación',
    linked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    unlinked_at TIMESTAMP NULL,
    
    -- Foreign Keys
    CONSTRAINT fk_link_patient 
        FOREIGN KEY (patient_id) REFERENCES users(id) 
        ON DELETE CASCADE,
    CONSTRAINT fk_link_psychologist 
        FOREIGN KEY (psychologist_id) REFERENCES users(id) 
        ON DELETE CASCADE,
    
    -- Constraint de unicidad
    UNIQUE KEY unique_patient_psychologist (patient_id, psychologist_id),
    
    -- Índices
    INDEX idx_patient_id (patient_id),
    INDEX idx_psychologist_id (psychologist_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Relaciones entre pacientes y psicólogos';

-- ============================================
-- TABLA: alerts
-- Sistema de alertas de riesgo para psicólogos
-- Se generan automáticamente cuando se detecta riesgo en conversaciones
-- ============================================
CREATE TABLE IF NOT EXISTS alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    psychologist_id INT NULL COMMENT 'Puede ser NULL si no hay psicólogo vinculado',
    alert_type ENUM('suicide', 'self_harm', 'crisis', 'anxiety', 'depression') NOT NULL,
    severity ENUM('yellow', 'orange', 'red') NOT NULL DEFAULT 'orange',
    message_snapshot TEXT NOT NULL COMMENT 'Mensaje que disparó la alerta',
    ai_analysis TEXT NULL COMMENT 'Análisis de la IA sobre el contexto',
    status ENUM('pending', 'acknowledged', 'in_progress', 'resolved', 'false_positive') NOT NULL DEFAULT 'pending',
    resolution_notes TEXT NULL COMMENT 'Notas del psicólogo al resolver',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    acknowledged_at TIMESTAMP NULL,
    resolved_at TIMESTAMP NULL,
    
    -- Foreign Keys
    CONSTRAINT fk_alerts_patient 
        FOREIGN KEY (patient_id) REFERENCES users(id) 
        ON DELETE CASCADE,
    CONSTRAINT fk_alerts_psychologist 
        FOREIGN KEY (psychologist_id) REFERENCES users(id) 
        ON DELETE SET NULL,
    
    -- Índices para dashboard y notificaciones
    INDEX idx_patient_id (patient_id),
    INDEX idx_psychologist_id (psychologist_id),
    INDEX idx_status (status),
    INDEX idx_severity (severity),
    INDEX idx_created_at (created_at),
    INDEX idx_psychologist_status (psychologist_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Alertas de riesgo para supervisión de psicólogos';

-- ============================================
-- TABLA: emergency_contacts
-- Contactos de emergencia de los pacientes
-- Se utilizan cuando hay alerta crítica y no hay psicólogo disponible
-- ============================================
CREATE TABLE IF NOT EXISTS emergency_contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    contact_name VARCHAR(100) NOT NULL,
    contact_phone VARCHAR(20) NOT NULL,
    contact_relationship VARCHAR(50) NOT NULL COMMENT 'Relación: padre, madre, hermano, amigo, etc.',
    priority INT NOT NULL DEFAULT 1 COMMENT 'Orden de contacto (1 = primero)',
    is_verified TINYINT(1) DEFAULT 0 COMMENT 'Si el contacto ha sido verificado',
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    CONSTRAINT fk_emergency_patient 
        FOREIGN KEY (patient_id) REFERENCES users(id) 
        ON DELETE CASCADE,
    
    -- Índices
    INDEX idx_patient_id (patient_id),
    INDEX idx_priority (priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Contactos de emergencia de pacientes';

-- ============================================
-- TABLA: rate_limits
-- Control de rate limiting para prevenir abuso
-- Almacena contadores de acciones por usuario
-- ============================================
CREATE TABLE IF NOT EXISTS rate_limits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(50) NOT NULL COMMENT 'Tipo de acción: send_message, login, etc.',
    request_count INT DEFAULT 1,
    window_start TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    CONSTRAINT fk_ratelimit_user 
        FOREIGN KEY (user_id) REFERENCES users(id) 
        ON DELETE CASCADE,
    
    -- Índices
    UNIQUE KEY unique_user_action (user_id, action),
    INDEX idx_window_start (window_start)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Control de rate limiting por usuario y acción';

-- ============================================
-- TABLA: error_logs
-- Registro de errores del sistema para debugging
-- ============================================
CREATE TABLE IF NOT EXISTS error_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    error_type VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    context JSON NULL,
    file VARCHAR(255) NULL,
    line INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign Keys (opcional, puede ser NULL para errores del sistema)
    CONSTRAINT fk_errorlog_user 
        FOREIGN KEY (user_id) REFERENCES users(id) 
        ON DELETE SET NULL,
    
    -- Índices
    INDEX idx_error_type (error_type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Registro de errores del sistema';

-- ============================================
-- VISTAS ÚTILES
-- ============================================

-- Vista: Pacientes con su psicólogo activo
CREATE OR REPLACE VIEW v_patient_with_psychologist AS
SELECT 
    u.id AS patient_id,
    u.name AS patient_name,
    u.email AS patient_email,
    u.age AS patient_age,
    p.id AS psychologist_id,
    p.name AS psychologist_name,
    ppl.linked_at,
    ppl.status AS link_status
FROM users u
LEFT JOIN patient_psychologist_link ppl ON u.id = ppl.patient_id AND ppl.status = 'active'
LEFT JOIN users p ON ppl.psychologist_id = p.id
WHERE u.role = 'patient' AND u.is_active = 1;

-- Vista: Resumen de alertas por psicólogo
CREATE OR REPLACE VIEW v_psychologist_alerts_summary AS
SELECT 
    a.psychologist_id,
    p.name AS psychologist_name,
    COUNT(CASE WHEN a.status = 'pending' THEN 1 END) AS pending_alerts,
    COUNT(CASE WHEN a.status = 'acknowledged' THEN 1 END) AS acknowledged_alerts,
    COUNT(CASE WHEN a.severity = 'red' AND a.status = 'pending' THEN 1 END) AS critical_pending,
    COUNT(*) AS total_alerts
FROM alerts a
JOIN users p ON a.psychologist_id = p.id
GROUP BY a.psychologist_id, p.name;

-- Vista: Estadísticas de sentimiento por paciente
CREATE OR REPLACE VIEW v_patient_sentiment_stats AS
SELECT 
    patient_id,
    DATE(created_at) AS date,
    COUNT(*) AS message_count,
    AVG(JSON_EXTRACT(sentiment_score, '$.positive')) AS avg_positive,
    AVG(JSON_EXTRACT(sentiment_score, '$.negative')) AS avg_negative,
    AVG(JSON_EXTRACT(sentiment_score, '$.anxiety')) AS avg_anxiety,
    AVG(JSON_EXTRACT(sentiment_score, '$.sadness')) AS avg_sadness,
    AVG(JSON_EXTRACT(sentiment_score, '$.anger')) AS avg_anger
FROM conversations
WHERE sender = 'user' AND sentiment_score IS NOT NULL
GROUP BY patient_id, DATE(created_at);

-- ============================================
-- FIN DEL ESQUEMA
-- ============================================
