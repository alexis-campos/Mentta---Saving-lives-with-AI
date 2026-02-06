-- ============================================
-- MENTTA - Datos de Prueba (Seed)
-- ============================================
-- IMPORTANTE: Ejecutar DESPUÉS de schema.sql
-- Password para todos los usuarios: Demo2025
-- Hash generado con password_hash('Demo2025', PASSWORD_BCRYPT)
-- ============================================

-- Comentado para compatibilidad con Railway (usa la DB asignada)
-- USE mentta;

-- ============================================
-- USUARIOS: Psicólogos y Pacientes
-- IDs explícitos para garantizar integridad referencial
-- ============================================
INSERT IGNORE INTO users (id, email, password_hash, name, age, role, language) VALUES
(1, 'psicologo1@mentta.com', '$2y$10$HRkTOJLT75dOsYUkWIQRdesgaqTN25B.M6fhmUX5Ez76a6Qk9DqPq', 'Dra. María Rodríguez', 38, 'psychologist', 'es'),
(2, 'psicologo2@mentta.com', '$2y$10$HRkTOJLT75dOsYUkWIQRdesgaqTN25B.M6fhmUX5Ez76a6Qk9DqPq', 'Dr. Carlos Méndez', 45, 'psychologist', 'es'),
(3, 'paciente1@mentta.com', '$2y$10$HRkTOJLT75dOsYUkWIQRdesgaqTN25B.M6fhmUX5Ez76a6Qk9DqPq', 'Ana García', 24, 'patient', 'es'),
(4, 'paciente2@mentta.com', '$2y$10$HRkTOJLT75dOsYUkWIQRdesgaqTN25B.M6fhmUX5Ez76a6Qk9DqPq', 'Pedro López', 30, 'patient', 'es'),
(5, 'paciente3@mentta.com', '$2y$10$HRkTOJLT75dOsYUkWIQRdesgaqTN25B.M6fhmUX5Ez76a6Qk9DqPq', 'Laura Torres', 22, 'patient', 'es'),
(6, 'paciente4@mentta.com', '$2y$10$HRkTOJLT75dOsYUkWIQRdesgaqTN25B.M6fhmUX5Ez76a6Qk9DqPq', 'Miguel Ríos', 28, 'patient', 'es'),
(7, 'paciente5@mentta.com', '$2y$10$HRkTOJLT75dOsYUkWIQRdesgaqTN25B.M6fhmUX5Ez76a6Qk9DqPq', 'Sofía Ramos', 26, 'patient', 'es');

-- ============================================
-- ENLACES PACIENTE-PSICÓLOGO
-- ============================================
INSERT IGNORE INTO patient_psychologist_link (patient_id, psychologist_id, status, notes) VALUES
(3, 1, 'active', 'Paciente referida por médico de cabecera. Presenta síntomas de ansiedad.'),
(4, 1, 'active', 'Paciente en tratamiento por depresión leve. Progreso favorable.'),
(5, 1, 'active', 'Estudiante universitaria con estrés académico.'),
(6, 2, 'active', 'Paciente con historial de ataques de pánico. En observación.'),
(7, 2, 'active', 'Paciente nueva, primera consulta hace 2 semanas.');

-- ============================================
-- MEMORIAS CONTEXTUALES DE PACIENTES
-- ============================================

-- Ana García (id=3)
INSERT IGNORE INTO patient_memory (patient_id, memory_type, key_name, value, context, importance) VALUES
(3, 'relationship', 'hermana', 'María, 28 años', 'Mi hermana María siempre me apoya cuando me siento mal', 4),
(3, 'relationship', 'novio', 'Lucas', 'Lucas y yo llevamos 2 años juntos pero últimamente discutimos mucho', 5),
(3, 'event', 'trabajo_actual', 'Diseñadora gráfica en agencia', 'Trabajo como diseñadora en una agencia pequeña', 3),
(3, 'event', 'universidad', 'Estudió diseño en la PUCP', 'Me gradué de diseño en la PUCP hace 2 años', 2),
(3, 'preference', 'hobbie', 'Pintar y dibujar', 'Me gusta pintar cuando estoy estresada, me relaja', 3),
(3, 'emotion', 'trigger_ansiedad', 'Fechas de entrega en el trabajo', 'Cuando tengo muchas entregas me pongo muy ansiosa', 5),
(3, 'location', 'vive_con', 'Sola en departamento en Miraflores', 'Vivo sola en Miraflores desde hace un año', 3);

