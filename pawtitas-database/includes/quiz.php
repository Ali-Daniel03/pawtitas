<?php
/**
 * PAWTITAS - Sistema de Quiz para Matching
 * Funciones para manejar el cuestionario de compatibilidad
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

class QuizSystem {
    private $db;
    
    public function __construct() {
        $this->db = obtener_conexion_db();
    }
    
    /**
     * Obtener todas las preguntas del quiz organizadas por categoría
     */
    public function obtener_preguntas_quiz() {
        try {
            $sql = "SELECT qp.*, 
                           GROUP_CONCAT(
                               CONCAT(qo.id, ':', qo.texto_opcion, ':', qo.valor_numerico) 
                               ORDER BY qo.orden_display SEPARATOR '|'
                           ) as opciones
                    FROM quiz_preguntas qp
                    LEFT JOIN quiz_opciones qo ON qp.id = qo.pregunta_id
                    WHERE qp.activa = 1
                    GROUP BY qp.id
                    ORDER BY qp.categoria, qp.orden_display";
            
            $preguntas = consultar_db($sql);
            if (!$preguntas) {
                return ['exito' => false, 'mensaje' => 'No se pudieron cargar las preguntas'];
            }
            
            // Organizar por categorías
            $quiz_organizado = [];
            foreach ($preguntas as $pregunta) {
                $categoria = $pregunta['categoria'];
                
                if (!isset($quiz_organizado[$categoria])) {
                    $quiz_organizado[$categoria] = [];
                }
                
                // Procesar opciones
                $opciones = [];
                if ($pregunta['opciones']) {
                    $opciones_raw = explode('|', $pregunta['opciones']);
                    foreach ($opciones_raw as $opcion_raw) {
                        $partes = explode(':', $opcion_raw);
                        if (count($partes) >= 3) {
                            $opciones[] = [
                                'id' => $partes[0],
                                'texto' => $partes[1],
                                'valor' => $partes[2]
                            ];
                        }
                    }
                }
                
                $quiz_organizado[$categoria][] = [
                    'id' => $pregunta['id'],
                    'pregunta' => $pregunta['pregunta'],
                    'tipo_respuesta' => $pregunta['tipo_respuesta'],
                    'opciones' => $opciones
                ];
            }
            
            return ['exito' => true, 'quiz' => $quiz_organizado];
            
        } catch (Exception $e) {
            error_log("Error obteniendo preguntas quiz: " . $e->getMessage());
            return ['exito' => false, 'mensaje' => 'Error interno del sistema'];
        }
    }
    
    /**
     * Procesar respuestas del quiz y crear/actualizar perfil
     */
    public function procesar_respuestas_quiz($usuario_id, $respuestas, $datos_adicionales = []) {
        try {
            $this->db->beginTransaction();
            
            // Eliminar respuestas anteriores
            $sql = "DELETE FROM quiz_respuestas WHERE usuario_id = ?";
            ejecutar_db($sql, [$usuario_id]);
            
            // Guardar nuevas respuestas y calcular puntuaciones
            $puntuaciones = $this->calcular_puntuaciones_respuestas($usuario_id, $respuestas);
            
            if (!$puntuaciones['exito']) {
                $this->db->rollBack();
                return $puntuaciones;
            }
            
            // Crear o actualizar perfil de adoptante
            $resultado_perfil = $this->crear_actualizar_perfil($usuario_id, $puntuaciones['puntuaciones'], $datos_adicionales);
            
            if (!$resultado_perfil['exito']) {
                $this->db->rollBack();
                return $resultado_perfil;
            }
            
            $this->db->commit();
            
            return [
                'exito' => true,
                'mensaje' => 'Quiz completado exitosamente',
                'puntuaciones' => $puntuaciones['puntuaciones']
            ];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error procesando quiz: " . $e->getMessage());
            return ['exito' => false, 'mensaje' => 'Error interno del sistema'];
        }
    }
    
    /**
     * Calcular puntuaciones basadas en las respuestas
     */
    private function calcular_puntuaciones_respuestas($usuario_id, $respuestas) {
        try {
            $puntuaciones = [
                'nivel_actividad' => 5,
                'experiencia_mascotas' => 0,
                'tiempo_disponible' => 5,
                'paciencia_entrenamiento' => 5,
                'tolerancia_ruido' => 5,
                'sociabilidad_deseada' => 5,
                'independencia_deseada' => 5,
                'cuidados_especiales' => 0
            ];
            
            $contadores = [
                'actividad' => 0,
                'experiencia' => 0,
                'tiempo' => 0,
                'entrenamiento' => 0,
                'ruido' => 0,
                'sociabilidad' => 0,
                'independencia' => 0,
                'cuidados' => 0
            ];
            
            foreach ($respuestas as $pregunta_id => $opcion_id) {
                // Obtener información de la pregunta y opción
                $sql = "SELECT qp.categoria, qo.valor_numerico, qo.texto_opcion
                        FROM quiz_preguntas qp
                        INNER JOIN quiz_opciones qo ON qp.id = qo.pregunta_id
                        WHERE qp.id = ? AND qo.id = ?";
                
                $resultado = consultar_db($sql, [$pregunta_id, $opcion_id]);
                if (!$resultado) {
                    continue;
                }
                
                $categoria = $resultado[0]['categoria'];
                $valor = $resultado[0]['valor_numerico'];
                $texto_opcion = $resultado[0]['texto_opcion'];
                
                // Guardar respuesta individual
                $sql = "INSERT INTO quiz_respuestas (usuario_id, pregunta_id, opcion_id, valor_calculado) 
                        VALUES (?, ?, ?, ?)";
                ejecutar_db($sql, [$usuario_id, $pregunta_id, $opcion_id, $valor]);
                
                // Acumular para promedio por categoría
                switch ($categoria) {
                    case 'actividad':
                        $puntuaciones['nivel_actividad'] += $valor;
                        $contadores['actividad']++;
                        break;
                    case 'experiencia':
                        $puntuaciones['experiencia_mascotas'] += $valor;
                        $contadores['experiencia']++;
                        break;
                    case 'tiempo':
                        $puntuaciones['tiempo_disponible'] += $valor;
                        $contadores['tiempo']++;
                        break;
                    case 'entrenamiento':
                        $puntuaciones['paciencia_entrenamiento'] += $valor;
                        $contadores['entrenamiento']++;
                        break;
                    case 'ruido':
                        $puntuaciones['tolerancia_ruido'] += $valor;
                        $contadores['ruido']++;
                        break;
                    case 'sociabilidad':
                        $puntuaciones['sociabilidad_deseada'] += $valor;
                        $contadores['sociabilidad']++;
                        break;
                    case 'independencia':
                        $puntuaciones['independencia_deseada'] += $valor;
                        $contadores['independencia']++;
                        break;
                    case 'cuidados':
                        $puntuaciones['cuidados_especiales'] += $valor;
                        $contadores['cuidados']++;
                        break;
                }
            }
            
            // Calcular promedios
            foreach ($contadores as $categoria => $contador) {
                if ($contador > 0) {
                    switch ($categoria) {
                        case 'actividad':
                            $puntuaciones['nivel_actividad'] = round($puntuaciones['nivel_actividad'] / ($contador + 1));
                            break;
                        case 'experiencia':
                            $puntuaciones['experiencia_mascotas'] = round($puntuaciones['experiencia_mascotas'] / $contador);
                            break;
                        case 'tiempo':
                            $puntuaciones['tiempo_disponible'] = round($puntuaciones['tiempo_disponible'] / ($contador + 1));
                            break;
                        case 'entrenamiento':
                            $puntuaciones['paciencia_entrenamiento'] = round($puntuaciones['paciencia_entrenamiento'] / ($contador + 1));
                            break;
                        case 'ruido':
                            $puntuaciones['tolerancia_ruido'] = round($puntuaciones['tolerancia_ruido'] / ($contador + 1));
                            break;
                        case 'sociabilidad':
                            $puntuaciones['sociabilidad_deseada'] = round($puntuaciones['sociabilidad_deseada'] / ($contador + 1));
                            break;
                        case 'independencia':
                            $puntuaciones['independencia_deseada'] = round($puntuaciones['independencia_deseada'] / ($contador + 1));
                            break;
                        case 'cuidados':
                            $puntuaciones['cuidados_especiales'] = round($puntuaciones['cuidados_especiales'] / $contador);
                            break;
                    }
                }
            }
            
            // Asegurar que estén en rango 0-10
            foreach ($puntuaciones as $key => $valor) {
                $puntuaciones[$key] = max(0, min(10, $valor));
            }
            
            return ['exito' => true, 'puntuaciones' => $puntuaciones];
            
        } catch (Exception $e) {
            error_log("Error calculando puntuaciones: " . $e->getMessage());
            return ['exito' => false, 'mensaje' => 'Error calculando puntuaciones'];
        }
    }
    
    /**
     * Crear o actualizar perfil de adoptante
     */
    private function crear_actualizar_perfil($usuario_id, $puntuaciones, $datos_adicionales) {
        try {
            // Verificar si ya existe perfil
            $sql = "SELECT id FROM perfil_adoptante WHERE usuario_id = ?";
            $existe = consultar_db($sql, [$usuario_id]);
            
            if ($existe) {
                // Actualizar perfil existente
                $sql = "UPDATE perfil_adoptante SET
                            nivel_actividad = ?, experiencia_mascotas = ?, tiempo_disponible = ?,
                            paciencia_entrenamiento = ?, tolerancia_ruido = ?, sociabilidad_deseada = ?,
                            independencia_deseada = ?, cuidados_especiales = ?, tipo_vivienda = ?,
                            tiene_niños = ?, edad_niños_menor = ?, tiene_otras_mascotas = ?,
                            tipo_otras_mascotas = ?, tamaño_preferido = ?, quiz_completado = 1,
                            fecha_quiz = NOW(), fecha_actualizacion = NOW()
                        WHERE usuario_id = ?";
                
                $parametros = [
                    $puntuaciones['nivel_actividad'], $puntuaciones['experiencia_mascotas'],
                    $puntuaciones['tiempo_disponible'], $puntuaciones['paciencia_entrenamiento'],
                    $puntuaciones['tolerancia_ruido'], $puntuaciones['sociabilidad_deseada'],
                    $puntuaciones['independencia_deseada'], $puntuaciones['cuidados_especiales'],
                    $datos_adicionales['tipo_vivienda'] ?? 'departamento',
                    isset($datos_adicionales['tiene_niños']) ? 1 : 0,
                    $datos_adicionales['edad_niños_menor'] ?? null,
                    isset($datos_adicionales['tiene_otras_mascotas']) ? 1 : 0,
                    $datos_adicionales['tipo_otras_mascotas'] ?? null,
                    $datos_adicionales['tamaño_preferido'] ?? 'mediano',
                    $usuario_id
                ];
            } else {
                // Crear nuevo perfil
                $sql = "INSERT INTO perfil_adoptante (
                            usuario_id, nivel_actividad, experiencia_mascotas, tiempo_disponible,
                            paciencia_entrenamiento, tolerancia_ruido, sociabilidad_deseada,
                            independencia_deseada, cuidados_especiales, tipo_vivienda,
                            tiene_niños, edad_niños_menor, tiene_otras_mascotas,
                            tipo_otras_mascotas, tamaño_preferido, quiz_completado, fecha_quiz
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())";
                
                $parametros = [
                    $usuario_id,
                    $puntuaciones['nivel_actividad'], $puntuaciones['experiencia_mascotas'],
                    $puntuaciones['tiempo_disponible'], $puntuaciones['paciencia_entrenamiento'],
                    $puntuaciones['tolerancia_ruido'], $puntuaciones['sociabilidad_deseada'],
                    $puntuaciones['independencia_deseada'], $puntuaciones['cuidados_especiales'],
                    $datos_adicionales['tipo_vivienda'] ?? 'departamento',
                    isset($datos_adicionales['tiene_niños']) ? 1 : 0,
                    $datos_adicionales['edad_niños_menor'] ?? null,
                    isset($datos_adicionales['tiene_otras_mascotas']) ? 1 : 0,
                    $datos_adicionales['tipo_otras_mascotas'] ?? null,
                    $datos_adicionales['tamaño_preferido'] ?? 'mediano'
                ];
            }
            
            if (ejecutar_db($sql, $parametros)) {
                return ['exito' => true];
            } else {
                return ['exito' => false, 'mensaje' => 'Error guardando perfil'];
            }
            
        } catch (Exception $e) {
            error_log("Error creando/actualizando perfil: " . $e->getMessage());
            return ['exito' => false, 'mensaje' => 'Error interno del sistema'];
        }
    }
    
    /**
     * Obtener respuestas anteriores del usuario
     */
    public function obtener_respuestas_usuario($usuario_id) {
        try {
            $sql = "SELECT pregunta_id, opcion_id FROM quiz_respuestas WHERE usuario_id = ?";
            $respuestas = consultar_db($sql, [$usuario_id]);
            
            $respuestas_organizadas = [];
            if ($respuestas) {
                foreach ($respuestas as $respuesta) {
                    $respuestas_organizadas[$respuesta['pregunta_id']] = $respuesta['opcion_id'];
                }
            }
            
            return ['exito' => true, 'respuestas' => $respuestas_organizadas];
            
        } catch (Exception $e) {
            error_log("Error obteniendo respuestas usuario: " . $e->getMessage());
            return ['exito' => false, 'mensaje' => 'Error interno del sistema'];
        }
    }
}

/**
 * Funciones helper para el quiz
 */

function obtener_quiz_system() {
    return new QuizSystem();
}

function obtener_preguntas_quiz() {
    $quiz = new QuizSystem();
    return $quiz->obtener_preguntas_quiz();
}

function procesar_quiz_usuario($usuario_id, $respuestas, $datos_adicionales = []) {
    $quiz = new QuizSystem();
    return $quiz->procesar_respuestas_quiz($usuario_id, $respuestas, $datos_adicionales);
}

function obtener_respuestas_quiz_usuario($usuario_id) {
    $quiz = new QuizSystem();
    return $quiz->obtener_respuestas_usuario($usuario_id);
}
?>
