<?php
/**
 * PAWTITAS - Resultados del Matching
 * Muestra los perritos más compatibles con el adoptante
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

// Verificar que tenga perfil completo
if (!usuario_tiene_perfil_matching($usuario['id'])) {
    redirigir_con_mensaje('quiz_matching.php', 'Debes completar el quiz primero', 'info');
}

// Obtener matches
$matching_system = obtener_matching_system();
$resultado_matches = $matching_system->obtener_mejores_matches($usuario['id'], 20);

if (!$resultado_matches['exito']) {
    $matches = [];
    $mensaje_error = $resultado_matches['mensaje'];
} else {
    $matches = $resultado_matches['matches'];
    $mensaje_error = '';
}

// Obtener estadísticas del usuario
$stats = obtener_estadisticas_matching($usuario['id']);

$page_title = "Tus Matches Perfectos - " . APP_NAME;
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
        .match-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            margin-bottom: 2rem;
        }
        .match-card:hover {
            transform: translateY(-5px);
        }
        .match-header {
            padding: 1.5rem;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 1px solid #dee2e6;
        }
        .compatibility-score {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }
        .score-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
            color: white;
        }
        .score-excellent { background: linear-gradient(135deg, #28a745, #20c997); }
        .score-good { background: linear-gradient(135deg, #17a2b8, #6f42c1); }
        .score-moderate { background: linear-gradient(135deg, #ffc107, #fd7e14); }
        .score-low { background: linear-gradient(135deg, #dc3545, #e83e8c); }
        .match-content {
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 1.5rem;
            padding: 1.5rem;
        }
        .dog-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
        }
        .dog-placeholder {
            width: 100%;
            height: 200px;
            background: #f8f9fa;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: #dee2e6;
        }
        .match-details {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .dog-basic-info {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }
        .info-tag {
            background: #e9ecef;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.85rem;
            color: #495057;
        }
        .compatibility-details {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
        }
        .compatibility-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 0.5rem;
            margin-top: 0.5rem;
        }
        .compatibility-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.25rem 0;
            font-size: 0.85rem;
        }
        .compatibility-bar {
            width: 60px;
            height: 6px;
            background: #e9ecef;
            border-radius: 3px;
            overflow: hidden;
        }
        .compatibility-fill {
            height: 100%;
            transition: width 0.3s ease;
        }
        .fill-excellent { background: #28a745; }
        .fill-good { background: #17a2b8; }
        .fill-moderate { background: #ffc107; }
        .fill-low { background: #dc3545; }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .recommendation-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            margin-left: 1rem;
        }
        .rec-excellent { background: #d4edda; color: #155724; }
        .rec-good { background: #d1ecf1; color: #0c5460; }
        .rec-moderate { background: #fff3cd; color: #856404; }
        .rec-low { background: #f8d7da; color: #721c24; }
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
                <a href="quiz_matching.php" class="btn btn-secondary">Rehacer Quiz</a>
                <a href="logout.php" class="btn btn-outline">Cerrar Sesión</a>
            </div>
        </nav>
    </header>

    <main style="padding-top: 100px; padding-bottom: 50px;">
        <div class="container">
            
            <!-- Título y estadísticas -->
            <div style="text-align: center; margin-bottom: 3rem;">
                <h1 style="color: #5a4a3a; margin-bottom: 1rem;">
                    🎯 Tus Matches Perfectos
                </h1>
                <p style="color: #8d6e63; font-size: 1.1rem;">
                    Basado en tu quiz de compatibilidad, estos son los perritos más adecuados para ti
                </p>
            </div>

            <!-- Estadísticas del matching -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div style="font-size: 2rem; color: #ff8a80; margin-bottom: 0.5rem;"><?php echo $stats['total_matches']; ?></div>
                    <div style="color: #666;">Perritos Evaluados</div>
                </div>
                <div class="stat-card">
                    <div style="font-size: 2rem; color: #28a745; margin-bottom: 0.5rem;"><?php echo $stats['mejor_match']; ?>%</div>
                    <div style="color: #666;">Mejor Compatibilidad</div>
                </div>
                <div class="stat-card">
                    <div style="font-size: 2rem; color: #17a2b8; margin-bottom: 0.5rem;"><?php echo $stats['matches_excelentes']; ?></div>
                    <div style="color: #666;">Matches Excelentes</div>
                </div>
                <div class="stat-card">
                    <div style="font-size: 2rem; color: #6f42c1; margin-bottom: 0.5rem;"><?php echo $stats['matches_buenos']; ?></div>
                    <div style="color: #666;">Matches Buenos</div>
                </div>
            </div>

            <!-- Resultados del matching -->
            <?php if ($mensaje_error): ?>
                <div class="message error" style="text-align: center; margin: 3rem 0;">
                    <h3>😔 <?php echo escapar_html($mensaje_error); ?></h3>
                    <p>Intenta completar el quiz nuevamente o contacta al administrador.</p>
                    <a href="quiz_matching.php" class="btn btn-primary">Rehacer Quiz</a>
                </div>
            <?php elseif (empty($matches)): ?>
                <div style="text-align: center; margin: 3rem 0; padding: 3rem; background: white; border-radius: 15px;">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">😔</div>
                    <h3>No encontramos matches disponibles</h3>
                    <p style="color: #666; margin: 1rem 0;">
                        Actualmente no hay perritos que coincidan con tu perfil, pero no te preocupes.
                        Nuevos perritos llegan constantemente al sistema.
                    </p>
                    <div style="margin-top: 2rem;">
                        <a href="explorar_perritos_simple.php" class="btn btn-primary">Ver Todos los Perritos</a>
                        <a href="quiz_matching.php" class="btn btn-outline">Ajustar Preferencias</a>
                    </div>
                </div>
            <?php else: ?>
                
                <!-- Lista de matches -->
                <?php foreach ($matches as $index => $match): 
                    $perrito = $match['perrito'];
                    $compatibilidad = $match['compatibilidad'];
                    $recomendacion = $match['recomendacion'];
                    $incompatibilidades = $match['incompatibilidades'];
                    
                    // Determinar clase de puntuación
                    if ($compatibilidad >= 85) {
                        $score_class = 'score-excellent';
                        $rec_class = 'rec-excellent';
                        $rec_text = 'Match Excelente';
                    } elseif ($compatibilidad >= 70) {
                        $score_class = 'score-good';
                        $rec_class = 'rec-good';
                        $rec_text = 'Buen Match';
                    } elseif ($compatibilidad >= 50) {
                        $score_class = 'score-moderate';
                        $rec_class = 'rec-moderate';
                        $rec_text = 'Match Moderado';
                    } else {
                        $score_class = 'score-low';
                        $rec_class = 'rec-low';
                        $rec_text = 'Match Bajo';
                    }
                ?>
                    <div class="match-card">
                        <!-- Header con puntuación -->
                        <div class="match-header">
                            <div class="compatibility-score">
                                <div>
                                    <h3 style="margin: 0; color: #5a4a3a;">
                                        <?php echo escapar_html($perrito['nombre']); ?>
                                        <?php if ($index === 0): ?>
                                            <span style="color: #ff8a80;">👑 Tu Mejor Match</span>
                                        <?php endif; ?>
                                    </h3>
                                    <div style="display: flex; align-items: center; margin-top: 0.5rem;">
                                        <span style="color: #666;">Compatibilidad:</span>
                                        <span class="recommendation-badge <?php echo $rec_class; ?>">
                                            <?php echo $rec_text; ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="score-circle <?php echo $score_class; ?>">
                                    <?php echo round($compatibilidad); ?>%
                                </div>
                            </div>
                            
                            <?php if (!empty($incompatibilidades)): ?>
                                <div style="background: #fff3cd; padding: 0.75rem; border-radius: 8px; border-left: 4px solid #ffc107;">
                                    <strong style="color: #856404;">⚠️ Consideraciones importantes:</strong>
                                    <ul style="margin: 0.5rem 0 0 1rem; color: #856404;">
                                        <?php if (isset($incompatibilidades['tamaño'])): ?>
                                            <li>El tamaño no coincide con tus preferencias</li>
                                        <?php endif; ?>
                                        <?php if (isset($incompatibilidades['niños'])): ?>
                                            <li>Puede no ser ideal para niños</li>
                                        <?php endif; ?>
                                        <?php if (isset($incompatibilidades['jardin'])): ?>
                                            <li>Necesita jardín para ser feliz</li>
                                        <?php endif; ?>
                                        <?php if (isset($incompatibilidades['otros_perros'])): ?>
                                            <li>Puede tener problemas con otros perros</li>
                                        <?php endif; ?>
                                        <?php if (isset($incompatibilidades['gatos'])): ?>
                                            <li>Puede tener problemas con gatos</li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Contenido principal -->
                        <div class="match-content">
                            <!-- Imagen del perrito -->
                            <div>
                                <?php if ($perrito['foto_principal']): ?>
                                    <img src="../uploads/dogs/<?php echo escapar_html($perrito['foto_principal']); ?>" 
                                         alt="<?php echo escapar_html($perrito['nombre']); ?>"
                                         class="dog-image">
                                <?php else: ?>
                                    <div class="dog-placeholder">🐕</div>
                                <?php endif; ?>
                            </div>

                            <!-- Detalles del perrito -->
                            <div class="match-details">
                                <!-- Información básica -->
                                <div class="dog-basic-info">
                                    <?php if ($perrito['raza']): ?>
                                        <span class="info-tag">🐕 <?php echo escapar_html($perrito['raza']); ?></span>
                                    <?php endif; ?>
                                    <?php if ($perrito['edad_aproximada']): ?>
                                        <span class="info-tag">🎂 <?php echo $perrito['edad_aproximada']; ?> años</span>
                                    <?php endif; ?>
                                    <span class="info-tag">📏 <?php echo ucfirst($perrito['tamaño']); ?></span>
                                    <span class="info-tag">⚧ <?php echo ucfirst($perrito['sexo']); ?></span>
                                    <?php if ($perrito['tipo_especial'] !== 'normal'): ?>
                                        <span class="info-tag" style="background: #fff3e0; color: #ef6c00;">
                                            ⭐ <?php echo str_replace('_', ' ', ucfirst($perrito['tipo_especial'])); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <!-- Personalidad -->
                                <?php if ($perrito['personalidad']): ?>
                                    <div>
                                        <strong style="color: #5a4a3a;">Personalidad:</strong>
                                        <p style="margin: 0.5rem 0; color: #666; line-height: 1.5;">
                                            <?php echo escapar_html($perrito['personalidad']); ?>
                                        </p>
                                    </div>
                                <?php endif; ?>

                                <!-- Botones de acción -->
                                <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                                    <a href="ver_perrito.php?id=<?php echo $perrito['id']; ?>" class="btn btn-primary">
                                        👀 Ver Detalles Completos
                                    </a>
                                    <a href="solicitar_adopcion.php?perrito_id=<?php echo $perrito['id']; ?>" class="btn btn-secondary">
                                        💕 Solicitar Adopción
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Botón para ver más -->
                <div style="text-align: center; margin-top: 3rem;">
                    <a href="explorar_perritos_simple.php" class="btn btn-outline btn-large">
                        Ver Todos los Perritos Disponibles
                    </a>
                </div>
            <?php endif; ?>

            <!-- Información adicional -->
            <div style="background: #f8f9fa; padding: 2rem; border-radius: 15px; margin-top: 3rem;">
                <h3 style="color: #5a4a3a; margin-bottom: 1rem;">💡 ¿Cómo funciona el matching?</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                    <div>
                        <h4 style="color: #ff8a80; margin-bottom: 0.5rem;">🎯 Compatibilidad de Personalidad</h4>
                        <p style="color: #666; font-size: 0.9rem;">
                            Comparamos tu nivel de actividad, experiencia y preferencias con las características de cada perrito.
                        </p>
                    </div>
                    <div>
                        <h4 style="color: #ff8a80; margin-bottom: 0.5rem;">🏠 Compatibilidad de Hogar</h4>
                        <p style="color: #666; font-size: 0.9rem;">
                            Verificamos que tu tipo de vivienda, presencia de niños y otras mascotas sean compatibles.
                        </p>
                    </div>
                    <div>
                        <h4 style="color: #ff8a80; margin-bottom: 0.5rem;">⭐ Puntuación Final</h4>
                        <p style="color: #666; font-size: 0.9rem;">
                            Calculamos un porcentaje de compatibilidad y te mostramos los mejores matches primero.
                        </p>
                    </div>
                </div>
                
                <div style="text-align: center; margin-top: 2rem;">
                    <a href="quiz_matching.php" class="btn btn-outline">
                        🔄 Actualizar Mis Preferencias
                    </a>
                </div>
            </div>
        </div>
    </main>

    <script src="../assets/js/main.js"></script>
</body>
</html>
