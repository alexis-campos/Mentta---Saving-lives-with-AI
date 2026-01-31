-- ============================================
-- MENTTA - Migration: Mental Health Centers Map
-- Version: 0.5.2
-- Date: 2026-01-30
-- Description: Creates table for mental health centers with geolocation
-- ============================================

USE mentta;

-- ============================================
-- 1. Create mental_health_centers table
-- ============================================
CREATE TABLE IF NOT EXISTS mental_health_centers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Información básica
    name VARCHAR(200) NOT NULL,
    address TEXT NOT NULL,
    district VARCHAR(100),
    city VARCHAR(100) DEFAULT 'Lima',
    
    -- Geolocalización
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    
    -- Contacto
    phone VARCHAR(20),
    email VARCHAR(100),
    website VARCHAR(255),
    
    -- Servicios
    services TEXT COMMENT 'Comma-separated: psicología,psiquiatría,emergencias',
    accepts_insurance BOOLEAN DEFAULT TRUE,
    insurance_providers TEXT COMMENT 'Comma-separated: Pacífico,Rimac,EPS',
    
    -- Características especiales
    has_mentta BOOLEAN DEFAULT FALSE COMMENT 'Uses Mentta platform',
    emergency_24h BOOLEAN DEFAULT FALSE COMMENT '24/7 emergency service',
    
    -- Horarios (JSON)
    schedule JSON COMMENT '{"lunes": "8am-6pm", ...}',
    
    -- Metadata
    verified BOOLEAN DEFAULT FALSE,
    rating DECIMAL(2,1) DEFAULT 0.0 COMMENT 'Rating 0.0 to 5.0',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Índices para búsqueda geográfica eficiente
    INDEX idx_location (latitude, longitude),
    INDEX idx_city (city),
    INDEX idx_district (district),
    INDEX idx_verified (verified),
    INDEX idx_mentta (has_mentta),
    INDEX idx_emergency (emergency_24h),
    
    -- Full text search
    FULLTEXT idx_search (name, address, district)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Mental health centers for map feature';

-- ============================================
-- 2. Insert sample data (Lima, Peru)
-- ============================================
INSERT INTO mental_health_centers 
(name, address, district, city, latitude, longitude, phone, email, services, has_mentta, emergency_24h, verified, rating, schedule)
VALUES
-- Hospitales y centros públicos principales
('Instituto Nacional de Salud Mental Honorio Delgado-Hideyo Noguchi', 
 'Jr. Eloy Espinoza 709', 'San Martín de Porres', 'Lima', 
 -12.0234, -77.0856, '01-614-9200', 'informes@insm.gob.pe',
 'psiquiatría,psicología,emergencias,hospitalización', 
 FALSE, TRUE, TRUE, 4.2,
 '{"lunes":"8:00-20:00","martes":"8:00-20:00","miercoles":"8:00-20:00","jueves":"8:00-20:00","viernes":"8:00-20:00","sabado":"8:00-14:00","domingo":"Cerrado"}'),

('Hospital Hermilio Valdizán', 
 'Carretera Central Km 3.5', 'Santa Anita', 'Lima', 
 -12.0456, -76.9734, '01-362-0902', 'informes@hhv.gob.pe',
 'psiquiatría,emergencias,hospitalización,rehabilitación', 
 FALSE, TRUE, TRUE, 4.0,
 '{"lunes":"24h","martes":"24h","miercoles":"24h","jueves":"24h","viernes":"24h","sabado":"24h","domingo":"24h"}'),

('Hospital Víctor Larco Herrera', 
 'Av. Del Río 601', 'Magdalena del Mar', 'Lima', 
 -12.0912, -77.0789, '01-261-5516', NULL,
 'psiquiatría,hospitalización,emergencias', 
 FALSE, TRUE, TRUE, 3.8,
 '{"lunes":"24h","martes":"24h","miercoles":"24h","jueves":"24h","viernes":"24h","sabado":"24h","domingo":"24h"}'),

-- Clínicas privadas
('Clínica San Felipe - Salud Mental', 
 'Av. Gregorio Escobedo 650', 'Jesús María', 'Lima', 
 -12.0723, -77.0503, '01-219-0000', 'citas@sanfelipe.com.pe',
 'psicología,psiquiatría,terapia familiar', 
 FALSE, FALSE, TRUE, 4.5,
 '{"lunes":"8:00-20:00","martes":"8:00-20:00","miercoles":"8:00-20:00","jueves":"8:00-20:00","viernes":"8:00-20:00","sabado":"8:00-14:00","domingo":"Cerrado"}'),