-- Pedro López (id=4)
INSERT IGNORE INTO patient_memory (patient_id, memory_type, key_name, value, context, importance) VALUES
(4, 'relationship', 'esposa', 'Carmen, 29 años', 'Carmen y yo llevamos 5 años de casados', 5),
(4, 'relationship', 'hijo', 'Mateo, 3 años', 'Mi hijo Mateo es lo más importante de mi vida', 5),
(4, 'event', 'despido', 'Perdió trabajo en Enero 2026', 'Me despidieron en enero y desde entonces me siento muy mal', 5),
(4, 'event', 'busqueda_trabajo', 'Buscando empleo como contador', 'Llevo 3 semanas enviando CVs sin respuesta', 4),
(4, 'emotion', 'trigger_depresion', 'No poder proveer para familia', 'Me siento inútil por no poder mantener a mi familia', 5),
(4, 'preference', 'deporte', 'Jugaba fútbol los domingos', 'Antes jugaba fútbol pero ya no tengo ganas de nada', 3);

-- Laura Torres (id=5)
INSERT IGNORE INTO patient_memory (patient_id, memory_type, key_name, value, context, importance) VALUES
(5, 'relationship', 'madre', 'Rosa, 50 años', 'Mi mamá siempre me presiona con las notas', 4),
(5, 'relationship', 'mejor_amiga', 'Carla', 'Carla es mi mejor amiga desde el colegio', 4),
(5, 'event', 'universidad', 'Estudiante de Medicina UNMSM', 'Estudio medicina en San Marcos, estoy en 4to año', 4),
(5, 'event', 'examen_fallido', 'Reprobó fisiología', 'Jalé fisiología y me siento muy mal, mi mamá se decepcionó', 5),
(5, 'emotion', 'trigger_estres', 'Exámenes y expectativas familiares', 'Siento que nunca seré suficiente para mi familia', 5),
(5, 'preference', 'escape', 'Escuchar música y caminar', 'Cuando estoy mal me pongo audífonos y camino sin rumbo', 3);

-- Miguel Ríos (id=6)
INSERT IGNORE INTO patient_memory (patient_id, memory_type, key_name, value, context, importance) VALUES
(6, 'event', 'ataque_panico_primero', 'Primer ataque en 2024', 'Tuve mi primer ataque de pánico en el trabajo hace 2 años', 5),
(6, 'relationship', 'hermano', 'Diego', 'Mi hermano Diego también tiene ansiedad, es genético parece', 3),
(6, 'event', 'trabajo', 'Programador en startup', 'Trabajo como programador, mucha presión por deadlines', 4),
(6, 'emotion', 'trigger_panico', 'Espacios cerrados con mucha gente', 'Me dan ataques en el metro cuando está lleno', 5),
(6, 'preference', 'tecnica_calma', 'Respiración 4-7-8', 'La respiración que me enseñaste me ayuda bastante', 4);

