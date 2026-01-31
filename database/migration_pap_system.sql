-- ============================================
-- MENTTA - Sistema PAP (Primeros Auxilios Psicológicos)
-- Migración de Base de Datos
-- ============================================
-- Fecha: 2026-01-30
-- Descripción: Tablas para el sistema PAP con protocolo ABCDE
-- ============================================

USE mentta;

-- ============================================
-- TABLA: crisis_preferences
-- Preferencias de escalamiento de crisis por usuario
-- ============================================
CREATE TABLE IF NOT EXISTS crisis_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    
    -- Permisos de escalamiento
    notify_psychologist BOOLEAN DEFAULT TRUE,
    notify_emergency_contacts BOOLEAN DEFAULT TRUE,
    auto_call_emergency_line BOOLEAN DEFAULT FALSE, -- Opt-in explícito
    
    -- Configuración de llamada automática
    emergency_line_preference ENUM('113', '106', 'both') DEFAULT '113',
    auto_call_threshold ENUM('critical', 'imminent') DEFAULT 'imminent',
    
    -- Consentimiento legal
    consent_given_at TIMESTAMP NULL,
    consent_ip VARCHAR(45) NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_crisis_prefs_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_auto_call (user_id, auto_call_emergency_line)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Preferencias de escalamiento de crisis por usuario';

