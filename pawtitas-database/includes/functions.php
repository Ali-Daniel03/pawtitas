<?php
/**
 * PAWTITAS - Funciones Auxiliares (Limpio - Sin Currículum)
 */

// ============================================================================
// FUNCIONES DE SEGURIDAD Y VALIDACIÓN
// ============================================================================

function limpiar_datos($datos) {
    if (is_array($datos)) {
        $resultado = [];
        foreach ($datos as $clave => $valor) {
            $resultado[$clave] = limpiar_datos($valor);
        }
        return $resultado;
    }
    
    $datos = trim($datos);
    $datos = stripslashes($datos);
    $datos = htmlspecialchars($datos, ENT_QUOTES, 'UTF-8');
    return $datos;
}

function es_email_valido($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function es_telefono_valido($telefono) {
    $solo_numeros = preg_replace('/[^0-9]/', '', $telefono);
    return strlen($solo_numeros) === 10;
}

function es_password_seguro($password) {
    return strlen($password) >= 6;
}

// ============================================================================
// FUNCIONES DE SESIÓN Y USUARIOS
// ============================================================================

function usuario_logueado() {
    return isset($_SESSION['user_id']) && 
           isset($_SESSION['logged_in']) && 
           $_SESSION['logged_in'] === true;
}

function obtener_usuario_actual() {
    if (!usuario_logueado()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'nombre' => $_SESSION['user_name'] ?? '',
        'email' => $_SESSION['user_email'] ?? '',
        'tipo' => $_SESSION['user_type'] ?? '',
        'nombre_completo' => $_SESSION['user_full_name'] ?? ''
    ];
}

function requerir_login($url_redireccion = 'login.php') {
    if (!usuario_logueado()) {
        header("Location: $url_redireccion");
        exit();
    }
}

function requerir_tipo_usuario($tipo_requerido, $url_redireccion = 'index.php') {
    requerir_login();
    
    $usuario = obtener_usuario_actual();
    if ($usuario['tipo'] !== $tipo_requerido) {
        header("Location: $url_redireccion");
        exit();
    }
}

function es_admin($user_id = null) {
    if ($user_id === null) {
        $usuario = obtener_usuario_actual();
        return $usuario && $usuario['tipo'] === 'admin';
    }
    
    try {
        $sql = "SELECT tipo_usuario FROM usuarios WHERE id = ?";
        $resultado = consultar_db($sql, [$user_id]);
        return $resultado && $resultado[0]['tipo_usuario'] === 'admin';
    } catch (Exception $e) {
        return false;
    }
}

function requerir_admin($url_redireccion = 'panel_adoptante.php') {
    requerir_login();
    
    if (!es_admin()) {
        header("Location: $url_redireccion");
        exit();
    }
}

// ============================================================================
// FUNCIONES DE MENSAJES
// ============================================================================

function guardar_mensaje($mensaje, $tipo = 'info') {
    $_SESSION['mensaje_flash'] = $mensaje;
    $_SESSION['tipo_mensaje_flash'] = $tipo;
}

function obtener_mensaje() {
    if (isset($_SESSION['mensaje_flash'])) {
        $mensaje = [
            'texto' => $_SESSION['mensaje_flash'],
            'tipo' => $_SESSION['tipo_mensaje_flash'] ?? 'info'
        ];
        unset($_SESSION['mensaje_flash'], $_SESSION['tipo_mensaje_flash']);
        return $mensaje;
    }
    return null;
}

function redirigir_con_mensaje($url, $mensaje, $tipo = 'info') {
    guardar_mensaje($mensaje, $tipo);
    header("Location: $url");
    exit();
}

// ============================================================================
// FUNCIONES DE BASE DE DATOS
// ============================================================================

function consultar_db($sql, $parametros = []) {
    try {
        $db = obtener_conexion_db();
        $stmt = $db->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        if (DEBUG_MODE) {
            die("Error en consulta: " . $e->getMessage());
        }
        return false;
    }
}

function ejecutar_db($sql, $parametros = []) {
    try {
        $db = obtener_conexion_db();
        $stmt = $db->prepare($sql);
        return $stmt->execute($parametros);
    } catch (Exception $e) {
        if (DEBUG_MODE) {
            die("Error en ejecución: " . $e->getMessage());
        }
        return false;
    }
}

function ultimo_id_insertado() {
    $db = obtener_conexion_db();
    return $db->lastInsertId();
}

// ============================================================================
// FUNCIONES DE UTILIDAD
// ============================================================================

function escapar_html($texto) {
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}

function cortar_texto($texto, $longitud = 100, $sufijo = '...') {
    if (strlen($texto) <= $longitud) {
        return $texto;
    }
    return substr($texto, 0, $longitud) . $sufijo;
}

function formatear_fecha($fecha, $formato = 'd/m/Y') {
    if (empty($fecha)) {
        return '';
    }
    
    try {
        $fecha_obj = new DateTime($fecha);
        return $fecha_obj->format($formato);
    } catch (Exception $e) {
        return $fecha;
    }
}

function registrar_actividad($user_id, $accion, $descripcion = '') {
    // Función simplificada - solo log básico
    error_log("PAWTITAS Activity - User: $user_id, Action: $accion, Description: $descripcion");
}

// ============================================================================
// FUNCIONES ESPECÍFICAS DEL SISTEMA
// ============================================================================

function obtener_estadisticas_sistema() {
    $stats = [];
    
    try {
        // Total perritos
        $sql = "SELECT COUNT(*) as total FROM perritos";
        $resultado = consultar_db($sql);
        $stats['total_perritos'] = $resultado ? $resultado[0]['total'] : 0;
        
        // Disponibles
        $sql = "SELECT COUNT(*) as total FROM perritos WHERE disponible = 1";
        $resultado = consultar_db($sql);
        $stats['disponibles'] = $resultado ? $resultado[0]['total'] : 0;
        
        // Adoptantes
        $sql = "SELECT COUNT(*) as total FROM usuarios WHERE tipo_usuario = 'adoptante' AND activo = 1";
        $resultado = consultar_db($sql);
        $stats['adoptantes'] = $resultado ? $resultado[0]['total'] : 0;
        
        // Solicitudes pendientes
        $sql = "SELECT COUNT(*) as total FROM solicitudes_adopcion WHERE estado = 'pendiente'";
        $resultado = consultar_db($sql);
        $stats['solicitudes_pendientes'] = $resultado ? $resultado[0]['total'] : 0;
        
        // Adopciones exitosas
        $sql = "SELECT COUNT(*) as total FROM solicitudes_adopcion WHERE estado = 'completada'";
        $resultado = consultar_db($sql);
        $stats['adopciones_exitosas'] = $resultado ? $resultado[0]['total'] : 0;
        
    } catch (Exception $e) {
        error_log("Error obteniendo estadísticas: " . $e->getMessage());
    }
    
    return $stats;
}

function procesar_imagen_subida($archivo, $carpeta_destino = 'uploads/dogs/', $tamaño_maximo = 5242880) {
    if (!isset($archivo['error']) || $archivo['error'] !== UPLOAD_ERR_OK) {
        return ['exito' => false, 'mensaje' => 'Error al subir el archivo'];
    }
    
    if ($archivo['size'] > $tamaño_maximo) {
        return ['exito' => false, 'mensaje' => 'El archivo es demasiado grande (máximo 5MB)'];
    }
    
    $tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($archivo['type'], $tipos_permitidos)) {
        return ['exito' => false, 'mensaje' => 'Solo se permiten imágenes (JPG, PNG, GIF, WEBP)'];
    }
    
    $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
    $nombre_archivo = uniqid() . '.' . $extension;
    $ruta_completa = $carpeta_destino . $nombre_archivo;
    
    if (!is_dir($carpeta_destino)) {
        mkdir($carpeta_destino, 0755, true);
    }
    
    if (move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
        return [
            'exito' => true, 
            'nombre_archivo' => $nombre_archivo, 
            'ruta_completa' => $ruta_completa
        ];
    } else {
        return ['exito' => false, 'mensaje' => 'Error al guardar el archivo'];
    }
}

/**
 * Verificar si un usuario tiene currículum emocional completo
 */
function usuario_tiene_curriculum($usuario_id) {
    // Esta función se mantiene para compatibilidad, pero ahora usamos el perfil de matching
    return usuario_tiene_perfil_matching($usuario_id);
}

/**
 * Verificar si un usuario tiene perfil de matching completo
 */
function usuario_tiene_perfil_matching($usuario_id) {
    try {
        $sql = "SELECT quiz_completado FROM perfil_adoptante WHERE usuario_id = ?";
        $resultado = consultar_db($sql, [$usuario_id]);
        return $resultado && $resultado[0]['quiz_completado'];
    } catch (Exception $e) {
        return false;
    }
}
?>
