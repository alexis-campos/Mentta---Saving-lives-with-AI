<?php
/**
 * MENTTA - Sistema de Autenticación
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

/**
 * Inicializa sesión de forma segura
 */
function initSession()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path' => '/',
            'domain' => '',
            'secure' => APP_ENV === 'production',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        session_start();
    }
}

/**
 * Inicio de sesión
 * @return array|false Datos del usuario o false si falla
 */
function login($email, $password)
{
    $email = sanitizeInput($email);

    if (!validateEmail($email)) {
        return false;
    }

    $sql = "SELECT id, email, password_hash, name, age, role, language, is_active 
            FROM users WHERE email = ? AND is_active = 1";

    $user = dbFetchOne($sql, [$email]);

    if (!$user || !password_verify($password, $user['password_hash'])) {
        logError('Login failed', ['email' => $email]);
        return false;
    }

    // Regenerar session ID por seguridad
    initSession();
    session_regenerate_id(true);

    // Guardar datos en sesión
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['language'] = $user['language'];
    $_SESSION['login_time'] = time();

    // Actualizar última actividad
    dbQuery("UPDATE users SET updated_at = NOW() WHERE id = ?", [$user['id']]);

    unset($user['password_hash']);
    return $user;
}

/**
 * Registro de nuevo usuario
 * @return int|false ID del usuario o false si falla
 */
function register($email, $password, $name, $age = null, $role = 'patient', $language = 'es')
{
    $email = sanitizeInput($email);
    $name = sanitizeInput($name);

    // Validaciones
    if (!validateEmail($email)) {
        return ['error' => 'Email inválido'];
    }

    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        return ['error' => 'La contraseña debe tener al menos ' . PASSWORD_MIN_LENGTH . ' caracteres'];
    }

    if (empty($name)) {
        return ['error' => 'El nombre es requerido'];
    }

    // Verificar email único
    $exists = dbFetchOne("SELECT id FROM users WHERE email = ?", [$email]);
    if ($exists) {
        return ['error' => 'El email ya está registrado'];
    }

    // Crear usuario
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    $userId = dbInsert('users', [
        'email' => $email,
        'password_hash' => $passwordHash,
        'name' => $name,
        'age' => $age,
        'role' => $role,
        'language' => $language
    ]);

    if (!$userId) {
        return ['error' => 'Error al crear el usuario'];
    }

    return $userId;
}

/**
 * Cierra la sesión
 */
function logout()
{
    initSession();

    // DEV-017: Invalidar sesión en BD
    $userId = $_SESSION['user_id'] ?? null;
    if ($userId) {
        try {
            require_once __DIR__ . '/db.php';
            dbQuery("DELETE FROM sessions WHERE user_id = ?", [$userId]);
        } catch (Exception $e) {
            // Silent fail - la tabla sessions podría no existir
        }
    }

    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    session_destroy();
    return true;
}

/**
 * Verifica si el usuario está autenticado
 * @return array|false Datos del usuario o false
 */
function checkAuth()
{
    initSession();

    if (empty($_SESSION['user_id'])) {
        return false;
    }

    // Verificar expiración de sesión
    if (
        isset($_SESSION['login_time']) &&
        (time() - $_SESSION['login_time']) > SESSION_LIFETIME
    ) {
        logout();
        return false;
    }

    return [
        'id' => $_SESSION['user_id'],
        'email' => $_SESSION['email'],
        'name' => $_SESSION['name'],
        'role' => $_SESSION['role'],
        'language' => $_SESSION['language'] ?? DEFAULT_LANGUAGE
    ];
}

/**
 * Fuerza autenticación o redirige a login
 */
function requireAuth($requiredRole = null)
{
    $user = checkAuth();

    if (!$user) {
        if (isAjaxRequest()) {
            jsonResponse(false, null, 'No autenticado', 401);
        }
        header('Location: ' . APP_URL . '/login.php');
        exit;
    }

    // DEV-006: Regenerar sesión en verificación de roles críticos
    if ($requiredRole !== null) {
        if ($user['role'] !== $requiredRole) {
            if (isAjaxRequest()) {
                jsonResponse(false, null, 'No autorizado', 403);
            }
            header('Location: ' . APP_URL . '/login.php');
            exit;
        }
        // Regenerar ID de sesión para roles privilegiados
        if ($requiredRole === 'psychologist' && !isset($_SESSION['role_verified'])) {
            session_regenerate_id(true);
            $_SESSION['role_verified'] = true;
        }
    }

    return $user;
}

/**
 * Verifica si hay sesión activa
 */
function isLoggedIn()
{
    return checkAuth() !== false;
}

/**
 * Obtiene datos del usuario actual
 */
function getCurrentUser()
{
    $auth = checkAuth();
    if (!$auth)
        return null;

    return dbFetchOne(
        "SELECT id, email, name, age, role, language, created_at 
         FROM users WHERE id = ?",
        [$auth['id']]
    );
}

/**
 * Obtiene el ID del usuario actual
 */
function getCurrentUserId()
{
    initSession();
    return $_SESSION['user_id'] ?? null;
}

/**
 * Obtiene el rol del usuario actual
 */
function getCurrentUserRole()
{
    initSession();
    return $_SESSION['role'] ?? null;
}

/**
 * Cambia el idioma del usuario
 */
function setUserLanguage($language)
{
    if (!in_array($language, SUPPORTED_LANGUAGES)) {
        return false;
    }

    initSession();
    $_SESSION['language'] = $language;

    $userId = getCurrentUserId();
    if ($userId) {
        dbQuery("UPDATE users SET language = ? WHERE id = ?", [$language, $userId]);
    }

    return true;
}

/**
 * Verifica si el usuario es psicólogo
 */
function isPsychologist()
{
    return getCurrentUserRole() === 'psychologist';
}

/**
 * Verifica si el usuario es paciente
 */
function isPatient()
{
    return getCurrentUserRole() === 'patient';
}