-- Sofía Ramos (id=7)
INSERT IGNORE INTO patient_memory (patient_id, memory_type, key_name, value, context, importance) VALUES
(7, 'relationship', 'padre_fallecido', 'Falleció hace 6 meses', 'Mi papá murió de cáncer hace 6 meses y no lo supero', 5),
(7, 'relationship', 'madre', 'Elena', 'Mi mamá está muy deprimida desde que papá murió', 4),
(7, 'event', 'duelo', 'Proceso de duelo activo', 'A veces siento que papá sigue aquí', 5),
(7, 'emotion', 'culpa', 'No estuvo en sus últimos días', 'Estaba de viaje cuando papá empeoró, no llegué a despedirme', 5),
(7, 'preference', 'recuerdo_padre', 'Café con él los domingos', 'Extraño tomar café con mi papá los domingos', 4);

-- ============================================
-- CONVERSACIONES DE PRUEBA
-- ============================================

-- Ana García (id=3)
INSERT IGNORE INTO conversations (patient_id, message, sender, sentiment_score, risk_level, created_at) VALUES
(3, 'Hola, hoy tuve un día muy pesado en el trabajo', 'user', '{"positive": 0.1, "negative": 0.6, "anxiety": 0.5, "sadness": 0.3, "anger": 0.2}', 'low', '2026-01-20 09:15:00'),
(3, 'Hola Ana, entiendo que los días pesados pueden ser agotadores. ¿Qué fue lo más difícil de hoy?', 'ai', NULL, 'none', '2026-01-20 09:15:05'),
(3, 'Me pidieron terminar 3 diseños para mañana y apenas puedo con uno', 'user', '{"positive": 0.05, "negative": 0.7, "anxiety": 0.8, "sadness": 0.4, "anger": 0.3}', 'medium', '2026-01-20 09:16:00'),
(3, 'Eso suena muy abrumador, Ana. Recuerdo que mencionaste que las fechas de entrega te generan mucha ansiedad. ¿Cómo te sientes físicamente ahora mismo?', 'ai', NULL, 'none', '2026-01-20 09:16:10'),
(3, 'Siento el pecho apretado y las manos me tiemblan un poco', 'user', '{"positive": 0.0, "negative": 0.8, "anxiety": 0.9, "sadness": 0.3, "anger": 0.1}', 'medium', '2026-01-20 09:17:00'),
(3, 'Gracias por compartir eso conmigo. Vamos a hacer algo juntos: ¿puedes tomar un momento para respirar profundo? Inhala contando hasta 4, mantén 4, exhala 4. Lo que sientes es válido, pero también pasará.', 'ai', NULL, 'none', '2026-01-20 09:17:10'),
(3, 'Ok, estoy respirando... ya me siento un poco mejor', 'user', '{"positive": 0.4, "negative": 0.3, "anxiety": 0.4, "sadness": 0.2, "anger": 0.0}', 'low', '2026-01-20 09:20:00'),
(3, 'Me alegra escuchar eso. ¿Qué te parece si hablamos sobre cómo podrías organizar esas entregas de manera más manejable? A veces dividir las tareas ayuda mucho.', 'ai', NULL, 'none', '2026-01-20 09:20:10'),
(3, 'Hola! Hoy terminé todos los diseños a tiempo y mi jefe me felicitó', 'user', '{"positive": 0.9, "negative": 0.0, "anxiety": 0.1, "sadness": 0.0, "anger": 0.0}', 'none', '2026-01-22 18:00:00'),
(3, '¡Qué maravillosa noticia, Ana! Todo el esfuerzo que pusiste valió la pena. ¿Cómo celebrarás este logro?', 'ai', NULL, 'none', '2026-01-22 18:00:10'),
(3, 'Creo que pintaré algo esta noche, hace tiempo que no lo hago', 'user', '{"positive": 0.8, "negative": 0.0, "anxiety": 0.0, "sadness": 0.1, "anger": 0.0}', 'none', '2026-01-22 18:01:00'),
(3, 'Me encanta que retomes eso que te relaja. Pintar siempre te ha ayudado. Disfruta tu noche creativa.', 'ai', NULL, 'none', '2026-01-22 18:01:10');

