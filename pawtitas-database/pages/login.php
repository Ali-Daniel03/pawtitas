<?php
/**
 * PAWTITAS - Página de Login (Corregida)
 */

require_once '../config/config.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Iniciar sesión
SessionManager::start();

$mensaje = '';
$tipo_mensaje = '';

// Si ya está logueado, redirigir
if (usuario_logueado()) {
    $usuario = obtener_usuario_actual();
    $redirect_url = ($usuario['tipo'] === 'refugio') ? 'panel_refugio.php' : 'panel_adoptante.php';
    header("Location: $redirect_url");
    exit();
}

// Mensaje de registro exitoso
if (isset($_GET['registered'])) {
    $mensaje = '¡Registro exitoso! Ahora puedes iniciar sesión';
    $tipo_mensaje = 'success';
}

// Procesar formulario de login
if ($_POST) {
    $email = limpiar_datos($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $mensaje = 'Email y contraseña son obligatorios';
        $tipo_mensaje = 'error';
    } else {
        $auth = new Auth();
        $resultado = $auth->iniciar_sesion($email, $password);
        
        if ($resultado['exito']) {
            // Verificar si es adoptante y si necesita completar el quiz
            if ($resultado['tipo_usuario'] === 'adoptante') {
                // Cargar funciones de matching para verificar perfil
                require_once '../includes/matching.php';
                
                // Obtener ID del usuario recién logueado
                $user_id = $_SESSION['user_id'];
                
                if (!usuario_tiene_perfil_matching($user_id)) {
                    // Redirigir al quiz si no lo ha completado
                    redirigir_con_mensaje('quiz_matching.php', '¡Bienvenido! Completa tu quiz de compatibilidad para encontrar a tu compañero perfecto.', 'info');
                } else {
                    // Ir al panel si ya completó el quiz
                    header("Location: panel_adoptante.php");
                }
            } else {
                // Admin va directo a su panel
                header("Location: panel_admin.php");
            }
            exit();
        } else {
            $mensaje = $resultado['mensaje'];
            $tipo_mensaje = 'error';
        }
    }
}

$page_title = "Iniciar Sesión - " . APP_NAME;
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
    <div class="form-container" style="margin-top: 100px;">
        <div style="text-align: center; margin-bottom: 20px;">
            <a href="../index.php" style="color: #999; text-decoration: none; font-size: 14px;">← Volver al inicio</a>
        </div>
        
        <div class="logo" style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: #ff8a80; font-size: 2.5em; margin-bottom: 10px;">🐾 <?php echo APP_NAME; ?></h1>
            <p style="color: #666; font-size: 1.1em;">Bienvenido de vuelta</p>
        </div>
        
        <?php if ($mensaje): ?>
            <div class="message <?php echo $tipo_mensaje; ?>">
                <?php echo escapar_html($mensaje); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required 
                       value="<?php echo escapar_html($_POST['email'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 15px; font-size: 18px;">
                Iniciar Sesión
            </button>
        </form>
        
        <div style="text-align: center; margin-top: 20px;">
            ¿No tienes cuenta? <a href="register.php" style="color: #ff8a80; text-decoration: none; font-weight: bold;">Regístrate aquí</a>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>
