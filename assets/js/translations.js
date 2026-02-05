/**
 * MENTTA - Translation System
 * English (primary) + Spanish (secondary)
 * v1.0
 */

const TRANSLATIONS = {
    en: {
        // Common
        common: {
            loading: 'Loading...',
            error: 'Error',
            success: 'Success',
            cancel: 'Cancel',
            confirm: 'Confirm',
            close: 'Close',
            save: 'Save',
            delete: 'Delete',
            edit: 'Edit',
            back: 'Back',
            next: 'Next',
            previous: 'Previous',
            submit: 'Submit',
            secure: 'Secure',
            private: 'Private',
            encrypted: 'Encrypted',
            eliteCare: 'Elite Care',
        },

        // Navigation
        nav: {
            login: 'Login',
            register: 'Register',
            logout: 'Logout',
            profile: 'Profile',
            settings: 'Settings',
            dashboard: 'Dashboard',
            chat: 'Chat',
            map: 'Centers Map',
        },

        // Login Page
        login: {
            title: 'Welcome Back',
            subtitle: 'Please enter your credentials to access your serene space.',
            emailLabel: 'Email Address',
            emailPlaceholder: 'name@example.com',
            passwordLabel: 'Password',
            passwordPlaceholder: '••••••••',
            forgotPassword: 'Forgot?',
            signIn: 'Sign In',
            noAccount: 'New to Mentta?',
            createAccount: 'Create Account',
            verifying: 'Identity verified. Preparing your space...',
            invalidCredentials: 'Invalid credentials. Please try again.',
            connectionError: 'Connection unstable. Please verify connection.',
            endToEndEncrypted: 'End-to-End Encrypted',
            premiumCare: 'Premium Care',
        },

        // Register Page
        register: {
            title: 'Create Account',
            subtitle: 'Join our community and start your journey to mental wellness.',
            nameLabel: 'Full Name',
            namePlaceholder: 'John Doe',
            emailLabel: 'Email Address',
            emailPlaceholder: 'name@example.com',
            passwordLabel: 'Password',
            passwordPlaceholder: 'At least 8 characters',
            confirmPasswordLabel: 'Confirm Password',
            confirmPasswordPlaceholder: '••••••••',
            ageLabel: 'Age (optional)',
            signUp: 'Create Account',
            hasAccount: 'Already have an account?',
            signIn: 'Sign In',
            passwordMismatch: 'Passwords do not match.',
            registrationSuccess: 'Account created! Redirecting...',
        },

        // Chat Page
        chat: {
            newChat: 'New Chat',
            centersMap: 'Centers Map',
            immediateHelp: 'Immediate Help',
            wellnessResources: 'Wellness Resources',
            liveSession: 'Mentta Live Session',
            history: 'History',
            searchHistory: 'Past reflections...',
            loadingHistory: 'Loading history...',
            weeklyVitality: 'Weekly Vitality',
            notifications: 'News',
            inputPlaceholder: 'Write here...',
            privateSpace: 'PRIVATE SPACE • ENCRYPTED • SECURE',
            analyzing: 'Analyzing...',
            yourEnergy: 'Your Energy',
        },

        // Greetings (time-based)
        greetings: {
            morning: 'Good morning',
            afternoon: 'Good afternoon',
            evening: 'Good evening',
            howAreYouMorning: 'How did you wake up today?',
            howAreYouAfternoon: 'How is your day going?',
            howAreYouEvening: 'How are you feeling tonight?',
            takeYourTime: 'Take your time, this is your refuge of clarity.',
        },

        // Logout Modal
        logout: {
            title: 'End Session',
            message: 'You can always come back when you need a moment of clarity. Take care. 🌿',
            cancel: 'Cancel',
            confirm: 'Log out',
        },

        // Crisis Modal
        crisis: {
            title: 'Crisis Support',
            subtitle: 'This is a safe space. Choose the option that best suits what you need right now:',
            contactPsychologist: 'Contact my Psychologist',
            priorityNotification: 'Priority notification',
            calmingExercises: 'Calming Exercises',
            breathingGrounding: 'Breathing and Grounding',
            emergencyContact: 'Emergency Contact',
            safetyNetwork: 'Safety network alert',
            callLine: 'Call Line 113',
            immediateUrgency: 'Immediate Urgency',
        },

        // Resources Modal
        resources: {
            title: 'Wellness Resources',
            subtitle: 'Tools designed to bring you back to center. Take the time you need.',
            consciousBreathing: 'Conscious Breathing',
            groundingTechnique: 'Grounding Technique',
            affirmations: 'Affirmations',
            findComfortablePosition: 'Find a comfortable position',
            startPractice: 'Start Practice',
            finish: 'Finish',
            inhale: 'Inhale',
            hold: 'Hold',
            exhale: 'Exhale',
            calm: 'Calm',
            // Grounding
            groundingInstruction: 'Connect with your senses to reduce anxiety. Tap each step when completed:',
            see5: '5 things you can see',
            touch4: '4 things you can touch',
            hear3: '3 things you can hear',
            smell2: '2 things you can smell',
            taste1: '1 thing you can taste',
            wellDone: 'Well done. You are here.',
            reset: 'Reset',
            // Affirmations
            nextAffirmation: 'Next affirmation',
            affirmation1: '"This feeling is temporary. You have overcome difficult days before and you will do it again."',
            affirmation2: '"You are stronger than you think. Every challenge is an opportunity to grow."',
            affirmation3: '"It\'s okay to not be okay. Healing is not linear."',
            affirmation4: '"You deserve peace and happiness. Every small step counts."',
            affirmation5: '"Your feelings are valid. You are doing the best you can."',
        },

        // Live Session
        live: {
            title: 'Mentta Live',
            realTimeSession: 'Real-Time Session',
            description: 'Connect with our AI emotional support through voice and video for a more human and profound experience.',
            preparation: 'Preparation:',
            findQuietPlace: 'Find a quiet, private place',
            checkMicCamera: 'Verify your microphone and camera',
            notNow: 'Not now',
            start: 'Start',
            startCall: 'Start Call',
            talkWithMentta: 'Talk with Mentta',
            immediateSupport: 'Immediate emotional support. Private, empathetic and always available.',
            empatheticAI: 'Empathic AI',
            activeListening: 'Active listening',
            privacy: 'Privacy',
            fullySafe: '100% Safe',
            analysis: 'Analysis',
            realTime: 'Real time',
            inCaseOfEmergency: 'In case of emergency, contact the line',
            goBack: 'Go Back',
        },

        // Index/Landing
        index: {
            sophisticatedMentalClarity: 'SOPHISTICATED MENTAL CLARITY',
            heroSubtitle: 'Support for your mind, anytime. A serene and intuitive space for mental well-being and personal growth.',
            elevatedExperience: 'ELEVATED EXPERIENCE',
            elevatedDesc: 'Mentta brings technology and mental well-being together. A seamless, thoughtful assistant guides you toward clarity, calm, and personal growth.',
            features: {
                empathy: 'EMPATHY',
                advancedIntelligence: 'Advanced Intelligence',
                advancedIntelligenceDesc: 'Advanced language models trained on clinical psychology protocols.',
                privacy: 'PRIVACY',
                yourDataSafe: 'Your Data is Safe',
                yourDataSafeDesc: 'End-to-end encryption ensures your conversations remain private.',
                support: 'SUPPORT',
                alwaysAvailable: 'Always Available',
                alwaysAvailableDesc: '24/7 access to compassionate AI-powered support.',
                insight: 'INSIGHT',
                personalGrowth: 'Personal Growth',
                personalGrowthDesc: 'Track your emotional journey and celebrate your progress.',
            },
            expertCuration: 'EXPERT CURATION',
            expertCurationDesc: 'Our approach is backed by evidence-based psychology and refined by mental health professionals.',
            watchVideo: 'WATCH VIDEO',
        },

        // Footer
        footer: {
            termsOfService: 'Terms of Service',
            privacyPolicy: 'Privacy Policy',
            allRightsReserved: 'All Rights Reserved',
        },

        // Language
        language: {
            select: 'Language',
            english: 'English',
            spanish: 'Español',
        },

        // Errors
        errors: {
            operationTimeout: 'The operation is taking too long. Please try again.',
            connectionFailed: 'Connection failed. Please check your internet.',
            sessionExpired: 'Your session has expired. Please log in again.',
        },
    },

    es: {
        // Common
        common: {
            loading: 'Cargando...',
            error: 'Error',
            success: 'Éxito',
            cancel: 'Cancelar',
            confirm: 'Confirmar',
            close: 'Cerrar',
            save: 'Guardar',
            delete: 'Eliminar',
            edit: 'Editar',
            back: 'Atrás',
            next: 'Siguiente',
            previous: 'Anterior',
            submit: 'Enviar',
            secure: 'Seguro',
            private: 'Privado',
            encrypted: 'Encriptado',
            eliteCare: 'Atención Elite',
        },

        // Navigation
        nav: {
            login: 'Ingresar',
            register: 'Registrarse',
            logout: 'Salir',
            profile: 'Perfil',
            settings: 'Configuración',
            dashboard: 'Panel',
            chat: 'Chat',
            map: 'Mapa de Centros',
        },

        // Login Page
        login: {
            title: 'Bienvenido',
            subtitle: 'Ingresa tus credenciales para acceder a tu espacio sereno.',
            emailLabel: 'Correo Electrónico',
            emailPlaceholder: 'nombre@ejemplo.com',
            passwordLabel: 'Contraseña',
            passwordPlaceholder: '••••••••',
            forgotPassword: '¿Olvidaste?',
            signIn: 'Ingresar',
            noAccount: '¿Nuevo en Mentta?',
            createAccount: 'Crear Cuenta',
            verifying: 'Identidad verificada. Preparando tu espacio...',
            invalidCredentials: 'Credenciales inválidas. Por favor intenta de nuevo.',
            connectionError: 'Conexión inestable. Por favor verifica tu conexión.',
            endToEndEncrypted: 'Encriptación de Extremo a Extremo',
            premiumCare: 'Atención Premium',
        },

        // Register Page
        register: {
            title: 'Crear Cuenta',
            subtitle: 'Únete a nuestra comunidad y comienza tu camino hacia el bienestar mental.',
            nameLabel: 'Nombre Completo',
            namePlaceholder: 'Juan Pérez',
            emailLabel: 'Correo Electrónico',
            emailPlaceholder: 'nombre@ejemplo.com',
            passwordLabel: 'Contraseña',
            passwordPlaceholder: 'Al menos 8 caracteres',
            confirmPasswordLabel: 'Confirmar Contraseña',
            confirmPasswordPlaceholder: '••••••••',
            ageLabel: 'Edad (opcional)',
            signUp: 'Crear Cuenta',
            hasAccount: '¿Ya tienes una cuenta?',
            signIn: 'Ingresar',
            passwordMismatch: 'Las contraseñas no coinciden.',
            registrationSuccess: '¡Cuenta creada! Redirigiendo...',
        },

        // Chat Page
        chat: {
            newChat: 'Nuevo Chat',
            centersMap: 'Mapa de Centros',
            immediateHelp: 'Ayuda Inmediata',
            wellnessResources: 'Recursos de Bienestar',
            liveSession: 'Sesión en Vivo Mentta',
            history: 'Historial',
            searchHistory: 'Reflexiones pasadas...',
            loadingHistory: 'Cargando historial...',
            weeklyVitality: 'Vitalidad Semanal',
            notifications: 'Novedades',
            inputPlaceholder: 'Escribe aquí...',
            privateSpace: 'ESPACIO PRIVADO • ENCRIPTADO • SEGURO',
            analyzing: 'Analizando...',
            yourEnergy: 'Tu Energía',
        },

        // Greetings (time-based)
        greetings: {
            morning: 'Buenos días',
            afternoon: 'Buenas tardes',
            evening: 'Buenas noches',
            howAreYouMorning: '¿Cómo amaneciste hoy?',
            howAreYouAfternoon: '¿Cómo va tu día?',
            howAreYouEvening: '¿Cómo te encuentras esta noche?',
            takeYourTime: 'Tómate tu tiempo, este es tu refugio de claridad.',
        },

        // Logout Modal
        logout: {
            title: 'Cerrar Sesión',
            message: 'Siempre puedes volver cuando necesites un momento de claridad. Cuídate mucho. 🌿',
            cancel: 'Cancelar',
            confirm: 'Salir',
        },

        // Crisis Modal
        crisis: {
            title: 'Soporte de Crisis',
            subtitle: 'Este es un espacio seguro. Elige la opción que mejor se adapte a lo que necesitas en este momento:',
            contactPsychologist: 'Contactar a mi Psicólogo',
            priorityNotification: 'Notificación prioritaria',
            calmingExercises: 'Ejercicios de Calma',
            breathingGrounding: 'Respiración y Grounding',
            emergencyContact: 'Contacto de Confianza',
            safetyNetwork: 'Aviso a red de seguridad',
            callLine: 'Llamar Línea 113',
            immediateUrgency: 'Urgencia Inmediata',
        },

        // Resources Modal
        resources: {
            title: 'Recursos de Bienestar',
            subtitle: 'Herramientas diseñadas para devolverte al centro. Tómate el tiempo que necesites.',
            consciousBreathing: 'Respiración Consciente',
            groundingTechnique: 'Técnica de Grounding',
            affirmations: 'Afirmaciones',
            findComfortablePosition: 'Encuentra una postura cómoda',
            startPractice: 'Iniciar Práctica',
            finish: 'Finalizar',
            inhale: 'Inhala',
            hold: 'Mantén',
            exhale: 'Exhala',
            calm: 'Calma',
            // Grounding
            groundingInstruction: 'Conecta con tus sentidos para reducir la ansiedad. Toca cada paso al completarlo:',
            see5: '5 cosas que puedes ver',
            touch4: '4 cosas que puedes tocar',
            hear3: '3 cosas que puedes oír',
            smell2: '2 cosas que puedes oler',
            taste1: '1 cosa que puedes saborear',
            wellDone: 'Bien hecho. Estás aquí.',
            reset: 'Reiniciar',
            // Affirmations
            nextAffirmation: 'Siguiente afirmación',
            affirmation1: '"Esto que sientes es temporal. Has superado días difíciles antes y lo harás de nuevo."',
            affirmation2: '"Eres más fuerte de lo que crees. Cada desafío es una oportunidad de crecer."',
            affirmation3: '"Está bien no estar bien. La sanación no es lineal."',
            affirmation4: '"Mereces paz y felicidad. Cada pequeño paso cuenta."',
            affirmation5: '"Tus sentimientos son válidos. Estás haciendo lo mejor que puedes."',
        },

        // Live Session
        live: {
            title: 'Mentta en Vivo',
            realTimeSession: 'Sesión en Tiempo Real',
            description: 'Conéctate con nuestra IA de apoyo emocional a través de voz y video para una experiencia más humana y profunda.',
            preparation: 'Preparación:',
            findQuietPlace: 'Busca un lugar tranquilo y privado',
            checkMicCamera: 'Verifica tu micrófono y cámara',
            notNow: 'Ahora no',
            start: 'Comenzar',
            startCall: 'Iniciar Llamada',
            talkWithMentta: 'Habla con Mentta',
            immediateSupport: 'Apoyo emocional inmediato. Privado, empático y disponible siempre.',
            empatheticAI: 'IA Empática',
            activeListening: 'Escucha activa',
            privacy: 'Privacidad',
            fullySafe: '100% Seguro',
            analysis: 'Análisis',
            realTime: 'Tiempo real',
            inCaseOfEmergency: 'En caso de emergencia, contacta la línea',
            goBack: 'Regresar',
        },

        // Index/Landing
        index: {
            sophisticatedMentalClarity: 'CLARIDAD MENTAL SOFISTICADA',
            heroSubtitle: 'Apoyo para tu mente, en cualquier momento. Un espacio sereno e intuitivo para el bienestar mental y el crecimiento personal.',
            elevatedExperience: 'EXPERIENCIA ELEVADA',
            elevatedDesc: 'Mentta une tecnología y bienestar mental. Un asistente fluido y reflexivo te guía hacia la claridad, la calma y el crecimiento personal.',
            features: {
                empathy: 'EMPATÍA',
                advancedIntelligence: 'Inteligencia Avanzada',
                advancedIntelligenceDesc: 'Modelos de lenguaje avanzados entrenados en protocolos de psicología clínica.',
                privacy: 'PRIVACIDAD',
                yourDataSafe: 'Tus Datos están Seguros',
                yourDataSafeDesc: 'Encriptación de extremo a extremo asegura que tus conversaciones permanezcan privadas.',
                support: 'SOPORTE',
                alwaysAvailable: 'Siempre Disponible',
                alwaysAvailableDesc: 'Acceso 24/7 a soporte compasivo impulsado por IA.',
                insight: 'PERSPECTIVA',
                personalGrowth: 'Crecimiento Personal',
                personalGrowthDesc: 'Rastrea tu viaje emocional y celebra tu progreso.',
            },
            expertCuration: 'CURACIÓN EXPERTA',
            expertCurationDesc: 'Nuestro enfoque está respaldado por psicología basada en evidencia y refinado por profesionales de la salud mental.',
            watchVideo: 'VER VIDEO',
        },

        // Footer
        footer: {
            termsOfService: 'Términos de Servicio',
            privacyPolicy: 'Política de Privacidad',
            allRightsReserved: 'Todos los Derechos Reservados',
        },

        // Language
        language: {
            select: 'Idioma',
            english: 'English',
            spanish: 'Español',
        },

        // Errors
        errors: {
            operationTimeout: 'La operación está tardando demasiado. Por favor intenta de nuevo.',
            connectionFailed: 'Conexión fallida. Por favor verifica tu internet.',
            sessionExpired: 'Tu sesión ha expirado. Por favor inicia sesión de nuevo.',
        },
    },
};