-- Pedro López (id=4)
INSERT IGNORE INTO conversations (patient_id, message, sender, sentiment_score, risk_level, created_at) VALUES
(4, 'Otro rechazo más. Nadie me quiere contratar.', 'user', '{"positive": 0.0, "negative": 0.9, "anxiety": 0.3, "sadness": 0.8, "anger": 0.4}', 'medium', '2026-01-21 11:30:00'),
(4, 'Sé que cada rechazo duele mucho, Pedro. Es completamente válido sentirse así cuando has estado esforzándote tanto. ¿Qué posición era esta vez?', 'ai', NULL, 'none', '2026-01-21 11:30:10'),
(4, 'Era perfecta para mí, contador senior en una empresa grande. Tenía la experiencia exacta.', 'user', '{"positive": 0.1, "negative": 0.8, "anxiety": 0.4, "sadness": 0.7, "anger": 0.5}', 'medium', '2026-01-21 11:31:00'),
(4, 'Entiendo tu frustración. Tienes las habilidades y experiencia, pero el proceso de búsqueda es difícil. ¿Cómo está Carmen apoyándote en este momento?', 'ai', NULL, 'none', '2026-01-21 11:31:10'),
(4, 'Ella intenta pero yo me alejo... me da vergüenza mirarla a los ojos', 'user', '{"positive": 0.0, "negative": 0.9, "anxiety": 0.5, "sadness": 0.9, "anger": 0.2}', 'high', '2026-01-21 11:32:00'),
(4, 'Pedro, lo que sientes no te define como persona ni como esposo. Carmen te eligió por quien eres, no por un empleo. ¿Has podido hablar con ella sobre cómo te sientes realmente?', 'ai', NULL, 'none', '2026-01-21 11:32:10'),
(4, 'No... tengo miedo de que me vea débil', 'user', '{"positive": 0.1, "negative": 0.7, "anxiety": 0.6, "sadness": 0.7, "anger": 0.1}', 'medium', '2026-01-21 11:33:00'),
(4, 'Mostrarse vulnerable con quien amamos no es debilidad, es valentía. Mateo y Carmen te necesitan presente, no perfecto. ¿Qué pequeño paso podrías dar hoy para reconectar con ellos?', 'ai', NULL, 'none', '2026-01-21 11:33:10'),
(4, 'A veces pienso que estarían mejor sin mí', 'user', '{"positive": 0.0, "negative": 1.0, "anxiety": 0.3, "sadness": 1.0, "anger": 0.1}', 'critical', '2026-01-23 02:15:00'),
(4, 'Pedro, gracias por confiar en mí con algo tan difícil. Lo que sientes es muy pesado, pero quiero que sepas que tus pensamientos no son la realidad. Mateo y Carmen te aman y te necesitan presente. ¿Estás en un lugar seguro ahora mismo?', 'ai', NULL, 'none', '2026-01-23 02:15:10'),
(4, 'Sí, estoy en casa... todos duermen', 'user', '{"positive": 0.05, "negative": 0.9, "anxiety": 0.4, "sadness": 0.95, "anger": 0.0}', 'high', '2026-01-23 02:16:00'),
(4, 'Me alegra que estés en casa. A veces la noche hace todo más pesado. ¿Qué te parece si hablamos un rato? Cuéntame, ¿qué estabas haciendo antes de escribirme?', 'ai', NULL, 'none', '2026-01-23 02:16:10'),
(4, 'No podía dormir, mirando el techo pensando en todo', 'user', '{"positive": 0.0, "negative": 0.8, "anxiety": 0.5, "sadness": 0.8, "anger": 0.1}', 'high', '2026-01-23 02:17:00'),
(4, 'El insomnio puede hacer que los pensamientos se sientan más grandes de lo que son. ¿Podrías acercarte a donde está Carmen? A veces solo estar cerca de alguien que amamos ayuda, aunque no hablemos.', 'ai', NULL, 'none', '2026-01-23 02:17:10');