('Clínica Montesur - Área de Psiquiatría', 
 'Av. Tomás Marsano 1280', 'Surquillo', 'Lima', 
 -12.1123, -77.0056, '01-207-4000', 'citas@montesur.com.pe',
 'psicología,psiquiatría,neuropsicología', 
 TRUE, FALSE, TRUE, 4.6,
 '{"lunes":"7:00-21:00","martes":"7:00-21:00","miercoles":"7:00-21:00","jueves":"7:00-21:00","viernes":"7:00-21:00","sabado":"8:00-14:00","domingo":"Cerrado"}'),

('Clínica Ricardo Palma - Salud Mental', 
 'Av. Javier Prado Este 1066', 'San Isidro', 'Lima', 
 -12.0899, -77.0234, '01-224-2224', NULL,
 'psiquiatría,psicología,emergencias', 
 FALSE, TRUE, TRUE, 4.4,
 '{"lunes":"24h","martes":"24h","miercoles":"24h","jueves":"24h","viernes":"24h","sabado":"24h","domingo":"24h"}'),

-- Centros comunitarios de salud mental (MINSA)
('Centro de Salud Mental Comunitario San Juan de Miraflores', 
 'Av. Guillermo Billinghurst 1069', 'San Juan de Miraflores', 'Lima', 
 -12.1567, -76.9745, '01-276-5641', NULL,
 'psicología,terapia grupal,atención comunitaria', 
 TRUE, FALSE, TRUE, 4.1,
 '{"lunes":"8:00-17:00","martes":"8:00-17:00","miercoles":"8:00-17:00","jueves":"8:00-17:00","viernes":"8:00-17:00","sabado":"Cerrado","domingo":"Cerrado"}'),

('Centro de Salud Mental Comunitario Villa El Salvador', 
 'Av. César Vallejo s/n Sector 2', 'Villa El Salvador', 'Lima', 
 -12.2134, -76.9456, '01-287-3421', NULL,
 'psicología,terapia familiar,atención infantil', 
 TRUE, FALSE, TRUE, 4.0,
 '{"lunes":"8:00-17:00","martes":"8:00-17:00","miercoles":"8:00-17:00","jueves":"8:00-17:00","viernes":"8:00-17:00","sabado":"Cerrado","domingo":"Cerrado"}'),

('Centro de Salud Mental Comunitario Comas', 
 'Av. Túpac Amaru Km 11', 'Comas', 'Lima', 
 -11.9456, -77.0567, '01-537-2890', NULL,
 'psicología,psiquiatría,terapia grupal', 
 TRUE, FALSE, TRUE, 3.9,
 '{"lunes":"8:00-17:00","martes":"8:00-17:00","miercoles":"8:00-17:00","jueves":"8:00-17:00","viernes":"8:00-17:00","sabado":"Cerrado","domingo":"Cerrado"}'),

-- Instituciones especializadas
('Centro Ann Sullivan del Perú', 
 'Av. Petronila Alvarez 180', 'San Miguel', 'Lima', 
 -12.0776, -77.0877, '01-263-3644', 'info@annsullivanperu.org',
 'psicología,terapia conductual,atención TEA', 
 FALSE, FALSE, TRUE, 4.7,
 '{"lunes":"8:00-18:00","martes":"8:00-18:00","miercoles":"8:00-18:00","jueves":"8:00-18:00","viernes":"8:00-18:00","sabado":"Cerrado","domingo":"Cerrado"}'),

('Instituto Gestalt de Lima', 
 'Calle Monterosa 371, Chacarilla', 'Santiago de Surco', 'Lima', 
 -12.0945, -76.9934, '01-372-0531', 'contacto@gestaltlima.com',
 'psicología,terapia gestalt,coaching', 
 TRUE, FALSE, TRUE, 4.5,
 '{"lunes":"9:00-20:00","martes":"9:00-20:00","miercoles":"9:00-20:00","jueves":"9:00-20:00","viernes":"9:00-20:00","sabado":"9:00-14:00","domingo":"Cerrado"}'),

('Centro Psicológico Miraflores', 
 'Av. Benavides 1555', 'Miraflores', 'Lima', 
 -12.1234, -77.0289, '01-445-6789', 'citas@psicologiamiraflores.pe',
 'psicología,terapia individual,terapia de pareja', 
 TRUE, FALSE, TRUE, 4.3,
 '{"lunes":"9:00-21:00","martes":"9:00-21:00","miercoles":"9:00-21:00","jueves":"9:00-21:00","viernes":"9:00-21:00","sabado":"9:00-15:00","domingo":"Cerrado"}'),

