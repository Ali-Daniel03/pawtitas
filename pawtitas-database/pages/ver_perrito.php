<?php
/**
 * PAWTITAS - Ver Detalles del Perrito
 * Página detallada de un perrito específico con información de matching
 */

require_once '../config/config.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/matching.php';

SessionManager::start();

$perrito_id = (int)($_GET['id'] ?? 0);
if (!$perrito_id) {
    redirigir_con_mensaje('explorar_perritos_simple.php', 'Perrito no encontrado', 'error');
}

// Obtener información del perrito
$sql = "SELECT p.*, pp.*, u.nombre as admin_nombre
        FROM perritos p
        LEFT JOIN perfil_perrito pp ON p.id = pp.perrito_id
        LEFT JOIN usuarios u ON p.admin_id = u.id
        WHERE p.id = ? AND p.disponible = 1";

$resultado = consultar_db($sql, [$perrito_id]);
if (!$resultado) {
    redirigir_con_mensaje('explorar_perritos_simple.php', 'Perrito no encontrado', 'error');
}

$perrito = $resultado[0];

// Verificar si hay usuario logueado
$usuario = null;
$compatibilidad = null;
$es_adoptante = false;

if (usuario_logueado()) {
    $usuario = obtener_usuario_actual();
    $es_adoptante = ($usuario['tipo'] === 'adoptante');
    
    // Si es adoptante y tiene perfil, calcular compatibilidad
    if ($es_adoptante && usuario_tiene_perfil_matching($usuario['id'])) {
        $matching_result = calcular_compatibilidad_usuario_perrito($usuario['id'], $perrito_id);
        if ($matching_result['exito']) {
            $compatibilidad = $matching_result;
        }
    }
}

// Obtener fotos adicionales
$sql = "SELECT * FROM fotos_perritos WHERE perrito_id = ? ORDER BY orden_display";
$fotos_adicionales = consultar_db($sql, [$perrito_id]) ?: [];

