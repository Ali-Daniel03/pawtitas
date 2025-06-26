<?php
/**
 * PAWTITAS - Agregar Nuevo Perrito (Solo Admin)
 */

require_once '../config/config.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Solo admin puede acceder
requerir_admin('panel_adoptante.php');

$usuario = obtener_usuario_actual();
$mensaje = '';
$tipo_mensaje = '';

// Procesar formulario
if ($_POST) {
    $datos = [
        'nombre' => limpiar_datos($_POST['nombre'] ?? ''),
        'raza' => limpiar_datos($_POST['raza'] ?? ''),
        'edad_aproximada' => (int)($_POST['edad_aproximada'] ?? 0),
        'tamaño' => $_POST['tamaño'] ?? 'mediano',
        'sexo' => $_POST['sexo'] ?? 'macho',
        'peso_aproximado' => (float)($_POST['peso_aproximado'] ?? 0),
        'nivel_energia' => $_POST['nivel_energia'] ?? 'medio',
        'personalidad' => limpiar_datos($_POST['personalidad'] ?? ''),
        'historia' => limpiar_datos($_POST['historia'] ?? ''),
        'cuidados_especiales' => limpiar_datos($_POST['cuidados_especiales'] ?? ''),
        'bueno_con_niños' => isset($_POST['bueno_con_niños']) ? 1 : 0,
        'bueno_con_otros_perros' => isset($_POST['bueno_con_otros_perros']) ? 1 : 0,
        'bueno_con_gatos' => isset($_POST['bueno_con_gatos']) ? 1 : 0,
        'necesita_jardin' => isset($_POST['necesita_jardin']) ? 1 : 0,
        'tipo_especial' => $_POST['tipo_especial'] ?? 'normal',
        'admin_id' => $usuario['id']
    ];
    
    // Validar campos obligatorios
    if (empty($datos['nombre']) || empty($datos['personalidad'])) {
        $mensaje = 'Por favor completa todos los campos obligatorios';
        $tipo_mensaje = 'error';
    } else {
        try {
            // Procesar imagen si se subió
            $foto_principal = null;
            if (isset($_FILES['foto_principal']) && $_FILES['foto_principal']['error'] === UPLOAD_ERR_OK) {
                $resultado_imagen = procesar_imagen_subida($_FILES['foto_principal']);
                if ($resultado_imagen['exito']) {
                    $foto_principal = $resultado_imagen['nombre_archivo'];
                } else {
                    $mensaje = $resultado_imagen['mensaje'];
                    $tipo_mensaje = 'error';
                }
            }
            
            if ($tipo_mensaje !== 'error') {
                $sql = "INSERT INTO perritos (
                            nombre, raza, edad_aproximada, tamaño, sexo, peso_aproximado,
                            nivel_energia, personalidad, historia, cuidados_especiales,
                            bueno_con_niños, bueno_con_otros_perros, bueno_con_gatos,
                            necesita_jardin, tipo_especial, foto_principal, admin_id,
                            disponible, fecha_registro
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())";
                
                $parametros = [
                    $datos['nombre'], $datos['raza'], $datos['edad_aproximada'],
                    $datos['tamaño'], $datos['sexo'], $datos['peso_aproximado'],
                    $datos['nivel_energia'], $datos['personalidad'], $datos['historia'],
                    $datos['cuidados_especiales'], $datos['bueno_con_niños'],
                    $datos['bueno_con_otros_perros'], $datos['bueno_con_gatos'],
                    $datos['necesita_jardin'], $datos['tipo_especial'],
                    $foto_principal, $datos['admin_id']
                ];
                
                if (ejecutar_db($sql, $parametros)) {
                    $perrito_id = ultimo_id_insertado();
                    registrar_actividad($usuario['id'], 'perrito_added', "Perrito agregado: {$datos['nombre']}");
                    redirigir_con_mensaje('panel_admin.php', "¡Perrito {$datos['nombre']} agregado exitosamente!", 'success');
                } else {
                    $mensaje = 'Error al guardar el perrito';
                    $tipo_mensaje = 'error';
                }
            }
            
        } catch (Exception $e) {
            $mensaje = 'Error: ' . $e->getMessage();
            $tipo_mensaje = 'error';
        }
    }
}

