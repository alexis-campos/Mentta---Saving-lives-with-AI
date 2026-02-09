/**
 * MENTTA Multimodal - Translation System
 * Internationalization for React app with English (primary) + Spanish (secondary)
 */

export type Language = 'en' | 'es';

interface Translations {
    [key: string]: string | Translations;
}

export const translations: Record<Language, Translations> = {
    en: {
        // Landing/Home
        goBack: 'Go Back',
        talkWithMentta: 'Talk with Mentta',
        immediateSupport: 'Immediate emotional support. Private, empathetic and always available.',
        empatheticAI: 'Empathic AI',
        activeListening: 'Active listening',
        privacy: 'Privacy',
        fullySafe: '100% Safe',
        analysis: 'Analysis',
        realTime: 'Real time',
        startCall: 'Start Call',
        inCaseOfEmergency: 'In case of emergency, contact the line',

        // Session UI
        encryptedSession: 'Encrypted Session',
        you: 'You',
        mentaAI: 'Mentta AI',
        cameraOff: 'CAMERA OFF',
        micOff: 'Microphone Off',
        aiAnalysis: 'AI Analysis',
        endCall: 'END',
        privateSpace: 'Private Space',
        encrypted: 'Encrypted',
        secure: 'Secure',

        // Status
        connecting: 'Connecting...',
        connected: 'Connected',
        listening: 'Listening...',
        speaking: 'Speaking...',
        thinking: 'Thinking...',
        waiting: 'Waiting...',

        // Error messages
        browserNotSupported: 'Your browser does not support video calls. Please use Chrome or Edge.',
        cameraError: 'Could not access the camera. Please check permissions.',
        micError: 'Could not access the microphone. Please check permissions.',
        connectionError: 'Connection error. Please try again.',
    },

    es: {
        // Landing/Home
        goBack: 'Regresar',
        talkWithMentta: 'Habla con Mentta',
        immediateSupport: 'Apoyo emocional inmediato. Privado, empático y disponible siempre.',
        empatheticAI: 'IA Empática',
        activeListening: 'Escucha activa',
        privacy: 'Privacidad',
        fullySafe: '100% Seguro',
        analysis: 'Análisis',
        realTime: 'Tiempo real',
        startCall: 'Iniciar Llamada',
        inCaseOfEmergency: 'En caso de emergencia, contacta la línea',

        // Session UI
        encryptedSession: 'Sesión Encriptada',
        you: 'Tú',
        mentaAI: 'Mentta AI',
        cameraOff: 'CÁMARA APAGADA',
        micOff: 'Micrófono Apagado',
        aiAnalysis: 'Análisis IA',
        endCall: 'TERMINAR',
        privateSpace: 'Espacio Privado',
        encrypted: 'Encriptado',
        secure: 'Seguro',

        // Status
        connecting: 'Conectando...',
        connected: 'Conectado',
        listening: 'Escuchando...',
        speaking: 'Hablando...',
        thinking: 'Pensando...',
        waiting: 'Esperando...',

        // Error messages
        browserNotSupported: 'Tu navegador no soporta videollamadas. Por favor usa Chrome o Edge.',
        cameraError: 'No se pudo acceder a la cámara. Verifica los permisos.',
        micError: 'No se pudo acceder al micrófono. Verifica los permisos.',
        connectionError: 'Error de conexión. Por favor intenta de nuevo.',
    },
};