-- Laura Torres (id=5)
INSERT IGNORE INTO conversations (patient_id, message, sender, sentiment_score, risk_level, created_at) VALUES
(5, 'Hoy tuve que contarle a mi mamá que jalé el examen', 'user', '{"positive": 0.0, "negative": 0.85, "anxiety": 0.7, "sadness": 0.8, "anger": 0.2}', 'medium', '2026-01-19 20:00:00'),
(5, 'Eso debió ser muy difícil, Laura. Sé cuánto te importa lo que tu mamá piensa. ¿Cómo reaccionó ella?', 'ai', NULL, 'none', '2026-01-19 20:00:10'),
(5, 'Peor de lo que esperaba. Dijo que estoy desperdiciando su esfuerzo, que ella trabaja tanto para pagarme la universidad.', 'user', '{"positive": 0.0, "negative": 0.95, "anxiety": 0.6, "sadness": 0.9, "anger": 0.3}', 'high', '2026-01-19 20:01:00'),
(5, 'Esas palabras duelen mucho, especialmente viniendo de alguien cuya opinión valoramos tanto. Pero Laura, un examen no define tu valor ni todo tu esfuerzo. ¿Tu mamá sabe cuántas noches estudias?', 'ai', NULL, 'none', '2026-01-19 20:01:10'),
(5, 'No creo que le importe... solo ve los resultados', 'user', '{"positive": 0.0, "negative": 0.8, "anxiety": 0.5, "sadness": 0.85, "anger": 0.4}', 'medium', '2026-01-19 20:02:00'),
(5, 'A veces los padres expresan su preocupación de maneras que nos lastiman sin querer. ¿Has podido hablar con Carla sobre cómo te sientes?', 'ai', NULL, 'none', '2026-01-19 20:02:10'),
(5, 'Carla tiene sus propios problemas... no quiero agobiarla', 'user', '{"positive": 0.1, "negative": 0.6, "anxiety": 0.4, "sadness": 0.7, "anger": 0.1}', 'low', '2026-01-19 20:03:00'),
(5, 'Entiendo que no quieras cargar a otros, pero los amigos están para apoyarse mutuamente. ¿Qué te parece si esta noche te pones tus audífonos y das una caminata para despejarte?', 'ai', NULL, 'none', '2026-01-19 20:03:10'),
(5, 'Tengo como 6 exámenes las próximas 2 semanas y siento que voy a colapsar', 'user', '{"positive": 0.0, "negative": 0.9, "anxiety": 0.95, "sadness": 0.5, "anger": 0.3}', 'high', '2026-01-25 14:30:00'),
(5, 'Laura, escucho que te sientes muy abrumada. Eso es mucha presión para cualquiera. ¿Podemos hacer un ejercicio juntas? Respira profundo y cuéntame: de esos 6 exámenes, ¿cuál es el que más te preocupa?', 'ai', NULL, 'none', '2026-01-25 14:30:10'),
(5, 'Bioquímica... es el que más pesa y el que menos entiendo', 'user', '{"positive": 0.1, "negative": 0.75, "anxiety": 0.8, "sadness": 0.4, "anger": 0.2}', 'medium', '2026-01-25 14:31:00'),
(5, 'Bien, enfoquémonos en uno a la vez. ¿Tienes grupo de estudio para bioquímica? A veces estudiar con otros hace que los temas difíciles sean más manejables.', 'ai', NULL, 'none', '2026-01-25 14:31:10');

