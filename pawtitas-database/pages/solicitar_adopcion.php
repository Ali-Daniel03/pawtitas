<?php
/**
 * PAWTITAS - Solicitar Adopción
 * Formulario para enviar solicitud de adopción
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
$perrito_id = (int)($_GET['perrito_id'] ?? 0);

if (!$perrito_id) {
    redirigir_con_mensaje('explorar_perritos_simple.php', 'Perrito no encontrado', 'error');
}

// Obtener información del perrito
$sql = "SELECT * FROM perritos WHERE id = ? AND disponible = 1";
$resultado = consultar_db($sql, [$perrito_id]);

if (!$resultado) {
    redirigir_con_mensaje('explorar_perritos_simple.php', 'Perrito no encontrado', 'error');
}

$perrito = $resultado[0];

// Verificar si ya tiene una solicitud para este perrito
$sql = "SELECT id, estado FROM solicitudes_adopcion WHERE usuario_id = ? AND perrito_id = ?";
$solicitud_existente = consultar_db($sql, [$usuario['id'], $perrito_id]);

if ($solicitud_existente) {
    $estado = $solicitud_existente[0]['estado'];
    if (in_array($estado, ['pendiente', 'en_revision', 'aprobada'])) {
        redirigir_con_mensaje('mis_solicitudes.php', 'Ya tienes una solicitud activa para este perrito', 'info');
    }
}

// Calcular compatibilidad si tiene perfil
$compatibilidad = null;
if (usuario_tiene_perfil_matching($usuario['id'])) {
    $matching_result = calcular_compatibilidad_usuario_perrito($usuario['id'], $perrito_id);
    if ($matching_result['exito']) {
        $compatibilidad = $matching_result;
    }
}

$mensaje = '';
$tipo_mensaje = '';

// Procesar formulario
if ($_POST) {
    $mensaje_adoptante = limpiar_datos($_POST['mensaje_adoptante'] ?? '');
    
    if (empty($mensaje_adoptante)) {
        $mensaje = 'Por favor escribe un mensaje explicando por qué quieres adoptar a este perrito';
        $tipo_mensaje = 'error';
    } else {
        try {
            $sql = "INSERT INTO solicitudes_adopcion (
                        usuario_id, perrito_id, mensaje_adoptante, puntuacion_compatibilidad, 
                        estado, fecha_solicitud
                    ) VALUES (?, ?, ?, ?, 'pendiente', NOW())";
            
            $puntuacion = $compatibilidad ? $compatibilidad['porcentaje_compatibilidad'] : null;
            
            if (ejecutar_db($sql, [$usuario['id'], $perrito_id, $mensaje_adoptante, $puntuacion])) {
                registrar_actividad($usuario['id'], 'solicitud_enviada', "Solicitud para {$perrito['nombre']}");
                redirigir_con_mensaje('mis_solicitudes.php', '¡Solicitud enviada exitosamente! Te contactaremos pronto.', 'success');
            } else {
                $mensaje = 'Error al enviar la solicitud. Intenta nuevamente.';
                $tipo_mensaje = 'error';
            }
            
        } catch (Exception $e) {
            $mensaje = 'Error interno. Intenta nuevamente.';
            $tipo_mensaje = 'error';
        }
    }
}

$page_title = "Adoptar a " . $perrito['nombre'] . " - " . APP_NAME;
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
                <a href="ver_perrito.php?id=<?php echo $perrito_id; ?>" class="btn btn-outline">← Volver</a>
                <a href="logout.php" class="btn btn-secondary">Cerrar Sesión</a>
            </div>
        </nav>
    </header>

    <main style="padding-top: 100px; padding-bottom: 50px;">
        <div class="container" style="max-width: 800px;">
            
            <div style="text-align: center; margin-bottom: 3rem;">
                <h1 style="color: #5a4a3a; margin-bottom: 1rem;">
                    💕 Solicitar Adopción de <?php echo escapar_html($perrito['nombre']); ?>
                </h1>
                <p style="color: #8d6e63; font-size: 1.1rem;">
                    Estás a un paso de darle un hogar lleno de amor
                </p>
            </div>

            <!-- Información del perrito -->
            <div style="background: white; padding: 2rem; border-radius: 15px; margin-bottom: 2rem; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
                <div style="display: grid; grid-template-columns: 150px 1fr; gap: 2rem; align-items: center;">
                    <div>
                        <?php if ($perrito['foto_principal']): ?>
                            <img src="../uploads/dogs/<?php echo escapar_html($perrito['foto_principal']); ?>" 
                                 alt="<?php echo escapar_html($perrito['nombre']); ?>"
                                 style="width: 100%; height: 150px; object-fit: cover; border-radius: 10px;">
                        <?php else: ?>
                            <div style="width: 100%; height: 150px; background: #f8f9fa; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 3rem; color: #dee2e6;">🐕</div>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <h3 style="color: #5a4a3a; margin-bottom: 1rem;"><?php echo escapar_html($perrito['nombre']); ?></h3>
                        
                        <div style="display: flex; gap: 1rem; margin-bottom: 1rem; flex-wrap: wrap;">
                            <?php if ($perrito['raza']): ?>
                                <span style="background: #f8f9fa; padding: 0.5rem 1rem; border-radius: 15px; font-size: 0.9rem;">
                                    🐕 <?php echo escapar_html($perrito['raza']); ?>
                                </span>
                            <?php endif; ?>
                            <span style="background: #f8f9fa; padding: 0.5rem 1rem; border-radius: 15px; font-size: 0.9rem;">
                                📏 <?php echo ucfirst($perrito['tamaño']); ?>
                            </span>
                            <?php if ($perrito['edad_aproximada']): ?>
                                <span style="background: #f8f9fa; padding: 0.5rem 1rem; border-radius: 15px; font-size: 0.9rem;">
                                    🎂 <?php echo $perrito['edad_aproximada']; ?> años
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($perrito['personalidad']): ?>
                            <p style="color: #666; font-size: 0.95rem; line-height: 1.5;">
                                <?php echo escapar_html(cortar_texto($perrito['personalidad'], 150)); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Compatibilidad -->
            <?php if ($compatibilidad): ?>
                <div style="background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%); padding: 2rem; border-radius: 15px; margin-bottom: 2rem;">
                    <h3 style="text-align: center; color: #5a4a3a; margin-bottom: 1.5rem;">
                        🎯 Tu Compatibilidad con <?php echo escapar_html($perrito['nombre']); ?>
                    </h3>
                    
                    <div style="text-align: center;">
                        <?php 
                        $score = $compatibilidad['porcentaje_compatibilidad'];
                        if ($score >= 85) {
                            $color = '#28a745';
                            $text = '¡Match Excelente!';
                            $desc = 'Este perrito es perfecto para ti';
                        } elseif ($score >= 70) {
                            $color = '#17a2b8';
                            $text = 'Buen Match';
                            $desc = 'Muy buena compatibilidad';
                        } elseif ($score >= 50) {
                            $color = '#ffc107';
                            $text = 'Match Moderado';
                            $desc = 'Compatibilidad aceptable';
                        } else {
                            $color = '#dc3545';
                            $text = 'Match Bajo';
                            $desc = 'Podrían no ser compatibles';
                        }
                        ?>
                        
                        <div style="width: 100px; height: 100px; border-radius: 50%; background: <?php echo $color; ?>; color: white; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 1.5rem; font-weight: bold;">
                            <?php echo round($score); ?>%
                        </div>
                        
                        <h4 style="color: #5a4a3a; margin-bottom: 0.5rem;"><?php echo $text; ?></h4>
                        <p style="color: #666;"><?php echo $desc; ?></p>
                    </div>
                    
                    <?php if (!empty($compatibilidad['incompatibilidades'])): ?>
                        <div style="background: rgba(255,193,7,0.1); padding: 1.5rem; border-radius: 10px; margin-top: 1.5rem; border-left: 4px solid #ffc107;">
                            <h5 style="color: #856404; margin-bottom: 1rem;">⚠️ Consideraciones Importantes:</h5>
                            <ul style="color: #856404; margin-left: 1rem;">
                                <?php foreach ($compatibilidad['incompatibilidades'] as $tipo => $existe): ?>
                                    <?php if ($existe): ?>
                                        <li>
                                            <?php
                                            switch ($tipo) {
                                                case 'tamaño':
                                                    echo 'El tamaño no coincide con tus preferencias';
                                                    break;
                                                case 'niños':
                                                    echo 'Puede no ser ideal para convivir con niños';
                                                    break;
                                                case 'jardin':
                                                    echo 'Necesita jardín para ser completamente feliz';
                                                    break;
                                                case 'otros_perros':
                                                    echo 'Podría tener dificultades con otros perros';
                                                    break;
                                                case 'gatos':
                                                    echo 'Podría tener dificultades con gatos';
                                                    break;
                                            }
                                            ?>
                                        </li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div style="background: #fff3cd; padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem; border-left: 4px solid #ffc107;">
                    <h4 style="color: #856404; margin-bottom: 0.5rem;">💡 Recomendación</h4>
                    <p style="color: #856404; margin-bottom: 1rem;">
                        Para conocer tu compatibilidad con <?php echo escapar_html($perrito['nombre']); ?>, 
                        te recomendamos completar nuestro quiz de matching.
                    </p>
                    <a href="quiz_matching.php" class="btn btn-secondary">Hacer Quiz de Compatibilidad</a>
                </div>
            <?php endif; ?>

            <!-- Formulario de solicitud -->
            <?php if ($mensaje): ?>
                <div class="message <?php echo $tipo_mensaje; ?>" style="margin-bottom: 2rem;">
                    <?php echo escapar_html($mensaje); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" style="background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
                <h3 style="color: #5a4a3a; margin-bottom: 2rem; text-align: center;">
                    📝 Cuéntanos por qué quieres adoptar a <?php echo escapar_html($perrito['nombre']); ?>
                </h3>
                
                <div class="form-group">
                    <label for="mensaje_adoptante">Tu mensaje para el refugio *</label>
                    <textarea id="mensaje_adoptante" name="mensaje_adoptante" required rows="6"
                              placeholder="Explica por qué quieres adoptar a este perrito, tu experiencia con mascotas, tu estilo de vida, y cómo planeas cuidarlo..."
                              style="width: 100%; padding: 1rem; border: 2px solid #e8e8e8; border-radius: 8px; resize: vertical; font-family: inherit;"><?php echo escapar_html($_POST['mensaje_adoptante'] ?? ''); ?></textarea>
                    <small style="color: #666; font-size: 0.85rem;">
                        Incluye información sobre tu experiencia con mascotas, tu rutina diaria, y por qué crees que serías un buen hogar para este perrito.
                    </small>
                </div>
                
                <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px; margin: 2rem 0;">
                    <h4 style="color: #5a4a3a; margin-bottom: 1rem;">📋 Información importante sobre el proceso:</h4>
                    <ul style="color: #666; line-height: 1.6; margin-left: 1.5rem;">
                        <li>Tu solicitud será revisada por nuestro equipo</li>
                        <li>Podríamos contactarte para una entrevista</li>
                        <li>Se puede requerir una visita domiciliaria</li>
                        <li>El proceso puede tomar de 3 a 7 días</li>
                        <li>Te notificaremos por email sobre cualquier actualización</li>
                    </ul>
                </div>
                
                <div style="text-align: center;">
                    <button type="submit" class="btn btn-primary btn-large" style="padding: 1rem 3rem; font-size: 1.1rem;">
                        💕 Enviar Solicitud de Adopción
                    </button>
                </div>
            </form>
            
            <div style="text-align: center; margin-top: 2rem;">
                <a href="ver_perrito.php?id=<?php echo $perrito_id; ?>" class="btn btn-outline">
                    ← Volver a ver a <?php echo escapar_html($perrito['nombre']); ?>
                </a>
            </div>
        </div>
    </main>

    <script src="../assets/js/main.js"></script>
</body>
</html>
