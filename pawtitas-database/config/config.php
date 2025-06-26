<?php
/**
 * PAWTITAS - Configuración Principal (Limpia)
 */

// Configuración de la aplicación
define('APP_NAME', 'PAWTITAS');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/pawtitas-database');
define('APP_TIMEZONE', 'America/Mexico_City');

// Configuración de base de datos
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'pawtitas_db');
define('DB_USER', 'root');
define('DB_PASS', 'ivan9808');
define('DB_CHARSET', 'utf8mb4');

// Configuración de sesiones
define('SESSION_LIFETIME', 3600);
define('SESSION_NAME', 'pawtitas_session');

// Configuración de archivos
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Configuración de seguridad
define('HASH_ALGO', PASSWORD_DEFAULT);
define('CSRF_TOKEN_LENGTH', 32);

// Configuración de paginación
define('ITEMS_PER_PAGE', 12);

// Establecer zona horaria
date_default_timezone_set(APP_TIMEZONE);

// Configuración de errores
if ($_SERVER['SERVER_NAME'] === 'localhost') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    define('DEBUG_MODE', true);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    define('DEBUG_MODE', false);
}

// Crear carpeta uploads si no existe
$upload_dir = __DIR__ . '/../uploads/dogs/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Iniciar sesión
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
