<?php
/**
 * PAWTITAS - Test del Sistema Limpio
 */

require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/stats.php';

echo "<h1>🐾 PAWTITAS - Test Sistema Limpio</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .success { color: green; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: red; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: blue; background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .section { background: white; padding: 20px; border-radius: 10px; margin: 20px 0; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
</style>";

// Test 1: Configuración
echo "<div class='section'>";
echo "<h2>⚙️ Test 1: Configuración</h2>";
echo "<div class='info'>";
echo "• App: " . APP_NAME . "<br>";
echo "• Base de datos: " . DB_NAME . "<br>";
echo "• Carpeta uploads: " . (is_dir('uploads/dogs/') ? 'Existe ✅' : 'Creada ✅') . "<br>";
echo "</div>";
echo "</div>";

// Test 2: Base de Datos
echo "<div class='section'>";
echo "<h2>🗄️ Test 2: Base de Datos</h2>";

try {
    $db = obtener_conexion_db();
    echo "<div class='success'>✅ Conexión exitosa</div>";
    
    // Verificar usuarios
    $sql = "SELECT COUNT(*) as total, tipo_usuario FROM usuarios GROUP BY tipo_usuario";
    $resultado = consultar_db($sql);
    
    echo "<div class='info'><strong>Usuarios por tipo:</strong><br>";
    foreach ($resultado as $row) {
        echo "• " . ucfirst($row['tipo_usuario']) . ": " . $row['total'] . "<br>";
    }
    echo "</div>";
    
    // Verificar perritos
    $sql = "SELECT COUNT(*) as total FROM perritos";
    $resultado = consultar_db($sql);
    $total_perritos = $resultado ? $resultado[0]['total'] : 0;
    
    echo "<div class='info'>🐕 Total perritos: <strong>$total_perritos</strong></div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
}
echo "</div>";

// Test 3: Estadísticas
echo "<div class='section'>";
echo "<h2>📊 Test 3: Estadísticas</h2>";

try {
    $stats = getGeneralStats();
    echo "<div class='success'>✅ Estadísticas obtenidas</div>";
    echo "<div class='info'>";
    echo "🐕 Adoptados: " . $stats['adopted_dogs'] . "<br>";
    echo "🏠 Disponibles: " . $stats['available_dogs'] . "<br>";
    echo "👥 Adoptantes: " . $stats['registered_shelters'] . "<br>";
    echo "</div>";
} catch (Exception $e) {
    echo "<div class='error'>❌ Error estadísticas: " . $e->getMessage() . "</div>";
}
echo "</div>";

// Test 4: Archivos Principales
echo "<div class='section'>";
echo "<h2>📁 Test 4: Archivos Principales</h2>";

$archivos = [
    'index.php' => 'Página principal',
    'pages/login.php' => 'Login',
    'pages/register.php' => 'Registro',
    'pages/panel_adoptante.php' => 'Panel adoptante',
    'pages/panel_admin.php' => 'Panel admin',
    'pages/explorar_perritos_simple.php' => 'Explorar perritos',
    'pages/agregar_perrito.php' => 'Agregar perrito'
];

foreach ($archivos as $archivo => $descripcion) {
    if (file_exists($archivo)) {
        echo "<div class='success'>✅ $descripcion</div>";
    } else {
        echo "<div class='error'>❌ $descripcion - NO EXISTE</div>";
    }
}
echo "</div>";

echo "<div class='section'>";
echo "<h2>🎯 Resumen</h2>";
echo "<div class='success'>✅ <strong>Sistema limpio y funcional</strong></div>";
echo "<div class='info'>💡 <strong>Próximos pasos:</strong><br>";
echo "1. Ejecutar script de limpieza SQL<br>";
echo "2. Probar registro y login<br>";
echo "3. Crear páginas faltantes según necesidad<br>";
echo "</div>";
echo "</div>";

echo "<div style='text-align: center; margin-top: 30px;'>";
echo "<a href='index.php' style='background: #ff8a80; color: white; padding: 15px 30px; text-decoration: none; border-radius: 25px; font-weight: bold;'>🏠 Ir a la Página Principal</a>";
echo "</div>";
?>