/**
 * Translation Manager Class
 */
class TranslationManager {
    constructor() {
        this.currentLang = this.getStoredLanguage() || 'en';
        this.listeners = [];
    }

    /**
     * Get stored language preference
     */
    getStoredLanguage() {
        return localStorage.getItem('mentta_language');
    }

    /**
     * Set and persist language preference
     */
    setLanguage(lang) {
        if (!TRANSLATIONS[lang]) {
            console.warn(`Language '${lang}' not supported, defaulting to 'en'`);
            lang = 'en';
        }
        this.currentLang = lang;
        localStorage.setItem('mentta_language', lang);
        document.documentElement.lang = lang;

        // Notify listeners
        this.listeners.forEach(cb => cb(lang));

        // Sync with server if logged in
        this.syncWithServer(lang);

        return lang;
    }

    /**
     * Get current language
     */
    getLanguage() {
        return this.currentLang;
    }

    /**
     * Translate a key path (e.g., 'login.title')
     */
    t(keyPath, fallback = '') {
        const keys = keyPath.split('.');
        let result = TRANSLATIONS[this.currentLang];

        for (const key of keys) {
            if (result && typeof result === 'object' && key in result) {
                result = result[key];
            } else {
                // Try English fallback
                result = TRANSLATIONS['en'];
                for (const k of keys) {
                    if (result && typeof result === 'object' && k in result) {
                        result = result[k];
                    } else {
                        return fallback || keyPath;
                    }
                }
                break;
            }
        }

        return typeof result === 'string' ? result : fallback || keyPath;
    }

