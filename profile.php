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
<html lang="es" data-theme="<?= htmlspecialchars($theme) ?>">

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
                        'mentta-primary': '#2d3a2d',
                        'mentta-secondary': '#cbaa8e',
                        'mentta-accent': '#8b9d8b',
                        'mentta-light': '#f5f5f0',
                        mentta: {
                            50: '#f0f2f0',
                            100: '#e8f0e8',
                            200: '#d1dbd1',
                            300: '#b4c2b4',
                            400: '#8b9d8b',
                            500: '#2d3a2d',
                            600: '#1e261e',
                            700: '#151a15',
                            800: '#0c0f0c',
                            900: '#000000'
                        }
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
            <h1 class="text-lg font-semibold" style="color: var(--text-primary);">Mi Cuenta</h1>
        </div>
    </header>

    <!-- Main Content -->
    <main class="pt-16 pb-8">
        <div class="max-w-2xl mx-auto px-4 py-6">

            <!-- Personal Info Section -->
            <section class="profile-section">
                <h2 class="profile-section-title">👤 Información Personal</h2>

                <form id="profile-form" onsubmit="Profile.updateProfile(event)">
                    <div class="form-group">
                        <label class="form-label" for="name">Nombre</label>
                        <input type="text" id="name" name="name" class="form-input"
                            value="<?= htmlspecialchars($user['name']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="email">Correo electrónico</label>
                        <input type="email" id="email" class="form-input"
                            value="<?= htmlspecialchars($user['email']) ?>" disabled>
                        <p style="font-size: 0.75rem; color: var(--text-tertiary); margin-top: 0.25rem;">
                            El correo no puede ser modificado
                        </p>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="age">Edad</label>
                        <input type="number" id="age" name="age" class="form-input" value="<?= $user['age'] ?? '' ?>"
                            min="13" max="120" placeholder="Opcional">
                    </div>

                    <button type="submit" class="btn-primary">
                        Guardar cambios
                    </button>
                </form>
            </section>

            <!-- Change Password Section -->
            <section class="profile-section">
                <h2 class="profile-section-title">🔐 Cambiar Contraseña</h2>

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
                    <p style="color: var(--text-tertiary); font-size: 0.875rem; text-align: center; padding: 1rem;">
                        No tienes un psicólogo vinculado
                    </p>
                    <button class="btn-secondary" style="width: 100%;" disabled>
                        🔍 Buscar profesional (Próximamente)
                    </button>
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
                    style="border: 1px solid var(--danger); border-radius: 0.5rem; padding: 0.75rem; margin-top: 0.5rem;">
                    <div class="preference-info">
                        <h4 style="color: var(--danger);">🚨 Ayuda automática de emergencia</h4>
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
                <h2 class="profile-section-title">⚙️ Preferencias</h2>

                <!-- Theme Toggle -->
                <div class="preference-item">
                    <div class="preference-info">
                        <h4>🌙 Modo Oscuro</h4>
                        <p>Reduce la fatiga visual en ambientes con poca luz</p>
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
                        <h4>🌐 Idioma / Language</h4>
                        <p>Elige tu idioma preferido para la interfaz</p>
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
                        <h4>⏸️ Pausar Análisis Emocional</h4>
                        <p>Desactiva temporalmente el análisis de emociones y alertas automáticas por 24 horas</p>

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
                <h2 class="profile-section-title">🔒 Privacidad</h2>

                <button class="btn-secondary btn-danger" style="width: 100%;" onclick="Profile.confirmDeleteHistory()">
                    🗑️ Eliminar historial de conversaciones
                </button>
                <p style="font-size: 0.75rem; color: var(--text-tertiary); margin-top: 0.5rem; text-align: center;">
                    Esta acción no se puede deshacer
                </p>
            </section>

            <!-- Logout Section -->
            <section class="profile-section" style="border-color: transparent; background: transparent; padding: 0;">
                <button class="btn-primary" style="background-color: #000;" onclick="Profile.logout()">
                    🚪 Cerrar Sesión
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

    <!-- JavaScript -->
    <script src="assets/js/utils.js"></script>
    <script src="assets/js/theme.js"></script>
    <script src="assets/js/profile.js"></script>

    <!-- Crisis Preferences Initialization -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
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