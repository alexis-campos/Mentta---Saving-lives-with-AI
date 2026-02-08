-- TABLA: psychologist_codes
-- Códigos temporales para vincular pacientes con psicólogos
CREATE TABLE IF NOT EXISTS psychologist_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    psychologist_id INT NOT NULL,
    code VARCHAR(6) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    
    CONSTRAINT fk_codes_psychologist 
        FOREIGN KEY (psychologist_id) REFERENCES users(id) 
        ON DELETE CASCADE,
    UNIQUE KEY unique_code (code),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Códigos temporales de vinculación paciente-psicólogo';
