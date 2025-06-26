<?php
require_once 'config/config.php';

echo "<h2>🔍 Prueba de Conexión PAWTITAS</h2>";
echo "<p><strong>Configuración actual:</strong></p>";
echo "Host: " . DB_HOST . "<br>";
echo "Base de datos: " . DB_NAME . "<br>";
echo "Usuario: " . DB_USER . "<br>";
echo "Contraseña: " . (empty(DB_PASS) ? '(vacía)' : '***configurada***') . "<br>";
echo "URL del proyecto: " . APP_URL . "<br><br>";

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "✅ <strong>¡Conexión exitosa a MariaDB!</strong><br><br>";
    
    // Probar consulta
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
    $usuarios = $stmt->fetch()['total'];
    
    echo "👥 Usuarios en la base de datos: <strong>$usuarios</strong><br>";
    
    // Verificar otras tablas
    $tablas = ['curriculum_emocional', 'perritos', 'solicitudes_adopcion', 'fotos_perritos'];
    echo "<br><strong>📊 Estado de las tablas:</strong><br>";
    
    foreach ($tablas as $tabla) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM $tabla");
            $total = $stmt->fetch()['total'];
            echo "• $tabla: <strong>$total</strong> registros<br>";
        } catch (PDOException $e) {
            echo "• $tabla: <span style='color: red;'>❌ No existe</span><br>";
        }
    }
    
    echo "<br>🎉 <strong>¡Todo funciona correctamente!</strong>";
    
} catch (PDOException $e) {
    echo "❌ <strong>Error de conexión:</strong><br>";
    echo $e->getMessage() . "<br><br>";
    
    echo "🔧 <strong>Posibles soluciones:</strong><br>";
    echo "1. Verifica que la contraseña sea correcta<br>";
    echo "2. Confirma que MariaDB esté ejecutándose<br>";
    echo "3. Verifica que la base de datos 'pawtitas_db' exista<br>";
    echo "4. En HeidiSQL, verifica que el usuario 'root' tenga permisos<br>";
}
?>