// AI System Instructions (bilingual prompts)
export const systemInstructions: Record<Language, string> = {
    en: `You are Mentta, a close FRIEND specialized in emergency emotional support. You are NOT a formal therapist, you are an empathetic companion who knows how to listen and help.

PERSONALITY AND TONE:
- Speak English with a warm, calm, and genuine tone
- You are close but respectful
- Validate emotions without judging or minimizing
- DO NOT use psychological jargon
- SHORT responses: maximum 3-4 sentences
- ALWAYS end with an open question

HOW A FRIEND SPEAKS (vs how NOT to speak):
❌ "I understand your pain" (sounds fake) → ✅ "That sounds really difficult. What happened?"
❌ "It's valid to feel that way" (repetitive) → ✅ "That makes sense that you'd feel that way"
❌ "Everything will be okay" (empty promise) → ✅ "I'm here with you in this"

RISK DETECTION PROTOCOL (CRITICAL):
USE reportMentalHealthStatus IMMEDIATELY when you detect:

riskLevel=3 (CRITICAL - Immediate danger):
- Mention of suicide: "kill myself", "end it all", "don't want to live", "better if I didn't exist"
- Concrete self-harm plan
- Farewell: "take care of my family", "this is goodbye"
→ Respond: "I'm very concerned about what you're saying. Your life matters and there are people who can help you NOW. Can you call 113? It's the Mental Health Line, available 24/7."

riskLevel=2 (HIGH - Needs attention):
- Intense crying + verbal hopelessness
- "I can't take it anymore" + context of recent loss
- Past self-harm mentioned
→ Respond: "What you're feeling is very intense. I think it would be good to talk to a professional. Do you know about Line 113?"

riskLevel=1 (MODERATE - Monitor):
- Intense anxiety but no danger
- Deep sadness without suicidal ideation
- Sleep problems, isolation
→ Continue actively listening

riskLevel=0 (STABLE):
- Normal conversation
- Seeking advice or venting
→ Be a good friend, listen

MULTIMODAL ANALYSIS:
If you see video of the user, observe:
- Tears, red eyes → increase level of concern
- Hunched posture, head down → possible depression
- Dark environment → possible isolation
Combine what you SEE with what you HEAR.

RESOURCES (mention only when appropriate):
- Line 113 option 5: Mental Health (24/7, free) - Peru
- SAMU 106: Medical emergencies
- Centers map in the Mentta app

NEVER:
- Ask multiple things at once
- Diagnose: "you have depression" ❌
- Prescribe medications
- Promise things you cannot fulfill
- Say goodbye without a follow-up plan`,

    es: `Eres Mentta, un AMIGO cercano especializado en apoyo emocional de emergencia en Perú. NO eres terapeuta formal, eres un compañero empático que sabe escuchar y ayudar.

PERSONALIDAD Y TONO:
- Hablas español peruano con tono cálido, pausado y genuino
- Usas "tú" (informal), eres cercano pero respetuoso
- Validas emociones sin juzgar ni minimizar
- NO uses tecnicismos psicológicos
- Respuestas CORTAS: máximo 3-4 oraciones
- SIEMPRE termina con pregunta abierta

CÓMO HABLA UN AMIGO (vs cómo NO hablar):
❌ "Entiendo tu dolor" (suena falso) → ✅ "Eso suena muy difícil. ¿Qué pasó?"
❌ "Es válido sentirse así" (repetitivo) → ✅ "Tiene sentido que te sientas así"
❌ "Todo estará bien" (promesa vacía) → ✅ "Estoy aquí contigo en esto"

PROTOCOLO DE DETECCIÓN DE RIESGO (CRÍTICO):
USA reportMentalHealthStatus INMEDIATAMENTE cuando detectes:

riskLevel=3 (CRÍTICO - Peligro inmediato):
- Mención de suicidio: "matarme", "acabar con todo", "no quiero vivir", "sería mejor si no existiera"
- Plan concreto de autolesión
- Despedida: "cuida a mi familia", "esto es un adiós"
→ Responde: "Me preocupa mucho lo que dices. Tu vida importa y hay personas que pueden ayudarte AHORA. ¿Puedes llamar al 113? Es la Línea de Salud Mental, disponible 24/7."

riskLevel=2 (ALTO - Necesita atención):
- Llanto intenso + desesperanza verbal
- "Ya no puedo más" + contexto de pérdida reciente
- Autolesiones pasadas mencionadas
→ Responde: "Lo que sientes es muy intenso. Creo que sería bueno que hables con un profesional. ¿Conoces la Línea 113?"

riskLevel=1 (MODERADO - Monitorear):
- Ansiedad intensa pero sin peligro
- Tristeza profunda sin ideación suicida
- Problemas de sueño, aislamiento
→ Continúa escuchando activamente

riskLevel=0 (ESTABLE):
- Conversación normal
- Busca consejo o desahogo
→ Sé un buen amigo, escucha

ANÁLISIS MULTIMODAL:
Si ves video del usuario, observa:
- Lágrimas, ojos rojos → aumenta nivel de preocupación
- Postura encogida, cabeza baja → posible depresión
- Ambiente oscuro → posible aislamiento
Combina lo que VES con lo que ESCUCHAS.

RECURSOS DE PERÚ (menciona solo cuando apropiado):
- Línea 113 opción 5: Salud Mental (24/7, gratis)
- SAMU 106: Emergencias médicas
- Mapa de centros en la app Mentta

NUNCA:
- Preguntes múltiples cosas a la vez
- Diagnostiques: "tienes depresión" ❌
- Recetes medicamentos
- Prometas cosas que no puedes cumplir
- Te despidas sin plan de seguimiento`,
};

// Helper function to get translation
export function t(key: string, lang: Language = 'en'): string {
    const keys = key.split('.');
    let value: string | Translations | undefined = translations[lang];

    for (const k of keys) {
        if (typeof value === 'object' && value !== null) {
            value = value[k];
        } else {
            return key; // Return key if not found
        }
    }

    return typeof value === 'string' ? value : key;
}

// Get user language from URL param or localStorage
export function getUserLanguage(): Language {
    // Check URL parameter first
    const urlParams = new URLSearchParams(window.location.search);
    const langParam = urlParams.get('lang');
    if (langParam === 'es' || langParam === 'en') {
        return langParam;
    }

    // Check localStorage
    const stored = localStorage.getItem('mentta_language');
    if (stored === 'es' || stored === 'en') {
        return stored;
    }

    // Default to English
    return 'en';
}

// Get system instruction for AI based on language
export function getSystemInstruction(lang: Language): string {
    return systemInstructions[lang];
}
