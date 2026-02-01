-- ==========================================================
-- MENTTA - Sistema de Apoyo Emocional con IA
-- MASTER SCHEMA (Unified v2.0)
-- ==========================================================
-- Fecha: 2026-02-01
-- Descripción: Esquema unificado que incluye Core, PAP, 
-- Mapas, Menú Hamburguesa y Circuit Breaker.
-- ==========================================================

-- 1. CONFIGURACIÓN INICIAL DE BASE DE DATOS
CREATE DATABASE IF NOT EXISTS mentta 
    CHARACTER SET utf8mb4 
    COLLATE utf8mb4_unicode_ci;

USE mentta;

-- ==========================================================
-- 2. TABLAS PRINCIPALES DE USUARIOS Y CONFIGURACIÓN
-- ==========================================================

-- TABLA: users
-- Almacena todos los usuarios y sus preferencias básicas
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    age INT NULL,
    role ENUM('patient', 'psychologist') NOT NULL DEFAULT 'patient',
    language ENUM('es', 'en') NOT NULL DEFAULT 'es',
    theme_preference ENUM('light', 'dark') DEFAULT 'light', -- Agregado de Hamburger v1.1
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_email (email),
    INDEX idx_role (role),
    INDEX idx_is_active (is_active),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Usuarios del sistema - pacientes y psicólogos';

-- TABLA: user_preferences
-- Configuraciones extendidas (pausas, notificaciones)
CREATE TABLE IF NOT EXISTS user_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    analysis_paused BOOLEAN DEFAULT FALSE,
    analysis_paused_until TIMESTAMP NULL,
    notifications_enabled BOOLEAN DEFAULT TRUE,
    daily_reminder_time TIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_user_preferences_user 
        FOREIGN KEY (user_id) REFERENCES users(id) 
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Preferencias extendidas del usuario';

-- TABLA: sessions
-- Manejo de autenticación
CREATE TABLE IF NOT EXISTS sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(64) NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_sessions_user 
        FOREIGN KEY (user_id) REFERENCES users(id) 
        ON DELETE CASCADE,
    UNIQUE KEY unique_session_token (session_token),
    INDEX idx_user_id (user_id),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Sesiones de autenticación de usuarios';

-- ==========================================================
-- 3. CORE: CHAT, MEMORIA Y LÓGICA PAP
-- ==========================================================