-- Miguel Ríos (id=6)
INSERT IGNORE INTO conversations (patient_id, message, sender, sentiment_score, risk_level, created_at) VALUES
(6, 'Tuve otro ataque en el trabajo hoy... tuve que salir corriendo', 'user', '{"positive": 0.0, "negative": 0.9, "anxiety": 0.95, "sadness": 0.4, "anger": 0.3}', 'medium', '2026-01-18 17:45:00'),
(6, 'Lamento escuchar eso, Miguel. Salir de la situación fue una decisión de autocuidado válida. ¿Cómo te sientes ahora? ¿Estás en un lugar tranquilo?', 'ai', NULL, 'none', '2026-01-18 17:45:10'),
(6, 'Sí, en mi carro en el estacionamiento. Todavía tiemblo un poco.', 'user', '{"positive": 0.1, "negative": 0.7, "anxiety": 0.8, "sadness": 0.3, "anger": 0.1}', 'medium', '2026-01-18 17:46:00'),
(6, 'Tu carro es un espacio seguro. Hagamos juntos la respiración 4-7-8 que practicamos: inhala por la nariz contando 4, mantén contando 7, exhala por la boca contando 8. ¿Puedes hacerlo conmigo?', 'ai', NULL, 'none', '2026-01-18 17:46:10'),
(6, '... ok, lo intenté 3 veces... ya estoy más tranquilo', 'user', '{"positive": 0.4, "negative": 0.4, "anxiety": 0.5, "sadness": 0.2, "anger": 0.0}', 'low', '2026-01-18 17:50:00'),
(6, 'Muy bien hecho, Miguel. Tu cuerpo está aprendiendo a regularse. ¿Qué estaba pasando cuando empezó el ataque?', 'ai', NULL, 'none', '2026-01-18 17:50:10'),
(6, 'Reunión con muchas personas en sala pequeña, aire acondicionado no funcionaba', 'user', '{"positive": 0.1, "negative": 0.6, "anxiety": 0.6, "sadness": 0.2, "anger": 0.4}', 'low', '2026-01-18 17:51:00'),
(6, 'Eso coincide con lo que me contaste - espacios cerrados con mucha gente son un disparador para ti. ¿Podrías hablar con tu jefe sobre tener reuniones en espacios más ventilados o participar por videollamada cuando sea posible?', 'ai', NULL, 'none', '2026-01-18 17:51:10'),
(6, 'Hoy pude quedarme en toda la reunión!', 'user', '{"positive": 0.85, "negative": 0.05, "anxiety": 0.2, "sadness": 0.0, "anger": 0.0}', 'none', '2026-01-24 16:00:00'),
(6, '¡Eso es un gran logro, Miguel! Todo el trabajo que has hecho está dando frutos. ¿Usaste alguna técnica durante la reunión?', 'ai', NULL, 'none', '2026-01-24 16:00:10'),
(6, 'Sí, me senté cerca de la puerta por si acaso, y respiré despacio cuando sentí que venía', 'user', '{"positive": 0.8, "negative": 0.1, "anxiety": 0.2, "sadness": 0.0, "anger": 0.0}', 'none', '2026-01-24 16:01:00'),
(6, 'Excelente uso de estrategias. Tener un plan y sentirte en control marca una gran diferencia. Estoy muy orgulloso de tu progreso.', 'ai', NULL, 'none', '2026-01-24 16:01:10');

