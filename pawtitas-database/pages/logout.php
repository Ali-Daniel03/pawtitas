<?php
/**
 * PAWTITAS - Cerrar Sesión (Corregido)
 */

require_once '../includes/session.php';
require_once '../includes/functions.php';

// Destruir sesión
SessionManager::destroy();

// Redirigir al inicio
header("Location: ../index.php");
exit();
?>