-- TABLA: conversations
-- Historial de chat con campos integrados para PAP y sesiones
CREATE TABLE IF NOT EXISTS conversations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    session_id VARCHAR(100) NULL COMMENT 'Identificador de sesión de chat',
    message TEXT NOT NULL,
    sender ENUM('user', 'ai') NOT NULL,
    
    -- Análisis de IA
    sentiment_score JSON NULL COMMENT 'Scores: {positive, negative, anxiety, sadness, anger} valores 0-1',
    risk_level ENUM('none', 'low', 'medium', 'high', 'critical') NOT NULL DEFAULT 'none',
    final_risk_level INT DEFAULT 0 COMMENT '0-5, nivel numérico refinado',
    
    -- Protocolo PAP (Primeros Auxilios Psicológicos)
    pap_phase CHAR(1) DEFAULT NULL COMMENT 'Fase PAP: A,B,C,D,E',
    requires_confirmation BOOLEAN DEFAULT FALSE,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_conversations_patient 
        FOREIGN KEY (patient_id) REFERENCES users(id) 
        ON DELETE CASCADE,
    
    INDEX idx_patient_id (patient_id),
    INDEX idx_created_at (created_at),
    INDEX idx_sender (sender),
    INDEX idx_risk_level (risk_level),
    INDEX idx_session (session_id),
    INDEX idx_risk_crisis (patient_id, final_risk_level, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Historial de conversaciones incluyendo protocolo PAP';

-- TABLA: patient_memory
-- Memoria a largo plazo (RAG contextual)
CREATE TABLE IF NOT EXISTS patient_memory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    memory_type ENUM('name', 'relationship', 'event', 'preference', 'emotion', 'location') NOT NULL,
    key_name VARCHAR(100) NOT NULL,
    value TEXT NOT NULL,
    context TEXT NULL,
    importance INT DEFAULT 1,
    last_mentioned_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_memory_patient 
        FOREIGN KEY (patient_id) REFERENCES users(id) 
        ON DELETE CASCADE,
    UNIQUE KEY unique_patient_memory (patient_id, memory_type, key_name),
    INDEX idx_patient_id (patient_id),
    INDEX idx_memory_type (memory_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Memorias contextuales para personalización';

-- ==========================================================
-- 4. GESTIÓN CLÍNICA Y ALERTAS
-- ==========================================================

-- TABLA: patient_psychologist_link
-- Relación paciente-especialista
CREATE TABLE IF NOT EXISTS patient_psychologist_link (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    psychologist_id INT NOT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    notes TEXT NULL,
    linked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    unlinked_at TIMESTAMP NULL,
    
    CONSTRAINT fk_link_patient FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_link_psychologist FOREIGN KEY (psychologist_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_patient_psychologist (patient_id, psychologist_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLA: alerts
-- Sistema de alertas (incluye manual_request)
CREATE TABLE IF NOT EXISTS alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    psychologist_id INT NULL,
    alert_type ENUM('suicide', 'self_harm', 'crisis', 'anxiety', 'depression', 'manual_request') NOT NULL,
    severity ENUM('yellow', 'orange', 'red') NOT NULL DEFAULT 'orange',
    message_snapshot TEXT NOT NULL,
    ai_analysis TEXT NULL,
    status ENUM('pending', 'acknowledged', 'in_progress', 'resolved', 'false_positive') NOT NULL DEFAULT 'pending',
    resolution_notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    acknowledged_at TIMESTAMP NULL,
    resolved_at TIMESTAMP NULL,
    
    CONSTRAINT fk_alerts_patient FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_alerts_psychologist FOREIGN KEY (psychologist_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_severity (severity),
    INDEX idx_psychologist_status (psychologist_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLA: notifications
-- Notificaciones para el usuario (App interna)
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('psychologist_message', 'upcoming_appointment', 'new_resource', 'daily_reminder', 'crisis_followup') NOT NULL,
    title VARCHAR(100) NOT NULL,
    message TEXT,
    action_url VARCHAR(255) NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 5. RECURSOS DE CRISIS Y PSICOEDUCACIÓN
-- ==========================================================

-- TABLA: emergency_contacts
CREATE TABLE IF NOT EXISTS emergency_contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    contact_name VARCHAR(100) NOT NULL,
    contact_phone VARCHAR(20) NOT NULL,
    contact_relationship VARCHAR(50) NOT NULL,
    priority INT NOT NULL DEFAULT 1,
    is_verified TINYINT(1) DEFAULT 0,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_emergency_patient FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLA: crisis_preferences
CREATE TABLE IF NOT EXISTS crisis_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    notify_psychologist BOOLEAN DEFAULT TRUE,
    notify_emergency_contacts BOOLEAN DEFAULT TRUE,
    auto_call_emergency_line BOOLEAN DEFAULT FALSE,
    emergency_line_preference ENUM('113', '106', 'both') DEFAULT '113',
    auto_call_threshold ENUM('critical', 'imminent') DEFAULT 'imminent',
    consent_given_at TIMESTAMP NULL,
    consent_ip VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_crisis_prefs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLA: crisis_resources (Base de conocimiento)
CREATE TABLE IF NOT EXISTS crisis_resources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resource_type ENUM('phone_line', 'chat', 'center', 'emergency') NOT NULL,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    contact VARCHAR(100),
    availability VARCHAR(50) DEFAULT '24/7',
    country VARCHAR(3) DEFAULT 'PE',
    region VARCHAR(100),
    for_crisis_level ENUM('all', 'low', 'moderate', 'high', 'critical', 'imminent') DEFAULT 'all',
    priority INT DEFAULT 1,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLA: psychoeducation_content (Base de conocimiento)
CREATE TABLE IF NOT EXISTS psychoeducation_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    topic ENUM('anxiety', 'depression', 'grief', 'trauma', 'stress', 'relationships', 'self_harm', 'general') NOT NULL,
    content_type ENUM('explanation', 'technique', 'myth_buster', 'resource') NOT NULL,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    length ENUM('short', 'medium', 'long') DEFAULT 'medium',
    language VARCHAR(5) DEFAULT 'es-PE',
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_topic_type (topic, content_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 6. GEOLOCALIZACIÓN Y MAPAS
-- ==========================================================

-- TABLA: mental_health_centers
CREATE TABLE IF NOT EXISTS mental_health_centers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    address TEXT NOT NULL,
    district VARCHAR(100),
    city VARCHAR(100) DEFAULT 'Lima',
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    website VARCHAR(255),
    services TEXT,
    accepts_insurance BOOLEAN DEFAULT TRUE,
    insurance_providers TEXT,
    has_mentta BOOLEAN DEFAULT FALSE,
    emergency_24h BOOLEAN DEFAULT FALSE,
    schedule JSON,
    verified BOOLEAN DEFAULT FALSE,
    rating DECIMAL(2,1) DEFAULT 0.0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_location (latitude, longitude),
    INDEX idx_mentta (has_mentta),
    FULLTEXT idx_search (name, address, district)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 7. SISTEMA Y SEGURIDAD
-- ==========================================================

-- TABLA: rate_limits
CREATE TABLE IF NOT EXISTS rate_limits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    request_count INT DEFAULT 1,
    window_start TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_ratelimit_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_action (user_id, action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLA: error_logs
CREATE TABLE IF NOT EXISTS error_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    error_type VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    context JSON NULL,
    file VARCHAR(255) NULL,
    line INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_errorlog_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLA: system_config (Circuit Breaker)
CREATE TABLE IF NOT EXISTS system_config (
  config_key varchar(100) NOT NULL,
  config_value text DEFAULT NULL,
  updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (config_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 8. VISTAS
-- ==========================================================

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

-- ==========================================================
-- 9. INSERCIÓN DE DATOS INICIALES (SEEDERS)
-- ==========================================================

-- System Config Defaults
INSERT IGNORE INTO system_config (config_key, config_value) VALUES
('circuit_breaker_state', 'closed'),
('ai_failure_count', '0'),
('ai_last_failure', '0');

-- Crisis Resources
INSERT INTO crisis_resources 
(resource_type, name, description, contact, availability, country, region, for_crisis_level, priority, active) 
VALUES
('phone_line', 'Línea 113 - Salud Mental', 'Atención psicológica gratuita del MINSA. Marca 113 opción 5.', '113', '24/7', 'PE', NULL, 'high', 1, TRUE),
('emergency', 'SAMU 106', 'Emergencias médicas inmediatas / intentos de suicidio', '106', '24/7', 'PE', NULL, 'imminent', 1, TRUE),
('phone_line', 'Línea 100', 'Violencia familiar y de género - Ministerio de la Mujer', '100', '24/7', 'PE', NULL, 'moderate', 2, TRUE),
('chat', 'Chat Línea 100', 'Chat para violencia (si no puede hablar por teléfono)', 'https://www.gob.pe/linea100', '24/7', 'PE', NULL, 'moderate', 3, TRUE),
('phone_line', 'Bomberos 116', 'Emergencias y rescates - incluye situaciones de suicidio', '116', '24/7', 'PE', NULL, 'imminent', 2, TRUE);

-- Mental Health Centers (Muestra Lima)
INSERT INTO mental_health_centers 
(name, address, district, city, latitude, longitude, phone, email, services, has_mentta, emergency_24h, verified, rating, schedule)
VALUES
('Instituto Nacional de Salud Mental Honorio Delgado-Hideyo Noguchi', 'Jr. Eloy Espinoza 709', 'San Martín de Porres', 'Lima', -12.0234, -77.0856, '01-614-9200', 'informes@insm.gob.pe', 'psiquiatría,psicología,emergencias,hospitalización', FALSE, TRUE, TRUE, 4.2, '{"lunes":"8:00-20:00","martes":"8:00-20:00","miercoles":"8:00-20:00","jueves":"8:00-20:00","viernes":"8:00-20:00","sabado":"8:00-14:00","domingo":"Cerrado"}'),
('Hospital Hermilio Valdizán', 'Carretera Central Km 3.5', 'Santa Anita', 'Lima', -12.0456, -76.9734, '01-362-0902', 'informes@hhv.gob.pe', 'psiquiatría,emergencias,hospitalización,rehabilitación', FALSE, TRUE, TRUE, 4.0, '{"lunes":"24h","martes":"24h","miercoles":"24h","jueves":"24h","viernes":"24h","sabado":"24h","domingo":"24h"}'),
('Hospital Víctor Larco Herrera', 'Av. Del Río 601', 'Magdalena del Mar', 'Lima', -12.0912, -77.0789, '01-261-5516', NULL, 'psiquiatría,hospitalización,emergencias', FALSE, TRUE, TRUE, 3.8, '{"lunes":"24h","martes":"24h","miercoles":"24h","jueves":"24h","viernes":"24h","sabado":"24h","domingo":"24h"}'),
('Clínica Ricardo Palma - Salud Mental', 'Av. Javier Prado Este 1066', 'San Isidro', 'Lima', -12.0899, -77.0234, '01-224-2224', NULL, 'psiquiatría,psicología,emergencias', FALSE, TRUE, TRUE, 4.4, '{"lunes":"24h","martes":"24h","miercoles":"24h","jueves":"24h","viernes":"24h","sabado":"24h","domingo":"24h"}'),
('Centro de Salud Mental Comunitario Villa El Salvador', 'Av. César Vallejo s/n Sector 2', 'Villa El Salvador', 'Lima', -12.2134, -76.9456, '01-287-3421', NULL, 'psicología,terapia familiar,atención infantil', TRUE, FALSE, TRUE, 4.0, '{"lunes":"8:00-17:00","martes":"8:00-17:00","miercoles":"8:00-17:00","jueves":"8:00-17:00","viernes":"8:00-17:00","sabado":"Cerrado","domingo":"Cerrado"}');

-- Psychoeducation Content (Muestra Seleccionada)
INSERT INTO psychoeducation_content 
(topic, content_type, title, content, length, language) 
VALUES
('anxiety', 'explanation', '¿Por qué siento ansiedad?', 'La ansiedad es una respuesta natural de tu cuerpo ante el peligro. Tu cerebro activa el "modo alerta" para protegerte. El problema es cuando se activa sin peligro real. Esto NO significa que estés "loco", significa que tu sistema de alarma es muy sensible. Es tratable y puedes aprender a manejarlo.', 'short', 'es-PE'),
('anxiety', 'technique', 'Técnica 5-4-3-2-1 para ansiedad', 'Cuando sientas ansiedad fuerte, intenta esto:\n• 5 cosas que VES a tu alrededor\n• 4 cosas que TOCAS (textura de tu ropa, silla, etc)\n• 3 cosas que ESCUCHAS\n• 2 cosas que HUELES\n• 1 cosa que SABOREAS\n\nEsto ayuda a tu cerebro a volver al presente y salir del "modo pánico".', 'medium', 'es-PE'),
('anxiety', 'technique', 'Respiración 4-7-8 (Calma rápida)', 'Esta técnica reduce la ansiedad en 2-3 minutos:\n1. Inhala por la nariz contando hasta 4\n2. Mantén el aire contando hasta 7\n3. Exhala por la boca contando hasta 8\n4. Repite 4 veces\n\nLo importante es que la exhalación sea más larga que la inhalación. Esto activa tu sistema nervioso parasimpático (el que te calma).', 'medium', 'es-PE'),
('depression', 'explanation', '¿Qué es la depresión?', 'La depresión NO es solo tristeza. Es una condición que afecta:\n• Tu energía (agotamiento constante)\n• Tu sueño (demasiado o muy poco)\n• Tu apetito (comer mucho o nada)\n• Tu concentración\n• Tu motivación\n• Tu visión del futuro\n\nNo es debilidad ni flojera. Es una condición médica que necesita tratamiento.', 'medium', 'es-PE'),
('self_harm', 'technique', 'Alternativas al autolesionarse', 'Si sientes la urgencia de hacerte daño, prueba primero:\n• Sostén un hielo en la mano (causa sensación intensa sin daño)\n• Dibuja líneas rojas en tu piel con un marcador\n• Haz ejercicio intenso por 5 minutos\n• Llama a alguien de confianza\n• Sumerge la cara en agua fría\n• Mastica algo muy picante o ácido\n\nEstas alternativas activan las mismas partes del cerebro sin causarte daño permanente.', 'medium', 'es-PE'),
('stress', 'technique', 'Técnica de la pausa de 60 segundos', 'Cuando te sientas abrumado:\n1. Para lo que estás haciendo\n2. Cierra los ojos (si puedes)\n3. Respira profundo 3 veces\n4. Pregúntate: ¿Qué necesito AHORA MISMO?\n5. Haz solo eso, ignora el resto temporalmente\n\nA veces solo necesitas permiso para pausar.', 'short', 'es-PE');

-- ==========================================================
-- FIN DEL ESQUEMA
-- ==========================================================