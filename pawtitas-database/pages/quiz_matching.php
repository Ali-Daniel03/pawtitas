<?php
/**
 * PAWTITAS - Quiz de Compatibilidad
 * Cuestionario para determinar el perfil del adoptante
 */

require_once '../config/config.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/quiz.php';

// Verificar que esté logueado y sea adoptante
requerir_login('../login.php');
requerir_tipo_usuario('adoptante', 'panel_admin.php');

$usuario = obtener_usuario_actual();
$mensaje = '';
$tipo_mensaje = '';

// Obtener preguntas del quiz
$quiz_system = obtener_quiz_system();
$resultado_preguntas = $quiz_system->obtener_preguntas_quiz();

if (!$resultado_preguntas['exito']) {
    redirigir_con_mensaje('panel_adoptante.php', 'Error cargando el quiz', 'error');
}

$quiz = $resultado_preguntas['quiz'];

// Obtener respuestas anteriores si existen
$respuestas_anteriores = [];
$resultado_respuestas = $quiz_system->obtener_respuestas_usuario($usuario['id']);
if ($resultado_respuestas['exito']) {
    $respuestas_anteriores = $resultado_respuestas['respuestas'];
}

// Procesar formulario
if ($_POST) {
    $respuestas = $_POST['respuestas'] ?? [];
    $datos_adicionales = [
        'tipo_vivienda' => $_POST['tipo_vivienda'] ?? 'departamento',
        'tiene_niños' => isset($_POST['tiene_niños']),
        'edad_niños_menor' => $_POST['edad_niños_menor'] ?? null,
        'tiene_otras_mascotas' => isset($_POST['tiene_otras_mascotas']),
        'tipo_otras_mascotas' => $_POST['tipo_otras_mascotas'] ?? null,
        'tamaño_preferido' => implode(',', $_POST['tamaño_preferido'] ?? ['mediano'])
    ];
    
    $resultado = $quiz_system->procesar_respuestas_quiz($usuario['id'], $respuestas, $datos_adicionales);
    
    if ($resultado['exito']) {
        redirigir_con_mensaje('resultados_matching.php', '¡Quiz completado! Ve tus matches perfectos', 'success');
    } else {
        $mensaje = $resultado['mensaje'];
        $tipo_mensaje = 'error';
    }
}

