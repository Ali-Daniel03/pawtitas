-- PAWTITAS - Consultas de testing para verificar funcionamiento
-- Prueba que todo funcione correctamente

USE pawtitas_db;

-- ============================================================================
-- TESTING BÁSICO DE DATOS
-- ============================================================================

-- Contar registros en cada tabla
SELECT 'usuarios' as tabla, COUNT(*) as total FROM usuarios
UNION ALL
SELECT 'perfil_adoptante', COUNT(*) FROM perfil_adoptante
UNION ALL
SELECT 'perritos', COUNT(*) FROM perritos
UNION ALL
SELECT 'perfil_perrito', COUNT(*) FROM perfil_perrito
UNION ALL
SELECT 'quiz_preguntas', COUNT(*) FROM quiz_preguntas
UNION ALL
SELECT 'quiz_opciones', COUNT(*) FROM quiz_opciones
UNION ALL
SELECT 'solicitudes_adopcion', COUNT(*) FROM solicitudes_adopcion;

-- ============================================================================
-- TESTING DE VISTAS
-- ============================================================================

-- Verificar vista de perritos disponibles
SELECT 
    COUNT(*) as total_perritos_disponibles,
    AVG(nivel_energia) as energia_promedio,
    AVG(experiencia_necesaria) as experiencia_promedio
FROM vista_matching_disponible;

-- Verificar vista de adoptantes activos
SELECT 
    COUNT(*) as total_adoptantes_activos,
    AVG(nivel_actividad) as actividad_promedio,
    COUNT(CASE WHEN tiene_niños = 1 THEN 1 END) as con_niños
FROM vista_adoptantes_activos;

-- ============================================================================
-- TESTING DE RELACIONES
-- ============================================================================

-- Verificar que todos los perritos tienen perfil
SELECT 
    p.id,
    p.nombre,
    CASE WHEN pp.id IS NULL THEN 'SIN PERFIL' ELSE 'CON PERFIL' END as estado_perfil
FROM perritos p
LEFT JOIN perfil_perrito pp ON p.id = pp.perrito_id
WHERE p.disponible = 1;

-- Verificar que todos los adoptantes activos tienen perfil
SELECT 
    u.id,
    u.nombre,
    u.apellidos,
    CASE WHEN pa.id IS NULL THEN 'SIN PERFIL' ELSE 'CON PERFIL' END as estado_perfil
FROM usuarios u
LEFT JOIN perfil_adoptante pa ON u.id = pa.usuario_id
WHERE u.tipo_usuario = 'adoptante' AND u.activo = 1;

-- ============================================================================
-- SIMULACIÓN DE MATCHING SIMPLE
-- ============================================================================

-- Ejemplo de cálculo de compatibilidad básico
SELECT 
    a.usuario_id,
    a.nombre as adoptante,
    p.perrito_id,
    p.nombre as perrito,
    -- Diferencias en puntuaciones (mientras menor, mejor)
    ABS(a.nivel_actividad - p.nivel_energia) as diff_energia,
    ABS(a.experiencia_mascotas - p.experiencia_necesaria) as diff_experiencia,
    ABS(a.tiempo_disponible - p.tiempo_atencion_necesario) as diff_tiempo,
    -- Compatibilidad básica (mientras menor la suma, mejor match)
    (ABS(a.nivel_actividad - p.nivel_energia) + 
     ABS(a.experiencia_mascotas - p.experiencia_necesaria) + 
     ABS(a.tiempo_disponible - p.tiempo_atencion_necesario)) as puntuacion_diferencia,
    -- Convertir a porcentaje de compatibilidad
    ROUND(100 - ((ABS(a.nivel_actividad - p.nivel_energia) + 
                  ABS(a.experiencia_mascotas - p.experiencia_necesaria) + 
                  ABS(a.tiempo_disponible - p.tiempo_atencion_necesario)) * 100 / 30), 2) as porcentaje_compatibilidad
FROM vista_adoptantes_activos a
CROSS JOIN vista_matching_disponible p
ORDER BY porcentaje_compatibilidad DESC
LIMIT 10;
