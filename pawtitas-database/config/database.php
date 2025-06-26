<?php
/**
 * PAWTITAS - Conexión Simple a Base de Datos (Corregida)
 * 
 * Este archivo maneja la conexión a la base de datos de forma sencilla
 * Usamos PDO que es más seguro que mysqli
 * 
 * @author Estudiantes ESCOM 4CV4
 */

// Incluir la configuración
require_once 'config.php';

/**
 * Clase simple para manejar la conexión a la base de datos
 * Usamos el patrón Singleton (una sola instancia) para no crear múltiples conexiones
 */
class Database {
    // Variable estática para guardar la única instancia
    private static $instancia = null;
    private $conexion;
    
    /**
     * Constructor privado - solo se puede crear desde dentro de la clase
     */
    private function __construct() {
        try {
            // Crear la cadena de conexión (DSN)
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            
            // Opciones para PDO (configuración de seguridad)
            $opciones = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Mostrar errores
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Devolver arrays asociativos
                PDO::ATTR_EMULATE_PREPARES   => false,                  // Usar prepared statements reales
            ];
            
            // Crear la conexión PDO
            $this->conexion = new PDO($dsn, DB_USER, DB_PASS, $opciones);
            
        } catch(PDOException $e) {
            // Si hay error, mostrar mensaje según el modo debug
            if (DEBUG_MODE) {
                die("❌ Error de conexión: " . $e->getMessage());
            } else {
                die("❌ Error de conexión a la base de datos");
            }
        }
    }
    
    /**
     * Método para obtener la única instancia de la clase
     * Si no existe, la crea. Si ya existe, la devuelve.
     */
    public static function getInstance() {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }
    
    /**
     * Método alternativo para compatibilidad
     */
    public static function obtenerInstancia() {
        return self::getInstance();
    }
    
    /**
     * Método para obtener la conexión PDO
     */
    public function getConnection() {
        return $this->conexion;
    }
    
    /**
     * Método alternativo para compatibilidad
     */
    public function obtenerConexion() {
        return $this->getConnection();
    }
    
    // Evitar que se pueda clonar la instancia
    private function __clone() {}
    
    // Evitar que se pueda deserializar
    public function __wakeup() {
        throw new Exception("No se puede deserializar un Singleton");
    }
}

/**
 * Función helper simple para obtener la conexión
 * Esto hace más fácil usar la base de datos en otros archivos
 */
function obtener_conexion_db() {
    $database = Database::getInstance();
    return $database->getConnection();
}
?>