    /**
     * Subscribe to language changes
     */
    onLanguageChange(callback) {
        this.listeners.push(callback);
        // Return unsubscribe function
        return () => {
            this.listeners = this.listeners.filter(cb => cb !== callback);
        };
    }

    /**
     * Sync language preference with server
     */
    async syncWithServer(lang) {
        try {
            await fetch('api/user/set-language.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ language: lang })
            });
        } catch (e) {
            // Silent fail - local storage is the primary source
        }
    }

    /**
     * Apply translations to elements with data-i18n attribute
     */
    applyTranslations() {
        document.querySelectorAll('[data-i18n]').forEach(el => {
            const key = el.getAttribute('data-i18n');
            const translated = this.t(key);

            // Handle different element types
            if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') {
                if (el.placeholder) {
                    el.placeholder = this.t(key + 'Placeholder', el.placeholder);
                }
            } else {
                el.textContent = translated;
            }
        });

        // Handle data-i18n-placeholder separately
        document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
            const key = el.getAttribute('data-i18n-placeholder');
            el.placeholder = this.t(key);
        });

        // Handle data-i18n-title for tooltips
        document.querySelectorAll('[data-i18n-title]').forEach(el => {
            const key = el.getAttribute('data-i18n-title');
            el.title = this.t(key);
        });
    }

    /**
     * Get greeting based on time of day
     */
    getGreeting() {
        const hour = new Date().getHours();
        if (hour >= 5 && hour < 12) {
            return {
                greeting: this.t('greetings.morning'),
                context: this.t('greetings.howAreYouMorning'),
            };
        } else if (hour >= 12 && hour < 18) {
            return {
                greeting: this.t('greetings.afternoon'),
                context: this.t('greetings.howAreYouAfternoon'),
            };
        } else {
            return {
                greeting: this.t('greetings.evening'),
                context: this.t('greetings.howAreYouEvening'),
            };
        }
    }

    /**
     * Create language switcher HTML
     */
    createLanguageSwitcher(containerId) {
        const container = document.getElementById(containerId);
        if (!container) return;

        container.innerHTML = `
      <div class="language-switcher">
        <button class="lang-btn ${this.currentLang === 'en' ? 'active' : ''}" 
                onclick="i18n.setLanguage('en'); i18n.applyTranslations(); updateLanguageSwitcher();">
          EN
        </button>
        <span class="lang-separator">|</span>
        <button class="lang-btn ${this.currentLang === 'es' ? 'active' : ''}" 
                onclick="i18n.setLanguage('es'); i18n.applyTranslations(); updateLanguageSwitcher();">
          ES
        </button>
      </div>
    `;
    }
}

// Create global instance
const i18n = new TranslationManager();

// Helper function for templates
function t(key, fallback) {
    return i18n.t(key, fallback);
}

// Update language switcher state
function updateLanguageSwitcher() {
    document.querySelectorAll('.lang-btn').forEach(btn => {
        const lang = btn.textContent.trim().toLowerCase();
        btn.classList.toggle('active', lang === i18n.getLanguage());
    });
}

// Auto-apply on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    document.documentElement.lang = i18n.getLanguage();
    i18n.applyTranslations();
});
