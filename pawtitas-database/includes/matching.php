<?php
/**
 * PAWTITAS - Sistema de Matching Automático
 * Funciones para calcular compatibilidad entre adoptantes y perritos
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

class MatchingSystem {
    private $db;
    
    public function __construct() {
        $this->db = obtener_conexion_db();
    }
    
    /**
     * Calcular compatibilidad entre un adoptante y un perrito
     */
    public function calcular_compatibilidad($usuario_id, $perrito_id) {
        try {
            // Obtener perfil del adoptante
            $adoptante = $this->obtener_perfil_adoptante($usuario_id);
            if (!$adoptante) {
                return ['exito' => false, 'mensaje' => 'Perfil de adoptante no encontrado'];
            }
            
            // Obtener perfil del perrito
            $perrito = $this->obtener_perfil_perrito($perrito_id);
            if (!$perrito) {
                return ['exito' => false, 'mensaje' => 'Perfil de perrito no encontrado'];
            }
            
            // Verificar incompatibilidades críticas
            $incompatibilidades = $this->verificar_incompatibilidades($adoptante, $perrito);
            
            // Calcular puntuaciones de diferencia
            $diferencias = $this->calcular_diferencias($adoptante, $perrito);
            
            // Calcular porcentaje de compatibilidad
            $porcentaje = $this->calcular_porcentaje_compatibilidad($diferencias, $incompatibilidades);
            
            // Guardar resultado en cache
            $this->guardar_resultado_matching($usuario_id, $perrito_id, $diferencias, $porcentaje, $incompatibilidades);
            
            return [
                'exito' => true,
                'porcentaje_compatibilidad' => $porcentaje,
                'diferencias' => $diferencias,
                'incompatibilidades' => $incompatibilidades,
                'recomendacion' => $this->generar_recomendacion($porcentaje, $incompatibilidades)
            ];
            
        } catch (Exception $e) {
            error_log("Error en matching: " . $e->getMessage());
            return ['exito' => false, 'mensaje' => 'Error interno del sistema'];
        }
    }
    
    /**
     * Obtener mejores matches para un adoptante
     */
    public function obtener_mejores_matches($usuario_id, $limite = 10) {
        try {
            // Verificar que el adoptante tenga perfil completo
            $adoptante = $this->obtener_perfil_adoptante($usuario_id);
            if (!$adoptante || !$adoptante['quiz_completado']) {
                return ['exito' => false, 'mensaje' => 'Debes completar el quiz primero'];
            }
            
            // Obtener perritos disponibles
            $sql = "SELECT p.id, p.nombre, p.raza, p.edad_aproximada, p.tamaño, 
                           p.sexo, p.foto_principal, p.personalidad, p.tipo_especial
                    FROM perritos p
                    INNER JOIN perfil_perrito pp ON p.id = pp.perrito_id
                    WHERE p.disponible = 1 AND pp.perfil_completado = 1";
            
            $perritos = consultar_db($sql);
            if (!$perritos) {
                return ['exito' => false, 'mensaje' => 'No hay perritos disponibles'];
            }
            
            $matches = [];
            
            foreach ($perritos as $perrito) {
                $resultado = $this->calcular_compatibilidad($usuario_id, $perrito['id']);
                if ($resultado['exito']) {
                    $matches[] = [
                        'perrito' => $perrito,
                        'compatibilidad' => $resultado['porcentaje_compatibilidad'],
                        'recomendacion' => $resultado['recomendacion'],
                        'incompatibilidades' => $resultado['incompatibilidades']
                    ];
                }
            }
            
            // Ordenar por compatibilidad descendente
            usort($matches, function($a, $b) {
                return $b['compatibilidad'] <=> $a['compatibilidad'];
            });
            
            return [
                'exito' => true,
                'matches' => array_slice($matches, 0, $limite),
                'total_evaluados' => count($perritos)
            ];
            
        } catch (Exception $e) {
            error_log("Error obteniendo matches: " . $e->getMessage());
            return ['exito' => false, 'mensaje' => 'Error interno del sistema'];
        }
    }
    
    /**
     * Obtener perfil completo del adoptante
     */
    private function obtener_perfil_adoptante($usuario_id) {
        $sql = "SELECT pa.*, u.nombre, u.apellidos 
                FROM perfil_adoptante pa
                INNER JOIN usuarios u ON pa.usuario_id = u.id
                WHERE pa.usuario_id = ?";
        
        $resultado = consultar_db($sql, [$usuario_id]);
        return $resultado ? $resultado[0] : null;
    }
    
    /**
     * Obtener perfil completo del perrito
     */
    private function obtener_perfil_perrito($perrito_id) {
        $sql = "SELECT pp.*, p.nombre, p.tamaño, p.tipo_especial
                FROM perfil_perrito pp
                INNER JOIN perritos p ON pp.perrito_id = p.id
                WHERE pp.perrito_id = ?";
        
        $resultado = consultar_db($sql, [$perrito_id]);
        return $resultado ? $resultado[0] : null;
    }
    
    /**
     * Verificar incompatibilidades críticas
     */
    private function verificar_incompatibilidades($adoptante, $perrito) {
        $incompatibilidades = [];
        
        // Verificar tamaño preferido
        $tamaños_preferidos = explode(',', $adoptante['tamaño_preferido']);
        if (!in_array($perrito['tamaño'], $tamaños_preferidos)) {
            $incompatibilidades['tamaño'] = true;
        }
        
        // Verificar compatibilidad con niños
        if ($adoptante['tiene_niños'] && !$perrito['bueno_con_niños']) {
            $incompatibilidades['niños'] = true;
        }
        
        // Verificar edad mínima de niños
        if ($adoptante['tiene_niños'] && $adoptante['edad_niños_menor'] < $perrito['edad_minima_niños']) {
            $incompatibilidades['edad_niños'] = true;
        }
        
        // Verificar otras mascotas
        if ($adoptante['tiene_otras_mascotas']) {
            $otras_mascotas = $adoptante['tipo_otras_mascotas'];
            if (strpos($otras_mascotas, 'perros') !== false && !$perrito['bueno_con_otros_perros']) {
                $incompatibilidades['otros_perros'] = true;
            }
            if (strpos($otras_mascotas, 'gatos') !== false && !$perrito['bueno_con_gatos']) {
                $incompatibilidades['gatos'] = true;
            }
        }
        
        // Verificar necesidad de jardín
        if ($perrito['necesita_jardin'] && $adoptante['tipo_vivienda'] !== 'casa_con_jardin') {
            $incompatibilidades['jardin'] = true;
        }
        
        return $incompatibilidades;
    }
    
    /**
     * Calcular diferencias en puntuaciones
     */
    private function calcular_diferencias($adoptante, $perrito) {
        return [
            'actividad' => abs($adoptante['nivel_actividad'] - $perrito['nivel_energia']),
            'experiencia' => abs($adoptante['experiencia_mascotas'] - $perrito['experiencia_necesaria']),
            'tiempo' => abs($adoptante['tiempo_disponible'] - $perrito['tiempo_atencion_necesario']),
            'entrenamiento' => abs($adoptante['paciencia_entrenamiento'] - $perrito['dificultad_entrenamiento']),
            'ruido' => abs($adoptante['tolerancia_ruido'] - $perrito['nivel_ruido']),
            'sociabilidad' => abs($adoptante['sociabilidad_deseada'] - $perrito['sociabilidad']),
            'independencia' => abs($adoptante['independencia_deseada'] - $perrito['independencia']),
            'cuidados' => abs($adoptante['cuidados_especiales'] - $perrito['necesita_cuidados_especiales'])
        ];
    }
    
    /**
     * Calcular porcentaje de compatibilidad
     */
    private function calcular_porcentaje_compatibilidad($diferencias, $incompatibilidades) {
        // Suma total de diferencias (máximo posible = 80, mínimo = 0)
        $suma_diferencias = array_sum($diferencias);
        
        // Calcular porcentaje base (100% = diferencia 0, 0% = diferencia máxima)
        $porcentaje_base = 100 - (($suma_diferencias / 80) * 100);
        
        // Aplicar penalizaciones por incompatibilidades críticas
        $penalizacion = 0;
        foreach ($incompatibilidades as $incompatibilidad => $existe) {
            if ($existe) {
                switch ($incompatibilidad) {
                    case 'tamaño':
                        $penalizacion += 15; // Penalización fuerte
                        break;
                    case 'niños':
                    case 'edad_niños':
                        $penalizacion += 20; // Penalización muy fuerte
                        break;
                    case 'jardin':
                        $penalizacion += 10; // Penalización moderada
                        break;
                    default:
                        $penalizacion += 5; // Penalización leve
                        break;
                }
            }
        }
        
        $porcentaje_final = max(0, min(100, $porcentaje_base - $penalizacion));
        
        return round($porcentaje_final, 2);
    }
    
    /**
     * Generar recomendación basada en compatibilidad
     */
    private function generar_recomendacion($porcentaje, $incompatibilidades) {
        if (!empty($incompatibilidades)) {
            return 'no_recomendado';
        }
        
        if ($porcentaje >= 85) {
            return 'excelente_match';
        } elseif ($porcentaje >= 70) {
            return 'buen_match';
        } elseif ($porcentaje >= 50) {
            return 'match_moderado';
        } else {
            return 'match_bajo';
        }
    }
    
    /**
     * Guardar resultado en cache
     */
    private function guardar_resultado_matching($usuario_id, $perrito_id, $diferencias, $porcentaje, $incompatibilidades) {
        try {
            // Eliminar resultado anterior si existe
            $sql = "DELETE FROM matching_resultados WHERE usuario_id = ? AND perrito_id = ?";
            ejecutar_db($sql, [$usuario_id, $perrito_id]);
            
            // Insertar nuevo resultado
            $sql = "INSERT INTO matching_resultados (
                        usuario_id, perrito_id, diferencia_actividad, diferencia_experiencia,
                        diferencia_tiempo, diferencia_entrenamiento, diferencia_ruido,
                        diferencia_sociabilidad, diferencia_independencia, diferencia_cuidados,
                        puntuacion_total, porcentaje_compatibilidad, incompatible_tamaño,
                        incompatible_niños, incompatible_mascotas, incompatible_vivienda,
                        valido_hasta
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 7 DAY))";
            
            $parametros = [
                $usuario_id, $perrito_id,
                $diferencias['actividad'], $diferencias['experiencia'],
                $diferencias['tiempo'], $diferencias['entrenamiento'],
                $diferencias['ruido'], $diferencias['sociabilidad'],
                $diferencias['independencia'], $diferencias['cuidados'],
                array_sum($diferencias), $porcentaje,
                isset($incompatibilidades['tamaño']) ? 1 : 0,
                isset($incompatibilidades['niños']) || isset($incompatibilidades['edad_niños']) ? 1 : 0,
                isset($incompatibilidades['otros_perros']) || isset($incompatibilidades['gatos']) ? 1 : 0,
                isset($incompatibilidades['jardin']) ? 1 : 0
            ];
            
            ejecutar_db($sql, $parametros);
            
        } catch (Exception $e) {
            error_log("Error guardando resultado matching: " . $e->getMessage());
        }
    }
}