$page_title = "Agregar Perrito - " . APP_NAME;
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
    <header class="header">
        <nav class="navbar">
            <div class="logo">
                <div class="logo-icon">🐾</div>
                <span class="logo-text"><?php echo APP_NAME; ?></span>
            </div>
            <div class="nav-buttons">
                <a href="panel_admin.php" class="btn btn-outline">← Panel Admin</a>
                <a href="logout.php" class="btn btn-secondary">Cerrar Sesión</a>
            </div>
        </nav>
    </header>

    <main style="padding-top: 100px; padding-bottom: 50px;">
        <div class="container" style="max-width: 800px;">
            
            <div style="text-align: center; margin-bottom: 3rem;">
                <h1 style="color: #5a4a3a; margin-bottom: 1rem;">➕ Agregar Nuevo Perrito</h1>
                <p style="color: #8d6e63;">Completa la información del perrito disponible para adopción</p>
            </div>

            <?php if ($mensaje): ?>
                <div class="message <?php echo $tipo_mensaje; ?>" style="margin-bottom: 2rem;">
                    <?php echo escapar_html($mensaje); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" enctype="multipart/form-data" style="background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
                
                <!-- Información Básica -->
                <div style="margin-bottom: 2rem;">
                    <h3 style="color: #5a4a3a; margin-bottom: 1rem; border-bottom: 2px solid #ff8a80; padding-bottom: 0.5rem;">
                        🐕 Información Básica
                    </h3>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <label for="nombre" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Nombre *</label>
                            <input type="text" id="nombre" name="nombre" required 
                                   style="width: 100%; padding: 0.75rem; border: 2px solid #e8e8e8; border-radius: 8px;"
                                   placeholder="Ej: Max, Luna, Rocky">
                        </div>
                        <div>
                            <label for="raza" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Raza</label>
                            <input type="text" id="raza" name="raza"
                                   style="width: 100%; padding: 0.75rem; border: 2px solid #e8e8e8; border-radius: 8px;"
                                   placeholder="Ej: Labrador, Mestizo, Golden">
                        </div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <label for="edad_aproximada" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Edad (años)</label>
                            <input type="number" id="edad_aproximada" name="edad_aproximada" min="0" max="20"
                                   style="width: 100%; padding: 0.75rem; border: 2px solid #e8e8e8; border-radius: 8px;">
                        </div>
                        <div>
                            <label for="tamaño" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Tamaño</label>
                            <select id="tamaño" name="tamaño" style="width: 100%; padding: 0.75rem; border: 2px solid #e8e8e8; border-radius: 8px;">
                                <option value="pequeño">Pequeño</option>
                                <option value="mediano" selected>Mediano</option>
                                <option value="grande">Grande</option>
                                <option value="gigante">Gigante</option>
                            </select>
                        </div>
                        <div>
                            <label for="sexo" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Sexo</label>
                            <select id="sexo" name="sexo" style="width: 100%; padding: 0.75rem; border: 2px solid #e8e8e8; border-radius: 8px;">
                                <option value="macho">Macho</option>
                                <option value="hembra">Hembra</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Características -->
                <div style="margin-bottom: 2rem;">
                    <h3 style="color: #5a4a3a; margin-bottom: 1rem; border-bottom: 2px solid #ff8a80; padding-bottom: 0.5rem;">
                        ⚡ Características
                    </h3>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <label for="peso_aproximado" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Peso (kg)</label>
                            <input type="number" id="peso_aproximado" name="peso_aproximado" min="0" max="100" step="0.1"
                                   style="width: 100%; padding: 0.75rem; border: 2px solid #e8e8e8; border-radius: 8px;">
                        </div>
                        <div>
                            <label for="nivel_energia" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Nivel de Energía</label>
                            <select id="nivel_energia" name="nivel_energia" style="width: 100%; padding: 0.75rem; border: 2px solid #e8e8e8; border-radius: 8px;">
                                <option value="bajo">Bajo (tranquilo)</option>
                                <option value="medio" selected>Medio (equilibrado)</option>
                                <option value="alto">Alto (muy activo)</option>
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <label for="personalidad" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Personalidad *</label>
                        <textarea id="personalidad" name="personalidad" required rows="3"
                                  style="width: 100%; padding: 0.75rem; border: 2px solid #e8e8e8; border-radius: 8px; resize: vertical;"
                                  placeholder="Describe la personalidad del perrito: juguetón, cariñoso, protector, etc."></textarea>
                    </div>
                </div>

                <!-- Compatibilidad -->
                <div style="margin-bottom: 2rem;">
                    <h3 style="color: #5a4a3a; margin-bottom: 1rem; border-bottom: 2px solid #ff8a80; padding-bottom: 0.5rem;">
                        👨‍👩‍👧‍👦 Compatibilidad
                    </h3>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem; border: 2px solid #e8e8e8; border-radius: 8px; cursor: pointer;">
                            <input type="checkbox" name="bueno_con_niños" value="1">
                            <span>👶 Bueno con niños</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem; border: 2px solid #e8e8e8; border-radius: 8px; cursor: pointer;">
                            <input type="checkbox" name="bueno_con_otros_perros" value="1">
                            <span>🐕 Bueno con otros perros</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem; border: 2px solid #e8e8e8; border-radius: 8px; cursor: pointer;">
                            <input type="checkbox" name="bueno_con_gatos" value="1">
                            <span>🐱 Bueno con gatos</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem; border: 2px solid #e8e8e8; border-radius: 8px; cursor: pointer;">
                            <input type="checkbox" name="necesita_jardin" value="1">
                            <span>🌳 Necesita jardín</span>
                        </label>
                    </div>
                </div>

                <!-- Información Adicional -->
                <div style="margin-bottom: 2rem;">
                    <h3 style="color: #5a4a3a; margin-bottom: 1rem; border-bottom: 2px solid #ff8a80; padding-bottom: 0.5rem;">
                        📝 Información Adicional
                    </h3>
                    
                    <div style="margin-bottom: 1rem;">
                        <label for="tipo_especial" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Tipo Especial</label>
                        <select id="tipo_especial" name="tipo_especial" style="width: 100%; padding: 0.75rem; border: 2px solid #e8e8e8; border-radius: 8px;">
                            <option value="normal">Normal</option>
                            <option value="apoyo_emocional">Apoyo Emocional</option>
                            <option value="perro_guia">Perro Guía</option>
                            <option value="terapia">Terapia</option>
                        </select>
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                        <label for="historia" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Historia</label>
                        <textarea id="historia" name="historia" rows="3"
                                  style="width: 100%; padding: 0.75rem; border: 2px solid #e8e8e8; border-radius: 8px; resize: vertical;"
                                  placeholder="Cuenta la historia del perrito: cómo llegó, de dónde viene, etc."></textarea>
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                        <label for="cuidados_especiales" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Cuidados Especiales</label>
                        <textarea id="cuidados_especiales" name="cuidados_especiales" rows="2"
                                  style="width: 100%; padding: 0.75rem; border: 2px solid #e8e8e8; border-radius: 8px; resize: vertical;"
                                  placeholder="Medicamentos, dietas especiales, cuidados veterinarios, etc."></textarea>
                    </div>
                </div>

                <!-- Foto -->
                <div style="margin-bottom: 2rem;">
                    <h3 style="color: #5a4a3a; margin-bottom: 1rem; border-bottom: 2px solid #ff8a80; padding-bottom: 0.5rem;">
                        📸 Foto Principal
                    </h3>
                    
                    <div>
                        <label for="foto_principal" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Subir foto</label>
                        <input type="file" id="foto_principal" name="foto_principal" accept="image/*"
                               style="width: 100%; padding: 0.75rem; border: 2px solid #e8e8e8; border-radius: 8px;">
                        <small style="color: #666; font-size: 0.85rem;">Formatos: JPG, PNG, GIF, WEBP. Máximo 5MB.</small>
                    </div>
                </div>

                <!-- Botones -->
                <div style="display: flex; gap: 1rem; justify-content: center; padding-top: 2rem; border-top: 1px solid #e8e8e8;">
                    <button type="submit" class="btn btn-primary" style="padding: 1rem 2rem;">
                        ➕ Agregar Perrito
                    </button>
                    <a href="panel_admin.php" class="btn btn-secondary" style="padding: 1rem 2rem;">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </main>

    <script src="../assets/js/main.js"></script>
</body>
</html>
