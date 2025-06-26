<?php
/**
 * PAWTITAS - Página Principal (Limpia)
 */

require_once 'config/config.php';
require_once 'includes/session.php';
require_once 'includes/functions.php';
require_once 'includes/stats.php';

SessionManager::start();

// Redirigir usuarios ya logueados a su panel
if (usuario_logueado()) {
    $usuario = obtener_usuario_actual();
    $redirect_url = ($usuario['tipo'] === 'admin') ? 'pages/panel_admin.php' : 'pages/panel_adoptante.php';
    header("Location: $redirect_url");
    exit();
}

// Obtener datos dinámicos
$stats = getGeneralStats();
$featured_dogs = getFeaturedDogs(3);

$page_title = "Adopta con el corazón - " . APP_NAME;
$page_description = "Plataforma de adopción de perritos que conecta adoptantes responsables con perros que necesitan un hogar.";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo escapar_html($page_title); ?></title>
    <meta name="description" content="<?php echo escapar_html($page_description); ?>">
    <meta name="keywords" content="adopción, perros, mascotas, adoptar perrito, PAWTITAS">
    
    <link rel="stylesheet" href="assets/css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Elementos decorativos de fondo -->
    <div class="background-decorations">
        <div class="paw-print paw-1">🐾</div>
        <div class="paw-print paw-2">🐾</div>
        <div class="paw-print paw-3">🐾</div>
        <div class="paw-print paw-4">🐾</div>
        <div class="paw-print paw-5">🐾</div>
        <div class="bone bone-1">🦴</div>
        <div class="bone bone-2">🦴</div>
        <div class="heart heart-1">💕</div>
        <div class="heart heart-2">💕</div>
    </div>

    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <div class="logo">
                <div class="logo-icon">🐾</div>
                <span class="logo-text"><?php echo APP_NAME; ?></span>
            </div>
            <div class="nav-buttons">
                <a href="pages/login.php" class="btn btn-outline">Iniciar Sesión</a>
                <a href="pages/register.php" class="btn btn-primary">Registrarse</a>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <main class="hero">
        <div class="hero-content">
            <div class="hero-text">
                <h1 class="hero-title">
                    <span class="title-line">Adopta con</span>
                    <span class="title-heart">el corazón 💖</span>
                </h1>
                <p class="hero-subtitle">
                    Conectamos patitas con hogares llenos de amor. 
                    Encuentra a tu compañero perfecto y cambia dos vidas para siempre.
                </p>
                <div class="hero-buttons">
                    <a href="pages/explorar_perritos_simple.php" class="btn btn-large btn-primary">
                        <span class="btn-icon">🐕</span>
                        Ver Perritos Disponibles
                    </a>
                    <a href="#about" class="btn btn-large btn-secondary">
                        <span class="btn-icon">ℹ️</span>
                        Conoce Más
                    </a>
                </div>
            </div>
            <div class="hero-image">
                <div class="floating-card">
                    <div class="dog-illustration">
                        <div class="dog-face">
                            <div class="dog-ears">
                                <div class="ear left-ear"></div>
                                <div class="ear right-ear"></div>
                            </div>
                            <div class="dog-head">
                                <div class="dog-eyes">
                                    <div class="eye left-eye"></div>
                                    <div class="eye right-eye"></div>
                                </div>
                                <div class="dog-nose"></div>
                                <div class="dog-mouth"></div>
                            </div>
                        </div>
                        <div class="dog-body"></div>
                    </div>
                    <div class="card-text">
                        <h3>¡Hola! Soy Luna 🌙</h3>
                        <p>Busco una familia que me ame</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- About Section -->
    <section id="about" class="about">
        <div class="container">
            <h2 class="section-title">¿Qué es <?php echo APP_NAME; ?>?</h2>
            <div class="about-grid">
                <div class="about-card">
                    <div class="card-icon">🏠</div>
                    <h3>Para Adoptantes</h3>
                    <p>Regístrate y encuentra al perrito perfecto para tu estilo de vida y hogar.</p>
                </div>
                <div class="about-card">
                    <div class="card-icon">🏥</div>
                    <h3>Gestión Profesional</h3>
                    <p>Nuestros administradores gestionan cada perrito con cuidado y profesionalismo.</p>
                </div>
                <div class="about-card">
                    <div class="card-icon">🦮</div>
                    <h3>Apoyo Especial</h3>
                    <p>Perritos entrenados para apoyo emocional y como guías para personas con discapacidad.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number" data-target="<?php echo $stats['adopted_dogs']; ?>"><?php echo $stats['adopted_dogs']; ?></div>
                    <div class="stat-label">Perritos Adoptados</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" data-target="<?php echo $stats['happy_families']; ?>"><?php echo $stats['happy_families']; ?></div>
                    <div class="stat-label">Familias Felices</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" data-target="<?php echo $stats['registered_shelters']; ?>"><?php echo $stats['registered_shelters']; ?></div>
                    <div class="stat-label">Administradores</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" data-target="<?php echo $stats['special_dogs']; ?>"><?php echo $stats['special_dogs']; ?></div>
                    <div class="stat-label">Perritos Especiales</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Dogs Section -->
    <?php if (!empty($featured_dogs)): ?>
    <section class="featured-dogs">
        <div class="container">
            <h2 class="section-title">Perritos Esperando un Hogar</h2>
            <div class="dogs-grid">
                <?php foreach ($featured_dogs as $dog): ?>
                    <div class="dog-card">
                        <?php if ($dog['foto_principal']): ?>
                            <img src="uploads/dogs/<?php echo escapar_html($dog['foto_principal']); ?>" 
                                 alt="<?php echo escapar_html($dog['nombre']); ?>"
                                 loading="lazy">
                        <?php else: ?>
                            <div class="dog-placeholder">🐕</div>
                        <?php endif; ?>
                        <div class="dog-info">
                            <h3><?php echo escapar_html($dog['nombre']); ?></h3>
                            <p class="dog-breed"><?php echo escapar_html($dog['raza'] ?? 'Mestizo'); ?></p>
                            <?php if ($dog['edad_aproximada']): ?>
                                <p class="dog-age"><?php echo $dog['edad_aproximada']; ?> años</p>
                            <?php endif; ?>
                            <?php if ($dog['personalidad']): ?>
                                <p class="dog-personality"><?php echo escapar_html(cortar_texto($dog['personalidad'], 100)); ?></p>
                            <?php endif; ?>
                            <a href="pages/explorar_perritos_simple.php" class="btn btn-primary">
                                Conocer más
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center" style="margin-top: 2rem;">
                <a href="pages/explorar_perritos_simple.php" class="btn btn-large btn-outline">
                    Ver Todos los Perritos
                </a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <div class="cta-content">
                <h2>¿Listo para cambiar una vida?</h2>
                <p>Únete a nuestra comunidad y ayuda a más perritos a encontrar su hogar perfecto.</p>
                <div class="cta-buttons">
                    <a href="pages/register.php" class="btn btn-large btn-primary">
                        Comenzar Ahora
                    </a>
                    <a href="pages/explorar_perritos_simple.php" class="btn btn-large btn-outline">
                        Ver Perritos
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <div class="logo">
                        <div class="logo-icon">🐾</div>
                        <span class="logo-text"><?php echo APP_NAME; ?></span>
                    </div>
                    <p>Conectando corazones con patitas desde <?php echo date('Y'); ?></p>
                </div>
                <div class="footer-links">
                    <div class="link-group">
                        <h4>Adopción</h4>
                        <a href="pages/explorar_perritos_simple.php">Ver Perritos</a>
                        <a href="pages/register.php">Registrarse</a>
                        <a href="pages/login.php">Iniciar Sesión</a>
                    </div>
                    <div class="link-group">
                        <h4>Información</h4>
                        <a href="#about">Acerca de</a>
                        <a href="pages/explorar_perritos_simple.php">Perritos Disponibles</a>
                        <a href="pages/register.php">Únete</a>
                    </div>
                    <div class="link-group">
                        <h4>Ayuda</h4>
                        <a href="pages/register.php">Cómo Adoptar</a>
                        <a href="pages/login.php">Mi Cuenta</a>
                        <a href="#about">Contacto</a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. Hecho con 💖 para conectar corazones.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>