-- Sofía Ramos (id=7)
INSERT IGNORE INTO conversations (patient_id, message, sender, sentiment_score, risk_level, created_at) VALUES
(7, 'Hoy fui a tomar café sola al lugar donde iba con mi papá', 'user', '{"positive": 0.2, "negative": 0.6, "anxiety": 0.2, "sadness": 0.8, "anger": 0.0}', 'low', '2026-01-20 10:30:00'),
(7, 'Eso fue muy valiente, Sofía. Ir a ese lugar especial toma mucho coraje. ¿Cómo te sentiste estando ahí?', 'ai', NULL, 'none', '2026-01-20 10:30:10'),
(7, 'Triste pero... también bien? Como que lo sentí cerca', 'user', '{"positive": 0.4, "negative": 0.4, "anxiety": 0.1, "sadness": 0.6, "anger": 0.0}', 'none', '2026-01-20 10:31:00'),
(7, 'Lo que describes es muy especial. Esos momentos donde podemos sentir la presencia de quienes amamos son un regalo. Tu papá sigue siendo parte de ti a través de estos recuerdos.', 'ai', NULL, 'none', '2026-01-20 10:31:10'),
(7, 'Pedí su café favorito, un americano sin azúcar, aunque a mí no me gusta jaja', 'user', '{"positive": 0.5, "negative": 0.3, "anxiety": 0.0, "sadness": 0.5, "anger": 0.0}', 'none', '2026-01-20 10:32:00'),
(7, 'Qué hermosa forma de honrarlo. A veces los pequeños rituales nos ayudan a mantener el vínculo vivo. ¿Cómo está tu mamá estos días?', 'ai', NULL, 'none', '2026-01-20 10:32:10'),
(7, 'Sigue muy mal... casi no sale del cuarto', 'user', '{"positive": 0.0, "negative": 0.7, "anxiety": 0.4, "sadness": 0.8, "anger": 0.1}', 'medium', '2026-01-20 10:33:00'),
(7, 'Es muy duro verla así mientras tú también procesas tu propio duelo. Recuerda que no es tu responsabilidad cargar con todo. ¿Han considerado buscar apoyo profesional para ella también?', 'ai', NULL, 'none', '2026-01-20 10:33:10'),
(7, 'Hoy es 6 meses desde que papá murió', 'user', '{"positive": 0.0, "negative": 0.8, "anxiety": 0.3, "sadness": 0.95, "anger": 0.1}', 'medium', '2026-01-26 07:00:00'),
(7, 'Sofía, gracias por compartir este día tan significativo conmigo. ¿Cómo estás llevando esta fecha?', 'ai', NULL, 'none', '2026-01-26 07:00:10'),
(7, 'Me desperté llorando. Soñé que llegaba a tiempo para despedirme...', 'user', '{"positive": 0.0, "negative": 0.9, "anxiety": 0.3, "sadness": 1.0, "anger": 0.0}', 'high', '2026-01-26 07:01:00'),
(7, 'Ese sueño refleja algo que todavía pesa mucho en tu corazón. Pero Sofía, tu papá sabía cuánto lo amabas. El amor no necesita despedidas para ser real y completo.', 'ai', NULL, 'none', '2026-01-26 07:01:10'),
(7, 'Ojalá pudiera creer eso...', 'user', '{"positive": 0.1, "negative": 0.7, "anxiety": 0.2, "sadness": 0.85, "anger": 0.0}', 'medium', '2026-01-26 07:02:00'),
(7, 'Sanar toma tiempo, y está bien tener días difíciles como hoy. ¿Hay algo especial que te gustaría hacer hoy para honrar su memoria?', 'ai', NULL, 'none', '2026-01-26 07:02:10');

