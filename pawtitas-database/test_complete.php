<?php
/**
 * PAWTITAS - Test Completo del Sistema
 */

require_once 'config/config.php';
require_once 'includes/session.php';
require_once 'includes/functions.php';
require_once 'includes/stats.php';

echo "<h1>🐾 PAWTITAS - Test Completo del Sistema</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .success { color: green; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: red; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: blue; background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .warning { color: orange; background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .section { background: white; padding: 20px; border-radius: 10px; margin: 20px 0; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    h2 { color: #ff8a80; border-bottom: 2px solid #ff8a80; padding-bottom: 10px; }
</style>";

$errores = 0;
$exitos = 0;

// Test 1: Configuración
echo "<div class='section'>";
echo "<h2>⚙️ Test 1: Configuración del Sistema</h2>";

echo "<div class='info'>";
echo "<strong>Configuración actual:</strong><br>";
echo "• App: " . APP_NAME . " v" . APP_VERSION . "<br>";
echo "• Base de datos: " . DB_NAME . "<br>";
echo "• Host: " . DB_HOST . "<br>";
echo "• Debug: " . (DEBUG_MODE ? 'Activado' : 'Desactivado') . "<br>";
echo "• Timezone: " . APP_TIMEZONE . "<br>";
echo "</div>";

if (defined('APP_NAME')) {
    echo "<div class='success'>✅ Configuración cargada correctamente</div>";
    $exitos++;
} else {
    echo "<div class='error'>❌ Error en configuración</div>";
    $errores++;
}
echo "</div>";

// Test 2: Base de Datos
echo "<div class='section'>";
echo "<h2>🗄️ Test 2: Conexión a Base de Datos</h2>";

try {
    $db = obtener_conexion_db();
    echo "<div class='success'>✅ Conexión a base de datos exitosa</div>";
    $exitos++;
    
    // Verificar tablas
    $tablas_requeridas = ['usuarios', 'perritos', 'curriculum_emocional', 'solicitudes_adopcion'];
    $stmt = $db->query("SHOW TABLES");
    $tablas_existentes = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tablas_requeridas as $tabla) {
        if (in_array($tabla, $tablas_existentes)) {
            echo "<div class='success'>✅ Tabla '$tabla' existe</div>";
            $exitos++;
        } else {
            echo "<div class='error'>❌ Tabla '$tabla' no existe</div>";
            $errores++;
        }
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error de conexión: " . $e->getMessage() . "</div>";
    $errores++;
}
echo "</div>";

// Test 3: Funciones Principales
echo "<div class='section'>";
echo "<h2>🔧 Test 3: Funciones Principales</h2>";

$funciones_test = [
    'limpiar_datos' => 'Limpiar datos de entrada',
    'es_email_valido' => 'Validar email',
    'es_telefono_valido' => 'Validar teléfono',
    'escapar_html' => 'Escapar HTML',
    'formatear_fecha' => 'Formatear fechas',
    'consultar_db' => 'Consultar base de datos',
    'getGeneralStats' => 'Obtener estadísticas'
];

foreach ($funciones_test as $funcion => $descripcion) {
    if (function_exists($funcion)) {
        echo "<div class='success'>✅ $descripcion ($funcion)</div>";
        $exitos++;
    } else {
        echo "<div class='error'>❌ $descripcion ($funcion) - NO EXISTE</div>";
        $errores++;
    }
}
echo "</div>";

// Test 4: Datos de Ejemplo
echo "<div class='section'>";
echo "<h2>📊 Test 4: Datos en el Sistema</h2>";

try {
    $stats = getGeneralStats();
    
    echo "<div class='info'>";
    echo "<strong>Estadísticas actuales:</strong><br>";
    echo "🐕 Perritos adoptados: " . $stats['adopted_dogs'] . "<br>";
    echo "🏠 Perritos disponibles: " . $stats['available_dogs'] . "<br>";
    echo "🏥 Refugios: " . $stats['registered_shelters'] . "<br>";
    echo "👨‍👩‍👧‍👦 Familias felices: " . $stats['happy_families'] . "<br>";
    echo "</div>";
    
    if ($stats['available_dogs'] > 0) {
        echo "<div class='success'>✅ Hay perritos disponibles para adopción</div>";
        $exitos++;
    } else {
        echo "<div class='warning'>⚠️ No hay perritos disponibles (ejecuta los scripts de datos)</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error obteniendo estadísticas: " . $e->getMessage() . "</div>";
    $errores++;
}
echo "</div>";

// Test 5: Archivos Importantes
echo "<div class='section'>";
echo "<h2>📁 Test 5: Archivos del Sistema</h2>";

$archivos_importantes = [
    'index.php' => 'Página principal',
    'pages/login.php' => 'Página de login',
    'pages/register.php' => 'Página de registro',
    'pages/panel_adoptante.php' => 'Panel del adoptante',
    'pages/panel_refugio.php' => 'Panel del refugio',
    'assets/css/styles.css' => 'Estilos CSS',
    'assets/js/main.js' => 'JavaScript principal'
];

foreach ($archivos_importantes as $archivo => $descripcion) {
    if (file_exists($archivo)) {
        echo "<div class='success'>✅ $descripcion ($archivo)</div>";
        $exitos++;
    } else {
        echo "<div class='error'>❌ $descripcion ($archivo) - NO EXISTE</div>";
        $errores++;
    }
}

// Verificar carpeta uploads
if (is_dir('uploads')) {
    echo "<div class='success'>✅ Carpeta uploads existe</div>";
    $exitos++;
} else {
    echo "<div class='warning'>⚠️ Carpeta uploads no existe (se creará automáticamente)</div>";
}
echo "</div>";

// Resumen Final
echo "<div class='section'>";
echo "<h2>🎯 Resumen Final</h2>";

$total_tests = $exitos + $errores;
$porcentaje = $total_tests > 0 ? round(($exitos / $total_tests) * 100) : 0;

echo "<div class='info'>";
echo "<strong>Resultados del Test:</strong><br>";
echo "✅ Tests exitosos: <strong>$exitos</strong><br>";
echo "❌ Tests fallidos: <strong>$errores</strong><br>";
echo "📊 Porcentaje de éxito: <strong>$porcentaje%</strong><br>";
echo "</div>";

if ($porcentaje >= 80) {
    echo "<div class='success'>🎉 <strong>¡Sistema funcionando correctamente!</strong> Listo para desarrollo.</div>";
} elseif ($porcentaje >= 60) {
    echo "<div class='warning'>⚠️ <strong>Sistema parcialmente funcional.</strong> Algunos componentes necesitan atención.</div>";
} else {
    echo "<div class='error'>❌ <strong>Sistema con problemas.</strong> Revisa los errores antes de continuar.</div>";
}

echo "<div class='info'>";
echo "<strong>Próximos pasos sugeridos:</strong><br>";
echo "1. Si hay errores de BD, ejecuta los scripts SQL<br>";
echo "2. Crea las páginas faltantes (explorar_perritos.php, etc.)<br>";
echo "3. Implementa el sistema de subida de imágenes<br>";
echo "4. Prueba el flujo completo de registro y login<br>";
echo "</div>";

echo "</div>";

echo "<div style='text-align: center; margin-top: 30px;'>";
echo "<a href='index.php' style='background: #ff8a80; color: white; padding: 15px 30px; text-decoration: none; border-radius: 25px; font-weight: bold;'>🏠 Ir a la Página Principal</a>";
echo "</div>";
?>
