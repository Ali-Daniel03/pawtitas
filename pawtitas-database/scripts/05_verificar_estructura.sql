-- PAWTITAS - Script para verificar índices y vistas
-- Ejecuta estas consultas para ver la estructura

USE pawtitas_db;

-- ============================================================================
-- VER TODAS LAS TABLAS
-- ============================================================================
SHOW TABLES;

-- ============================================================================
-- VER TODAS LAS VISTAS
-- ============================================================================
SHOW FULL TABLES WHERE Table_type = 'VIEW';

-- ============================================================================
-- VER ÍNDICES DE CADA TABLA IMPORTANTE
-- ============================================================================

-- Índices de usuarios
SHOW INDEX FROM usuarios;

-- Índices de perfil_adoptante
SHOW INDEX FROM perfil_adoptante;

-- Índices de perritos
SHOW INDEX FROM perritos;

-- Índices de perfil_perrito
SHOW INDEX FROM perfil_perrito;

-- Índices de solicitudes_adopcion
SHOW INDEX FROM solicitudes_adopcion;

-- Índices de matching_resultados
SHOW INDEX FROM matching_resultados;

-- ============================================================================
-- VER ESTRUCTURA DE LAS VISTAS
-- ============================================================================

-- Estructura de vista_matching_disponible
DESCRIBE vista_matching_disponible;

-- Estructura de vista_adoptantes_activos
DESCRIBE vista_adoptantes_activos;

-- ============================================================================
-- PROBAR LAS VISTAS
-- ============================================================================

-- Ver perritos disponibles para matching
SELECT perrito_id, nombre, raza, tamaño, nivel_energia, experiencia_necesaria 
FROM vista_matching_disponible 
LIMIT 5;

-- Ver adoptantes con perfil completo
SELECT usuario_id, nombre, apellidos, nivel_actividad, experiencia_mascotas, tipo_vivienda 
FROM vista_adoptantes_activos 
LIMIT 5;

-- ============================================================================
-- VER WARNINGS DEL ÚLTIMO COMANDO
-- ============================================================================
SHOW WARNINGS;

-- ============================================================================
-- INFORMACIÓN DETALLADA DE ÍNDICES
-- ============================================================================

-- Ver todos los índices de la base de datos
SELECT 
    TABLE_NAME,
    INDEX_NAME,
    COLUMN_NAME,
    SEQ_IN_INDEX,
    NON_UNIQUE,
    INDEX_TYPE
FROM INFORMATION_SCHEMA.STATISTICS 
WHERE TABLE_SCHEMA = 'pawtitas_db'
ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX;

-- ============================================================================
-- VERIFICAR TAMAÑO DE ÍNDICES
-- ============================================================================

SELECT 
    TABLE_NAME,
    INDEX_NAME,
    ROUND(STAT_VALUE * @@innodb_page_size / 1024 / 1024, 2) AS 'Index Size (MB)'
FROM INFORMATION_SCHEMA.INNODB_SYS_TABLESTATS 
WHERE TABLE_NAME LIKE '%pawtitas%';
