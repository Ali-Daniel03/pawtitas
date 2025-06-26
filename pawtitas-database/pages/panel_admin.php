<?php
/**
 * PAWTITAS - Panel de Administrador
 * Gestión centralizada de perritos y solicitudes
 */

require_once '../config/config.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Verificar que esté logueado y sea admin
requerir_login('../login.php');
requerir_tipo_usuario('admin', 'panel_adoptante.php');

$usuario = obtener_usuario_actual();

// Obtener estadísticas del admin
$stats = [
    'total_perritos' => 0,
    'disponibles' => 0,
    'solicitudes_pendientes' => 0,
    'adopciones_completadas' => 0
];

try {
    // Total de perritos
    $sql = "SELECT COUNT(*) as total FROM perritos";
    $resultado = consultar_db($sql);
    $stats['total_perritos'] = $resultado ? $resultado[0]['total'] : 0;
    
    // Disponibles
    $sql = "SELECT COUNT(*) as total FROM perritos WHERE disponible = 1";
    $resultado = consultar_db($sql);
    $stats['disponibles'] = $resultado ? $resultado[0]['total'] : 0;
    
    // Solicitudes pendientes
    $sql = "SELECT COUNT(*) as total FROM solicitudes_adopcion WHERE estado = 'pendiente'";
    $resultado = consultar_db($sql);
    $stats['solicitudes_pendientes'] = $resultado ? $resultado[0]['total'] : 0;
    
    // Adopciones completadas
    $sql = "SELECT COUNT(*) as total FROM solicitudes_adopcion WHERE estado = 'completada'";
    $resultado = consultar_db($sql);
    $stats['adopciones_completadas'] = $resultado ? $resultado[0]['total'] : 0;
    
} catch (Exception $e) {
    error_log("Error obteniendo estadísticas admin: " . $e->getMessage());
}

$page_title = "Panel de Administrador - " . APP_NAME;
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
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <div class="logo">
                <div class="logo-icon">🐾</div>
                <span class="logo-text"><?php echo APP_NAME; ?></span>
            </div>
            <div class="nav-buttons">
                <span>Admin: <?php echo escapar_html($usuario['nombre']); ?> 👑</span>
                <a href="logout.php" class="btn btn-outline">Cerrar Sesión</a>
            </div>
        </nav>
    </header>

    <!-- Contenido Principal -->
    <main style="padding-top: 100px;">
        <div class="container">
            <!-- Título -->
            <div class="welcome-section" style="text-align: center; margin-bottom: 3rem;">
                <h1>Panel de Administración 👑</h1>
                <p style="color: #8d6e63; font-size: 1.1rem;">
                    Gestiona perritos, solicitudes y adopciones
                </p>
            </div>

            <!-- Estadísticas -->
            <div class="stats-section" style="margin-bottom: 3rem; padding: 2rem; background: rgba(255,255,255,0.5); border-radius: 15px;">
                <h3 style="text-align: center; margin-bottom: 2rem; color: #5a4a3a;">📊 Estadísticas del Sistema</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem; text-align: center;">
                    <div>
                        <div style="font-size: 2rem; font-weight: bold; color: #ff8a80;"><?php echo $stats['total_perritos']; ?></div>
                        <div style="color: #8d6e63;">Total Perritos</div>
                    </div>
                    <div>
                        <div style="font-size: 2rem; font-weight: bold; color: #4caf50;"><?php echo $stats['disponibles']; ?></div>
                        <div style="color: #8d6e63;">Disponibles</div>
                    </div>
                    <div>
                        <div style="font-size: 2rem; font-weight: bold; color: #ff9800;"><?php echo $stats['solicitudes_pendientes']; ?></div>
                        <div style="color: #8d6e63;">Solicitudes Pendientes</div>
                    </div>
                    <div>
                        <div style="font-size: 2rem; font-weight: bold; color: #2196f3;"><?php echo $stats['adopciones_completadas']; ?></div>
                        <div style="color: #8d6e63;">Adopciones Exitosas</div>
                    </div>
                </div>
            </div>

            <!-- Opciones del Panel -->
            <div class="dashboard-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                
                <!-- Gestionar Perritos -->
                <div class="dashboard-card">
                    <div class="card-icon" style="font-size: 3rem; margin-bottom: 1rem;">🐕</div>
                    <h3>Gestionar Perritos</h3>
                    <p>Agregar, editar y administrar todos los perritos del sistema</p>
                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                        <a href="agregar_perrito.php" class="btn btn-primary">➕ Agregar</a>
                        <a href="lista_perritos_admin.php" class="btn btn-secondary">📋 Ver Todos</a>
                    </div>
                </div>

                <!-- Solicitudes de Adopción -->
                <div class="dashboard-card">
                    <div class="card-icon" style="font-size: 3rem; margin-bottom: 1rem;">📨</div>
                    <h3>Solicitudes de Adopción</h3>
                    <p>Revisar y gestionar todas las solicitudes de adopción</p>
                    <a href="solicitudes_admin.php" class="btn btn-primary">
                        Ver Solicitudes
                        <?php if ($stats['solicitudes_pendientes'] > 0): ?>
                            <span style="background: #ff5722; color: white; padding: 2px 8px; border-radius: 10px; font-size: 0.8rem; margin-left: 5px;">
                                <?php echo $stats['solicitudes_pendientes']; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </div>

                <!-- Usuarios -->
                <div class="dashboard-card">
                    <div class="card-icon" style="font-size: 3rem; margin-bottom: 1rem;">👥</div>
                    <h3>Gestionar Usuarios</h3>
                    <p>Administrar adoptantes y sus perfiles</p>
                    <a href="lista_usuarios.php" class="btn btn-outline">Ver Usuarios</a>
                </div>

                <!-- Reportes -->
                <div class="dashboard-card">
                    <div class="card-icon" style="font-size: 3rem; margin-bottom: 1rem;">📊</div>
                    <h3>Reportes y Estadísticas</h3>
                    <p>Análisis detallado del sistema de adopciones</p>
                    <a href="reportes.php" class="btn btn-outline">Ver Reportes</a>
                </div>

            </div>

            <!-- Acciones Rápidas -->
            <div class="quick-actions" style="margin-top: 3rem; text-align: center;">
                <h3 style="margin-bottom: 2rem; color: #5a4a3a;">⚡ Acciones Rápidas</h3>
                <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                    <a href="agregar_perrito.php" class="btn btn-primary">
                        ➕ Nuevo Perrito
                    </a>
                    <a href="solicitudes_admin.php" class="btn btn-secondary">
                        📨 Revisar Solicitudes
                    </a>
                    <a href="explorar_perritos_simple.php" class="btn btn-outline">
                        👀 Ver Como Usuario
                    </a>
                </div>
            </div>
        </div>
    </main>

    <script src="../assets/js/main.js"></script>
</body>
</html>
