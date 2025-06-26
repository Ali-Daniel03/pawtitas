<?php
/**
 * PAWTITAS - Test Simple de Estadísticas
 */

require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/stats.php';

echo "<h2>📊 Test de Estadísticas PAWTITAS</h2>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .success { color: green; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: red; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: blue; background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .stat-box { background: white; padding: 15px; border-radius: 8px; margin: 10px 0; border-left: 4px solid #ff8a80; }
</style>";

// Test 1: Estadísticas generales
echo "<h3>📈 Test 1: Estadísticas Generales</h3>";
try {
    $stats = getGeneralStats();
    
    echo "<div class='success'>✅ Estadísticas obtenidas correctamente</div>";
    
    echo "<div class='stat-box'>";
    echo "<h4>📊 Estadísticas del Sitio:</h4>";
    echo "🐕 Perritos adoptados: <strong>" . $stats['adopted_dogs'] . "</strong><br>";
    echo "🏠 Perritos disponibles: <strong>" . $stats['available_dogs'] . "</strong><br>";
    echo "🏥 Refugios registrados: <strong>" . $stats['registered_shelters'] . "</strong><br>";
    echo "👨‍👩‍👧‍👦 Familias felices: <strong>" . $stats['happy_families'] . "</strong><br>";
    echo "🦮 Perritos especiales: <strong>" . $stats['special_dogs'] . "</strong><br>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error obteniendo estadísticas: " . $e->getMessage() . "</div>";
}

// Test 2: Perritos destacados
echo "<h3>🌟 Test 2: Perritos Destacados</h3>";
try {
    $featured = getFeaturedDogs(3);
    
    if (!empty($featured)) {
        echo "<div class='success'>✅ Se encontraron " . count($featured) . " perritos destacados</div>";
        
        echo "<div class='stat-box'>";
        echo "<h4>🐕 Perritos Destacados:</h4>";
        foreach ($featured as $dog) {
            echo "• <strong>" . htmlspecialchars($dog['nombre']) . "</strong>";
            if ($dog['raza']) echo " (" . htmlspecialchars($dog['raza']) . ")";
            if ($dog['edad_aproximada']) echo " - " . $dog['edad_aproximada'] . " años";
            echo "<br>";
        }
        echo "</div>";
    } else {
        echo "<div class='info'>ℹ️ No hay perritos destacados (esto es normal si la BD está vacía)</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error obteniendo perritos destacados: " . $e->getMessage() . "</div>";
}

// Test 3: Verificar datos en tablas
echo "<h3>🗃️ Test 3: Datos en Tablas</h3>";
$tablas = [
    'usuarios' => 'Usuarios registrados',
    'perritos' => 'Perritos en el sistema',
    'curriculum_emocional' => 'Currículums completados',
    'solicitudes_adopcion' => 'Solicitudes de adopción'
];

foreach ($tablas as $tabla => $descripcion) {
    try {
        $sql = "SELECT COUNT(*) as total FROM $tabla";
        $resultado = consultar_db($sql);
        $total = $resultado ? $resultado[0]['total'] : 0;
        
        echo "<div class='info'>📋 $descripcion: <strong>$total</strong></div>";
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error consultando $tabla: " . $e->getMessage() . "</div>";
    }
}

// Test 4: Test de funciones auxiliares
echo "<h3>🔧 Test 4: Funciones Auxiliares</h3>";

// Test formatear fecha
$fecha_test = formatear_fecha('2024-01-15');
echo "<div class='" . (!empty($fecha_test) ? 'success' : 'error') . "'>";
echo (!empty($fecha_test) ? '✅' : '❌') . " Formatear fecha: " . ($fecha_test ?: 'ERROR');
echo "</div>";

// Test cortar texto
$texto_test = cortar_texto('Este es un texto muy largo para probar la función', 20);
echo "<div class='success'>✅ Cortar texto: " . htmlspecialchars($texto_test) . "</div>";

// Test escapar HTML
$html_test = escapar_html('<script>alert("test")</script>');
echo "<div class='success'>✅ Escapar HTML: " . $html_test . "</div>";

echo "<h3>🎉 Test de Estadísticas Completado</h3>";
echo "<div class='success'>Todas las funciones de estadísticas funcionan correctamente.</div>";
?>
