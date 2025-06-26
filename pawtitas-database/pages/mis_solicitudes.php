<?php
/**
 * PAWTITAS - Mis Solicitudes de Adopción
 * Página para que el adoptante vea sus solicitudes
 */

require_once '../config/config.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Verificar que esté logueado y sea adoptante
requerir_login('../login.php');
requerir_tipo_usuario('adoptante', 'panel_admin.php');

$usuario = obtener_usuario_actual();

// Obtener solicitudes del usuario
$sql = "SELECT sa.*, p.nombre as perrito_nombre, p.raza, p.foto_principal, p.tamaño, p.edad_aproximada
        FROM solicitudes_adopcion sa
        INNER JOIN perritos p ON sa.perrito_id = p.id
        WHERE sa.usuario_id = ?
        ORDER BY sa.fecha_solicitud DESC";

$solicitudes = consultar_db($sql, [$usuario['id']]) ?: [];

$page_title = "Mis Solicitudes - " . APP_NAME;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo escapar_html($page_title); ?></title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .solicitud-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            display: grid;
            grid-template-columns: 100px 1fr auto;
            gap: 1.5rem;
            align-items: center;
        }
        .perrito-thumb {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
        }
        .thumb-placeholder {
            width: 100px;
            height: 100px;
            background: #f8f9fa;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: #dee2e6;
        }
        .estado-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .estado-pendiente { background: #fff3cd; color: #856404; }
        .estado-en_revision { background: #d1ecf1; color: #0c5460; }
        .estado-aprobada { background: #d4edda; color: #155724; }
        .estado-rechazada { background: #f8d7da; color: #721c24; }
        .estado-completada { background: #d4edda; color: #155724; }
        .estado-cancelada { background: #e2e3e5; color: #6c757d; }
    </style>
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <div class="logo">
                <div class="logo-icon">🐾</div>
                <span class="logo-text"><?php echo APP_NAME; ?></span>
            </div>
            <div class="nav-buttons">
                <a href="panel_adoptante.php" class="btn btn-outline">← Panel</a>
                <a href="logout.php" class="btn btn-secondary">Cerrar Sesión</a>
            </div>
        </nav>
    </header>

    <main style="padding-top: 100px; padding-bottom: 50px;">
        <div class="container">
            
            <div style="text-align: center; margin-bottom: 3rem;">
                <h1 style="color: #5a4a3a; margin-bottom: 1rem;">📋 Mis Solicitudes de Adopción</h1>
                <p style="color: #8d6e63; font-size: 1.1rem;">
                    Aquí puedes ver el estado de todas tus solicitudes
                </p>
            </div>

            <?php if (empty($solicitudes)): ?>
                <div style="text-align: center; padding: 3rem; background: white; border-radius: 15px;">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">📝</div>
                    <h3>No tienes solicitudes aún</h3>
                    <p style="color: #666; margin: 1rem 0;">
                        Explora nuestros perritos disponibles y envía tu primera solicitud de adopción
                    </p>
                    <div style="margin-top: 2rem;">
                        <a href="explorar_perritos_simple.php" class="btn btn-primary">Ver Perritos Disponibles</a>
                        <a href="resultados_matching.php" class="btn btn-secondary">Ver Mis Matches</a>
                    </div>
                </div>
            <?php else: ?>
                
                <?php foreach ($solicitudes as $solicitud): ?>
                    <div class="solicitud-card">
                        <!-- Foto del perrito -->
                        <div>
                            <?php if ($solicitud['foto_principal']): ?>
                                <img src="../uploads/dogs/<?php echo escapar_html($solicitud['foto_principal']); ?>" 
                                     alt="<?php echo escapar_html($solicitud['perrito_nombre']); ?>"
                                     class="perrito-thumb">
                            <?php else: ?>
                                <div class="thumb-placeholder">🐕</div>
                            <?php endif; ?>
                        </div>

                        <!-- Información de la solicitud -->
                        <div>
                            <h3 style="color: #5a4a3a; margin-bottom: 0.5rem;">
                                <?php echo escapar_html($solicitud['perrito_nombre']); ?>
                            </h3>
                            
                            <div style="display: flex; gap: 1rem; margin-bottom: 0.5rem; font-size: 0.9rem; color: #666;">
                                <?php if ($solicitud['raza']): ?>
                                    <span>🐕 <?php echo escapar_html($solicitud['raza']); ?></span>
                                <?php endif; ?>
                                <span>📏 <?php echo ucfirst($solicitud['tamaño']); ?></span>
                                <?php if ($solicitud['edad_aproximada']): ?>
                                    <span>🎂 <?php echo $solicitud['edad_aproximada']; ?> años</span>
                                <?php endif; ?>
                            </div>
                            
                            <p style="color: #666; font-size: 0.9rem; margin-bottom: 0.5rem;">
                                <strong>Solicitud enviada:</strong> <?php echo formatear_fecha($solicitud['fecha_solicitud'], 'd/m/Y H:i'); ?>
                            </p>
                            
                            <?php if ($solicitud['fecha_respuesta']): ?>
                                <p style="color: #666; font-size: 0.9rem; margin-bottom: 0.5rem;">
                                    <strong>Respuesta:</strong> <?php echo formatear_fecha($solicitud['fecha_respuesta'], 'd/m/Y H:i'); ?>
                                </p>
                            <?php endif; ?>
                            
                            <?php if ($solicitud['puntuacion_compatibilidad']): ?>
                                <p style="color: #666; font-size: 0.9rem;">
                                    <strong>Compatibilidad:</strong> <?php echo round($solicitud['puntuacion_compatibilidad']); ?>%
                                </p>
                            <?php endif; ?>
                            
                            <?php if ($solicitud['mensaje_adoptante']): ?>
                                <div style="background: #f8f9fa; padding: 0.75rem; border-radius: 8px; margin-top: 0.5rem;">
                                    <small style="color: #666;">
                                        <strong>Tu mensaje:</strong><br>
                                        <?php echo escapar_html(cortar_texto($solicitud['mensaje_adoptante'], 100)); ?>
                                    </small>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($solicitud['comentarios_admin']): ?>
                                <div style="background: #e3f2fd; padding: 0.75rem; border-radius: 8px; margin-top: 0.5rem;">
                                    <small style="color: #1976d2;">
                                        <strong>Comentarios del refugio:</strong><br>
                                        <?php echo escapar_html($solicitud['comentarios_admin']); ?>
                                    </small>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($solicitud['razon_rechazo']): ?>
                                <div style="background: #ffebee; padding: 0.75rem; border-radius: 8px; margin-top: 0.5rem;">
                                    <small style="color: #c62828;">
                                        <strong>Razón del rechazo:</strong><br>
                                        <?php echo escapar_html($solicitud['razon_rechazo']); ?>
                                    </small>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Estado y acciones -->
                        <div style="text-align: center;">
                            <div class="estado-badge estado-<?php echo $solicitud['estado']; ?>" style="margin-bottom: 1rem;">
                                <?php
                                switch ($solicitud['estado']) {
                                    case 'pendiente':
                                        echo '⏳ Pendiente';
                                        break;
                                    case 'en_revision':
                                        echo '👀 En Revisión';
                                        break;
                                    case 'aprobada':
                                        echo '✅ Aprobada';
                                        break;
                                    case 'rechazada':
                                        echo '❌ Rechazada';
                                        break;
                                    case 'completada':
                                        echo '🎉 Completada';
                                        break;
                                    case 'cancelada':
                                        echo '🚫 Cancelada';
                                        break;
                                    default:
                                        echo ucfirst($solicitud['estado']);
                                }
                                ?>
                            </div>
                            
                            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                <a href="ver_perrito.php?id=<?php echo $solicitud['perrito_id']; ?>" class="btn btn-outline" style="font-size: 0.85rem; padding: 0.5rem 1rem;">
                                    👀 Ver Perrito
                                </a>
                                
                                <?php if ($solicitud['estado'] === 'pendiente'): ?>
                                    <button onclick="cancelarSolicitud(<?php echo $solicitud['id']; ?>)" class="btn btn-secondary" style="font-size: 0.85rem; padding: 0.5rem 1rem;">
                                        🚫 Cancelar
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Estadísticas rápidas -->
                <div style="background: #f8f9fa; padding: 2rem; border-radius: 15px; margin-top: 3rem;">
                    <h3 style="color: #5a4a3a; text-align: center; margin-bottom: 2rem;">📊 Resumen de tus Solicitudes</h3>
                    
                    <?php
                    $stats = [
                        'total' => count($solicitudes),
                        'pendientes' => 0,
                        'aprobadas' => 0,
                        'completadas' => 0,
                        'rechazadas' => 0
                    ];
                    
                    foreach ($solicitudes as $sol) {
                        if (isset($stats[$sol['estado']])) {
                            $stats[$sol['estado']]++;
                        }
                    }
                    ?>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1.5rem; text-align: center;">
                        <div>
                            <div style="font-size: 2rem; color: #ff8a80; margin-bottom: 0.5rem;"><?php echo $stats['total']; ?></div>
                            <div style="color: #666;">Total</div>
                        </div>
                        <div>
                            <div style="font-size: 2rem; color: #ffc107; margin-bottom: 0.5rem;"><?php echo $stats['pendientes']; ?></div>
                            <div style="color: #666;">Pendientes</div>
                        </div>
                        <div>
                            <div style="font-size: 2rem; color: #28a745; margin-bottom: 0.5rem;"><?php echo $stats['aprobadas']; ?></div>
                            <div style="color: #666;">Aprobadas</div>
                        </div>
                        <div>
                            <div style="font-size: 2rem; color: #17a2b8; margin-bottom: 0.5rem;"><?php echo $stats['completadas']; ?></div>
                            <div style="color: #666;">Completadas</div>
                        </div>
                    </div>
                </div>

                <!-- Acciones adicionales -->
                <div style="text-align: center; margin-top: 3rem;">
                    <a href="explorar_perritos_simple.php" class="btn btn-primary">Ver Más Perritos</a>
                    <a href="resultados_matching.php" class="btn btn-secondary">Ver Mis Matches</a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="../assets/js/main.js"></script>
    <script>
        function cancelarSolicitud(solicitudId) {
            if (confirm('¿Estás seguro de que quieres cancelar esta solicitud?')) {
                // Aquí iría la lógica para cancelar la solicitud
                // Por ahora solo mostramos un mensaje
                alert('Funcionalidad de cancelación pendiente de implementar');
            }
        }
    </script>
</body>
</html>