('Centro de Salud Mental La Molina', 
 'Av. La Molina 1234', 'La Molina', 'Lima', 
 -12.0678, -76.9456, '01-348-9012', NULL,
 'psicología,psiquiatría,neurología', 
 FALSE, FALSE, TRUE, 4.2,
 '{"lunes":"8:00-18:00","martes":"8:00-18:00","miercoles":"8:00-18:00","jueves":"8:00-18:00","viernes":"8:00-18:00","sabado":"8:00-12:00","domingo":"Cerrado"}'),

('Centro de Atención Psicosocial MINSA', 
 'Av. Salaverry 801', 'Jesús María', 'Lima', 
 -12.0834, -77.0512, '01-315-6600', NULL,
 'psicología,terapia familiar,atención crisis', 
 FALSE, FALSE, TRUE, 4.0,
 '{"lunes":"8:00-17:00","martes":"8:00-17:00","miercoles":"8:00-17:00","jueves":"8:00-17:00","viernes":"8:00-17:00","sabado":"Cerrado","domingo":"Cerrado"}'),

-- Centro adicionales
('Consultorio Psicológico San Borja', 
 'Av. San Borja Sur 678', 'San Borja', 'Lima', 
 -12.1012, -77.0034, '01-226-7890', 'consultas@psicosanborja.pe',
 'psicología,coaching,mindfulness', 
 TRUE, FALSE, TRUE, 4.4,
 '{"lunes":"9:00-20:00","martes":"9:00-20:00","miercoles":"9:00-20:00","jueves":"9:00-20:00","viernes":"9:00-20:00","sabado":"9:00-13:00","domingo":"Cerrado"}'),

('Centro Terapéutico Barranco', 
 'Jr. Domeyer 326', 'Barranco', 'Lima', 
 -12.1456, -77.0234, '01-247-5678', 'info@terapiabarranco.com',
 'psicología,arteterapia,musicoterapia', 
 FALSE, FALSE, TRUE, 4.6,
 '{"lunes":"10:00-20:00","martes":"10:00-20:00","miercoles":"10:00-20:00","jueves":"10:00-20:00","viernes":"10:00-20:00","sabado":"10:00-14:00","domingo":"Cerrado"}'),

('Clínica Psiquiátrica Delgado', 
 'Av. Arequipa 3456', 'San Isidro', 'Lima', 
 -12.0989, -77.0345, '01-421-7654', 'citas@clinicadelgado.pe',
 'psiquiatría,hospitalización,tratamiento adicciones', 
 FALSE, TRUE, TRUE, 4.3,
 '{"lunes":"24h","martes":"24h","miercoles":"24h","jueves":"24h","viernes":"24h","sabado":"24h","domingo":"24h"}'),

('Centro de Bienestar Mental Surco', 
 'Av. Caminos del Inca 2345', 'Santiago de Surco', 'Lima', 
 -12.1234, -76.9789, '01-344-5678', 'bienestar@mentalsurco.pe',
 'psicología,meditación,terapia holística', 
 TRUE, FALSE, TRUE, 4.5,
 '{"lunes":"8:00-21:00","martes":"8:00-21:00","miercoles":"8:00-21:00","jueves":"8:00-21:00","viernes":"8:00-21:00","sabado":"9:00-15:00","domingo":"Cerrado"}'),

('ESSALUD - Hospital Rebagliati - Salud Mental', 
 'Av. Rebagliati 490', 'Jesús María', 'Lima', 
 -12.0789, -77.0412, '01-265-4901', NULL,
 'psiquiatría,psicología,emergencias', 
 FALSE, TRUE, TRUE, 3.9,
 '{"lunes":"24h","martes":"24h","miercoles":"24h","jueves":"24h","viernes":"24h","sabado":"24h","domingo":"24h"}'),

('Centro de Salud Mental Ate', 
 'Av. Los Quechuas 789', 'Ate', 'Lima', 
 -12.0256, -76.9123, '01-350-4567', NULL,
 'psicología,atención comunitaria', 
 FALSE, FALSE, TRUE, 3.8,
 '{"lunes":"8:00-17:00","martes":"8:00-17:00","miercoles":"8:00-17:00","jueves":"8:00-17:00","viernes":"8:00-17:00","sabado":"Cerrado","domingo":"Cerrado"}');

-- ============================================
-- END MIGRATION
-- ============================================
