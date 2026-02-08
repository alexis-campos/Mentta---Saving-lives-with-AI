<?php
/**
 * MENTTA - Patient Profile Page
 * User profile, settings, emergency contacts, and preferences
 */

require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Requiere autenticación como paciente
$user = requireAuth('patient');

// Obtener preferencias del usuario
$theme = $user['theme_preference'] ?? 'light';
$userLanguage = $user['language'] ?? 'es';

// Obtener datos adicionales
$db = getDB();

// Obtener preferencias de análisis
$analysisPaused = false;
$pausedUntil = null;
try {
    $stmt = $db->prepare("SELECT analysis_paused, analysis_paused_until FROM user_preferences WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $prefs = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($prefs && $prefs['analysis_paused'] && strtotime($prefs['analysis_paused_until']) > time()) {
        $analysisPaused = true;
        $pausedUntil = $prefs['analysis_paused_until'];
    }
} catch (Exception $e) {
}

// Obtener contactos de emergencia
$emergencyContacts = [];
try {
    $stmt = $db->prepare("SELECT * FROM emergency_contacts WHERE patient_id = ? ORDER BY priority ASC");
    $stmt->execute([$user['id']]);
    $emergencyContacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
}

// Obtener psicólogo vinculado
$linkedPsychologist = null;
try {
    $stmt = $db->prepare("
        SELECT u.name, u.email, ppl.linked_at 
        FROM patient_psychologist_link ppl 
        JOIN users u ON ppl.psychologist_id = u.id 
        WHERE ppl.patient_id = ? AND ppl.status = 'active'
    ");
    $stmt->execute([$user['id']]);
    $linkedPsychologist = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($userLanguage) ?>" data-theme="<?= htmlspecialchars($theme) ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="<?= $theme === 'dark' ? '#1F2937' : '#6366F1' ?>">
    <title>Mi Cuenta - Mentta</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'mentta-primary': '#111111',
                        'mentta-secondary': '#666666',
                        'mentta-accent': '#333333',
                        'mentta-light': '#f5f5f5',
                        mentta: {
                            50: '#f9f9f9',
                            100: '#f5f5f5',
                            200: '#e5e5e5',
                            300: '#d4d4d4',
                            400: '#a3a3a3',
                            500: '#737373',
                            600: '#525252',
                            700: '#404040',
                            800: '#262626',
                            900: '#171717'
                        }
                    },
                    fontFamily: {
                        serif: ['Playfair Display', 'serif'],
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <!-- Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,700;1,700&display=swap"
        rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/theme.css">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-primary);
        }

        .profile-section {
            background-color: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 1.5rem;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-card);
        }

        .profile-section-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-tertiary);
            margin-bottom: 0.5rem;
        }

        .form-input {
            width: 100%;
            padding: 0.875rem 1rem;
            background-color: var(--bg-input);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            color: var(--text-primary);
            font-size: 0.9375rem;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--accent-secondary);
            background-color: var(--bg-secondary);
            box-shadow: 0 0 0 4px rgba(203, 170, 142, 0.1);
        }

        .form-input:disabled {
            background-color: var(--bg-tertiary);
            color: var(--text-tertiary);
            cursor: not-allowed;
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            padding: 1rem 1.5rem;
            background: var(--accent-primary);
            color: var(--text-inverse);
            border: none;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-primary:hover {
            background-color: var(--accent-hover);
            transform: translateY(-1px);
            box-shadow: var(--shadow-lg);
        }

        .btn-primary:active {
            transform: scale(0.98);
        }

        .btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.875rem 1.25rem;
            background-color: var(--bg-tertiary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-secondary:hover {
            background-color: var(--bg-secondary);
            border-color: var(--accent-secondary);
            color: var(--accent-secondary);
        }

        .btn-danger {
            background-color: var(--danger-light);
            color: var(--danger);
            border: 1px solid var(--danger-light);
        }

        .btn-danger:hover {
            background-color: var(--danger);
            color: var(--text-inverse);
            border-color: var(--danger);
        }

        .contact-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem;
            background-color: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-radius: 1.25rem;
            margin-bottom: 0.75rem;
            transition: all 0.3s ease;
        }

        .contact-card:hover {
            border-color: var(--border-focus);
            background-color: var(--bg-secondary);
            box-shadow: var(--shadow-sm);
        }

        .contact-info {
            flex: 1;
        }

        .contact-name {
            font-weight: 700;
            color: var(--text-primary);
            font-size: 0.9375rem;
        }

        .contact-details {
            font-size: 0.8125rem;
            color: var(--text-tertiary);
            margin-top: 0.25rem;
        }

        .preference-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.5rem;
            padding: 1.25rem 0;
            border-bottom: 1px solid rgba(45, 58, 45, 0.05);
        }

        .preference-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .preference-info h4 {
            font-weight: 700;
            color: var(--text-primary);
            margin: 0 0 0.25rem 0;
            font-size: 0.9375rem;
        }

        .preference-info p {
            font-size: 0.8125rem;
            color: var(--text-tertiary);
            margin: 0;
            line-height: 1.5;
        }

        .analysis-warning {
            background-color: #fffbeb;
            border: 1px solid #fef3c7;
            padding: 0.75rem 1rem;
            border-radius: 1rem;
            margin-top: 0.75rem;
        }

        .analysis-warning p {
            color: #b45309;
            font-size: 0.75rem;
            font-weight: 600;
            margin: 0;
        }

        /* Header customization */
        header {
            background-color: var(--bg-secondary) !important;
            backdrop-filter: blur(12px);
        }
        
        /* ============================================
           MOBILE-FIRST RESPONSIVE STYLES
           ============================================ */
        
        /* Mobile Form Inputs - Prevent iOS Zoom */
        .form-input,
        .form-input:focus {
            font-size: 16px; /* Prevents iOS zoom on focus */
        }
        
        /* Mobile Base (under 480px) */
        @media (max-width: 479px) {
            .profile-section {
                padding: 1.25rem;
                border-radius: 1.25rem;
                margin-bottom: 1.5rem;
            }
            
            .profile-section-title {
                font-size: 1.125rem;
                margin-bottom: 1.25rem;
            }
            
            .form-group {
                margin-bottom: 1.25rem;
            }
            
            .form-input {
                padding: 0.875rem 1rem;
                min-height: 48px;
                border-radius: 0.875rem;
            }
            
            .btn-primary,
            .btn-secondary {
                min-height: 48px;
                padding: 0.875rem 1.25rem;
                font-size: 0.75rem;
            }
            
            .preference-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
                padding: 1rem 0;
            }
            
            .toggle-switch {
                align-self: flex-end;
            }
            
            .contact-card {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
                padding: 1rem;
            }
            
            .contact-card button {
                width: 100%;
                min-height: 44px;
            }
            
            /* Modal mobile styles */
            .modal-content {
                margin: 0.75rem;
                max-width: calc(100% - 1.5rem);
                max-height: calc(100vh - 1.5rem);
                border-radius: 1.25rem;
            }
            
            .modal-body {
                padding: 1rem;
            }
            
            .modal-header {
                padding: 0.875rem 1rem;
            }
            
            .modal-footer {
                padding: 0.875rem 1rem;
                flex-direction: column;
                gap: 0.75rem;
            }
            
            .modal-footer button {
                width: 100%;
            }
        }
        
        /* Small Phones (under 375px) */
        @media (max-width: 374px) {
            main {
                padding-top: 3.5rem;
            }
            
            .max-w-2xl {
                padding-left: 0.75rem;
                padding-right: 0.75rem;
            }
            
            .profile-section {
                padding: 1rem;
            }
            
            .preference-info h4 {
                font-size: 0.875rem;
            }
            
            .preference-info p {
                font-size: 0.75rem;
            }
        }
        
        /* Touch Device Optimizations */
        @media (hover: none) and (pointer: coarse) {
            .btn-primary:hover,
            .btn-secondary:hover,
            .contact-card:hover {
                transform: none;
            }
            
            .btn-primary:active,
            .btn-secondary:active {
                transform: scale(0.98);
            }
            
            /* Larger touch targets for toggles */
            .toggle-switch {
                min-width: 54px;
                min-height: 30px;
            }
        }
    </style>
