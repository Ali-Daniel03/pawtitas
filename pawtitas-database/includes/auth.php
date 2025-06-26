<?php
/**
 * PAWTITAS - Sistema de Autenticación (Limpio - Solo Admin/Adoptante)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/functions.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = obtener_conexion_db();
        SessionManager::start();
    }
    
    /**
     * Registrar un nuevo usuario (solo adoptantes)
     */
    public function registrar_usuario($datos) {
        try {
            // Validar los datos
            $validacion = $this->validar_datos_registro($datos);
            if (!$validacion['valido']) {
                return ['exito' => false, 'mensaje' => $validacion['mensaje']];
            }
            
            // Verificar que el email no exista
            if ($this->email_ya_existe($datos['email'])) {
                return ['exito' => false, 'mensaje' => 'Este email ya está registrado'];
            }
            
            // Solo permitir registro de adoptantes (admin se crea manualmente)
            if ($datos['tipo_usuario'] !== 'adoptante') {
                return ['exito' => false, 'mensaje' => 'Solo se permite registro de adoptantes'];
            }
            
            // Encriptar la contraseña
            $password_encriptado = password_hash($datos['password'], PASSWORD_DEFAULT);
            
            // Preparar los datos para insertar
            $datos_limpios = $this->preparar_datos_usuario($datos, $password_encriptado);
            
            // Insertar en la base de datos
            $sql = "INSERT INTO usuarios (
                        nombre, apellidos, email, tipo_usuario, password, telefono, 
                        fecha_nacimiento, direccion, ciudad, estado, codigo_postal, 
                        ocupacion, estado_civil, tiene_discapacidad, tipo_discapacidad, 
                        fecha_registro
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $parametros = [
                $datos_limpios['nombre'],
                $datos_limpios['apellidos'],
                $datos_limpios['email'],
                'adoptante', // Forzar adoptante
                $datos_limpios['password'],
                $datos_limpios['telefono'],
                $datos_limpios['fecha_nacimiento'],
                $datos_limpios['direccion'],
                $datos_limpios['ciudad'],
                $datos_limpios['estado'],
                $datos_limpios['codigo_postal'],
                $datos_limpios['ocupacion'],
                $datos_limpios['estado_civil'],
                $datos_limpios['tiene_discapacidad'],
                $datos_limpios['tipo_discapacidad']
            ];
            
            if (ejecutar_db($sql, $parametros)) {
                $user_id = ultimo_id_insertado();
                registrar_actividad($user_id, 'user_registered', 'Nuevo adoptante registrado');
                return ['exito' => true, 'mensaje' => 'Usuario registrado exitosamente', 'user_id' => $user_id];
            } else {
                return ['exito' => false, 'mensaje' => 'Error al registrar usuario'];
            }
            
        } catch (Exception $e) {
            if (DEBUG_MODE) {
                return ['exito' => false, 'mensaje' => 'Error: ' . $e->getMessage()];
            }
            return ['exito' => false, 'mensaje' => 'Error interno del servidor'];
        }
    }
    
    /**
     * Iniciar sesión de usuario
     */
    public function iniciar_sesion($email, $password) {
        try {
            $email = strtolower(trim($email));
            
            $sql = "SELECT id, nombre, apellidos, email, tipo_usuario, password, activo
                   FROM usuarios WHERE email = ? AND activo = 1";
            
            $usuarios = consultar_db($sql, [$email]);
            
            if (!$usuarios || count($usuarios) === 0) {
                return ['exito' => false, 'mensaje' => 'Usuario no encontrado o inactivo'];
            }
            
            $usuario = $usuarios[0];
            
            if (password_verify($password, $usuario['password'])) {
                $this->actualizar_ultimo_login($usuario['id']);
                
                // Guardar datos en la sesión
                SessionManager::set('user_id', $usuario['id']);
                SessionManager::set('user_name', $usuario['nombre']);
                SessionManager::set('user_full_name', $usuario['nombre'] . ' ' . $usuario['apellidos']);
                SessionManager::set('user_email', $usuario['email']);
                SessionManager::set('user_type', $usuario['tipo_usuario']);
                SessionManager::set('logged_in', true);
                SessionManager::set('login_time', time());
                
                registrar_actividad($usuario['id'], 'user_login', 'Usuario inició sesión');
                
                return [
                    'exito' => true, 
                    'tipo_usuario' => $usuario['tipo_usuario'],
                    'nombre_usuario' => $usuario['nombre']
                ];
            } else {
                return ['exito' => false, 'mensaje' => 'Contraseña incorrecta'];
            }
            
        } catch (Exception $e) {
            if (DEBUG_MODE) {
                return ['exito' => false, 'mensaje' => 'Error: ' . $e->getMessage()];
            }
            return ['exito' => false, 'mensaje' => 'Error interno del servidor'];
        }
    }
    
    /**
     * Cerrar sesión
     */
    public function cerrar_sesion() {
        $user_id = SessionManager::getUserId();
        if ($user_id) {
            registrar_actividad($user_id, 'user_logout', 'Usuario cerró sesión');
        }
        
        SessionManager::destroy();
        return true;
    }
    
    /**
     * Verificar si hay un usuario logueado
     */
    public function esta_logueado() {
        return SessionManager::isLoggedIn();
    }
    
    /**
     * Obtener información del usuario actual
     */
    public function obtener_usuario_actual() {
        if (!$this->esta_logueado()) {
            return null;
        }
        
        return [
            'id' => SessionManager::getUserId(),
            'nombre' => SessionManager::getUserName(),
            'email' => SessionManager::get('user_email'),
            'tipo' => SessionManager::getUserType(),
            'nombre_completo' => SessionManager::get('user_full_name')
        ];
    }
    
    // ========================================================================
    // MÉTODOS PRIVADOS
    // ========================================================================
    
    private function validar_datos_registro($datos) {
        if (empty($datos['nombre']) || empty($datos['apellidos']) || 
            empty($datos['email']) || empty($datos['password'])) {
            return ['valido' => false, 'mensaje' => 'Todos los campos obligatorios deben ser completados'];
        }
        
        if (!es_email_valido($datos['email'])) {
            return ['valido' => false, 'mensaje' => 'Email no válido'];
        }
        
        if (!es_password_seguro($datos['password'])) {
            return ['valido' => false, 'mensaje' => 'La contraseña debe tener al menos 6 caracteres'];
        }
        
        if (!empty($datos['telefono']) && !es_telefono_valido($datos['telefono'])) {
            return ['valido' => false, 'mensaje' => 'Teléfono no válido (debe tener 10 dígitos)'];
        }
        
        return ['valido' => true];
    }
    
    private function email_ya_existe($email) {
        $sql = "SELECT COUNT(*) as total FROM usuarios WHERE email = ?";
        $resultado = consultar_db($sql, [strtolower(trim($email))]);
        return $resultado && $resultado[0]['total'] > 0;
    }
    
    private function preparar_datos_usuario($datos, $password_encriptado) {
        return [
            'nombre' => limpiar_datos($datos['nombre']),
            'apellidos' => limpiar_datos($datos['apellidos']),
            'email' => strtolower(trim($datos['email'])),
            'password' => $password_encriptado,
            'telefono' => limpiar_datos($datos['telefono'] ?? ''),
            'fecha_nacimiento' => !empty($datos['fecha_nacimiento']) ? $datos['fecha_nacimiento'] : null,
            'direccion' => limpiar_datos($datos['direccion'] ?? ''),
            'ciudad' => limpiar_datos($datos['ciudad'] ?? ''),
            'estado' => limpiar_datos($datos['estado'] ?? ''),
            'codigo_postal' => limpiar_datos($datos['codigo_postal'] ?? ''),
            'ocupacion' => limpiar_datos($datos['ocupacion'] ?? ''),
            'estado_civil' => $datos['estado_civil'] ?? 'soltero',
            'tiene_discapacidad' => isset($datos['tiene_discapacidad']) ? 1 : 0,
            'tipo_discapacidad' => isset($datos['tiene_discapacidad']) ? 
                                  limpiar_datos($datos['tipo_discapacidad'] ?? '') : null
        ];
    }
    
    private function actualizar_ultimo_login($user_id) {
        $sql = "UPDATE usuarios SET fecha_actualizacion = NOW() WHERE id = ?";
        ejecutar_db($sql, [$user_id]);
    }
}

// Funciones helper
function verificar_login() {
    $auth = new Auth();
    return $auth->esta_logueado();
}

function obtener_usuario_logueado() {
    $auth = new Auth();
    return $auth->obtener_usuario_actual();
}
?>
