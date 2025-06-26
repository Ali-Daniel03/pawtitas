<?php
/**
 * PAWTITAS - Panel del Adoptante (Versión Simplificada)
 */

require_once '../config/config.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/matching.php';

// Verificar que esté logueado y sea adoptante
requerir_login('../login.php');
requerir_tipo_usuario('adoptante', 'panel_admin.php');

$usuario = obtener_usuario_actual();

// Obtener estadísticas del usuario
$sql = "SELECT COUNT(*) as total FROM solicitudes_adopcion WHERE usuario_id = ?";
$resultado = consultar_db($sql, [$usuario['id']]);
$total_solicitudes = $resultado ? $resultado[0]['total'] : 0;

$sql = "SELECT COUNT(*) as total FROM solicitudes_adopcion WHERE usuario_id = ? AND estado = 'aprobada'";
$resultado = consultar_db($sql, [$usuario['id']]);
$solicitudes_aprobadas = $resultado ? $resultado[0]['total'] : 0;

$sql = "SELECT COUNT(*) as total FROM solicitudes_adopcion WHERE usuario_id = ? AND estado = 'pendiente'";
$resultado = consultar_db($sql, [$usuario['id']]);
$solicitudes_pendientes = $resultado ? $resultado[0]['total'] : 0;

$page_title = "Panel de Adoptante - " . APP_NAME;
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
    <header class="header">
        <nav class="navbar">
            <div class="logo">
                <div class="logo-icon">🐾</div>
                <span class="logo-text"><?php echo APP_NAME; ?></span>
            </div>
            <div class="nav-buttons">
                <span>Hola, <?php echo escapar_html($usuario['nombre']); ?>! 👋</span>
                <a href="logout.php" class="btn btn-outline">Cerrar Sesión</a>
            </div>
        </nav>
    </header>

    <main style="padding-top: 100px;">
        <div class="container">
            <div class="welcome-section" style="text-align: center; margin-bottom: 3rem;">
                <h1>¡Bienvenido a tu Panel de Adopción! 🏠</h1>
                <p style="color: #8d6e63; font-size: 1.1rem;">
                    Encuentra y solicita la adopción de tu compañero perfecto
                </p>
            </div>

            <?php 
            $mensaje = obtener_mensaje();
            if ($mensaje): 
            ?>
                <div class="message <?php echo $mensaje['tipo']; ?>" style="max-width: 800px; margin: 0 auto 2rem;">
                    <?php echo escapar_html($mensaje['texto']); ?>
                </div>
            <?php endif; ?>

            <!-- Verificar si necesita completar el quiz -->
            <?php 
            $tiene_perfil = usuario_tiene_perfil_matching($usuario['id']);
            if (!$tiene_perfil): 
            ?>
                <div style="background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%); padding: 2rem; border-radius: 15px; text-align: center; margin-bottom: 3rem; border: 2px solid #2196f3;">
                    <h3 style="color: #1976d2; margin-bottom: 1rem;">🎯 ¡Completa tu Quiz de Compatibilidad!</h3>
                    <p style="color: #1976d2; margin-bottom: 1.5rem; font-size: 1.1rem;">
                        Para encontrar a tu compañero perfecto, necesitas completar nuestro quiz de compatibilidad.
                        Solo toma 5 minutos y te ayudará a encontrar los mejores matches.
                    </p>
                    <a href="quiz_matching.php" class="btn btn-primary btn-large" style="font-size: 1.2rem; padding: 1rem 2rem;">
                        🎯 Hacer Quiz Ahora
                    </a>
                </div>
            <?php else: ?>
                <div style="background: #d4edda; padding: 1.5rem; border-radius: 10px; text-align: center; margin-bottom: 2rem; border-left: 4px solid #28a745;">
                    <h4 style="color: #155724; margin-bottom: 0.5rem;">✅ Quiz Completado</h4>
                    <p style="color: #155724; margin-bottom: 1rem;">Ya tienes tu perfil de compatibilidad listo</p>
                    <a href="resultados_matching.php" class="btn btn-secondary">Ver Mis Matches</a>
                </div>
            <?php endif; ?>

            <div class="dashboard-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                
                <div class="dashboard-card">
                    <div class="card-icon" style="font-size: 3rem; margin-bottom: 1rem;">🐕</div>
                    <h3>Explorar Perritos</h3>
                    <p>Conoce a todos los perritos disponibles para adopción</p>
                    <a href="explorar_perritos_simple.php" class="btn btn-primary">Ver Perritos</a>
                </div>

                <div class="dashboard-card">
                    <div class="card-icon" style="font-size: 3rem; margin-bottom: 1rem;">🎯</div>
                    <h3>Mis Matches</h3>
                    <p><?php echo $tiene_perfil ? 'Ve tus perritos más compatibles' : 'Completa el quiz para ver matches'; ?></p>
                    <?php if ($tiene_perfil): ?>
                        <a href="resultados_matching.php" class="btn btn-primary">Ver Matches</a>
                    <?php else: ?>
                        <a href="quiz_matching.php" class="btn btn-primary">Hacer Quiz</a>
                    <?php endif; ?>
                </div>

                <div class="dashboard-card">
                    <div class="card-icon" style="font-size: 3rem; margin-bottom: 1rem;">📋</div>
                    <h3>Mis Solicitudes</h3>
                    <p>Revisa el estado de tus solicitudes de adopción</p>
                    <a href="mis_solicitudes.php" class="btn btn-secondary">Ver Solicitudes</a>
                </div>

                <div class="dashboard-card">
                    <div class="card-icon" style="font-size: 3rem; margin-bottom: 1rem;">👤</div>
                    <h3>Mi Perfil</h3>
                    <p>Actualiza tu información personal</p>
                    <a href="mi_perfil.php" class="btn btn-outline">Editar Perfil</a>
                </div>

            </div>

            <div class="stats-section" style="margin-top: 3rem; padding: 2rem; background: rgba(255,255,255,0.5); border-radius: 15px;">
                <h3 style="text-align: center; margin-bottom: 2rem; color: #5a4a3a;">📊 Tu Actividad</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem; text-align: center;">
                    <div>
                        <div style="font-size: 2rem; font-weight: bold; color: #ff8a80;"><?php echo $total_solicitudes; ?></div>
                        <div style="color: #8d6e63;">Solicitudes Enviadas</div>
                    </div>
                    <div>
                        <div style="font-size: 2rem; font-weight: bold; color: #4caf50;"><?php echo $solicitudes_aprobadas; ?></div>
                        <div style="color: #8d6e63;">Solicitudes Aprobadas</div>
                    </div>
                    <div>
                        <div style="font-size: 2rem; font-weight: bold; color: #ff9800;"><?php echo $solicitudes_pendientes; ?></div>
                        <div style="color: #8d6e63;">En Proceso</div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="../assets/js/main.js"></script>
</body>
</html>
