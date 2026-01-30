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
} catch (Exception $e) {}

// Obtener contactos de emergencia
$emergencyContacts = [];
try {
    $stmt = $db->prepare("SELECT * FROM emergency_contacts WHERE patient_id = ? ORDER BY priority ASC");
    $stmt->execute([$user['id']]);
    $emergencyContacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

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
} catch (Exception $e) {}
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
                        mentta: {
                            50: '#EEF2FF',
                            100: '#E0E7FF',
                            200: '#C7D2FE',
                            300: '#A5B4FC',
                            400: '#818CF8',
                            500: '#6366F1',
                            600: '#4F46E5',
                            700: '#4338CA',
                            800: '#3730A3',
                            900: '#312E81'
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/theme.css">
    
    <style>
        .profile-section {
            background-color: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 0.75rem;
            padding: 1.25rem;
            margin-bottom: 1rem;
        }
        
        .profile-section-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-secondary);
            margin-bottom: 0.375rem;
        }
        
        .form-input {
            width: 100%;
            padding: 0.625rem 0.875rem;
            background-color: var(--bg-input);
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            color: var(--text-primary);
            font-size: 0.9375rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--accent-primary);
            box-shadow: 0 0 0 3px var(--accent-light);
        }
        
        .form-input:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            background: linear-gradient(135deg, #6366F1 0%, #8B5CF6 100%);
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-size: 0.9375rem;
            font-weight: 500;
            cursor: pointer;
            transition: box-shadow 0.2s ease, transform 0.1s ease;
        }
        
        .btn-primary:hover {
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
        }
        
        .btn-primary:active {
            transform: scale(0.98);
        }
        
        .btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            background-color: var(--bg-tertiary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            font-size: 0.9375rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }
        
        .btn-secondary:hover {
            background-color: var(--border-color);
        }
        
        .btn-danger {
            background-color: var(--danger);
            color: white;
            border: none;
        }
        
        .btn-danger:hover {
            background-color: #DC2626;
        }
        
        .contact-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem;
            background-color: var(--bg-tertiary);
            border-radius: 0.5rem;
            margin-bottom: 0.5rem;
        }
        
        .contact-info {
            flex: 1;
        }
        
        .contact-name {
            font-weight: 500;
            color: var(--text-primary);
        }
        
        .contact-details {
            font-size: 0.8125rem;
            color: var(--text-secondary);
        }
        
        .preference-item {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .preference-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
        
        .preference-info h4 {
            font-weight: 500;
            color: var(--text-primary);
            margin: 0 0 0.25rem 0;
        }
        
        .preference-info p {
            font-size: 0.8125rem;
            color: var(--text-secondary);
            margin: 0;
        }
        
        .analysis-warning {
            background-color: var(--warning-light);
            border-left: 4px solid var(--warning);
            padding: 0.75rem;
            border-radius: 0 0.5rem 0.5rem 0;
            margin-top: 0.5rem;
        }
        
        .analysis-warning p {
            color: var(--warning);
            font-size: 0.8125rem;
            margin: 0;
        }
    </style>
</head>
<body class="antialiased" style="background-color: var(--bg-primary);">
    
    <!-- Header -->
    <header class="fixed top-0 left-0 right-0 z-50" style="background-color: var(--bg-secondary); border-bottom: 1px solid var(--border-color);">
        <div class="max-w-2xl mx-auto px-4 py-3 flex items-center gap-3">
            <a href="chat.php" class="p-2 rounded-lg transition-colors" style="color: var(--text-secondary);" title="Volver al chat">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                        <input type="number" id="age" name="age" class="form-input" 
                               value="<?= $user['age'] ?? '' ?>" min="13" max="120" placeholder="Opcional">
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
                        <input type="password" id="current_password" name="current_password" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="new_password">Nueva contraseña</label>
                        <input type="password" id="new_password" name="new_password" class="form-input" 
                               minlength="8" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="confirm_password">Confirmar contraseña</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-input" required>
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
                
                <button class="btn-secondary" style="width: 100%; margin-top: 0.75rem;" onclick="Profile.showAddContactModal()">
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
                        <input type="checkbox" id="theme-toggle" 
                               <?= $theme === 'dark' ? 'checked' : '' ?> 
                               onchange="toggleTheme()">
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                
                <!-- Pause Analysis Toggle -->
                <div class="preference-item">
                    <div class="preference-info">
                        <h4>⏸️ Pausar Análisis Emocional</h4>
                        <p>Desactiva temporalmente el análisis de emociones y alertas automáticas por 24 horas</p>
                        
                        <?php if ($analysisPaused): ?>
                            <div class="analysis-warning">
                                <p>⏸️ Análisis pausado hasta: <strong><?= date('d M, H:i', strtotime($pausedUntil)) ?></strong></p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" id="analysis-toggle" 
                               <?= $analysisPaused ? 'checked' : '' ?> 
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
            <section class="profile-section" style="border-color: transparent; background: transparent;">
                <button class="btn-secondary" style="width: 100%;" onclick="Profile.logout()">
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
                    <button type="button" class="btn-secondary" onclick="Profile.closeAddContactModal()">Cancelar</button>
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

    <!-- JavaScript -->
    <script src="assets/js/utils.js"></script>
    <script src="assets/js/theme.js"></script>
    <script src="assets/js/profile.js"></script>
</body>
</html>