-- ============================================
-- TABLA: crisis_resources
-- Recursos de crisis disponibles (líneas de ayuda, centros)
-- ============================================
CREATE TABLE IF NOT EXISTS crisis_resources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resource_type ENUM('phone_line', 'chat', 'center', 'emergency') NOT NULL,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    contact VARCHAR(100), -- Número o URL
    availability VARCHAR(50) DEFAULT '24/7',
    country VARCHAR(3) DEFAULT 'PE',
    region VARCHAR(100), -- "Lima", "Nacional", NULL=todos
    for_crisis_level ENUM('all', 'low', 'moderate', 'high', 'critical', 'imminent') DEFAULT 'all',
    priority INT DEFAULT 1,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_active_priority (active, priority),
    INDEX idx_crisis_level (for_crisis_level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Recursos de crisis disponibles en Perú';

-- Insertar recursos de Perú
INSERT INTO crisis_resources 
(resource_type, name, description, contact, availability, country, region, for_crisis_level, priority, active) 
VALUES
('phone_line', 'Línea 113 - Salud Mental', 'Atención psicológica gratuita del MINSA. Marca 113 opción 5.', '113', '24/7', 'PE', NULL, 'high', 1, TRUE),
('emergency', 'SAMU 106', 'Emergencias médicas inmediatas / intentos de suicidio', '106', '24/7', 'PE', NULL, 'imminent', 1, TRUE),
('phone_line', 'Línea 100', 'Violencia familiar y de género - Ministerio de la Mujer', '100', '24/7', 'PE', NULL, 'moderate', 2, TRUE),
('chat', 'Chat Línea 100', 'Chat para violencia (si no puede hablar por teléfono)', 'https://www.gob.pe/linea100', '24/7', 'PE', NULL, 'moderate', 3, TRUE),
('phone_line', 'Bomberos 116', 'Emergencias y rescates - incluye situaciones de suicidio', '116', '24/7', 'PE', NULL, 'imminent', 2, TRUE);

-- ============================================
-- TABLA: psychoeducation_content
-- Contenido de psicoeducación por temas
-- ============================================
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
    
    INDEX idx_topic_type (topic, content_type),
    INDEX idx_active (active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Contenido de psicoeducación para usuarios';

-- Insertar contenido completo de psicoeducación (50+ registros)
INSERT INTO psychoeducation_content 
(topic, content_type, title, content, length, language) 
VALUES

-- ==================== ANSIEDAD ====================
('anxiety', 'explanation', '¿Por qué siento ansiedad?', 
'La ansiedad es una respuesta natural de tu cuerpo ante el peligro. Tu cerebro activa el "modo alerta" para protegerte. El problema es cuando se activa sin peligro real. Esto NO significa que estés "loco", significa que tu sistema de alarma es muy sensible. Es tratable y puedes aprender a manejarlo.', 
'short', 'es-PE'),

('anxiety', 'technique', 'Técnica 5-4-3-2-1 para ansiedad', 
'Cuando sientas ansiedad fuerte, intenta esto:\n• 5 cosas que VES a tu alrededor\n• 4 cosas que TOCAS (textura de tu ropa, silla, etc)\n• 3 cosas que ESCUCHAS\n• 2 cosas que HUELES\n• 1 cosa que SABOREAS\n\nEsto ayuda a tu cerebro a volver al presente y salir del "modo pánico".', 
'medium', 'es-PE'),

('anxiety', 'technique', 'Respiración 4-7-8 (Calma rápida)', 
'Esta técnica reduce la ansiedad en 2-3 minutos:\n1. Inhala por la nariz contando hasta 4\n2. Mantén el aire contando hasta 7\n3. Exhala por la boca contando hasta 8\n4. Repite 4 veces\n\nLo importante es que la exhalación sea más larga que la inhalación. Esto activa tu sistema nervioso parasimpático (el que te calma).', 
'medium', 'es-PE'),

('anxiety', 'technique', 'Técnica de la mano para ansiedad', 
'Mira tu mano y con el dedo índice de la otra mano:\n1. Sube por el pulgar mientras INHALAS\n2. Baja por el pulgar mientras EXHALAS\n3. Repite con cada dedo\n4. Al terminar los 5 dedos, habrás respirado 5 veces\n\nEsta técnica es discreta y puedes usarla en cualquier lugar sin que nadie lo note.', 
'short', 'es-PE'),

('anxiety', 'explanation', 'Síntomas físicos de la ansiedad', 
'La ansiedad causa síntomas físicos reales:\n• Corazón acelerado\n• Dificultad para respirar\n• Sudoración\n• Temblores\n• Tensión muscular\n• Mareos\n• Dolor de estómago\n\nEstos síntomas NO son peligrosos aunque se sientan así. Son tu cuerpo preparándose para "pelear o huir".', 
'medium', 'es-PE'),

('anxiety', 'myth_buster', 'Mito: La ansiedad es solo nerviosismo', 
'MITO: "Es solo que eres muy nervioso, relájate"\n\nREALIDAD: La ansiedad clínica es diferente al nerviosismo normal. Interfiere con tu vida diaria, causa sufrimiento significativo, y a veces necesita tratamiento profesional. No es algo que puedas "superar" solo con fuerza de voluntad.', 
'short', 'es-PE'),

('anxiety', 'technique', 'Técnica de relajación muscular progresiva', 
'Tensa y relaja cada grupo muscular por 5 segundos:\n1. Pies: aprieta los dedos\n2. Pantorrillas: estíralas\n3. Muslos: apriétalos\n4. Abdomen: contráelo\n5. Manos: cierra los puños\n6. Brazos: flexiona bíceps\n7. Hombros: súbelos a las orejas\n8. Cara: aprieta los ojos\n\nAl relajar cada músculo, la tensión de la ansiedad se libera.', 
'medium', 'es-PE'),

('anxiety', 'technique', 'Técnica STOP para pensamientos ansiosos', 
'Cuando los pensamientos ansiosos te abrumen:\n\nS - STOP: Para mentalmente\nT - TOMA distancia: "Esto es un pensamiento, no la realidad"\nO - OBSERVA: ¿Qué está pasando en mi cuerpo?\nP - PROCEDE: Haz algo pequeño y concreto ahora\n\nLos pensamientos no son hechos. Puedes notarlos sin creerlos.', 
'short', 'es-PE'),

-- ==================== ATAQUES DE PÁNICO ====================
('anxiety', 'explanation', '¿Qué es un ataque de pánico?', 
'Un ataque de pánico es una ola intensa de miedo que aparece de repente. Síntomas:\n• Corazón muy acelerado\n• Sensación de ahogo\n• Mareo o desmayo\n• Hormigueo en manos/cara\n• Miedo a morir o "volverse loco"\n\nDURA 10-20 MINUTOS MÁXIMO. No es peligroso aunque se sienta terrible. Tu cuerpo NO puede mantener ese nivel de alerta más tiempo.', 
'medium', 'es-PE'),

('anxiety', 'technique', 'Qué hacer durante un ataque de pánico', 
'Durante un ataque de pánico:\n1. RECUERDA: No vas a morir, esto pasará en minutos\n2. RESPIRA: Lento, enfocándote en exhalar largo\n3. GROUNDING: Pon los pies en el suelo, siente la gravedad\n4. NO LUCHES: Resistir lo empeora. Deja que pase como una ola\n5. HÁBLATE: "Esto es incómodo pero no peligroso"\n\nDespués: descansa, toma agua, sé amable contigo.', 
'medium', 'es-PE'),

('anxiety', 'myth_buster', 'Mito: Un ataque de pánico significa que algo está muy mal', 
'MITO: "Si tengo ataques de pánico, algo terrible pasa con mi salud"\n\nREALIDAD: Los ataques de pánico son incómodos pero NO peligrosos. No causan ataques al corazón ni daño cerebral. Son tu sistema de alarma activándose por error. Con tratamiento, se pueden controlar completamente.', 
'short', 'es-PE'),

-- ==================== DEPRESIÓN ====================
('depression', 'explanation', '¿Qué es la depresión?', 
'La depresión NO es solo tristeza. Es una condición que afecta:\n• Tu energía (agotamiento constante)\n• Tu sueño (demasiado o muy poco)\n• Tu apetito (comer mucho o nada)\n• Tu concentración\n• Tu motivación\n• Tu visión del futuro\n\nNo es debilidad ni flojera. Es una condición médica que necesita tratamiento.', 
'medium', 'es-PE'),

('depression', 'myth_buster', 'Mito: La depresión es solo tristeza', 
'MITO: "Si quieres, puedes salir de la depresión solo con actitud positiva"\n\nREALIDAD: La depresión es una condición médica que afecta químicamente tu cerebro. No es debilidad ni falta de voluntad. Necesitas ayuda profesional, igual que necesitarías un doctor para una fractura. Pedir ayuda es un acto de fortaleza, no de debilidad.', 
'medium', 'es-PE'),

('depression', 'technique', 'Activación conductual mínima', 
'Cuando sientas que no puedes hacer nada:\n1. Haz UNA cosa pequeña: levántate, toma agua, abre la ventana\n2. No tienes que hacer más\n3. Si puedes hacer otra cosa pequeña después, hazla\n4. Cada pequeña acción cuenta\n\nNo intentes "superar" la depresión. Solo haz lo mínimo necesario para sobrevivir hoy.', 
'medium', 'es-PE'),

('depression', 'technique', 'Lista de placeres mínimos', 
'Crea una lista de cosas que antes te daban placer (aunque ahora no sientas nada):\n• Ver un video corto gracioso\n• Acariciar una mascota\n• Tomar algo caliente\n• Escuchar una canción\n• Sentir el sol en la cara\n\nHaz UNA de estas cosas al día, aunque no tengas ganas. El placer puede volver poco a poco.', 
'short', 'es-PE'),

('depression', 'explanation', 'Señales de alerta de depresión', 
'Busca ayuda profesional si:\n• Estos síntomas duran más de 2 semanas\n• Afectan tu trabajo, estudios o relaciones\n• Tienes pensamientos de hacerte daño\n• No encuentras placer en NADA\n• Sientes que eres una carga para otros\n\nLa depresión es tratable. El 80% de las personas mejoran con tratamiento adecuado.', 
'medium', 'es-PE'),

('depression', 'myth_buster', 'Mito: Los antidepresivos te vuelven adicto', 
'MITO: "Los antidepresivos son drogas que te hacen dependiente"\n\nREALIDAD: Los antidepresivos NO causan adicción. Ayudan a regular los químicos de tu cerebro. Algunos se retiran gradualmente, pero eso no es adicción, es ajuste químico. Millones de personas los usan de forma segura con supervisión médica.', 
'short', 'es-PE'),

('depression', 'technique', 'Rutina mínima de supervivencia', 
'Cuando todo sea muy difícil, solo haz esto:\n\nMañana:\n• Levántate (aunque sea tarde)\n• Lávate la cara\n• Toma agua\n\nTarde:\n• Come algo (aunque sea poco)\n• Abre una ventana o sal brevemente\n\nNoche:\n• Intenta dormir a una hora similar\n\nEsto es suficiente. Estás sobreviviendo, y eso cuenta.', 
'medium', 'es-PE'),

-- ==================== DUELO Y PÉRDIDA ====================
('grief', 'explanation', 'El duelo no es lineal', 
'El duelo no sigue etapas ordenadas. Puedes sentir alivio un día y tristeza profunda al siguiente. Esto es NORMAL.\n\nNo hay un tiempo "correcto" para superar una pérdida. Cada persona tiene su propio proceso.\n\nPermítete sentir lo que sientes sin juzgarte.', 
'short', 'es-PE'),

('grief', 'explanation', 'Tipos de pérdidas que causan duelo', 
'El duelo no solo es por muerte. También puedes hacer duelo por:\n• Una relación que terminó\n• Un trabajo perdido\n• Tu salud (enfermedad crónica)\n• Un sueño o meta que no se cumplió\n• Tu país de origen (migración)\n• Tu juventud o una etapa de vida\n\nTodas estas pérdidas son válidas y merecen ser procesadas.', 
'medium', 'es-PE'),

('grief', 'technique', 'Cómo acompañar el duelo', 
'Cosas que ayudan en el duelo:\n• Hablar de la persona/cosa perdida\n• Llorar cuando lo necesites\n• Mantener rituales significativos\n• Permitirte días malos\n• Conectar con otros que entienden\n\nCosas que NO ayudan:\n• Forzarte a "superarlo"\n• Evitar hablar del tema\n• Sentirte culpable por reír o disfrutar algo', 
'medium', 'es-PE'),

('grief', 'explanation', 'Duelo complicado vs. normal', 
'Busca ayuda profesional si después de varios meses:\n• No puedes aceptar que la pérdida ocurrió\n• Sientes que la vida no tiene sentido sin esa persona/cosa\n• Evitas todo lo que te recuerde la pérdida\n• Tienes pensamientos constantes de querer morir\n• No puedes funcionar en tu vida diaria\n\nEl duelo complicado es tratable con terapia especializada.', 
'medium', 'es-PE'),

-- ==================== TRAUMA ====================
('trauma', 'explanation', '¿Qué es el trauma?', 
'El trauma es una herida emocional causada por eventos abrumadores:\n• Accidentes graves\n• Violencia o abuso\n• Pérdidas repentinas\n• Desastres naturales\n• Experiencias de guerra\n\nNo todos reaccionan igual. Lo que es traumático para uno puede no serlo para otro. Tu reacción es VÁLIDA.', 
'medium', 'es-PE'),

('trauma', 'explanation', 'Síntomas del trauma', 
'El trauma puede causar:\n• Pesadillas o flashbacks\n• Evitar lugares/personas/temas relacionados\n• Estar siempre alerta (sobresalto fácil)\n• Dificultad para dormir\n• Irritabilidad o ira\n• Sentirte "desconectado" de ti mismo\n• Culpa o vergüenza intensa\n\nEstos síntomas son reacciones NORMALES a situaciones anormales.', 
'medium', 'es-PE'),

('trauma', 'technique', 'Grounding para flashbacks', 
'Si sientes que estás reviviendo algo traumático:\n1. MIRA: Nombra 5 colores que ves\n2. TOCA: Siente tus pies en el suelo, algo frío o caliente\n3. ESCUCHA: Identifica 3 sonidos actuales\n4. REPITE: "Estoy en [lugar], es [fecha], estoy seguro/a ahora"\n\nEl flashback es un recuerdo, no está pasando ahora. Tú estás en el presente.', 
'medium', 'es-PE'),

('trauma', 'myth_buster', 'Mito: El tiempo cura todas las heridas', 
'MITO: "Solo necesitas tiempo para superar un trauma"\n\nREALIDAD: El tiempo solo no cura el trauma. Sin procesar, el trauma puede quedarse "atascado" en tu sistema nervioso. Terapias como EMDR, terapia cognitivo-conductual para trauma, o terapia somática ayudan a procesar la experiencia de forma segura.', 
'short', 'es-PE'),

('trauma', 'technique', 'Contenedor mental para recuerdos difíciles', 
'Visualiza un contenedor fuerte (caja, cofre, bóveda) donde puedes guardar recuerdos difíciles temporalmente:\n1. Imagina el contenedor con detalle\n2. "Coloca" el recuerdo doloroso dentro\n3. Ciérralo con llave\n4. Recuerda: puedes abrirlo cuando estés listo/a y con apoyo\n\nEsto te da control sobre cuándo procesar el trauma.', 
'medium', 'es-PE'),

-- ==================== ESTRÉS ====================
('stress', 'explanation', '¿Qué es el estrés crónico?', 
'El estrés agudo (corto) es normal y hasta útil. El problema es el estrés CRÓNICO:\n• Semanas o meses de tensión constante\n• Tu cuerpo nunca se relaja completamente\n• Afecta tu salud física (dolores, digestión, inmunidad)\n• Afecta tu mente (concentración, memoria, humor)\n\nEl estrés crónico necesita intervención activa, no desaparece solo.', 
'medium', 'es-PE'),

('stress', 'technique', 'Técnica de la pausa de 60 segundos', 
'Cuando te sientas abrumado:\n1. Para lo que estás haciendo\n2. Cierra los ojos (si puedes)\n3. Respira profundo 3 veces\n4. Pregúntate: ¿Qué necesito AHORA MISMO?\n5. Haz solo eso, ignora el resto temporalmente\n\nA veces solo necesitas permiso para pausar.', 
'short', 'es-PE'),

('stress', 'technique', 'Técnica de priorización simple', 
'Cuando todo se siente urgente:\n1. Haz una lista de TODO lo que tienes que hacer\n2. Marca con ⭐ lo que tiene CONSECUENCIAS REALES si no lo haces hoy\n3. Tacha lo que puedes delegar o ignorar\n4. Lo que queda: hazlo UNO A LA VEZ\n\nLa sensación de "todo es urgente" generalmente es falsa. Muy pocas cosas son realmente urgentes.', 
'medium', 'es-PE'),

('stress', 'technique', 'Límites saludables', 
'Establecer límites reduce el estrés:\n• "No puedo hacer eso ahora, pero puedo [alternativa]"\n• "Necesito pensarlo antes de comprometerme"\n• "Eso no funciona para mí"\n• "Aprecio la oferta pero debo declinar"\n\nDecir "no" a algunas cosas te permite decir "sí" a tu bienestar.', 
'short', 'es-PE'),

('stress', 'explanation', 'Señales de burnout', 
'El burnout es estrés crónico llevado al extremo:\n• Agotamiento físico y emocional total\n• Cinismo hacia tu trabajo/estudios\n• Sensación de ineficacia\n• Desconexión de lo que antes te importaba\n\nEl burnout requiere cambios reales en tu situación, no solo "más descanso". Considera hablar con un profesional.', 
'medium', 'es-PE'),

-- ==================== AUTOLESIÓN ====================
('self_harm', 'explanation', '¿Por qué algunas personas se autolesionan?', 
'La autolesión NO es un intento de suicidio. Usualmente es:\n• Una forma de manejar dolor emocional intenso\n• Una manera de "sentir algo" cuando te sientes vacío\n• Un intento de castigarte a ti mismo\n• Una forma de comunicar sufrimiento\n\nNo es "llamar la atención" de forma manipuladora. Es una señal de sufrimiento real que necesita ayuda profesional.', 
'medium', 'es-PE'),

('self_harm', 'technique', 'Alternativas al autolesionarse', 
'Si sientes la urgencia de hacerte daño, prueba primero:\n• Sostén un hielo en la mano (causa sensación intensa sin daño)\n• Dibuja líneas rojas en tu piel con un marcador\n• Haz ejercicio intenso por 5 minutos\n• Llama a alguien de confianza\n• Sumerge la cara en agua fría\n• Mastica algo muy picante o ácido\n\nEstas alternativas activan las mismas partes del cerebro sin causarte daño permanente.', 
'medium', 'es-PE'),

('self_harm', 'technique', 'Plan de seguridad personal', 
'Crea tu plan para momentos de crisis:\n1. Señales de alerta: ¿qué sientes antes de la urgencia?\n2. Estrategias propias: ¿qué puedo hacer solo? (lista de 3)\n3. Personas de confianza: ¿a quién puedo llamar? (nombres y teléfonos)\n4. Profesionales: número de mi terapeuta, Línea 113\n5. Recordatorio: ¿por qué quiero estar bien?\n\nTen este plan a la mano siempre.', 
'medium', 'es-PE'),

('self_harm', 'myth_buster', 'Mito: La autolesión es solo para llamar la atención', 
'MITO: "Se corta para manipular o llamar la atención"\n\nREALIDAD: La mayoría de personas que se autolesionan lo OCULTAN. Sienten vergüenza, no orgullo. Es un mecanismo de afrontamiento disfuncional para dolor emocional real. Necesitan compasión y ayuda profesional, no juicio.', 
'short', 'es-PE'),

-- ==================== RELACIONES ====================
('relationships', 'explanation', 'Señales de una relación tóxica', 
'Una relación puede ser tóxica si:\n• Te sientes peor contigo mismo/a después de interactuar\n• Tienes miedo de expresar tus opiniones\n• Te controla o aísla de otros\n• Te culpa de sus problemas o reacciones\n• Hay insultos, humillaciones o violencia\n• Sientes que caminas "sobre cáscaras de huevo"\n\nMerecer amor y respeto no es negociable.', 
'medium', 'es-PE'),

('relationships', 'explanation', 'Señales de una relación saludable', 
'Una relación saludable tiene:\n• Respeto mutuo\n• Comunicación honesta\n• Espacio para ser tú mismo/a\n• Apoyo en momentos difíciles\n• Capacidad de resolver conflictos sin agresión\n• Confianza\n• Ambos pueden decir "no" sin consecuencias graves\n\nNinguna relación es perfecta, pero las saludables te hacen sentir MEJOR, no peor.', 
'medium', 'es-PE'),

('relationships', 'technique', 'Comunicación no violenta', 
'Para expresar necesidades sin atacar:\n1. OBSERVACIÓN: "Cuando [situación específica]..."\n2. SENTIMIENTO: "Yo me siento [emoción]..."\n3. NECESIDAD: "Porque necesito [necesidad]..."\n4. PETICIÓN: "¿Podrías [acción concreta]?"\n\nEjemplo: "Cuando llegas tarde sin avisar, me siento preocupado porque necesito saber que estás bien. ¿Podrías enviarme un mensaje si te retrasas?"', 
'medium', 'es-PE'),

('relationships', 'technique', 'Cómo poner límites con personas difíciles', 
'Pasos para establecer límites:\n1. Decide qué comportamiento NO aceptarás\n2. Comunícalo de forma clara y breve\n3. Indica la consecuencia si no se respeta\n4. CUMPLE la consecuencia si es necesario\n\nEjemplo: "No voy a continuar esta conversación si me gritas. Si sigues gritando, me iré."\n\nLos límites protegen tu bienestar. No son castigo.', 
'medium', 'es-PE'),

('relationships', 'resource', 'Violencia en la pareja - Recursos', 
'Si estás en una relación violenta:\n• Línea 100: Violencia familiar y de género (24/7)\n• CEM (Centros de Emergencia Mujer): atención gratuita\n• Comisaría más cercana: denuncia\n\nNo es tu culpa. El abuso nunca está justificado. La violencia tiende a ESCALAR con el tiempo.\n\nSi no puedes irte ahora, crea un plan de seguridad y guarda documentos importantes.', 
'medium', 'es-PE'),

-- ==================== SUEÑO ====================
('general', 'technique', 'Higiene del sueño básica', 
'Para dormir mejor:\n• Acuéstate y levántate a horas similares\n• Evita pantallas 1 hora antes de dormir\n• Cuarto oscuro y fresco\n• No uses la cama para trabajar o ver TV\n• Evita cafeína después de las 2pm\n• Ejercicio sí, pero no justo antes de dormir\n\nEl sueño es FUNDAMENTAL para la salud mental.', 
'medium', 'es-PE'),

('general', 'technique', 'Qué hacer si no puedes dormir', 
'Si llevas más de 20 minutos sin dormir:\n1. Levántate de la cama\n2. Ve a otro espacio (si puedes)\n3. Haz algo aburrido con luz tenue (leer algo no estimulante)\n4. Cuando sientas sueño, vuelve a la cama\n5. Repite si es necesario\n\nQuedarte en la cama sin dormir entrena a tu cerebro a asociar la cama con frustración.', 
'medium', 'es-PE'),

('general', 'explanation', 'Sueño y salud mental', 
'El sueño y la salud mental están conectados:\n• Dormir mal EMPEORA ansiedad, depresión, estrés\n• Problemas emocionales DIFICULTAN dormir bien\n\nEs un ciclo que puede romperse. Priorizar el sueño es una de las intervenciones más efectivas para mejorar tu estado emocional.', 
'short', 'es-PE'),

-- ==================== MANEJO DE IRA ====================
('general', 'explanation', '¿Por qué siento tanta ira?', 
'La ira es una emoción normal. Se vuelve problema cuando:\n• Explotas con frecuencia\n• Dices/haces cosas que luego lamentas\n• Afecta tus relaciones\n• Te sientes fuera de control\n\nDebajo de la ira usualmente hay otras emociones: miedo, tristeza, frustración, impotencia. La ira las "protege".', 
'medium', 'es-PE'),

('general', 'technique', 'Técnica TIPP para ira intensa', 
'Cuando sientas que vas a explotar:\n\nT - Temperatura: Agua fría en cara o manos\nI - Intensidad: Ejercicio intenso por 5-10 min\nP - Pace (ritmo): Respiración lenta (exhala más largo)\nP - Paired relaxation: Tensa y relaja músculos\n\nEstas técnicas bajan la activación física de la ira para que puedas pensar con claridad.', 
'medium', 'es-PE'),

('general', 'technique', 'Time-out constructivo', 
'Cuando sientas que vas a decir algo hiriente:\n1. Di: "Necesito un momento, volvamos a esto en 20 minutos"\n2. Aléjate físicamente\n3. Haz algo que baje tu activación (caminar, agua fría)\n4. Regresa cuando estés más calmado\n5. Retoma la conversación\n\nEsto NO es "huir". Es proteger la relación de palabras que no puedes retirar.', 
'short', 'es-PE'),

-- ==================== GENERAL ====================
('general', 'resource', 'Centros de Salud Mental Comunitaria', 
'En Perú existen Centros de Salud Mental Comunitaria (CSMC) gratuitos en cada distrito. Ofrecen:\n• Psicología\n• Psiquiatría\n• Terapia grupal\n• Visitas domiciliarias en crisis\n\nNo necesitas referencia médica, puedes ir directamente. Busca "CSMC [tu distrito]" en Google para encontrar el más cercano.', 
'medium', 'es-PE'),

('general', 'explanation', 'Cuándo buscar ayuda profesional', 
'Considera buscar ayuda profesional si:\n• Los síntomas duran más de 2 semanas\n• Afectan tu trabajo, estudios o relaciones\n• Tienes pensamientos de hacerte daño\n• Usas alcohol/drogas para sentirte mejor\n• Sientes que no puedes manejarlo solo/a\n\nBuscar ayuda es un acto de valentía, no de debilidad.', 
'medium', 'es-PE'),

('general', 'myth_buster', 'Mito: Ir al psicólogo es solo para locos', 
'MITO: "Solo los locos van al psicólogo"\n\nREALIDAD: Los psicólogos ayudan a personas normales con problemas normales: estrés, duelo, ansiedad, problemas de pareja, transiciones de vida. Ir al psicólogo es como ir al gimnasio para tu mente. No necesitas estar "loco" para beneficiarte.', 
'short', 'es-PE'),

('general', 'explanation', 'Diferencia entre psicólogo y psiquiatra', 
'PSICÓLOGO:\n• Hace terapia (conversar, técnicas, herramientas)\n• NO puede recetar medicamentos\n• Para la mayoría de problemas emocionales\n\nPSIQUIATRA:\n• Es médico especializado\n• PUEDE recetar medicamentos\n• Para casos que necesitan medicación o diagnósticos complejos\n\nMuchas veces trabajan juntos como equipo.', 
'medium', 'es-PE'),

('general', 'technique', 'Diario de emociones básico', 
'Escribe diariamente:\n1. ¿Qué emoción sentí hoy? (pon nombre)\n2. ¿Qué la disparó? (situación)\n3. ¿Qué hice? (reacción)\n4. ¿Funcionó? (resultado)\n\nCon el tiempo verás patrones. Esto te ayuda a conocerte mejor y anticipar situaciones difíciles.', 
'short', 'es-PE'),

('general', 'technique', 'Autocuidado no es egoísmo', 
'El autocuidado incluye:\n• Descansar cuando estás agotado\n• Decir no a compromisos excesivos\n• Hacer cosas que te gustan\n• Cuidar tu cuerpo (comida, sueño, movimiento)\n• Pedir ayuda cuando la necesitas\n\nNo puedes cuidar a otros si estás vacío. Cuidarte es necesario, no egoísta.', 
'short', 'es-PE'),

('general', 'technique', 'Práctica de gratitud', 
'Cada noche, escribe 3 cosas por las que estás agradecido/a:\n• Pueden ser pequeñas (café caliente, un mensaje amable)\n• No tienen que ser "importantes"\n• Intenta que sean diferentes cada día\n\nEsta práctica entrena a tu cerebro a notar lo positivo. No niega lo negativo, solo amplía tu perspectiva.', 
'short', 'es-PE'),

('general', 'explanation', 'Medicamentos para salud mental', 
'Los medicamentos psiquiátricos:\n• Solo los receta un psiquiatra (médico)\n• No son "drogas" que te hacen adicto\n• Pueden tomar semanas en hacer efecto\n• Tienen efectos secundarios que usualmente mejoran\n• Se retiran gradualmente, nunca de golpe\n\nSon una herramienta válida, no una muleta ni una vergüenza.', 
'medium', 'es-PE'),

('general', 'resource', 'Apps de apoyo emocional', 
'Apps gratuitas que pueden ayudar:\n• Calm/Headspace: meditación guiada\n• Daylio: seguimiento de estado de ánimo\n• Woebot: chatbot de salud mental\n• Breathe2Relax: ejercicios de respiración\n\nEstas apps son COMPLEMENTO, no reemplazo de ayuda profesional cuando la necesitas.', 
'short', 'es-PE'),

('general', 'technique', 'Cómo apoyar a alguien en crisis', 
'Si alguien te cuenta que está pasando un momento muy difícil:\n1. ESCUCHA sin juzgar ni interrumpir\n2. VALIDA: "Eso suena muy difícil"\n3. NO minimices: evita "pero podrías estar peor"\n4. PREGUNTA: "¿Cómo puedo ayudarte?"\n5. SUGIERE ayuda profesional suavemente\n6. NO prometas guardar secretos sobre autolesión o suicidio\n\nTu presencia ya es ayuda.', 
'medium', 'es-PE'),

('general', 'myth_buster', 'Mito: Si pregunto sobre suicidio, le daré la idea', 
'MITO: "Si le pregunto a alguien si está pensando en suicidarse, le voy a dar la idea"\n\nREALIDAD: Preguntar directamente sobre suicidio NO aumenta el riesgo. De hecho, permite que la persona hable de algo que probablemente ya está pensando. Preguntar puede salvar vidas.', 
'short', 'es-PE'),

('general', 'explanation', 'Salud mental en el trabajo/estudios', 
'Tu salud mental afecta tu rendimiento:\n• Estrés crónico reduce concentración y creatividad\n• La ansiedad dificulta tomar decisiones\n• La depresión afecta motivación y energía\n\nCuidar tu salud mental ES cuidar tu productividad. No son opuestos. Muchas empresas/universidades tienen servicios de apoyo psicológico gratuitos.', 
'medium', 'es-PE'),

('general', 'technique', 'Mindfulness en 1 minuto', 
'Meditación express para cualquier momento:\n1. Cierra los ojos (o mira un punto fijo)\n2. Nota 3 respiraciones sin cambiarlas\n3. Nota qué sensaciones hay en tu cuerpo\n4. Nota qué sonidos escuchas\n5. Abre los ojos\n\nNo necesitas "vaciar tu mente". Solo observar lo que hay, sin juzgar.', 
'short', 'es-PE');

-- ============================================
-- MODIFICAR TABLA: conversations
-- Añadir columnas para protocolo PAP
-- ============================================
-- Nota: Usamos ALTER TABLE con IF NOT EXISTS simulado para evitar errores

-- Añadir columna final_risk_level (0-5)
SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_SCHEMA = 'mentta' 
               AND TABLE_NAME = 'conversations' 
               AND COLUMN_NAME = 'final_risk_level');

SET @sql := IF(@exist = 0, 
    'ALTER TABLE conversations ADD COLUMN final_risk_level INT DEFAULT 0 COMMENT "0-5, decidido por IA" AFTER risk_level',
    'SELECT "Column final_risk_level already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Añadir columna pap_phase
SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_SCHEMA = 'mentta' 
               AND TABLE_NAME = 'conversations' 
               AND COLUMN_NAME = 'pap_phase');

SET @sql := IF(@exist = 0, 
    'ALTER TABLE conversations ADD COLUMN pap_phase CHAR(1) DEFAULT NULL COMMENT "Fase PAP: A,B,C,D,E" AFTER final_risk_level',
    'SELECT "Column pap_phase already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Añadir columna requires_confirmation
SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_SCHEMA = 'mentta' 
               AND TABLE_NAME = 'conversations' 
               AND COLUMN_NAME = 'requires_confirmation');

SET @sql := IF(@exist = 0, 
    'ALTER TABLE conversations ADD COLUMN requires_confirmation BOOLEAN DEFAULT FALSE AFTER pap_phase',
    'SELECT "Column requires_confirmation already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Añadir columna session_id si no existe
SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_SCHEMA = 'mentta' 
               AND TABLE_NAME = 'conversations' 
               AND COLUMN_NAME = 'session_id');

SET @sql := IF(@exist = 0, 
    'ALTER TABLE conversations ADD COLUMN session_id VARCHAR(100) DEFAULT NULL AFTER patient_id',
    'SELECT "Column session_id already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Añadir índice para búsquedas de crisis
CREATE INDEX IF NOT EXISTS idx_risk_crisis ON conversations(patient_id, final_risk_level, created_at);

-- ============================================
-- FIN DE LA MIGRACIÓN PAP
-- ============================================
