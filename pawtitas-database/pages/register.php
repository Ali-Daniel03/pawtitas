<?php
/**
 * PAWTITAS - Página de Registro (Solo Adoptantes)
 */

require_once '../config/config.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

SessionManager::start();

$mensaje = '';
$tipo_mensaje = '';

// Si ya está logueado, redirigir
if (usuario_logueado()) {
    $usuario = obtener_usuario_actual();
    $redirect_url = ($usuario['tipo'] === 'admin') ? 'panel_admin.php' : 'panel_adoptante.php';
    header("Location: $redirect_url");
    exit();
}

// Procesar formulario de registro
if ($_POST) {
    $datos = [
        'nombre' => limpiar_datos($_POST['nombre'] ?? ''),
        'apellidos' => limpiar_datos($_POST['apellidos'] ?? ''),
        'email' => limpiar_datos($_POST['email'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'confirm_password' => $_POST['confirm_password'] ?? '',
        'tipo_usuario' => 'adoptante', // Forzar adoptante
        'telefono' => limpiar_datos($_POST['telefono'] ?? ''),
        'fecha_nacimiento' => limpiar_datos($_POST['fecha_nacimiento'] ?? ''),
        'direccion' => limpiar_datos($_POST['direccion'] ?? ''),
        'ciudad' => limpiar_datos($_POST['ciudad'] ?? ''),
        'estado' => limpiar_datos($_POST['estado'] ?? ''),
        'codigo_postal' => limpiar_datos($_POST['codigo_postal'] ?? ''),
        'ocupacion' => limpiar_datos($_POST['ocupacion'] ?? ''),
        'estado_civil' => limpiar_datos($_POST['estado_civil'] ?? ''),
        'tiene_discapacidad' => isset($_POST['tiene_discapacidad']) ? 1 : 0,
        'tipo_discapacidad' => limpiar_datos($_POST['tipo_discapacidad'] ?? '')
    ];
    
    // Validar confirmación de contraseña
    if ($datos['password'] !== $datos['confirm_password']) {
        $mensaje = 'Las contraseñas no coinciden';
        $tipo_mensaje = 'error';
    } else {
        $auth = new Auth();
        $resultado = $auth->registrar_usuario($datos);
        
        if ($resultado['exito']) {
            // Iniciar sesión automáticamente después del registro
            $auth = new Auth();
            $login_result = $auth->iniciar_sesion($datos['email'], $datos['password']);

            if ($login_result['exito']) {
                // Redirigir directamente al quiz
                redirigir_con_mensaje('quiz_matching.php', '¡Registro exitoso! Ahora completa tu quiz de compatibilidad para encontrar a tu compañero perfecto.', 'success');
            } else {
                // Si falla el login automático, ir a login manual
                header('Location: login.php?registered=1');
            }
            exit();
        } else {
            $mensaje = $resultado['mensaje'];
            $tipo_mensaje = 'error';
        }
    }
}

$page_title = "Registro de Adoptante - " . APP_NAME;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo escapar_html($page_title); ?></title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="form-container" style="margin-top: 50px; max-width: 600px;">
        <div style="text-align: center; margin-bottom: 20px;">
            <a href="../index.php" style="color: #999; text-decoration: none; font-size: 14px;">← Volver al inicio</a>
        </div>
        
        <div class="logo" style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: #ff8a80; font-size: 2.5em; margin-bottom: 10px;">🐾 <?php echo APP_NAME; ?></h1>
            <p style="color: #666; font-size: 1.1rem;">Regístrate como Adoptante</p>
        </div>
        
        <?php if ($mensaje): ?>
            <div class="message <?php echo $tipo_mensaje; ?>">
                <?php echo escapar_html($mensaje); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="nombre">Nombre *</label>
                    <input type="text" id="nombre" name="nombre" required 
                           value="<?php echo escapar_html($_POST['nombre'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="apellidos">Apellidos *</label>
                    <input type="text" id="apellidos" name="apellidos" required 
                           value="<?php echo escapar_html($_POST['apellidos'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" required 
                       value="<?php echo escapar_html($_POST['email'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="telefono">Teléfono</label>
                <input type="tel" id="telefono" name="telefono" placeholder="10 dígitos"
                       value="<?php echo escapar_html($_POST['telefono'] ?? ''); ?>">
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="password">Contraseña *</label>
                    <input type="password" id="password" name="password" required 
                           minlength="6" placeholder="Mínimo 6 caracteres">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirmar Contraseña *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required 
                           minlength="6">
                </div>
            </div>
            
            <div class="form-group">
                <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento"
                       value="<?php echo escapar_html($_POST['fecha_nacimiento'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="direccion">Dirección</label>
                <input type="text" id="direccion" name="direccion"
                       value="<?php echo escapar_html($_POST['direccion'] ?? ''); ?>">
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="ciudad">Ciudad</label>
                    <input type="text" id="ciudad" name="ciudad"
                           value="<?php echo escapar_html($_POST['ciudad'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="estado">Estado</label>
                    <input type="text" id="estado" name="estado"
                           value="<?php echo escapar_html($_POST['estado'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="codigo_postal">Código Postal</label>
                    <input type="text" id="codigo_postal" name="codigo_postal"
                           value="<?php echo escapar_html($_POST['codigo_postal'] ?? ''); ?>">
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="ocupacion">Ocupación</label>
                    <input type="text" id="ocupacion" name="ocupacion"
                           value="<?php echo escapar_html($_POST['ocupacion'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="estado_civil">Estado Civil</label>
                    <select id="estado_civil" name="estado_civil">
                        <option value="soltero" <?php echo ($_POST['estado_civil'] ?? '') == 'soltero' ? 'selected' : ''; ?>>Soltero/a</option>
                        <option value="casado" <?php echo ($_POST['estado_civil'] ?? '') == 'casado' ? 'selected' : ''; ?>>Casado/a</option>
                        <option value="divorciado" <?php echo ($_POST['estado_civil'] ?? '') == 'divorciado' ? 'selected' : ''; ?>>Divorciado/a</option>
                        <option value="viudo" <?php echo ($_POST['estado_civil'] ?? '') == 'viudo' ? 'selected' : ''; ?>>Viudo/a</option>
                        <option value="union_libre" <?php echo ($_POST['estado_civil'] ?? '') == 'union_libre' ? 'selected' : ''; ?>>Unión Libre</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" name="tiene_discapacidad" value="1" 
                           <?php echo isset($_POST['tiene_discapacidad']) ? 'checked' : ''; ?>
                           onchange="toggleDiscapacidad()">
                    Tengo alguna discapacidad
                </label>
            </div>
            
            <div class="form-group" id="tipo_discapacidad_group" style="display: none;">
                <label for="tipo_discapacidad">Tipo de Discapacidad</label>
                <input type="text" id="tipo_discapacidad" name="tipo_discapacidad"
                       value="<?php echo escapar_html($_POST['tipo_discapacidad'] ?? ''); ?>">
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 15px; font-size: 18px;">
                Registrarse como Adoptante
            </button>
        </form>
        
        <div style="text-align: center; margin-top: 20px;">
            ¿Ya tienes cuenta? <a href="login.php" style="color: #ff8a80; text-decoration: none; font-weight: bold;">Inicia sesión aquí</a>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
function toggleDiscapacidad() {
    const checkbox = document.querySelector('input[name="tiene_discapacidad"]');
    const group = document.getElementById('tipo_discapacidad_group');
    
    if (checkbox.checked) {
        group.style.display = 'block';
    } else {
        group.style.display = 'none';
        document.getElementById('tipo_discapacidad').value = '';
    }
}

// Verificar estado inicial
document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('input[name="tiene_discapacidad"]').checked) {
        document.getElementById('tipo_discapacidad_group').style.display = 'block';
    }
});
</script>
</body>
</html>
