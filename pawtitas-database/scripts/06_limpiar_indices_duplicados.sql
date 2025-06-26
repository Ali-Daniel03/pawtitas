-- PAWTITAS - Limpiar índices duplicados si es necesario
-- Solo ejecuta si hay problemas con índices

USE pawtitas_db;

-- ============================================================================
-- ELIMINAR ÍNDICES DUPLICADOS (SI EXISTEN)
-- ============================================================================

-- Verificar si existen índices duplicados antes de eliminar
-- Ejecuta solo las líneas que necesites según los warnings

-- Ejemplo de cómo eliminar un índice duplicado:
-- DROP INDEX nombre_del_indice ON nombre_tabla;

-- Si hay problemas con índices compuestos muy largos:
-- DROP INDEX idx_adoptante_perfil_matching ON perfil_adoptante;
-- DROP INDEX idx_perrito_perfil_matching ON perfil_perrito;

-- ============================================================================
-- RECREAR ÍNDICES OPTIMIZADOS
-- ============================================================================

-- Índices más simples y eficientes
-- CREATE INDEX idx_adoptante_basico ON perfil_adoptante(usuario_id, quiz_completado);
-- CREATE INDEX idx_perrito_basico ON perfil_perrito(perrito_id, perfil_completado);

-- ============================================================================
-- VERIFICAR ESTADO FINAL
-- ============================================================================
SHOW WARNINGS;