$page_title = "Conoce a " . $perrito['nombre'] . " - " . APP_NAME;
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
        .perrito-header {
            background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
            padding: 2rem 0;
            margin-top: 80px;
        }
        .perrito-main {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 3rem;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        .perrito-gallery {
            position: sticky;
            top: 100px;
            height: fit-content;
        }
        .main-photo {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            margin-bottom: 1rem;
        }
        .photo-placeholder {
            width: 100%;
            height: 400px;
            background: #f8f9fa;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 6rem;
            color: #dee2e6;
            margin-bottom: 1rem;
        }
        .additional-photos {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
            gap: 0.5rem;
        }
        .additional-photo {
            width: 100%;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        .additional-photo:hover {
            transform: scale(1.05);
        }
        .perrito-info {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .info-section {
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #f0f0f0;
        }
        .info-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        .section-title {
            color: #5a4a3a;
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .basic-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .info-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .compatibility-section {
            background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }
        .compatibility-score {
            text-align: center;
            margin-bottom: 2rem;
        }
        .score-display {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
            font-weight: bold;
            color: white;
        }
        .compatibility-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        .compatibility-item {
            background: rgba(255,255,255,0.7);
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
        }
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
            flex-wrap: wrap;
        }
        .characteristics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }
        .characteristic-item {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
        }
        .characteristic-bar {
            width: 100%;
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 0.5rem;
        }
        .characteristic-fill {
            height: 100%;
            background: linear-gradient(135deg, #ff8a80 0%, #ffab91 100%);
            transition: width 0.3s ease;
        }
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
                <?php if ($usuario): ?>
                    <a href="<?php echo $es_adoptante ? 'resultados_matching.php' : 'panel_admin.php'; ?>" class="btn btn-outline">← Volver</a>
                    <a href="logout.php" class="btn btn-secondary">Cerrar Sesión</a>
                <?php else: ?>
                    <a href="explorar_perritos_simple.php" class="btn btn-outline">← Volver</a>
                    <a href="login.php" class="btn btn-primary">Iniciar Sesión</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <main>
        <!-- Header del perrito -->
        <div class="perrito-header">
            <div class="container" style="text-align: center;">
                <h1 style="color: #5a4a3a; margin-bottom: 0.5rem;">
                    Conoce a <?php echo escapar_html($perrito['nombre']); ?>
                    <?php if ($perrito['tipo_especial'] !== 'normal'): ?>
                        <span style="color: #ff8a80;">⭐</span>
                    <?php endif; ?>
                </h1>
                <p style="color: #8d6e63; font-size: 1.1rem;">
                    <?php if ($perrito['raza']): ?>
                        <?php echo escapar_html($perrito['raza']); ?> • 
                    <?php endif; ?>
                    <?php echo ucfirst($perrito['tamaño']); ?> • 
                    <?php echo ucfirst($perrito['sexo']); ?>
                    <?php if ($perrito['edad_aproximada']): ?>
                        • <?php echo $perrito['edad_aproximada']; ?> años
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <!-- Compatibilidad (solo para adoptantes logueados) -->
        <?php if ($compatibilidad): ?>
            <div class="container" style="margin-top: 2rem;">
                <div class="compatibility-section">
                    <h2 style="text-align: center; color: #5a4a3a; margin-bottom: 2rem;">
                        🎯 Tu Compatibilidad con <?php echo escapar_html($perrito['nombre']); ?>
                    </h2>
                    
                    <div class="compatibility-score">
                        <?php 
                        $score = $compatibilidad['porcentaje_compatibilidad'];
                        if ($score >= 85) {
                            $score_class = 'score-excellent';
                            $rec_text = '¡Match Excelente!';
                            $rec_desc = 'Este perrito es perfecto para ti';
                        } elseif ($score >= 70) {
                            $score_class = 'score-good';
                            $rec_text = 'Buen Match';
                            $rec_desc = 'Muy buena compatibilidad';
                        } elseif ($score >= 50) {
                            $score_class = 'score-moderate';
                            $rec_text = 'Match Moderado';
                            $rec_desc = 'Compatibilidad aceptable';
                        } else {
                            $score_class = 'score-low';
                            $rec_text = 'Match Bajo';
                            $rec_desc = 'Podrían no ser compatibles';
                        }
                        ?>
                        <div class="score-display <?php echo $score_class; ?>">
                            <?php echo round($score); ?>%
                        </div>
                        <h3 style="color: #5a4a3a; margin-bottom: 0.5rem;"><?php echo $rec_text; ?></h3>
                        <p style="color: #666;"><?php echo $rec_desc; ?></p>
                    </div>

                    <?php if (!empty($compatibilidad['incompatibilidades'])): ?>
                        <div style="background: rgba(255,193,7,0.1); padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem; border-left: 4px solid #ffc107;">
                            <h4 style="color: #856404; margin-bottom: 1rem;">⚠️ Consideraciones Importantes:</h4>
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
            </div>
        <?php endif; ?>

        <!-- Contenido principal -->
        <div class="container" style="margin-top: 2rem;">
            <div class="perrito-main">
                
                <!-- Galería de fotos -->
                <div class="perrito-gallery">
                    <?php if ($perrito['foto_principal']): ?>
                        <img src="../uploads/dogs/<?php echo escapar_html($perrito['foto_principal']); ?>" 
                             alt="<?php echo escapar_html($perrito['nombre']); ?>"
                             class="main-photo" id="mainPhoto">
                    <?php else: ?>
                        <div class="photo-placeholder">🐕</div>
                    <?php endif; ?>
                    
                    <?php if (!empty($fotos_adicionales)): ?>
                        <div class="additional-photos">
                            <?php foreach ($fotos_adicionales as $foto): ?>
                                <img src="../uploads/dogs/<?php echo escapar_html($foto['ruta_foto']); ?>" 
                                     alt="<?php echo escapar_html($foto['descripcion'] ?: $perrito['nombre']); ?>"
                                     class="additional-photo"
                                     onclick="changeMainPhoto(this.src)">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Información detallada -->
                <div class="perrito-info">
                    
                    <!-- Información básica -->
                    <div class="info-section">
                        <h2 class="section-title">
                            <span>📋</span>
                            Información Básica
                        </h2>
                        
                        <div class="basic-info-grid">
                            <?php if ($perrito['raza']): ?>
                                <div class="info-item">
                                    <span>🐕</span>
                                    <div>
                                        <strong>Raza:</strong><br>
                                        <?php echo escapar_html($perrito['raza']); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <div class="info-item">
                                <span>📏</span>
                                <div>
                                    <strong>Tamaño:</strong><br>
                                    <?php echo ucfirst($perrito['tamaño']); ?>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <span>⚧</span>
                                <div>
                                    <strong>Sexo:</strong><br>
                                    <?php echo ucfirst($perrito['sexo']); ?>
                                </div>
                            </div>
                            
                            <?php if ($perrito['edad_aproximada']): ?>
                                <div class="info-item">
                                    <span>🎂</span>
                                    <div>
                                        <strong>Edad:</strong><br>
                                        <?php echo $perrito['edad_aproximada']; ?> años
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($perrito['peso']): ?>
                                <div class="info-item">
                                    <span>⚖️</span>
                                    <div>
                                        <strong>Peso:</strong><br>
                                        <?php echo $perrito['peso']; ?> kg
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($perrito['color']): ?>
                                <div class="info-item">
                                    <span>🎨</span>
                                    <div>
                                        <strong>Color:</strong><br>
                                        <?php echo escapar_html($perrito['color']); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Estado médico -->
                        <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 1rem;">
                            <?php if ($perrito['esterilizado']): ?>
                                <span style="background: #d4edda; color: #155724; padding: 0.5rem 1rem; border-radius: 15px; font-size: 0.85rem;">
                                    ✅ Esterilizado
                                </span>
                            <?php endif; ?>
                            <?php if ($perrito['vacunas_completas']): ?>
                                <span style="background: #d4edda; color: #155724; padding: 0.5rem 1rem; border-radius: 15px; font-size: 0.85rem;">
                                    💉 Vacunas completas
                                </span>
                            <?php endif; ?>
                            <?php if ($perrito['microchip']): ?>
                                <span style="background: #d4edda; color: #155724; padding: 0.5rem 1rem; border-radius: 15px; font-size: 0.85rem;">
                                    🔍 Microchip
                                </span>
                            <?php endif; ?>
                            <?php if ($perrito['tipo_especial'] !== 'normal'): ?>
                                <span style="background: #fff3e0; color: #ef6c00; padding: 0.5rem 1rem; border-radius: 15px; font-size: 0.85rem;">
                                    ⭐ <?php echo str_replace('_', ' ', ucfirst($perrito['tipo_especial'])); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Personalidad -->
                    <?php if ($perrito['personalidad']): ?>
                        <div class="info-section">
                            <h2 class="section-title">
                                <span>💖</span>
                                Personalidad
                            </h2>
                            <p style="color: #666; line-height: 1.6; font-size: 1.1rem;">
                                <?php echo nl2br(escapar_html($perrito['personalidad'])); ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <!-- Características de comportamiento -->
                    <?php if ($perrito['perfil_completado']): ?>
                        <div class="info-section">
                            <h2 class="section-title">
                                <span>🎯</span>
                                Características de Comportamiento
                            </h2>
                            
                            <div class="characteristics-grid">
                                <div class="characteristic-item">
                                    <strong>Nivel de Energía</strong>
                                    <div class="characteristic-bar">
                                        <div class="characteristic-fill" style="width: <?php echo ($perrito['nivel_energia'] * 10); ?>%"></div>
                                    </div>
                                    <small><?php echo $perrito['nivel_energia']; ?>/10</small>
                                </div>
                                
                                <div class="characteristic-item">
                                    <strong>Experiencia Necesaria</strong>
                                    <div class="characteristic-bar">
                                        <div class="characteristic-fill" style="width: <?php echo ($perrito['experiencia_necesaria'] * 10); ?>%"></div>
                                    </div>
                                    <small><?php echo $perrito['experiencia_necesaria']; ?>/10</small>
                                </div>
                                
                                <div class="characteristic-item">
                                    <strong>Atención Requerida</strong>
                                    <div class="characteristic-bar">
                                        <div class="characteristic-fill" style="width: <?php echo ($perrito['tiempo_atencion_necesario'] * 10); ?>%"></div>
                                    </div>
                                    <small><?php echo $perrito['tiempo_atencion_necesario']; ?>/10</small>
                                </div>
                                
                                <div class="characteristic-item">
                                    <strong>Sociabilidad</strong>
                                    <div class="characteristic-bar">
                                        <div class="characteristic-fill" style="width: <?php echo ($perrito['sociabilidad'] * 10); ?>%"></div>
                                    </div>
                                    <small><?php echo $perrito['sociabilidad']; ?>/10</small>
                                </div>
                            </div>

                            <!-- Compatibilidades específicas -->
                            <div style="margin-top: 2rem;">
                                <h4 style="color: #5a4a3a; margin-bottom: 1rem;">Compatibilidades:</h4>
                                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                                    <?php if ($perrito['bueno_con_niños']): ?>
                                        <span style="background: #d4edda; color: #155724; padding: 0.5rem 1rem; border-radius: 15px; font-size: 0.85rem;">
                                            👶 Bueno con niños
                                            <?php if ($perrito['edad_minima_niños'] > 0): ?>
                                                (<?php echo $perrito['edad_minima_niños']; ?>+ años)
                                            <?php endif; ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($perrito['bueno_con_otros_perros']): ?>
                                        <span style="background: #d4edda; color: #155724; padding: 0.5rem 1rem; border-radius: 15px; font-size: 0.85rem;">
                                            🐕 Bueno con otros perros
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($perrito['bueno_con_gatos']): ?>
                                        <span style="background: #d4edda; color: #155724; padding: 0.5rem 1rem; border-radius: 15px; font-size: 0.85rem;">
                                            🐱 Bueno con gatos
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($perrito['necesita_jardin']): ?>
                                        <span style="background: #fff3cd; color: #856404; padding: 0.5rem 1rem; border-radius: 15px; font-size: 0.85rem;">
                                            🌳 Necesita jardín
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Historia -->
                    <?php if ($perrito['historia']): ?>
                        <div class="info-section">
                            <h2 class="section-title">
                                <span>📖</span>
                                Mi Historia
                            </h2>
                            <p style="color: #666; line-height: 1.6; font-size: 1.1rem;">
                                <?php echo nl2br(escapar_html($perrito['historia'])); ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <!-- Cuidados especiales -->
                    <?php if ($perrito['cuidados_especiales']): ?>
                        <div class="info-section">
                            <h2 class="section-title">
                                <span>💊</span>
                                Cuidados Especiales
                            </h2>
                            <div style="background: #fff3cd; padding: 1.5rem; border-radius: 10px; border-left: 4px solid #ffc107;">
                                <p style="color: #856404; line-height: 1.6; margin: 0;">
                                    <?php echo nl2br(escapar_html($perrito['cuidados_especiales'])); ?>
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Información del refugio -->
                    <?php if ($perrito['refugio_origen'] || $perrito['admin_nombre']): ?>
                        <div class="info-section">
                            <h2 class="section-title">
                                <span>🏥</span>
                                Información del Refugio
                            </h2>
                            <?php if ($perrito['refugio_origen']): ?>
                                <p><strong>Refugio de origen:</strong> <?php echo escapar_html($perrito['refugio_origen']); ?></p>
                            <?php endif; ?>
                            <?php if ($perrito['admin_nombre']): ?>
                                <p><strong>Gestionado por:</strong> <?php echo escapar_html($perrito['admin_nombre']); ?></p>
                            <?php endif; ?>
                            <?php if ($perrito['fecha_rescate']): ?>
                                <p><strong>Fecha de rescate:</strong> <?php echo formatear_fecha($perrito['fecha_rescate']); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Botones de acción -->
                    <div class="action-buttons">
                        <?php if ($es_adoptante): ?>
                            <a href="solicitar_adopcion.php?perrito_id=<?php echo $perrito['id']; ?>" class="btn btn-primary btn-large">
                                💕 Solicitar Adopción
                            </a>
                            <?php if (!$compatibilidad): ?>
                                <a href="quiz_matching.php" class="btn btn-secondary">
                                    🎯 Hacer Quiz de Compatibilidad
                                </a>
                            <?php endif; ?>
                        <?php elseif (!$usuario): ?>
                            <a href="register.php" class="btn btn-primary btn-large">
                                📝 Registrarse para Adoptar
                            </a>
                            <a href="login.php" class="btn btn-secondary">
                                🔑 Iniciar Sesión
                            </a>
                        <?php endif; ?>
                        
                        <a href="explorar_perritos_simple.php" class="btn btn-outline">
                            🔍 Ver Más Perritos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="../assets/js/main.js"></script>
    <script>
        function changeMainPhoto(src) {
            document.getElementById('mainPhoto').src = src;
        }
    </script>
</body>
</html>