/**
 * Funciones helper para el sistema de matching
 */

function obtener_matching_system() {
    return new MatchingSystem();
}

function calcular_compatibilidad_usuario_perrito($usuario_id, $perrito_id) {
    $matching = new MatchingSystem();
    return $matching->calcular_compatibilidad($usuario_id, $perrito_id);
}

function obtener_mejores_matches_usuario($usuario_id, $limite = 10) {
    $matching = new MatchingSystem();
    return $matching->obtener_mejores_matches($usuario_id, $limite);
}
/**
 * Obtener estadísticas de matching de un usuario
 */
function obtener_estadisticas_matching($usuario_id) {
    try {
        $stats = [];
        
        // Total de matches calculados
        $sql = "SELECT COUNT(*) as total FROM matching_resultados WHERE usuario_id = ?";
        $resultado = consultar_db($sql, [$usuario_id]);
        $stats['total_matches'] = $resultado ? $resultado[0]['total'] : 0;
        
        // Mejor match
        $sql = "SELECT MAX(porcentaje_compatibilidad) as mejor FROM matching_resultados WHERE usuario_id = ?";
        $resultado = consultar_db($sql, [$usuario_id]);
        $stats['mejor_match'] = $resultado ? $resultado[0]['mejor'] : 0;
        
        // Matches excelentes (>= 85%)
        $sql = "SELECT COUNT(*) as total FROM matching_resultados WHERE usuario_id = ? AND porcentaje_compatibilidad >= 85";
        $resultado = consultar_db($sql, [$usuario_id]);
        $stats['matches_excelentes'] = $resultado ? $resultado[0]['total'] : 0;
        
        // Matches buenos (>= 70%)
        $sql = "SELECT COUNT(*) as total FROM matching_resultados WHERE usuario_id = ? AND porcentaje_compatibilidad >= 70";
        $resultado = consultar_db($sql, [$usuario_id]);
        $stats['matches_buenos'] = $resultado ? $resultado[0]['total'] : 0;
        
        return $stats;
        
    } catch (Exception $e) {
        error_log("Error obteniendo estadísticas matching: " . $e->getMessage());
        return [
            'total_matches' => 0,
            'mejor_match' => 0,
            'matches_excelentes' => 0,
            'matches_buenos' => 0
        ];
    }
}
?>