$page_title = "Quiz de Compatibilidad - " . APP_NAME;
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
        .quiz-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .quiz-header {
            background: linear-gradient(135deg, #ff8a80 0%, #ffab91 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .quiz-progress {
            background: rgba(255,255,255,0.2);
            height: 8px;
            border-radius: 4px;
            margin-top: 1rem;
            overflow: hidden;
        }
        .quiz-progress-bar {
            background: white;
            height: 100%;
            width: 0%;
            transition: width 0.3s ease;
        }
        .quiz-section {
            padding: 2rem;
            border-bottom: 1px solid #f0f0f0;
        }
        .quiz-section:last-child {
            border-bottom: none;
        }
        .section-title {
            color: #5a4a3a;
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .question {
            margin-bottom: 2rem;
        }
        .question-text {
            font-weight: 500;
            margin-bottom: 1rem;
            color: #5a4a3a;
        }
        .options {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .option {
            display: flex;
            align-items: center;
            padding: 0.75rem;
            border: 2px solid #e8e8e8;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .option:hover {
            border-color: #ff8a80;
            background: #fef7f7;
        }
        .option.selected {
            border-color: #ff8a80;
            background: #fef7f7;
        }
        .option input[type="radio"] {
            margin-right: 0.75rem;
        }
        .additional-info {
            background: #f8f9fa;
            padding: 2rem;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }
        .checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border: 2px solid #e8e8e8;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .checkbox-item:hover {
            border-color: #ff8a80;
            background: #fef7f7;
        }
        .checkbox-item.checked {
            border-color: #ff8a80;
            background: #fef7f7;
        }
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
                <a href="logout.php" class="btn btn-secondary">Cerrar Sesión</a>
            </div>
        </nav>
    </header>

    <main style="padding-top: 100px; padding-bottom: 50px;">
        <div class="container">
            
            <?php if ($mensaje): ?>
                <div class="message <?php echo $tipo_mensaje; ?>" style="margin-bottom: 2rem;">
                    <?php echo escapar_html($mensaje); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="quizForm">
                <div class="quiz-container">
                    
                    <!-- Header del Quiz -->
                    <div class="quiz-header">
                        <h1>🎯 Quiz de Compatibilidad</h1>
                        <p>Responde estas preguntas para encontrar a tu compañero perfecto</p>
                        <div class="quiz-progress">
                            <div class="quiz-progress-bar" id="progressBar"></div>
                        </div>
                        <small id="progressText">0% completado</small>
                    </div>

                    <!-- Preguntas por categoría -->
                    <?php 
                    $categoria_icons = [
                        'actividad' => '🏃‍♂️',
                        'experiencia' => '🎓',
                        'tiempo' => '⏰',
                        'entrenamiento' => '🎯',
                        'ruido' => '🔊',
                        'sociabilidad' => '👥',
                        'independencia' => '🏠',
                        'cuidados' => '💊'
                    ];
                    
                    $categoria_nombres = [
                        'actividad' => 'Nivel de Actividad',
                        'experiencia' => 'Experiencia con Mascotas',
                        'tiempo' => 'Tiempo Disponible',
                        'entrenamiento' => 'Paciencia y Entrenamiento',
                        'ruido' => 'Tolerancia al Ruido',
                        'sociabilidad' => 'Sociabilidad',
                        'independencia' => 'Independencia',
                        'cuidados' => 'Cuidados Especiales'
                    ];
                    
                    foreach ($quiz as $categoria => $preguntas): 
                    ?>
                        <div class="quiz-section">
                            <h3 class="section-title">
                                <span><?php echo $categoria_icons[$categoria] ?? '📝'; ?></span>
                                <?php echo $categoria_nombres[$categoria] ?? ucfirst($categoria); ?>
                            </h3>
                            
                            <?php foreach ($preguntas as $pregunta): ?>
                                <div class="question">
                                    <div class="question-text">
                                        <?php echo escapar_html($pregunta['pregunta']); ?>
                                    </div>
                                    
                                    <div class="options">
                                        <?php foreach ($pregunta['opciones'] as $opcion): ?>
                                            <label class="option" onclick="selectOption(this)">
                                                <input type="radio" 
                                                       name="respuestas[<?php echo $pregunta['id']; ?>]" 
                                                       value="<?php echo $opcion['id']; ?>"
                                                       <?php echo (isset($respuestas_anteriores[$pregunta['id']]) && $respuestas_anteriores[$pregunta['id']] == $opcion['id']) ? 'checked' : ''; ?>
                                                       onchange="updateProgress()">
                                                <span><?php echo escapar_html($opcion['texto']); ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>

                    <!-- Información Adicional -->
                    <div class="additional-info">
                        <h3 class="section-title">
                            <span>🏠</span>
                            Información de tu Hogar
                        </h3>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="tipo_vivienda">Tipo de Vivienda</label>
                                <select id="tipo_vivienda" name="tipo_vivienda" required>
                                    <option value="departamento">Departamento</option>
                                    <option value="casa_sin_jardin">Casa sin jardín</option>
                                    <option value="casa_con_jardin">Casa con jardín</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="tiene_niños" value="1" onchange="toggleNiños()">
                                    Tengo niños en casa
                                </label>
                                <div id="edad_niños_group" style="display: none; margin-top: 0.5rem;">
                                    <label for="edad_niños_menor">Edad del niño más pequeño:</label>
                                    <input type="number" id="edad_niños_menor" name="edad_niños_menor" min="1" max="18">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="tiene_otras_mascotas" value="1" onchange="toggleMascotas()">
                                    Tengo otras mascotas
                                </label>
                                <div id="tipo_mascotas_group" style="display: none; margin-top: 0.5rem;">
                                    <input type="text" name="tipo_otras_mascotas" placeholder="Ej: gatos, perros, otros">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group" style="margin-top: 1.5rem;">
                            <label>Tamaños de perro que me interesan:</label>
                            <div class="checkbox-group">
                                <label class="checkbox-item" onclick="toggleCheckbox(this)">
                                    <input type="checkbox" name="tamaño_preferido[]" value="pequeño">
                                    <span>🐕 Pequeño (hasta 10kg)</span>
                                </label>
                                <label class="checkbox-item" onclick="toggleCheckbox(this)">
                                    <input type="checkbox" name="tamaño_preferido[]" value="mediano" checked>
                                    <span>🐕‍🦺 Mediano (10-25kg)</span>
                                </label>
                                <label class="checkbox-item" onclick="toggleCheckbox(this)">
                                    <input type="checkbox" name="tamaño_preferido[]" value="grande">
                                    <span>🐕‍🦺 Grande (25-40kg)</span>
                                </label>
                                <label class="checkbox-item" onclick="toggleCheckbox(this)">
                                    <input type="checkbox" name="tamaño_preferido[]" value="gigante">
                                    <span>🐕‍🦺 Gigante (más de 40kg)</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Botón de envío -->
                    <div style="padding: 2rem; text-align: center; background: #f8f9fa;">
                        <button type="submit" class="btn btn-primary btn-large" id="submitBtn" disabled>
                            🎯 Completar Quiz y Ver Mis Matches
                        </button>
                        <p style="margin-top: 1rem; color: #666; font-size: 0.9rem;">
                            Completa todas las preguntas para activar el botón
                        </p>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <script src="../assets/js/main.js"></script>
    <script>
        function selectOption(label) {
            // Remover selección anterior en el grupo
            const name = label.querySelector('input').name;
            document.querySelectorAll(`input[name="${name}"]`).forEach(input => {
                input.closest('.option').classList.remove('selected');
            });
            
            // Agregar selección actual
            label.classList.add('selected');
            updateProgress();
        }
        
        function updateProgress() {
            const totalQuestions = document.querySelectorAll('input[type="radio"]').length / <?php echo array_sum(array_map('count', $quiz)); ?>;
            const answeredQuestions = document.querySelectorAll('input[type="radio"]:checked').length;
            const progress = (answeredQuestions / <?php echo array_sum(array_map('count', $quiz)); ?>) * 100;
            
            document.getElementById('progressBar').style.width = progress + '%';
            document.getElementById('progressText').textContent = Math.round(progress) + '% completado';
            
            // Habilitar botón si está completo
            const submitBtn = document.getElementById('submitBtn');
            if (progress >= 100) {
                submitBtn.disabled = false;
                submitBtn.textContent = '🎯 Completar Quiz y Ver Mis Matches';
            } else {
                submitBtn.disabled = true;
                submitBtn.textContent = `Completa ${<?php echo array_sum(array_map('count', $quiz)); ?> - answeredQuestions} preguntas más`;
            }
        }
        
        function toggleNiños() {
            const checkbox = document.querySelector('input[name="tiene_niños"]');
            const group = document.getElementById('edad_niños_group');
            group.style.display = checkbox.checked ? 'block' : 'none';
        }
        
        function toggleMascotas() {
            const checkbox = document.querySelector('input[name="tiene_otras_mascotas"]');
            const group = document.getElementById('tipo_mascotas_group');
            group.style.display = checkbox.checked ? 'block' : 'none';
        }
        
        function toggleCheckbox(label) {
            const checkbox = label.querySelector('input[type="checkbox"]');
            if (checkbox.checked) {
                label.classList.add('checked');
            } else {
                label.classList.remove('checked');
            }
        }
        
        // Inicializar estado
        document.addEventListener('DOMContentLoaded', function() {
            // Marcar opciones seleccionadas
            document.querySelectorAll('input[type="radio"]:checked').forEach(input => {
                input.closest('.option').classList.add('selected');
            });
            
            // Marcar checkboxes seleccionados
            document.querySelectorAll('input[type="checkbox"]:checked').forEach(input => {
                if (input.closest('.checkbox-item')) {
                    input.closest('.checkbox-item').classList.add('checked');
                }
            });
            
            updateProgress();
        });
    </script>
</body>
</html>