</head>

<body class="antialiased loaded" style="background-color: var(--bg-primary);">
    <script>
        // Ensure body is visible immediately
        document.body.classList.add('loaded');
    </script>

    <!-- Header -->
    <header class="fixed top-0 left-0 right-0 z-50"
        style="background-color: var(--bg-secondary); border-bottom: 1px solid var(--border-color);">
        <div class="max-w-2xl mx-auto px-4 py-3 flex items-center gap-3">
            <a href="chat.php" class="p-2 rounded-lg transition-colors" style="color: var(--text-secondary);"
                title="Volver al chat">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <h1 class="text-lg font-semibold" style="color: var(--text-primary);" data-i18n="profile.myAccount">My Account</h1>
        </div>
    </header>

    <!-- Main Content -->
    <main class="pt-16 pb-8">
        <div class="max-w-2xl mx-auto px-4 py-6">

            <!-- Personal Info Section -->
            <section class="profile-section">
                <h2 class="profile-section-title">👤 <span data-i18n="profile.personalInfo">Personal Information</span></h2>

                <form id="profile-form" onsubmit="Profile.updateProfile(event)">
                    <div class="form-group">
                        <label class="form-label" for="name" data-i18n="profile.name">Name</label>
                        <input type="text" id="name" name="name" class="form-input"
                            value="<?= htmlspecialchars($user['name']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="email" data-i18n="profile.email">Email</label>
                        <input type="email" id="email" class="form-input"
                            value="<?= htmlspecialchars($user['email']) ?>" disabled>
                            El correo no puede ser modificado</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="age" data-i18n="profile.age">Age</label>
                        <input type="number" id="age" name="age" class="form-input" value="<?= $user['age'] ?? '' ?>"
                            min="13" max="120" data-i18n-placeholder="profile.optional" placeholder="Optional">
                    </div>

                    <button type="submit" class="btn-primary" data-i18n="profile.saveChanges">
                        Save Changes
                    </button>
                </form>
            </section>

            <!-- Change Password Section -->
            <section class="profile-section">
                <h2 class="profile-section-title">🔐 <span data-i18n="profile.changePassword">Change Password</span></h2>

                <form id="password-form" onsubmit="Profile.changePassword(event)">
                    <div class="form-group">
                        <label class="form-label" for="current_password">Contraseña actual</label>
                        <input type="password" id="current_password" name="current_password" class="form-input"
                            required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="new_password">Nueva contraseña</label>
                        <input type="password" id="new_password" name="new_password" class="form-input" minlength="8"
                            required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="confirm_password">Confirmar contraseña</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-input"
                            required>
                    </div>

                    <button type="submit" class="btn-primary">
                        Cambiar contraseña
                    </button>
                </form>
            </section>

            <!-- Emergency Contacts Section -->
            <section class="profile-section">
                <h2 class="profile-section-title">👪 Contactos de Emergencia</h2>
                <p style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 1rem;">
                    Estos contactos serán notificados en caso de una situación de crisis.
                </p>

                <div id="contacts-list">
                    <?php if (empty($emergencyContacts)): ?>
                        <p style="color: var(--text-tertiary); font-size: 0.875rem; text-align: center; padding: 1rem;">
                            No tienes contactos de emergencia configurados
                        </p>
                    <?php else: ?>
                        <?php foreach ($emergencyContacts as $contact): ?>
                            <div class="contact-card" data-contact-id="<?= $contact['id'] ?>">
                                <div class="contact-info">
                                    <div class="contact-name"><?= htmlspecialchars($contact['contact_name']) ?></div>
                                    <div class="contact-details">
                                        <?= htmlspecialchars($contact['contact_relationship']) ?> •
                                        <?= htmlspecialchars($contact['contact_phone']) ?>
                                    </div>
                                </div>
                                <button class="btn-secondary" style="padding: 0.5rem; font-size: 0.75rem; color: var(--danger);"
                                    onclick="Profile.deleteContact(<?= $contact['id'] ?>)">
                                    Eliminar
                                </button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <button class="btn-secondary" style="width: 100%; margin-top: 0.75rem;"
                    onclick="Profile.showAddContactModal()">
                    ➕ Agregar Contacto
                </button>
            </section>

            <!-- Linked Psychologist Section -->
            <section class="profile-section">
                <h2 class="profile-section-title">👨‍⚕️ Psicólogo Vinculado</h2>

                <?php if ($linkedPsychologist): ?>
                    <div class="contact-card">
                        <div class="contact-info">
                            <div class="contact-name"><?= htmlspecialchars($linkedPsychologist['name']) ?></div>
                            <div class="contact-details">
                                Vinculado desde <?= date('d/m/Y', strtotime($linkedPsychologist['linked_at'])) ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="mt-4">
                        <form onsubmit="Profile.linkPsychologist(event)" class="flex flex-col gap-3">
                            <label class="form-label text-center mb-0">Vincular con Código</label>
                            <div class="flex gap-2">
                                <input type="text" name="code" class="form-input text-center font-mono uppercase tracking-widest text-lg" 
                                    placeholder="AB12CD" maxlength="6" required style="text-transform: uppercase;">
                            </div>
                            <button type="button" onclick="Profile.openScanner()" class="btn-secondary flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                                Escanear QR
                            </button>
                            <button type="submit" class="btn-primary">
                                🔗 Vincular
                            </button>
                        </form>
                        <p class="text-[10px] text-center text-gray-400 mt-2">
                            Pide el código de 6 dígitos a tu psicólogo
                        </p>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Crisis Preferences Section (NEW - PAP System) -->
            <section class="profile-section">
                <h2 class="profile-section-title">🆘 Protocolo de Emergencia Automática</h2>
                <p style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 1rem;">
                    Configura cómo Mentta debe actuar cuando detecte una crisis emocional grave.
                </p>

                <div class="preference-item">
                    <div class="preference-info">
                        <h4>👨‍⚕️ Notificar a mi psicólogo</h4>
                        <p>Enviar alerta a mi psicólogo vinculado en caso de crisis</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" id="notify-psychologist" checked
                            onchange="Profile.saveCrisisPreferences()">
                        <span class="toggle-slider"></span>
                    </label>
                </div>

                <div class="preference-item">
                    <div class="preference-info">
                        <h4>👪 Contactar a mis contactos de emergencia</h4>
                        <p>Notificar a mis contactos si estoy en peligro</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" id="notify-contacts" checked onchange="Profile.saveCrisisPreferences()">
                        <span class="toggle-slider"></span>
                    </label>
                </div>

                <div class="preference-item"
                    style="border-top: 1px solid var(--border-color); padding-top: 1.25rem; margin-top: 0.5rem;">
                    <div class="preference-info">
                        <h4 style="color: var(--text-primary);">🚨 Ayuda automática de emergencia</h4>
                        <p>Mostrar botón de llamada al 113/106 cuando se detecte peligro inminente</p>
                        <p style="font-size: 0.75rem; color: var(--text-tertiary); margin-top: 0.25rem;">
                            Requiere tu consentimiento explícito
                        </p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" id="auto-call-emergency" onchange="Profile.handleAutoCallChange(this)">
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </section>

            <!-- Preferences Section -->
            <section class="profile-section">
                <h2 class="profile-section-title">⚙️ <span data-i18n="profile.preferences">Preferences</span></h2>

                <!-- Theme Toggle -->
                <div class="preference-item">
                    <div class="preference-info">
                        <h4>🌙 <span data-i18n="profile.darkMode">Dark Mode</span></h4>
                        <p data-i18n="profile.reducesEyeStrain">Reduces eye strain in low-light environments</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" id="theme-toggle" <?= $theme === 'dark' ? 'checked' : '' ?>
                            onchange="toggleTheme()">
                        <span class="toggle-slider"></span>
                    </label>
                </div>

                <!-- Language Selector -->
                <div class="preference-item">
                    <div class="preference-info">
                        <h4>🌐 <span data-i18n="profile.languageLabel">Language</span></h4>
                        <p data-i18n="profile.chooseLanguage">Choose your preferred language for the interface</p>
                    </div>
                    <select id="language-select" 
                        class="form-input" 
                        style="width: auto; min-width: 120px; padding: 0.5rem 1rem;"
                        onchange="Profile.changeLanguage(this.value)">
                        <option value="es" <?= $userLanguage === 'es' ? 'selected' : '' ?>>Español</option>
                        <option value="en" <?= $userLanguage === 'en' ? 'selected' : '' ?>>English</option>
                    </select>
                </div>

                <!-- Pause Analysis Toggle -->
                <div class="preference-item">
                    <div class="preference-info">
                        <h4>⏸️ <span data-i18n="profile.pauseAnalysis">Pause Emotional Analysis</span></h4>
                        <p data-i18n="profile.pauseAnalysisDesc">Temporarily disable emotion analysis and automatic alerts for 24 hours</p>

                        <?php if ($analysisPaused): ?>
                            <div class="analysis-warning">
                                <p>⏸️ Análisis pausado hasta:
                                    <strong><?= date('d M, H:i', strtotime($pausedUntil)) ?></strong>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" id="analysis-toggle" <?= $analysisPaused ? 'checked' : '' ?>
                            onchange="Profile.toggleAnalysisPause()">
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </section>

            <!-- Privacy Section -->
            <section class="profile-section">
                <h2 class="profile-section-title">🔒 <span data-i18n="profile.privacy">Privacy</span></h2>

                <button class="btn-secondary btn-danger" style="width: 100%;" onclick="Profile.confirmDeleteHistory()">
                    🗑️ <span data-i18n="profile.deleteHistory">Delete conversation history</span>
                </button>
                <p style="font-size: 0.75rem; color: var(--text-tertiary); margin-top: 0.5rem; text-align: center;" data-i18n="profile.cannotUndo">
                    This action cannot be undone
                </p>
            </section>

            <!-- Logout Section -->
            <section class="profile-section" style="border-color: transparent; background: transparent; padding: 0;">
                <button class="btn-primary" style="background-color: #000;" onclick="Profile.logout()">
                    🚪 <span data-i18n="profile.logout">Log Out</span>
                </button>
            </section>

        </div>
    </main>

    <!-- Add Contact Modal -->
    <div id="add-contact-modal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">➕ Agregar Contacto</h3>
                <button class="modal-close" onclick="Profile.closeAddContactModal()">&times;</button>
            </div>
            <form id="add-contact-form" onsubmit="Profile.addContact(event)">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label" for="contact_name">Nombre</label>
                        <input type="text" id="contact_name" name="name" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="contact_relationship">Relación</label>
                        <select id="contact_relationship" name="relationship" class="form-input" required>
                            <option value="">Seleccionar...</option>
                            <option value="Padre">Padre</option>
                            <option value="Madre">Madre</option>
                            <option value="Hermano/a">Hermano/a</option>
                            <option value="Pareja">Pareja</option>
                            <option value="Amigo/a">Amigo/a</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="contact_phone">Teléfono</label>
                        <input type="tel" id="contact_phone" name="phone" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="contact_priority">Prioridad</label>
                        <select id="contact_priority" name="priority" class="form-input">
                            <option value="1">1 - Principal</option>
                            <option value="2">2 - Secundario</option>
                            <option value="3">3 - Alternativo</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary"
                        onclick="Profile.closeAddContactModal()">Cancelar</button>
                    <button type="submit" class="btn-primary">Agregar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Confirm Delete Modal -->
    <div id="confirm-delete-modal" class="modal-overlay">
        <div class="modal-content" style="max-width: 24rem;">
            <div class="modal-body text-center py-6">
                <div style="font-size: 3rem; margin-bottom: 1rem;">⚠️</div>
                <h3 style="color: var(--text-primary); font-size: 1.125rem; font-weight: 600; margin-bottom: 0.5rem;">
                    ¿Eliminar todo el historial?
                </h3>
                <p style="color: var(--text-secondary); font-size: 0.9375rem; margin-bottom: 1.5rem;">
                    Se eliminarán todas tus conversaciones y la IA perderá el contexto que ha aprendido sobre ti.
                    Esta acción no se puede deshacer.
                </p>
                <div class="flex gap-3">
                    <button class="btn-secondary" style="flex: 1;" onclick="Profile.closeConfirmDeleteModal()">
                        Cancelar
                    </button>
                    <button class="btn-primary btn-danger" style="flex: 1;" onclick="Profile.deleteHistory()">
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Consent Modal for Auto-Call Feature -->
    <div id="consent-modal" class="modal-overlay">
        <div class="modal-content" style="max-width: 28rem;">
            <div class="modal-body py-6">
                <div style="font-size: 3rem; margin-bottom: 1rem; text-align: center;">🚨</div>
                <h3
                    style="color: var(--text-primary); font-size: 1.125rem; font-weight: 600; margin-bottom: 0.5rem; text-align: center;">
                    Consentimiento para Ayuda de Emergencia
                </h3>
                <p style="color: var(--text-secondary); font-size: 0.9375rem; margin-bottom: 1rem;">
                    Al activar esta opción, autorizas a Mentta a:
                </p>
                <ul
                    style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 1rem; padding-left: 1.5rem;">
                    <li>Mostrar un botón de llamada rápida al 113 o 106 cuando detecte que estás en peligro</li>
                    <li>Registrar esta preferencia para futuras sesiones</li>
                </ul>
                <p style="color: var(--text-tertiary); font-size: 0.75rem; margin-bottom: 1.5rem;">
                    Puedes desactivar esta opción en cualquier momento. Tu privacidad es importante para nosotros.
                </p>
                <div class="flex gap-3">
                    <button class="btn-secondary" style="flex: 1;" onclick="Profile.cancelConsent()">
                        No, gracias
                    </button>
                    <button class="btn-primary" style="flex: 1; background: #ef4444;" onclick="Profile.acceptConsent()">
                        Sí, acepto
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- HTML5-QR Code Library -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    <!-- Scanner Modal -->
    <div id="qr-scanner-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/90 backdrop-blur-md opacity-0 transition-opacity duration-300">
        <div class="relative w-full max-w-md mx-4 overflow-hidden rounded-[2rem] shadow-2xl bg-black">
            <!-- Close Button -->
            <button onclick="Profile.closeScanner()" class="absolute top-6 right-6 z-20 w-10 h-10 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white backdrop-blur-md transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            
            <!-- Header -->
            <div class="absolute top-0 left-0 right-0 z-10 p-6 text-center">
                <h3 class="text-white font-bold text-lg tracking-wide">Escanear QR</h3>
                <p class="text-white/60 text-xs mt-1">Coloca el código dentro del marco</p>
            </div>

            <!-- Scanner Area -->
            <div class="relative aspect-[3/4] bg-black">
                <div id="qr-reader" class="w-full h-full object-cover"></div>
                
                <!-- Overlay Guide -->
                <div class="absolute inset-0 z-10 flex items-center justify-center pointer-events-none">
                    <div class="w-64 h-64 border-2 border-white/30 rounded-3xl relative">
                        <!-- Corners -->
                        <div class="absolute top-[-2px] left-[-2px] w-8 h-8 border-t-4 border-l-4 border-blue-500 rounded-tl-3xl"></div>
                        <div class="absolute top-[-2px] right-[-2px] w-8 h-8 border-t-4 border-r-4 border-blue-500 rounded-tr-3xl"></div>
                        <div class="absolute bottom-[-2px] left-[-2px] w-8 h-8 border-b-4 border-l-4 border-blue-500 rounded-bl-3xl"></div>
                        <div class="absolute bottom-[-2px] right-[-2px] w-8 h-8 border-b-4 border-r-4 border-blue-500 rounded-br-3xl"></div>
                        
                        <!-- Scanning Animation -->
                        <div class="absolute inset-0 bg-blue-500/10 animate-pulse rounded-3xl"></div>
                        <div class="absolute top-1/2 left-4 right-4 h-0.5 bg-blue-400 shadow-[0_0_10px_rgba(59,130,246,0.8)] animate-scan"></div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="absolute bottom-0 left-0 right-0 z-10 p-6 text-center bg-gradient-to-t from-black/80 to-transparent">
                 <p id="scanner-status" class="text-white/80 text-sm font-medium animate-pulse">Buscando cámara...</p>
            </div>
        </div>
    </div>

    <style>
        @keyframes scan {
            0% { transform: translateY(-120px); opacity: 0; }
            50% { opacity: 1; }
            100% { transform: translateY(120px); opacity: 0; }
        }
        .animate-scan {
            animation: scan 2s cubic-bezier(0.4, 0, 0.2, 1) infinite;
        }
    </style>


    <!-- JavaScript -->
    <script src="assets/js/utils.js"></script>
    <script src="assets/js/theme.js"></script>
    <script src="assets/js/translations.js"></script>
    <script src="assets/js/profile.js"></script>

    <!-- Crisis Preferences Initialization -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Apply translations
            if (typeof i18n !== 'undefined') {
                i18n.applyTranslations();
            }
            
            // Load crisis preferences
            Profile.loadCrisisPreferences();
        });

        // Extend Profile object with crisis preference methods
        Profile.loadCrisisPreferences = async function () {
            try {
                const response = await fetch('api/patient/get-crisis-preferences.php');
                const data = await response.json();

                if (data.success) {
                    document.getElementById('notify-psychologist').checked = data.data.notify_psychologist;
                    document.getElementById('notify-contacts').checked = data.data.notify_emergency_contacts;
                    document.getElementById('auto-call-emergency').checked = data.data.auto_call_emergency_line;
                }
            } catch (error) {
                console.error('Error loading crisis preferences:', error);
            }
        };

        Profile.saveCrisisPreferences = async function () {
            const formData = new FormData();
            formData.append('notify_psychologist', document.getElementById('notify-psychologist').checked);
            formData.append('notify_emergency_contacts', document.getElementById('notify-contacts').checked);
            formData.append('auto_call_emergency_line', document.getElementById('auto-call-emergency').checked);

            try {
                const response = await fetch('api/patient/save-crisis-preferences.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    Utils.toast('Preferencias guardadas');
                } else {
                    Utils.toast('Error al guardar: ' + (data.error || 'Intenta de nuevo'));
                }
            } catch (error) {
                console.error('Error saving crisis preferences:', error);
                Utils.toast('Error de conexión');
            }
        };

        Profile.handleAutoCallChange = function (checkbox) {
            if (checkbox.checked) {
                // Show consent modal
                document.getElementById('consent-modal').classList.add('active');
            } else {
                Profile.saveCrisisPreferences();
            }
        };

        Profile.cancelConsent = function () {
            document.getElementById('auto-call-emergency').checked = false;
            document.getElementById('consent-modal').classList.remove('active');
        };

        Profile.acceptConsent = function () {
            document.getElementById('consent-modal').classList.remove('active');
            Profile.saveCrisisPreferences();
            Utils.toast('Ayuda de emergencia activada');
        };

        // Language change handler
        Profile.changeLanguage = async function (language) {
            try {
                const response = await fetch('api/user/set-language.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ language: language })
                });
                const data = await response.json();

                if (data.success) {
                    // Update localStorage for translations.js
                    localStorage.setItem('mentta_language', language);
                    
                    // Show success message in the new language
                    Utils.toast(language === 'es' ? 'Idioma actualizado a Español' : 'Language updated to English');
                    
                    // Reload page to apply translations
                    setTimeout(() => window.location.reload(), 500);
                } else {
                    Utils.toast('Error: ' + (data.error || 'Error al cambiar idioma'));
                    // Revert select
                    document.getElementById('language-select').value = language === 'es' ? 'en' : 'es';
                }
            } catch (error) {
                console.error('Error changing language:', error);
                Utils.toast('Error de conexión');
            }
        };
    </script>
</body>

</html>