-- ============================================
-- ALERTAS HISTÓRICAS
-- ============================================
INSERT IGNORE INTO alerts (patient_id, psychologist_id, alert_type, severity, message_snapshot, ai_analysis, status, resolution_notes, created_at, acknowledged_at, resolved_at) VALUES
(4, 1, 'suicide', 'red', 'A veces pienso que estarían mejor sin mí', 'El paciente expresó ideación suicida pasiva en contexto de desempleo prolongado y sentimientos de ser una carga para su familia. Nivel de riesgo alto por perfil demográfico (hombre adulto en crisis financiera).', 'resolved', 'Contacté al paciente inmediatamente. Tuvimos sesión de emergencia por videollamada. Se comprometió a un plan de seguridad y programamos cita presencial para mañana.', '2026-01-23 02:15:30', '2026-01-23 02:20:00', '2026-01-23 03:45:00'),
(5, 1, 'crisis', 'orange', 'siento que voy a colapsar', 'Paciente muestra niveles altos de ansiedad por carga académica. No hay ideación suicida pero sí agotamiento emocional significativo que requiere seguimiento.', 'acknowledged', NULL, '2026-01-25 14:30:30', '2026-01-25 14:45:00', NULL),
(7, 2, 'depression', 'orange', 'Ojalá pudiera creer eso...', 'Paciente en proceso de duelo, fecha significativa (6 meses del fallecimiento). Sentimientos de culpa persistentes. Sin ideación suicida pero requiere apoyo adicional.', 'in_progress', NULL, '2026-01-26 07:02:30', '2026-01-26 08:00:00', NULL),
(3, 1, 'anxiety', 'yellow', 'Siento el pecho apretado y las manos me tiemblan un poco', 'Síntomas físicos de ansiedad por estrés laboral. Situación controlada durante la conversación con técnicas de respiración.', 'false_positive', 'Síntomas normales de estrés agudo que la paciente logró manejar con herramientas aprendidas. No requirió intervención adicional.', '2026-01-20 09:17:30', '2026-01-20 10:00:00', '2026-01-20 10:05:00'),
(6, 2, 'anxiety', 'yellow', 'tuve que salir corriendo', 'Ataque de pánico en ambiente laboral. Paciente tiene historial conocido. Logró manejar la situación aunque tuvo que abandonar la reunión.', 'resolved', 'Revisé el caso. Paciente usó correctamente las técnicas aprendidas. Se reforzarán estrategias preventivas en próxima sesión.', '2026-01-18 17:45:30', '2026-01-18 18:00:00', '2026-01-19 09:00:00');

-- ============================================
-- CONTACTOS DE EMERGENCIA
-- ============================================

-- Ana García (id=3)
INSERT IGNORE INTO emergency_contacts (patient_id, contact_name, contact_phone, contact_relationship, priority, is_verified, notes) VALUES
(3, 'María García', '+51 999 111 222', 'Hermana', 1, 1, 'Vive cerca, puede llegar en 15 minutos'),
(3, 'Lucas Fernández', '+51 999 333 444', 'Novio', 2, 1, NULL);

-- Pedro López (id=4)
INSERT IGNORE INTO emergency_contacts (patient_id, contact_name, contact_phone, contact_relationship, priority, is_verified, notes) VALUES
(4, 'Carmen López', '+51 999 555 666', 'Esposa', 1, 1, 'Viven juntos'),
(4, 'Rosa López', '+51 999 777 888', 'Madre', 2, 1, 'Vive en Arequipa, contactar solo si es grave'),
(4, 'Juan López', '+51 999 111 333', 'Hermano', 3, 0, NULL);

-- Laura Torres (id=5)
INSERT IGNORE INTO emergency_contacts (patient_id, contact_name, contact_phone, contact_relationship, priority, is_verified, notes) VALUES
(5, 'Rosa Torres', '+51 999 222 333', 'Madre', 1, 1, NULL),
(5, 'Carla Mendoza', '+51 999 444 555', 'Mejor amiga', 2, 1, 'Estudian juntas en San Marcos');

-- Miguel Ríos (id=6)
INSERT IGNORE INTO emergency_contacts (patient_id, contact_name, contact_phone, contact_relationship, priority, is_verified, notes) VALUES
(6, 'Diego Ríos', '+51 999 666 777', 'Hermano', 1, 1, 'También tiene ansiedad, entiende la situación'),
(6, 'Patricia Vega', '+51 999 888 999', 'Madre', 2, 1, NULL);

-- Sofía Ramos (id=7)
INSERT IGNORE INTO emergency_contacts (patient_id, contact_name, contact_phone, contact_relationship, priority, is_verified, notes) VALUES
(7, 'Elena Ramos', '+51 999 123 456', 'Madre', 1, 1, 'En proceso de duelo también, contactar con cuidado'),
(7, 'Camila Ramos', '+51 999 789 012', 'Tía', 2, 1, 'Hermana de su padre fallecido, muy cercana');

-- ============================================
-- FIN DE DATOS DE PRUEBA
-- ============================================
