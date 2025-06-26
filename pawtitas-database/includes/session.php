<?php
/**
 * PAWTITAS - Manejo de Sesiones
 */

require_once __DIR__ . '/../config/config.php';

class SessionManager {
    
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
            ini_set('session.use_strict_mode', 1);
            ini_set('session.cookie_samesite', 'Strict');
            
            session_name(SESSION_NAME);
            session_start();
            
            if (!isset($_SESSION['created'])) {
                $_SESSION['created'] = time();
            } else if (time() - $_SESSION['created'] > 1800) {
                session_regenerate_id(true);
                $_SESSION['created'] = time();
            }
        }
    }
    
    public static function set($key, $value) {
        self::start();
        $_SESSION[$key] = $value;
    }
    
    public static function get($key, $default = null) {
        self::start();
        return $_SESSION[$key] ?? $default;
    }
    
    public static function has($key) {
        self::start();
        return isset($_SESSION[$key]);
    }
    
    public static function remove($key) {
        self::start();
        unset($_SESSION[$key]);
    }
    
    public static function destroy() {
        self::start();
        session_unset();
        session_destroy();
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
    }
    
    public static function isLoggedIn() {
        return self::has('user_id') && self::has('logged_in') && self::get('logged_in') === true;
    }
    
    public static function getUserId() {
        return self::get('user_id');
    }
    
    public static function getUserType() {
        return self::get('user_type');
    }
    
    public static function getUserName() {
        return self::get('user_name');
    }
    
    public static function generateCSRFToken() {
        if (!self::has('csrf_token')) {
            self::set('csrf_token', bin2hex(random_bytes(CSRF_TOKEN_LENGTH)));
        }
        return self::get('csrf_token');
    }
    
    public static function validateCSRFToken($token) {
        return hash_equals(self::get('csrf_token', ''), $token);
    }
}
?>
