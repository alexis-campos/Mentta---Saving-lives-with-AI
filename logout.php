<?php
/**
 * MENTTA - Logout
 * Cierra la sesión del usuario
 */

require_once 'includes/auth.php';

logout();

header('Location: login.php');
exit;
