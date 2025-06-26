-- PAWTITAS - Vistas y funciones básicas
-- Compatible con MariaDB 10.9.2

USE pawtitas_db;

-- ============================================================================
-- VISTA PARA MATCHING DISPONIBLE
-- ============================================================================

CREATE VIEW vista_matching_disponible AS
SELECT 
    p.id as perrito_id,
    p.nombre,
    p.raza,
    p.edad_aproximada,
    p.tamaño,
    p.sexo,
    p.foto_principal,
    p.personalidad,
    p.tipo_especial,
    pp.nivel_energia,
    pp.experiencia_necesaria,
    pp.tiempo_atencion_necesario,
    pp.dificultad_entrenamiento,
    pp.nivel_ruido,
    pp.sociabilidad,
    pp.independencia,
    pp.necesita_cuidados_especiales,
    pp.bueno_con_niños,
    pp.edad_minima_niños,
    pp.bueno_con_otros_perros,
    pp.bueno_con_gatos,
    pp.necesita_jardin
FROM perritos p
INNER JOIN perfil_perrito pp ON p.id = pp.perrito_id
WHERE p.disponible = 1 
  AND pp.perfil_completado = 1;

-- ============================================================================
-- VISTA PARA ADOPTANTES CON PERFIL COMPLETO
-- ============================================================================

CREATE VIEW vista_adoptantes_activos AS
SELECT 
    u.id as usuario_id,
    u.nombre,
    u.apellidos,
    u.email,
    pa.nivel_actividad,
    pa.experiencia_mascotas,
    pa.tiempo_disponible,
    pa.paciencia_entrenamiento,
    pa.tolerancia_ruido,
    pa.sociabilidad_deseada,
    pa.independencia_deseada,
    pa.cuidados_especiales,
    pa.tipo_vivienda,
    pa.tiene_niños,
    pa.edad_niños_menor,
    pa.tiene_otras_mascotas,
    pa.tipo_otras_mascotas,
    pa.tamaño_preferido,
    pa.fecha_quiz
FROM usuarios u
INNER JOIN perfil_adoptante pa ON u.id = pa.usuario_id
WHERE u.activo = 1 
  AND u.tipo_usuario = 'adoptante'
  AND pa.quiz_completado = 1;

-- ============================================================================
-- ÍNDICES ADICIONALES PARA PERFORMANCE
-- ============================================================================

-- Índices para búsquedas frecuentes
CREATE INDEX idx_perritos_disponible_tamaño ON perritos(disponible, tamaño);
CREATE INDEX idx_perfil_adoptante_completado ON perfil_adoptante(quiz_completado, fecha_quiz);
CREATE INDEX idx_perfil_perrito_completado ON perfil_perrito(perfil_completado);
CREATE INDEX idx_matching_usuario_porcentaje ON matching_resultados(usuario_id, porcentaje_compatibilidad);

-- Índices compuestos para el sistema de matching
CREATE INDEX idx_adoptante_perfil_matching ON perfil_adoptante(
    usuario_id, nivel_actividad, experiencia_mascotas, tiempo_disponible, 
    paciencia_entrenamiento, tolerancia_ruido, sociabilidad_deseada, 
    independencia_deseada, cuidados_especiales
);

CREATE INDEX idx_perrito_perfil_matching ON perfil_perrito(
    perrito_id, nivel_energia, experiencia_necesaria, tiempo_atencion_necesario, 
    dificultad_entrenamiento, nivel_ruido, sociabilidad, independencia, 
    necesita_cuidados_especiales
);
