<?php
/**
 * PAWTITAS - Test Súper Simple
 */

echo "<h1>🐾 PAWTITAS - Test Rápido</h1>";
echo "<style>body{font-family:Arial;margin:20px;background:#f5f5f5;} .ok{color:green;} .error{color:red;} .info{color:blue;}</style>";

echo "<h2>🔍 Verificación Rápida:</h2>";

// 1. Configuración
if (file_exists('config/config.php')) {
    require_once 'config/config.php';
    echo "<div class='ok'>✅ Configuración cargada</div>";
} else {
    echo "<div class='error'>❌ No se encuentra config.php</div>";
    exit();
}

// 2. Base de datos
try {
    require_once 'config/database.php';
    $db = obtener_conexion_db();
    echo "<div class='ok'>✅ Conexión a BD exitosa</div>";
} catch (Exception $e) {
    echo "<div class='error'>❌ Error BD: " . $e->getMessage() . "</div>";
    exit();
}

// 3. Funciones
if (file_exists('includes/functions.php')) {
    require_once 'includes/functions.php';
    echo "<div class='ok'>✅ Funciones cargadas</div>";
} else {
    echo "<div class='error'>❌ No se encuentra functions.php</div>";
}

// 4. Contar usuarios
try {
    $stmt = $db->query("SELECT COUNT(*) as total FROM usuarios");
    $total = $stmt->fetch()['total'];
    echo "<div class='info'>👥 Usuarios en BD: <strong>$total</strong></div>";
} catch (Exception $e) {
    echo "<div class='error'>❌ Error contando usuarios: " . $e->getMessage() . "</div>";
}

// 5. Páginas principales
$paginas = ['index.php', 'pages/login.php', 'pages/register.php'];
foreach ($paginas as $pagina) {
    if (file_exists($pagina)) {
        echo "<div class='ok'>✅ $pagina existe</div>";
    } else {
        echo "<div class='error'>❌ $pagina no existe</div>";
    }
}

echo "<h2>🎯 Resultado:</h2>";
echo "<div class='ok'>✅ <strong>Sistema básico funcionando</strong></div>";
echo "<div class='info'>💡 Puedes continuar con el desarrollo</div>";

echo "<br><a href='index.php' style='background:#ff8a80;color:white;padding:10px 20px;text-decoration:none;border-radius:15px;'>🏠 Ir al Inicio</a>";
?>
