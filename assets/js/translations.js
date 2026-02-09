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





            // Crisis / Panic
            panicTitle: 'Crisis Alert',
            panicMessage: 'If you need immediate help, please call the crisis line.',
            call: 'Call',
            panicFooter: 'Line 113 (Mental Health) • SAMU 106 (Emergency)',
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
            today: 'Today',
            noMessages: 'No messages yet',
        },

        // Map Page
        map: {
            title: 'Centers Map - Mentta',
            loading: 'Loading map...',
            locator: 'Locator',
            search: 'Search',
            searchInputPlaceholder: 'Search by name or district...',
            mobileSearchInputPlaceholder: 'Name, district or specialty...',
            realTimeResults: 'Real-time results',
            currentEnvironment: 'Current Environment',
            online: 'Online',
            detecting: 'Detecting...',
            optimizedForYou: 'Optimized for you',
            filters: {
                all: 'All',
                mentta: 'Mentta Network',
                emergency: '24h',
                health: 'Health',
                community: 'Community',
            },
            nearbyResults: 'Nearby Results',
            noResults: 'No centers found',
            openNow: 'Open Now',
            closed: 'Closed',
            call: 'Call',
            directions: 'Directions',
            website: 'Website',
            kilometers: 'km',
            menttaElite: 'Mentta Elite',
            emergency24h: 'Urgency 24h',
            yourLocation: 'Your current location',
            defaultLocation: 'Lima (default location)',
            usingApproxLocation: 'Using approximate location',
            noCentersNearby: 'No centers registered near your location.',
            errorLoadingCenters: 'Error loading centers',
        },

        // Landing Page
        landing: {
            // Hero
            topText: 'SOPHISTICATED MENTAL CLARITY',
            title: 'MENTTA',
            subtitle: 'Support for your mind, anytime. A serene and intuitive space for mental well-being and personal growth.',
            login: 'LOGIN',
            register: 'REGISTER',

            // Stats
            statsLabel: 'Our Impact',
            statsTitle: 'Numbers That Matter',
            activeUsers: 'Active Users',
            sessionsCompleted: 'Sessions Completed',
            userSatisfaction: 'User Satisfaction',
            alwaysAvailable: 'Always Available',

            // How It Works
            hiwLabel: 'Simple Process',
            hiwTitle: 'How Mentta Works',
            hiwSubtitle: 'Begin your journey to mental clarity in three simple steps',
            step1Title: 'Create Your Account',
            step1Desc: 'Sign up in seconds with just your email. Your privacy is our priority from day one.',
            step2Title: 'Start a Conversation',
            step2Desc: 'Open a chat with our AI companion. It\'s trained to listen, understand, and support without judgment.',
            step3Title: 'Receive Personalized Support',
            step3Desc: 'Get insights, techniques, and real-time emotional support tailored to your unique needs.',

            // Features
            featuresLabel: 'Capabilities',
            featuresTitle: 'What Makes Us Different',
            feature1Title: 'AI Emotional Analysis',
            feature1Desc: 'Advanced algorithms detect emotional patterns and provide real-time insights to support your wellbeing.',
            feature2Title: 'Crisis Detection',
            feature2Desc: 'Intelligent monitoring identifies signs of distress and provides immediate resources when you need them most.',
            feature3Title: 'Mentta Live Sessions',
            feature3Desc: 'Connect through video calls for a more personal, human-like experience with our AI companion.',
            feature4Title: 'Professional Connection',
            feature4Desc: 'Seamlessly connect with licensed psychologists when you need human expertise beyond AI support.',
            feature5Title: 'Bank-Level Security',
            feature5Desc: 'End-to-end encryption ensures your conversations remain private and protected at all times.',
            feature6Title: '24/7 Availability',
            feature6Desc: 'Mental health support doesn\'t wait. Mentta is here for you anytime, day or night, holidays included.',

            // Testimonials
            testimonialsLabel: 'Stories',
            testimonialsTitle: 'What Our Users Say',
            testimonial1: 'Mentta helped me through my darkest moments. Having someone to talk to at 3 AM when anxiety hits is invaluable.',
            testimonial1Author: 'Anonymous User',
            testimonial1Meta: 'Member since 2025',
            testimonial2: 'I was skeptical about AI therapy, but Mentta truly understands context. It remembers our conversations and builds on them.',
            testimonial2Author: 'M. García',
            testimonial2Meta: '3 months with Mentta',
            testimonial3: 'The breathing exercises and grounding techniques have become part of my daily routine. Simple but life-changing.',
            testimonial3Author: 'Carlos R.',
            testimonial3Meta: 'Active user',

            // FAQ
            faqLabel: 'Questions',
            faqTitle: 'Frequently Asked',
            q1: 'Is my information secure and private?',
            a1: 'Absolutely. We use bank-level encryption for all data. Your conversations are never shared, sold, or used for advertising. You can delete your data at any time from your profile settings.',
            q2: 'How does the AI understand my emotions?',
            a2: 'Our AI is trained on clinical psychology protocols and uses advanced natural language processing to detect emotional cues, context, and patterns in your messages. It continuously learns from our conversations to better support you.',
            q3: 'What happens if I\'m in a crisis?',
            a3: 'Mentta has built-in crisis detection. If we identify you may be in danger, we\'ll provide immediate access to emergency hotlines (like 113), calming exercises, and—if you\'ve set it up—notify your emergency contacts or linked psychologist.',
            q4: 'Is Mentta free to use?',
            a4: 'Yes, Mentta offers a free tier with full access to our AI companion, chat history, and wellness resources. Premium features like Mentta Live video sessions and professional psychologist connections may have associated costs.',

            // CTA
            ctaLabel: 'Ready to Begin?',
            ctaTitle: 'Your Journey to Clarity Starts Now',
            ctaDesc: 'Join thousands who have found solace, understanding, and growth with Mentta. Your mental well-being deserves attention.',
            createAccount: 'Create Free Account',
            signIn: 'Sign In',
            emergencyText: 'In case of emergency, call 113 or 106 immediately.',
            footer: {
                hq: 'GLOBAL HEADQUARTERS',
                legal: 'LEGAL',
                security: 'SECURITY',
                privacy: 'PRIVACY POLICY',
            }
        },

        // Profile
        profile: {
            mySettings: 'My Settings',
            myAccount: 'My Account',
            personalInfo: 'Personal Information',
            name: 'Name',
            email: 'Email',
            age: 'Age',
            optional: 'Optional',
            emailCannotChange: 'Email cannot be modified',
            saveChanges: 'Save Changes',
            changePassword: 'Change Password',
            currentPassword: 'Current Password',
            newPassword: 'New Password',
            confirmPassword: 'Confirm Password',
            emergencyContacts: 'Emergency Contacts',
            addContact: 'Add Contact',
            noContacts: 'No emergency contacts added yet',
            linkedPsychologist: 'Linked Psychologist',
            linkedSince: 'Linked since',
            noPsychologist: "You don't have a linked psychologist",
            findProfessional: 'Find professional (Coming soon)',
            emergencyProtocol: 'Automatic Emergency Protocol',
            configureEmergency: 'Configure how Mentta should act when it detects a severe emotional crisis.',
            notifyPsychologist: 'Notify my psychologist',
            sendAlertPsychologist: 'Send alert to my linked psychologist in case of crisis',
            contactEmergency: 'Contact my emergency contacts',
            notifyContactsDanger: 'Notify my contacts if I am in danger',
            autoEmergencyHelp: 'Automatic emergency help',
            showCallButton: 'Show call button to 113/106 when imminent danger is detected',
            requiresConsent: 'Requires your explicit consent',
            preferences: 'Preferences',
            darkMode: 'Dark Mode',
            reducesEyeStrain: 'Reduces eye strain in low-light environments',
            languageLabel: 'Language',
            chooseLanguage: 'Choose your preferred language for the interface',
            pauseAnalysis: 'Pause Emotional Analysis',
            pauseAnalysisDesc: 'Temporarily disable emotion analysis and automatic alerts for 24 hours',
            analysisPausedUntil: 'Analysis paused until:',
            privacy: 'Privacy',
            deleteHistory: 'Delete conversation history',
            cannotUndo: 'This action cannot be undone',
            logout: 'Log Out',

            // Missing Description Keys
            emergencyProtocolDesc: 'Configure how Mentta should act when it detects a severe emotional crisis.',
            notifyPsychologistDesc: 'Send alert to my linked psychologist in case of crisis',
            notifyContacts: 'Contact my emergency contacts',
            notifyContactsDesc: 'Notify my contacts if I am in danger',
            autoHelp: 'Automatic emergency help',
            autoHelpDesc: 'Show call button to 113/106 when imminent danger is detected',
            explicitConsent: 'Requires your explicit consent',

            // Missing keys for modals & detailed sections
            emergencyContactsSection: 'Emergency Contacts',
            emergencyContactsDesc: 'These contacts will be notified in case of a crisis situation.',
            delete: 'Delete',
            addContactButton: 'Add Contact',
            addContactTitle: 'Add Contact',
            contactName: 'Name',
            contactRel: 'Relationship',
            contactPhone: 'Phone',
            contactPriority: 'Priority',
            cancel: 'Cancel',
            add: 'Add',

            linkWithCode: 'Link with Code',
            askCode: 'Ask your psychologist for the 6-digit code',
            scanQR: 'Scan QR',
            linkButton: 'Link',

            deleteHistoryConfirm: 'Delete entire history?',
            deleteHistoryWarning: 'All your conversations will be deleted and the AI will lose the context it has learned about you. This action cannot be undone.',

            consentTitle: 'Consent for Emergency Help',
            consentDesc: 'By activating this option, you authorize Mentta to:',
            consentPoint1: 'Show a quick call button to 113 or 106 when it detects you are in danger',
            consentPoint2: 'Record this preference for future sessions',
            consentFooter: 'You can deactivate this option at any time. Your privacy is important to us.',
            noThanks: 'No, thanks',
            yesIAccept: 'Yes, I accept',

            // Javascript messages
            updated: 'Profile updated',
            updateError: 'Error updating profile',
            connectionError: 'Connection error',
            passwordsNoMatch: 'Passwords do not match',
            passwordUpdated: 'Password updated',
            passwordError: 'Error changing password',
            contactAdded: 'Contact added',
            contactAddError: 'Error adding contact',
            confirmDeleteContact: 'Delete this emergency contact?',
            contactDeleted: 'Contact deleted',
            contactDeleteError: 'Error deleting contact',
            analysisToggleConfirm: 'Are you sure you want to pause emotional analysis?\n\nSafety alerts will be disabled for 24 hours. This means you won\'t receive automatic help if you share something concerning.',
            configError: 'Error changing configuration',
            historyDeleted: 'History deleted',
            historyDeleteError: 'Error deleting history',
            logoutConfirm: 'Log out?',
            codeLengthError: 'Code must be 6 characters long',
            linking: 'Linking...',
            linkSuccess: 'Linked with {name}!',
            linkError: 'Error linking',
        },

        // Dashboard (Psychologist)
        dashboard: {
            title: 'Mentta Professional',
            subtitle: 'Mental Health Suite',
            specialist: 'Specialist',
            newPatient: 'New Patient',
            myPatients: 'My Patients',
            refresh: 'Refresh',
            searchPlaceholder: 'Search by name...',
            logout: 'Log Out',

            // Empty State / Welcome
            carePortal: 'Care Portal',
            welcomeMessage: 'Manage your consultations with the precision of AI analysis and the warmth of human care. Select a patient to start.',
            selectCard: 'Select a card',

            // Patient Detail
            stable: 'Stable',
            monitor: 'Monitor',
            risk: 'At Risk',
            years: 'years',
            since: 'Since',

            // Alerts & Status
            critical: 'Critical',
            priority: 'Priority',
            pending: 'Pending',
            attended: 'Attended',
            markAttended: 'Mark as done',
            viewSession: 'View Session',
            ignore: 'Ignore',
            noAlerts: 'No recent alerts',

            // Metrics
            sessions: 'Sessions',
            msgPerDay: 'Msg / Day',
            lastActive: 'Last Active',
            streak: 'Streak',
            days: 'Days',

            // Charts & Analysis
            wellnessEvolution: 'Wellness Evolution',
            analysis30Days: '30 Day Analysis',
            processingInfo: 'Information processing',
            criticalLine: 'Critical Line',
            recurringConcepts: 'Recurring Concepts',
            generatedBy: 'Generated by Mentta AI Analytics',

            // Connect Modal
            linkPatient: 'Link New Patient',
            shareCodeInstruction: 'Share this code with your patient or ask them to scan the QR.',
            linkCode: 'Link Code',
            validFor24h: 'Valid for 24 hours',
            close: 'Close',

            // JS Dynamic
            loadingPatients: 'Loading patients...',
            errorLoading: 'Error loading',
            connectionError: 'Connection error',
            welcomeTitle: 'Welcome to the Dashboard!',
            noPatientsYet: 'You don\'t have linked patients yet.',
            linkFromProfile: 'Patients can link from their profile.',
            now: 'Just now',
            ago: 'ago',
            hours: 'hours',
            days_lowercase: 'days',
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
            // Session UI (React)
            encryptedSession: 'Encrypted Session',
            you: 'You',
            cameraOff: 'CAMERA OFF',
            micOff: 'Microphone Off',
            aiAnalysis: 'AI Analysis',
            endCall: 'END',
            privateSpace: 'Private Space',
            secure: 'Secure',
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

            // Crisis / Panic
            panicTitle: 'Ayuda Inmediata',
            panicMessage: 'Si necesitas ayuda inmediata, puedes llamar a la línea de crisis.',
            call: 'Llamar',
            panicFooter: 'Línea 113 (Salud Mental) • SAMU 106 (Emergencias)',
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
            today: 'Hoy',
            noMessages: 'Sin mensajes aún',
        },

        // Map Page
        map: {
            title: 'Mapa de Centros - Mentta',
            loading: 'Cargando mapa...',
            locator: 'Localizador',
            search: 'Buscar',
            searchInputPlaceholder: 'Busca por nombre o distrito...',
            mobileSearchInputPlaceholder: 'Nombre, distrito o especialidad...',
            realTimeResults: 'Resultados en tiempo real',
            currentEnvironment: 'Entorno Actual',
            online: 'En línea',
            detecting: 'Detectando...',
            optimizedForYou: 'Optimizado para ti',
            filters: {
                all: 'Todos',
                mentta: 'Red Mentta',
                emergency: '24h',
                health: 'Salud',
                community: 'Comunidad',
            },
            nearbyResults: 'Resultados Cercanos',
            noResults: 'No se encontraron centros',
            openNow: 'Abierto ahora',
            closed: 'Cerrado',
            call: 'Llamar',
            directions: 'Cómo llegar',
            website: 'Sitio Web',
            kilometers: 'km',
            menttaElite: 'Mentta Elite',
            emergency24h: 'Urgencias 24h',
            yourLocation: 'Tu ubicación actual',
            defaultLocation: 'Lima (ubicación predeterminada)',
            usingApproxLocation: 'Usando ubicación aproximada',
            noCentersNearby: 'No hay centros registrados cerca de tu ubicación.',
            errorLoadingCenters: 'Error al cargar centros',
        },

        // Landing Page
        landing: {
            // Hero
            topText: 'CLARIDAD MENTAL SOFISTICADA',
            title: 'MENTTA',
            subtitle: 'Apoyo para tu mente, en cualquier momento. Un espacio sereno e intuitivo para el bienestar mental y crecimiento personal.',
            login: 'INGRESAR',
            register: 'REGISTRARSE',

            // Stats
            statsLabel: 'Nuestro Impacto',
            statsTitle: 'Números que Importan',
            activeUsers: 'Usuarios Activos',
            sessionsCompleted: 'Sesiones Completadas',
            userSatisfaction: 'Satisfacción de Usuarios',
            alwaysAvailable: 'Siempre Disponible',

            // How It Works
            hiwLabel: 'Proceso Simple',
            hiwTitle: 'Cómo Funciona Mentta',
            hiwSubtitle: 'Comienza tu viaje hacia la claridad mental en tres simples pasos',
            step1Title: 'Crea Tu Cuenta',
            step1Desc: 'Regístrate en segundos solo con tu email. Tu privacidad es nuestra prioridad desde el primer día.',
            step2Title: 'Inicia una Conversación',
            step2Desc: 'Abre un chat con nuestro compañero IA. Entrenado para escuchar, entender y apoyar sin juzgar.',
            step3Title: 'Recibe Apoyo Personalizado',
            step3Desc: 'Obtén perspectivas, técnicas y apoyo emocional en tiempo real adaptado a tus necesidades únicas.',

            // Features
            featuresLabel: 'Capacidades',
            featuresTitle: 'Qué nos hace diferentes',
            feature1Title: 'Análisis Emocional IA',
            feature1Desc: 'Algoritmos avanzados detectan patrones emocionales y brindan perspectivas en tiempo real para apoyar tu bienestar.',
            feature2Title: 'Detección de Crisis',
            feature2Desc: 'Monitoreo inteligente identifica señales de angustia y proporciona recursos inmediatos cuando más los necesitas.',
            feature3Title: 'Sesiones en Vivo Mentta',
            feature3Desc: 'Conecta a través de videollamadas para una experiencia más personal y humana con nuestro compañero IA.',
            feature4Title: 'Conexión Profesional',
            feature4Desc: 'Conecta sin problemas con psicólogos licenciados cuando necesites experiencia humana más allá del apoyo IA.',
            feature5Title: 'Seguridad Nivel Bancario',
            feature5Desc: 'Cifrado de extremo a extremo asegura que tus conversaciones permanezcan privadas y protegidas en todo momento.',
            feature6Title: 'Disponibilidad 24/7',
            feature6Desc: 'El apoyo a la salud mental no espera. Mentta está aquí para ti en cualquier momento, día o noche, feriados incluidos.',

            // Testimonials
            testimonialsLabel: 'Historias',
            testimonialsTitle: 'Lo Que Dicen Nuestros Usuarios',
            testimonial1: 'Mentta me ayudó en mis momentos más oscuros. Tener a alguien con quien hablar a las 3 AM cuando la ansiedad ataca es invaluable.',
            testimonial1Author: 'Usuario Anónimo',
            testimonial1Meta: 'Miembro desde 2025',
            testimonial2: 'Era escéptico sobre la terapia con IA, pero Mentta realmente entiende el contexto. Recuerda nuestras conversaciones y construye sobre ellas.',
            testimonial2Author: 'M. García',
            testimonial2Meta: '3 meses con Mentta',
            testimonial3: 'Los ejercicios de respiración y técnicas de anclaje se han vuelto parte de mi rutina diaria. Simple pero cambia la vida.',
            testimonial3Author: 'Carlos R.',
            testimonial3Meta: 'Usuario activo',

            // FAQ
            faqLabel: 'Preguntas',
            faqTitle: 'Preguntas Frecuentes',
            q1: '¿Mi información es segura y privada?',
            a1: 'Absolutamente. Usamos cifrado de nivel bancario para todos los datos. Tus conversaciones nunca se comparten, venden o usan para publicidad. Puedes eliminar tus datos en cualquier momento desde la configuración de tu perfil.',
            q2: '¿Cómo entiende la IA mis emociones?',
            a2: 'Nuestra IA está entrenada en protocolos de psicología clínica y usa procesamiento de lenguaje natural avanzado para detectar señales emocionales, contexto y patrones en tus mensajes. Aprende continuamente de nuestras conversaciones para apoyarte mejor.',
            q3: '¿Qué sucede si estoy en una crisis?',
            a3: 'Mentta tiene detección de crisis integrada. Si identificamos que puedes estar en peligro, proporcionaremos acceso inmediato a líneas de emergencia (como el 113), ejercicios de calma y —si lo configuraste— notificaremos a tus contactos de emergencia o psicólogo vinculado.',
            q4: '¿Es Mentta gratis de usar?',
            a4: 'Sí, Mentta ofrece un nivel gratuito con acceso completo a nuestro compañero IA, historial de chat y recursos de bienestar. Funciones premium como las sesiones de video Mentta Live y conexiones con psicólogos profesionales pueden tener costos asociados.',

            // CTA
            ctaLabel: '¿Listo para Comenzar?',
            ctaTitle: 'Tu Viaje hacia la Claridad Comienza Ahora',
            ctaDesc: 'Únete a miles que han encontrado consuelo, comprensión y crecimiento con Mentta. Tu bienestar mental merece atención.',
            createAccount: 'Crear Cuenta Gratis',
            signIn: 'Ingresar',
            emergencyText: 'En caso de emergencia, llama al 113 o 106 inmediatamente.',
            footer: {
                hq: 'SEDE GLOBAL',
                legal: 'LEGAL',
                security: 'SEGURIDAD',
                privacy: 'POLÍTICA DE PRIVACIDAD',
            }
        },


        // Profile
        profile: {
            mySettings: 'Mi Configuración',
            myAccount: 'Mi Cuenta',
            personalInfo: 'Información Personal',
            name: 'Nombre',
            email: 'Correo Electrónico',
            age: 'Edad',
            optional: 'Opcional',
            emailCannotChange: 'El correo no puede ser modificado',
            saveChanges: 'Guardar Cambios',
            changePassword: 'Cambiar Contraseña',
            currentPassword: 'Contraseña Actual',
            newPassword: 'Nueva Contraseña',
            confirmPassword: 'Confirmar Contraseña',
            emergencyContacts: 'Contactos de Emergencia',
            addContact: 'Agregar Contacto',
            noContacts: 'No hay contactos de emergencia agregados',
            linkedPsychologist: 'Psicólogo Vinculado',
            linkedSince: 'Vinculado desde',
            noPsychologist: 'No tienes un psicólogo vinculado',
            findProfessional: 'Buscar profesional (Próximamente)',
            emergencyProtocol: 'Protocolo de Emergencia Automática',
            configureEmergency: 'Configura cómo Mentta debe actuar cuando detecte una crisis emocional grave.',
            notifyPsychologist: 'Notificar a mi psicólogo',
            sendAlertPsychologist: 'Enviar alerta a mi psicólogo vinculado en caso de crisis',
            contactEmergency: 'Contactar a mis contactos de emergencia',
            notifyContactsDanger: 'Notificar a mis contactos si estoy en peligro',
            autoEmergencyHelp: 'Ayuda automática de emergencia',
            showCallButton: 'Mostrar botón de llamada al 113/106 cuando se detecte peligro inminente',
            requiresConsent: 'Requiere tu consentimiento explícito',
            preferences: 'Preferencias',
            darkMode: 'Modo Oscuro',
            reducesEyeStrain: 'Reduce la fatiga visual en ambientes con poca luz',
            languageLabel: 'Idioma / Language',
            chooseLanguage: 'Elige tu idioma preferido para la interfaz',
            pauseAnalysis: 'Pausar Análisis Emocional',
            pauseAnalysisDesc: 'Desactiva temporalmente el análisis de emociones y alertas automáticas por 24 horas',
            analysisPausedUntil: 'Análisis pausado hasta:',
            privacy: 'Privacidad',
            deleteHistory: 'Eliminar historial de conversaciones',
            cannotUndo: 'Esta acción no se puede deshacer',
            logout: 'Cerrar Sesión',

            // Missing Description Keys
            emergencyProtocolDesc: 'Configura cómo Mentta debe actuar cuando detecte una crisis emocional grave.',
            notifyPsychologistDesc: 'Enviar alerta a mi psicólogo vinculado en caso de crisis',
            notifyContacts: 'Contactar a mis contactos de emergencia',
            notifyContactsDesc: 'Notificar a mis contactos si estoy en peligro',
            autoHelp: 'Ayuda automática de emergencia',
            autoHelpDesc: 'Mostrar botón de llamada al 113/106 cuando se detecte peligro inminente',
            explicitConsent: 'Requiere tu consentimiento explícito',

            // Missing keys for modals & detailed sections
            emergencyContactsSection: 'Contactos de Emergencia',
            emergencyContactsDesc: 'Estos contactos serán notificados en caso de una situación de crisis.',
            delete: 'Eliminar',
            addContactButton: 'Agregar Contacto',
            addContactTitle: 'Agregar Contacto',
            contactName: 'Nombre',
            contactRel: 'Relación',
            contactPhone: 'Teléfono',
            contactPriority: 'Prioridad',
            cancel: 'Cancelar',
            add: 'Agregar',

            linkWithCode: 'Vincular con Código',
            askCode: 'Pide el código de 6 dígitos a tu psicólogo',
            scanQR: 'Escanear QR',
            linkButton: 'Vincular',

            deleteHistoryConfirm: '¿Eliminar historial completo?',
            deleteHistoryWarning: 'Se eliminarán todas tus conversaciones y la IA perderá el contexto que ha aprendido sobre ti. Esta acción no se puede deshacer.',

            consentTitle: 'Consentimiento para Ayuda de Emergencia',
            consentDesc: 'Al activar esta opción, autorizas a Mentta a:',
            consentPoint1: 'Mostrar un botón de llamada rápida al 113 o 106 cuando detecte que estás en peligro',
            consentPoint2: 'Registrar esta preferencia para futuras sesiones',
            consentFooter: 'Puedes desactivar esta opción en cualquier momento. Tu privacidad es importante para nosotros.',
            noThanks: 'No, gracias',
            yesIAccept: 'Sí, acepto',

            // Javascript messages
            updated: 'Perfil actualizado',
            updateError: 'Error al actualizar perfil',
            connectionError: 'Error de conexión',
            passwordsNoMatch: 'Las contraseñas no coinciden',
            passwordUpdated: 'Contraseña actualizada',
            passwordError: 'Error al cambiar contraseña',
            contactAdded: 'Contacto agregado',
            contactAddError: 'Error al agregar contacto',
            confirmDeleteContact: '¿Eliminar este contacto de emergencia?',
            contactDeleted: 'Contacto eliminado',
            contactDeleteError: 'Error al eliminar contacto',
            analysisToggleConfirm: '¿Seguro que deseas pausar el análisis emocional?\\n\\nLas alertas de seguridad se desactivarán por 24 horas. Esto significa que no recibirás ayuda automática si compartes algo preocupante.',
            configError: 'Error al cambiar configuración',
            historyDeleted: 'Historial eliminado',
            historyDeleteError: 'Error al eliminar historial',
            logoutConfirm: '¿Cerrar sesión?',
            codeLengthError: 'El código debe tener 6 caracteres',
            linking: 'Vinculando...',
            linkSuccess: '¡Vinculado con {name}!',
            linkError: 'Error al vincular',
        },

        // Dashboard (Psychologist)
        dashboard: {
            title: 'Mentta Profesional',
            subtitle: 'Mental Health Suite',
            specialist: 'Especialista',
            newPatient: 'Nuevo Paciente',
            myPatients: 'Mis Pacientes',
            refresh: 'Actualizar',
            searchPlaceholder: 'Buscar por nombre...',
            logout: 'Cerrar Sesión',

            // Empty State / Welcome
            carePortal: 'Portal de Cuidado',
            welcomeMessage: 'Gestiona tus consultas con la precisión del análisis de IA y la calidez de la atención humana. Selecciona un paciente para iniciar.',
            selectCard: 'Selecciona una tarjeta',

            // Patient Detail
            stable: 'Estable',
            monitor: 'Monitorear',
            risk: 'En Riesgo',
            years: 'años',
            since: 'Desde',

            // Alerts & Status
            critical: 'Crítica',
            priority: 'Prioritaria',
            pending: 'Pendiente',
            attended: 'Atendida',
            markAttended: 'Marcar atendida',
            viewSession: 'Ver Sesión',
            ignore: 'Ignorar',
            noAlerts: 'No hay alertas recientes',

            // Metrics
            sessions: 'Sesiones',
            msgPerDay: 'Msj / Día',
            lastActive: 'Última Vez',
            streak: 'Racha',
            days: 'Días',

            // Charts & Analysis
            wellnessEvolution: 'Evolución del Bienestar',
            analysis30Days: 'Análisis 30 Días',
            processingInfo: 'Información en proceso de análisis',
            criticalLine: 'Línea Crítica',
            recurringConcepts: 'Conceptos Recurrentes',
            generatedBy: 'Generado por Mentta AI Analytics',

            // Connect Modal
            linkPatient: 'Vincular Nuevo Paciente',
            shareCodeInstruction: 'Comparte este código con tu paciente o pídele que escanee el QR.',
            linkCode: 'Código de Vinculación',
            validFor24h: 'Válido por 24 horas',
            close: 'Cerrar',

            // JS Dynamic
            loadingPatients: 'Cargando pacientes...',
            errorLoading: 'Error al cargar',
            connectionError: 'Error de conexión',
            welcomeTitle: '¡Bienvenido al Panel!',
            noPatientsYet: 'Aún no tienes pacientes vinculados.',
            linkFromProfile: 'Los pacientes pueden vincularse desde su perfil.',
            now: 'Ahora mismo',
            ago: 'hace',
            hours: 'horas',
            days_lowercase: 'días',
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
            // Session UI (React)
            encryptedSession: 'Sesión Encriptada',
            you: 'Tú',
            cameraOff: 'CÁMARA APAGADA',
            micOff: 'Micrófono Apagado',
            aiAnalysis: 'Análisis IA',
            endCall: 'TERMINAR',
            privateSpace: 'Espacio Privado',
            secure: 'Seguro',
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

        // Try current language
        let result = TRANSLATIONS[this.currentLang];
        let found = true;

        for (const key of keys) {
            if (result && typeof result === 'object' && key in result) {
                result = result[key];
            } else {
                found = false;
                break;
            }
        }

        if (found && typeof result === 'string') {
            return result;
        }

        // Try English fallback
        result = TRANSLATIONS['en'];
        found = true;
        for (const key of keys) {
            if (result && typeof result === 'object' && key in result) {
                result = result[key];
            } else {
                found = false;
                break;
            }
        }

        if (found && typeof result === 'string') {
            return result;
        }

        return fallback || keyPath;
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
window.i18n = i18n;

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
