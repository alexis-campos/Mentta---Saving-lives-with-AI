<?php
/**
 * MENTTA - Logout
 * Cierra la sesión y redirige al inicio
 */

require_once 'includes/auth.php';

logout();

header('Location: index.php');
exit;
