<?php
/**
 * PAWTITAS - Test Simple de Autenticación
 */

require_once 'config/config.php';
require_once 'includes/session.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

echo "<h2>🔐 Test de Autenticación PAWTITAS</h2>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .success { color: green; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: red; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: blue; background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 10px 0; }
</style>";

// Test 1: Crear instancia de Auth
echo "<h3>📝 Test 1: Inicializar Auth</h3>";
try {
    $auth = new Auth();
    echo "<div class='success'>✅ Auth inicializado correctamente</div>";
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
    exit();
}

// Test 2: Verificar funciones de validación
echo "<h3>🔍 Test 2: Funciones de Validación</h3>";

// Test email válido
$email_test = es_email_valido('test@example.com');
echo "<div class='" . ($email_test ? 'success' : 'error') . "'>";
echo ($email_test ? '✅' : '❌') . " Validación email: " . ($email_test ? 'CORRECTO' : 'ERROR');
echo "</div>";

// Test teléfono válido
$phone_test = es_telefono_valido('5551234567');
echo "<div class='" . ($phone_test ? 'success' : 'error') . "'>";
echo ($phone_test ? '✅' : '❌') . " Validación teléfono: " . ($phone_test ? 'CORRECTO' : 'ERROR');
echo "</div>";

// Test password seguro
$pass_test = es_password_seguro('123456');
echo "<div class='" . ($pass_test ? 'success' : 'error') . "'>";
echo ($pass_test ? '✅' : '❌') . " Validación password: " . ($pass_test ? 'CORRECTO' : 'ERROR');
echo "</div>";

// Test 3: Verificar usuarios existentes
echo "<h3>👥 Test 3: Usuarios en Base de Datos</h3>";
try {
    $sql = "SELECT COUNT(*) as total FROM usuarios";
    $resultado = consultar_db($sql);
    $total_usuarios = $resultado ? $resultado[0]['total'] : 0;
    
    echo "<div class='info'>📊 Total de usuarios: <strong>$total_usuarios</strong></div>";
    
    if ($total_usuarios > 0) {
        $sql = "SELECT email, tipo_usuario FROM usuarios LIMIT 3";
        $usuarios = consultar_db($sql);
        
        echo "<div class='info'><strong>Usuarios de ejemplo:</strong><br>";
        foreach ($usuarios as $user) {
            echo "• " . $user['email'] . " (" . $user['tipo_usuario'] . ")<br>";
        }
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error consultando usuarios: " . $e->getMessage() . "</div>";
}

// Test 4: Test de registro (simulado)
echo "<h3>📝 Test 4: Simulación de Registro</h3>";
$datos_test = [
    'nombre' => 'Usuario',
    'apellidos' => 'Prueba',
    'email' => 'test_' . time() . '@pawtitas.test',
    'password' => 'test123456',
    'confirm_password' => 'test123456',
    'tipo_usuario' => 'adoptante',
    'telefono' => '5551234567'
];

echo "<div class='info'>📋 Datos de prueba preparados:<br>";
echo "• Email: " . $datos_test['email'] . "<br>";
echo "• Tipo: " . $datos_test['tipo_usuario'] . "<br>";
echo "• Teléfono: " . $datos_test['telefono'] . "</div>";

try {
    $resultado = $auth->registrar_usuario($datos_test);
    
    if ($resultado['exito']) {
        echo "<div class='success'>✅ Registro simulado exitoso. ID: " . $resultado['user_id'] . "</div>";
        
        // Limpiar - eliminar usuario de prueba
        $sql = "DELETE FROM usuarios WHERE id = ?";
        if (ejecutar_db($sql, [$resultado['user_id']])) {
            echo "<div class='info'>🗑️ Usuario de prueba eliminado correctamente</div>";
        }
    } else {
        echo "<div class='error'>❌ Error en registro: " . $resultado['mensaje'] . "</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Excepción en registro: " . $e->getMessage() . "</div>";
}

echo "<h3>🎉 Test Completado</h3>";
echo "<div class='success'>Todos los tests de autenticación ejecutados.</div>";
echo "<div class='info'>💡 <strong>Siguiente paso:</strong> Puedes probar el login con los usuarios existentes en la base de datos.</div>";
?>
