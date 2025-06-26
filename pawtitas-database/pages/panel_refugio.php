<?php
/**
 * PAWTITAS - Panel del Refugio (Corregido)
 */

require_once '../config/config.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Verificar que esté logueado y sea refugio
requerir_login('../login.php');
requerir_tipo_usuario('refugio', 'panel_adoptante.php');

// Obtener información del usuario
$usuario = obtener_usuario_actual();

// Obtener estadísticas del refugio
$db = obtener_conexion_db();

// Total de perritos del refugio
$sql = "SELECT COUNT(*) as total FROM perritos WHERE refugio_id = ?";
$resultado = consultar_db($sql, [$usuario['id']]);
$total_perritos = $resultado ? $resultado[0]['total'] : 0;

// Perritos disponibles
$sql = "SELECT COUNT(*) as total FROM perritos WHERE refugio_id = ? AND disponible = 1";
$resultado = consultar_db($sql, [$usuario['id']]);
$disponibles = $resultado ? $resultado[0]['total'] : 0;

// Solicitudes recibidas
$sql = "SELECT COUNT(*) as total FROM solicitudes_adopcion sa 
        JOIN perritos p ON sa.perrito_id = p.id 
        WHERE p.refugio_id = ?";
$resultado = consultar_db($sql, [$usuario['id']]);
$solicitudes_recibidas = $resultado ? $resultado[0]['total'] : 0;

$page_title = "Panel de Refugio - " . APP_NAME;
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
    <!-- Header del Panel -->
    <header class="header">
        <nav class="navbar">
            <div class="logo">
                <div class="logo-icon">🐾</div>
                <span class="logo-text"><?php echo APP_NAME; ?></span>
            </div>
            <div class="nav-buttons">
                <span>Hola, <?php echo escapar_html($usuario['nombre']); ?>! 🏥</span>
                <a href="logout.php" class="btn btn-outline">Cerrar Sesión</a>
            </div>
        </nav>
    </header>

    <!-- Contenido Principal -->
    <main style="padding-top: 100px;">
        <div class="container">
            <div class="welcome-section" style="text-align: center; margin-bottom: 3rem;">
                <h1>¡Panel de Gestión del Refugio! 🏥</h1>
                <p style="color: #8d6e63; font-size: 1.1rem;">
                    Gestiona tus perritos y solicitudes de adopción
                </p>
            </div>

            <!-- Mostrar mensaje si existe -->
            <?php 
            $mensaje = obtener_mensaje();
            if ($mensaje): 
            ?>
                <div class="message <?php echo $mensaje['tipo']; ?>" style="max-width: 800px; margin: 0 auto 2rem;">
                    <?php echo escapar_html($mensaje['texto']); ?>
                </div>
            <?php endif; ?>

            <!-- Estadísticas Rápidas -->
            <div class="stats-section" style="margin-bottom: 3rem; padding: 2rem; background: rgba(255,255,255,0.5); border-radius: 15px;">
                <h3 style="text-align: center; margin-bottom: 2rem; color: #5a4a3a;">📊 Estadísticas de tu Refugio</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem; text-align: center;">
                    <div>
                        <div style="font-size: 2rem; font-weight: bold; color: #ff8a80;"><?php echo $total_perritos; ?></div>
                        <div style="color: #8d6e63;">Total de Perritos</div>
                    </div>
                    
                    <div>
                        <div style="font-size: 2rem; font-weight: bold; color: #4caf50;"><?php echo $disponibles; ?></div>
                        <div style="color: #8d6e63;">Disponibles</div>
                    </div>
                    
                    <div>
                        <div style="font-size: 2rem; font-weight: bold; color: #2196f3;"><?php echo $solicitudes_recibidas; ?></div>
                        <div style="color: #8d6e63;">Solicitudes Recibidas</div>
                    </div>
                    
                    <div>
                        <div style="font-size: 2rem; font-weight: bold; color: #ff9800;"><?php echo $total_perritos - $disponibles; ?></div>
                        <div style="color: #8d6e63;">Adoptados</div>
                    </div>
                </div>
            </div>

            <!-- Opciones del Panel -->
            <div class="dashboard-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                
                <!-- Registrar Perrito -->
                <div class="dashboard-card">
                    <div class="card-icon" style="font-size: 3rem; margin-bottom: 1rem;">➕</div>
                    <h3>Registrar Perrito</h3>
                    <p>Agrega un nuevo perrito disponible para adopción</p>
                    <a href="registrar_perrito.php" class="btn btn-primary">Agregar Perrito</a>
                </div>

                <!-- Mis Perritos -->
                <div class="dashboard-card">
                    <div class="card-icon" style="font-size: 3rem; margin-bottom: 1rem;">🐕</div>
                    <h3>Mis Perritos</h3>
                    <p>Gestiona todos los perritos de tu refugio</p>
                    <a href="mis_perritos.php" class="btn btn-secondary">Ver Mis Perritos</a>
                </div>

                <!-- Solicitudes Recibidas -->
                <div class="dashboard-card">
                    <div class="card-icon" style="font-size: 3rem; margin-bottom: 1rem;">📨</div>
                    <h3>Solicitudes de Adopción</h3>
                    <p>Revisa y gestiona las solicitudes recibidas</p>
                    <a href="solicitudes_refugio.php" class="btn btn-outline">
                        Ver Solicitudes 
                        <?php if ($solicitudes_recibidas > 0): ?>
                            <span style="background: #ff8a80; color: white; padding: 2px 8px; border-radius: 10px; font-size: 0.8rem; margin-left: 5px;">
                                <?php echo $solicitudes_recibidas; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </div>

                <!-- Mi Perfil -->
                <div class="dashboard-card">
                    <div class="card-icon" style="font-size: 3rem; margin-bottom: 1rem;">🏥</div>
                    <h3>Perfil del Refugio</h3>
                    <p>Actualiza la información de tu refugio</p>
                    <a href="mi_perfil.php" class="btn btn-outline">Editar Perfil</a>
                </div>

            </div>

            <!-- Acciones Rápidas -->
            <div class="quick-actions" style="margin-top: 3rem; text-align: center;">
                <h3 style="margin-bottom: 2rem; color: #5a4a3a;">⚡ Acciones Rápidas</h3>
                <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                    <a href="registrar_perrito.php" class="btn btn-primary">
                        ➕ Nuevo Perrito
                    </a>
                    <a href="solicitudes_refugio.php" class="btn btn-secondary">
                        📨 Ver Solicitudes
                    </a>
                    <a href="estadisticas.php" class="btn btn-outline">
                        📊 Estadísticas Detalladas
                    </a>
                </div>
            </div>
        </div>
    </main>

    <script src="../assets/js/main.js"></script>
</body>
</html>
