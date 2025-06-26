<?php
/**
 * PAWTITAS - Explorar Perritos (Versión Simple)
 * Sin matching complejo, solo lista básica
 */

require_once '../config/config.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

SessionManager::start();

$usuario = null;
$es_adoptante = false;

// Verificar si hay usuario logueado
if (usuario_logueado()) {
    $usuario = obtener_usuario_actual();
    $es_adoptante = ($usuario['tipo'] === 'adoptante');
}

// Obtener todos los perritos disponibles con su perfil
$sql = "SELECT p.*, 
               pp.nivel_energia,
               pp.experiencia_necesaria,
               pp.bueno_con_niños,
               pp.bueno_con_otros_perros,
               pp.bueno_con_gatos,
               pp.necesita_jardin,
               pp.sociabilidad,
               pp.independencia,
               CASE 
                   WHEN p.admin_id IS NOT NULL THEN 'Administrador'
                   ELSE 'Sistema'
               END as gestionado_por
        FROM perritos p 
        LEFT JOIN perfil_perrito pp ON p.id = pp.perrito_id
        WHERE p.disponible = 1 
        ORDER BY p.fecha_registro DESC 
        LIMIT 20";

$perritos = consultar_db($sql) ?: [];

$page_title = "Perritos Disponibles - " . APP_NAME;
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
                <?php if ($usuario): ?>
                    <a href="<?php echo $es_adoptante ? 'panel_adoptante.php' : 'panel_admin.php'; ?>" class="btn btn-outline">← Panel</a>
                    <a href="logout.php" class="btn btn-secondary">Cerrar Sesión</a>
                <?php else: ?>
                    <a href="../index.php" class="btn btn-outline">← Inicio</a>
                    <a href="login.php" class="btn btn-primary">Iniciar Sesión</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <!-- Contenido Principal -->
    <main style="padding-top: 100px; padding-bottom: 50px;">
        <div class="container">
            
            <!-- Título -->
            <div style="text-align: center; margin-bottom: 3rem;">
                <h1 style="color: #5a4a3a; margin-bottom: 1rem;">
                    🐕 Perritos Disponibles para Adopción
                </h1>
                <p style="color: #8d6e63; font-size: 1.1rem;">
                    Encuentra a tu nuevo mejor amigo
                </p>
            </div>

            <!-- Información para usuarios no logueados -->
            <?php if (!$usuario): ?>
                <div style="background: #e3f2fd; padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem; text-align: center; border-left: 4px solid #2196f3;">
                    <h3 style="color: #1976d2; margin-bottom: 0.5rem;">💡 ¿Te interesa adoptar?</h3>
                    <p style="color: #1976d2; margin-bottom: 1rem;">
                        Regístrate para poder enviar solicitudes de adopción y gestionar tu proceso
                    </p>
                    <a href="register.php" class="btn btn-primary">Registrarse Gratis</a>
                </div>
            <?php endif; ?>

            <!-- Lista de Perritos -->
            <?php if (!empty($perritos)): ?>
                <div class="dogs-grid">
                    <?php foreach ($perritos as $perrito): ?>
                        <div class="dog-card">
                            <!-- Imagen del perrito -->
                            <?php if ($perrito['foto_principal']): ?>
                                <img src="../uploads/dogs/<?php echo escapar_html($perrito['foto_principal']); ?>" 
                                     alt="<?php echo escapar_html($perrito['nombre']); ?>"
                                     loading="lazy">
                            <?php else: ?>
                                <div class="dog-placeholder">🐕</div>
                            <?php endif; ?>
                            
                            <!-- Información del perrito -->
                            <div class="dog-info">
                                <h3><?php echo escapar_html($perrito['nombre']); ?></h3>
                                
                                <div style="display: flex; gap: 1rem; margin-bottom: 0.5rem; font-size: 0.9rem; color: #666;">
                                    <?php if ($perrito['raza']): ?>
                                        <span>🐕 <?php echo escapar_html($perrito['raza']); ?></span>
                                    <?php endif; ?>
                                    <?php if ($perrito['edad_aproximada']): ?>
                                        <span>🎂 <?php echo $perrito['edad_aproximada']; ?> años</span>
                                    <?php endif; ?>
                                </div>
                                
                                <div style="display: flex; gap: 1rem; margin-bottom: 0.5rem; font-size: 0.9rem; color: #666;">
                                    <span>📏 <?php echo ucfirst($perrito['tamaño'] ?? 'Mediano'); ?></span>
                                    <?php if (isset($perrito['nivel_energia'])): ?>
                                        <span>⚡ Energía: <?php echo $perrito['nivel_energia']; ?>/10</span>
                                    <?php endif; ?>
                                    <?php if ($perrito['sexo']): ?>
                                        <span><?php echo $perrito['sexo'] === 'macho' ? '♂️' : '♀️'; ?> <?php echo ucfirst($perrito['sexo']); ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($perrito['personalidad']): ?>
                                    <p class="dog-personality" style="font-size: 0.85rem; color: #555; margin-bottom: 1rem; line-height: 1.4;">
                                        <?php echo escapar_html(cortar_texto($perrito['personalidad'], 100)); ?>
                                    </p>
                                <?php endif; ?>
                                
                                <!-- Características especiales -->
                                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1rem;">
                                    <?php if (isset($perrito['bueno_con_niños']) && $perrito['bueno_con_niños']): ?>
                                        <span style="background: #e8f5e8; color: #2e7d32; padding: 2px 8px; border-radius: 10px; font-size: 0.75rem;">👶 Bueno con niños</span>
                                    <?php endif; ?>
                                    <?php if (isset($perrito['bueno_con_otros_perros']) && $perrito['bueno_con_otros_perros']): ?>
                                        <span style="background: #e3f2fd; color: #1976d2; padding: 2px 8px; border-radius: 10px; font-size: 0.75rem;">🐕 Sociable</span>
                                    <?php endif; ?>
                                    <?php if (isset($perrito['bueno_con_gatos']) && $perrito['bueno_con_gatos']): ?>
                                        <span style="background: #f3e5f5; color: #7b1fa2; padding: 2px 8px; border-radius: 10px; font-size: 0.75rem;">🐱 Bueno con gatos</span>
                                    <?php endif; ?>
                                    <?php if ($perrito['tipo_especial'] !== 'normal'): ?>
                                        <span style="background: #fff3e0; color: #ef6c00; padding: 2px 8px; border-radius: 10px; font-size: 0.75rem;">
                                            ⭐ <?php echo str_replace('_', ' ', ucfirst($perrito['tipo_especial'] ?? '')); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (isset($perrito['nivel_energia']) && $perrito['nivel_energia'] > 7): ?>
                                        <span style="background: #ffebee; color: #c62828; padding: 2px 8px; border-radius: 10px; font-size: 0.75rem;">⚡ Alta energía</span>
                                    <?php elseif (isset($perrito['nivel_energia']) && $perrito['nivel_energia'] < 4): ?>
                                        <span style="background: #e8f5e8; color: #2e7d32; padding: 2px 8px; border-radius: 10px; font-size: 0.75rem;">😌 Tranquilo</span>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Botones de acción -->
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="ver_perrito.php?id=<?php echo $perrito['id']; ?>" class="btn btn-primary" style="flex: 1;">
                                        Ver Detalles
                                    </a>
                                    <?php if ($es_adoptante): ?>
                                        <a href="solicitar_adopcion.php?perrito_id=<?php echo $perrito['id']; ?>" class="btn btn-secondary">
                                            💕 Adoptar
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Paginación simple (para futuro) -->
                <div style="text-align: center; margin-top: 3rem;">
                    <p style="color: #666;">Mostrando <?php echo count($perritos); ?> perritos disponibles</p>
                </div>
                
            <?php else: ?>
                <div style="text-align: center; padding: 3rem; color: #666;">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">😔</div>
                    <h3>No hay perritos disponibles en este momento</h3>
                    <p>Vuelve pronto, siempre estamos recibiendo nuevos perritos que necesitan hogar.</p>
                    <?php if ($usuario && $usuario['tipo'] === 'admin'): ?>
                        <div style="margin-top: 2rem;">
                            <a href="agregar_perrito.php" class="btn btn-primary">➕ Agregar Primer Perrito</a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- CTA para usuarios no logueados -->
            <?php if (!$usuario): ?>
                <div style="background: #fce4ec; padding: 2rem; border-radius: 15px; text-align: center; margin-top: 3rem;">
                    <h3 style="color: #c2185b; margin-bottom: 1rem;">💖 ¿Te enamoraste de algún perrito?</h3>
                    <p style="color: #c2185b; margin-bottom: 1.5rem;">
                        Regístrate para poder solicitar adopciones y formar parte de nuestra comunidad
                    </p>
                    <a href="register.php" class="btn btn-primary btn-large">
                        Registrarse Ahora
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="../assets/js/main.js"></script>
</body>
</html>
