<?php
/**
 * PAWTITAS - Dashboard Principal (Limpio)
 * Redirige al usuario a su panel correspondiente
 */

require_once '../config/config.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

SessionManager::start();

// Verificar que el usuario esté logueado
if (!usuario_logueado()) {
    header('Location: login.php');
    exit();
}

// Obtener información del usuario actual
$usuario = obtener_usuario_actual();

// Verificar que se obtuvo la información correctamente
if (!$usuario) {
    SessionManager::destroy();
    header('Location: login.php');
    exit();
}

// Redirigir según el tipo de usuario (solo admin o adoptante)
if ($usuario['tipo'] === 'adoptante') {
    header('Location: panel_adoptante.php');
    exit();
} elseif ($usuario['tipo'] === 'admin') {
    header('Location: panel_admin.php');
    exit();
} else {
    // Tipo de usuario no válido
    guardar_mensaje('Tipo de usuario no válido', 'error');
    header('Location: login.php');
    exit();
}
?>
