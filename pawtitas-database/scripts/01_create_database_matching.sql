-- PAWTITAS - Base de Datos para Sistema de Matching
-- Compatible con MariaDB 10.9.2

-- Crear base de datos
DROP DATABASE IF EXISTS pawtitas_db;
CREATE DATABASE pawtitas_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pawtitas_db;

-- ============================================================================
-- TABLA DE USUARIOS
-- ============================================================================
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    tipo_usuario ENUM('adoptante', 'admin') NOT NULL DEFAULT 'adoptante',
    telefono VARCHAR(20),
    fecha_nacimiento DATE,
    direccion TEXT,
    ciudad VARCHAR(100),
    estado VARCHAR(100),
    codigo_postal VARCHAR(10),
    ocupacion VARCHAR(100),
    estado_civil ENUM('soltero', 'casado', 'divorciado', 'viudo', 'union_libre') DEFAULT 'soltero',
    tiene_discapacidad BOOLEAN DEFAULT FALSE,
    tipo_discapacidad VARCHAR(200),
    foto_perfil VARCHAR(255),
    activo BOOLEAN DEFAULT TRUE,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Índices para usuarios
CREATE INDEX idx_email ON usuarios(email);
CREATE INDEX idx_tipo_usuario ON usuarios(tipo_usuario);
CREATE INDEX idx_activo ON usuarios(activo);

-- ============================================================================
-- TABLA DE PERFIL DE MATCHING DEL ADOPTANTE
-- ============================================================================
CREATE TABLE perfil_adoptante (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    
    -- Puntuaciones del quiz (0-10)
    nivel_actividad TINYINT NOT NULL DEFAULT 5,
    experiencia_mascotas TINYINT NOT NULL DEFAULT 0,
    tiempo_disponible TINYINT NOT NULL DEFAULT 5,
    paciencia_entrenamiento TINYINT NOT NULL DEFAULT 5,
    tolerancia_ruido TINYINT NOT NULL DEFAULT 5,
    sociabilidad_deseada TINYINT NOT NULL DEFAULT 5,
    independencia_deseada TINYINT NOT NULL DEFAULT 5,
    cuidados_especiales TINYINT NOT NULL DEFAULT 0,
    
    -- Información del hogar
    tipo_vivienda ENUM('departamento', 'casa_sin_jardin', 'casa_con_jardin') NOT NULL,
    tiene_niños BOOLEAN DEFAULT FALSE,
    edad_niños_menor TINYINT DEFAULT NULL,
    tiene_otras_mascotas BOOLEAN DEFAULT FALSE,
    tipo_otras_mascotas VARCHAR(100) DEFAULT NULL,
    
    -- Preferencias de tamaño (usando VARCHAR en lugar de SET)
    tamaño_preferido VARCHAR(100) NOT NULL,
    
    -- Metadatos
    quiz_completado BOOLEAN DEFAULT FALSE,
    fecha_quiz TIMESTAMP NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    UNIQUE KEY unique_perfil_adoptante (usuario_id)
);

-- ============================================================================
-- TABLA DE PERRITOS
-- ============================================================================
CREATE TABLE perritos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    raza VARCHAR(100),
    edad_aproximada TINYINT,
    tamaño ENUM('pequeño', 'mediano', 'grande', 'gigante') NOT NULL,
    peso DECIMAL(5,2),
    sexo ENUM('macho', 'hembra') NOT NULL,
    color VARCHAR(100),
    
    -- Información médica
    esterilizado BOOLEAN DEFAULT FALSE,
    vacunas_completas BOOLEAN DEFAULT FALSE,
    microchip BOOLEAN DEFAULT FALSE,
    cuidados_especiales TEXT,
    
    -- Información descriptiva
    personalidad TEXT,
    historia TEXT,
    foto_principal VARCHAR(255),
    
    -- Estado
    disponible BOOLEAN DEFAULT TRUE,
    tipo_especial ENUM('normal', 'apoyo_emocional', 'perro_guia', 'terapia') DEFAULT 'normal',
    
    -- Gestión
    admin_id INT,
    refugio_origen VARCHAR(200),
    fecha_rescate DATE,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (admin_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Índices para perritos
CREATE INDEX idx_disponible ON perritos(disponible);
CREATE INDEX idx_tamaño ON perritos(tamaño);
CREATE INDEX idx_tipo_especial ON perritos(tipo_especial);
CREATE INDEX idx_admin ON perritos(admin_id);

-- ============================================================================
-- TABLA DE PERFIL DE MATCHING DEL PERRITO
-- ============================================================================
CREATE TABLE perfil_perrito (
    id INT AUTO_INCREMENT PRIMARY KEY,
    perrito_id INT NOT NULL,
    
    -- Puntuaciones del perrito (0-10)
    nivel_energia TINYINT NOT NULL DEFAULT 5,
    experiencia_necesaria TINYINT NOT NULL DEFAULT 0,
    tiempo_atencion_necesario TINYINT NOT NULL DEFAULT 5,
    dificultad_entrenamiento TINYINT NOT NULL DEFAULT 5,
    nivel_ruido TINYINT NOT NULL DEFAULT 5,
    sociabilidad TINYINT NOT NULL DEFAULT 5,
    independencia TINYINT NOT NULL DEFAULT 5,
    necesita_cuidados_especiales TINYINT NOT NULL DEFAULT 0,
    
    -- Compatibilidad específica
    bueno_con_niños BOOLEAN DEFAULT TRUE,
    edad_minima_niños TINYINT DEFAULT 0,
    bueno_con_otros_perros BOOLEAN DEFAULT TRUE,
    bueno_con_gatos BOOLEAN DEFAULT TRUE,
    necesita_jardin BOOLEAN DEFAULT FALSE,
    
    -- Metadatos
    perfil_completado BOOLEAN DEFAULT FALSE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (perrito_id) REFERENCES perritos(id) ON DELETE CASCADE,
    UNIQUE KEY unique_perfil_perrito (perrito_id)
);

-- ============================================================================
-- TABLA DE FOTOS ADICIONALES
-- ============================================================================
CREATE TABLE fotos_perritos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    perrito_id INT NOT NULL,
    ruta_foto VARCHAR(255) NOT NULL,
    descripcion VARCHAR(200),
    orden_display TINYINT DEFAULT 1,
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (perrito_id) REFERENCES perritos(id) ON DELETE CASCADE
);

CREATE INDEX idx_perrito_orden ON fotos_perritos(perrito_id, orden_display);

-- ============================================================================
-- TABLA DE SOLICITUDES DE ADOPCIÓN
-- ============================================================================
CREATE TABLE solicitudes_adopcion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    perrito_id INT NOT NULL,
    
    -- Información de la solicitud
    estado ENUM('pendiente', 'en_revision', 'aprobada', 'rechazada', 'completada', 'cancelada') DEFAULT 'pendiente',
    puntuacion_compatibilidad DECIMAL(5,2) DEFAULT NULL,
    mensaje_adoptante TEXT,
    comentarios_admin TEXT,
    razon_rechazo TEXT,
    
    -- Fechas importantes
    fecha_solicitud TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_respuesta TIMESTAMP NULL,
    fecha_completada TIMESTAMP NULL,
    
    -- Metadatos
    prioridad ENUM('normal', 'alta') DEFAULT 'normal',
    notas_internas TEXT,
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (perrito_id) REFERENCES perritos(id) ON DELETE CASCADE,
    UNIQUE KEY unique_solicitud_activa (usuario_id, perrito_id)
);

-- Índices para solicitudes
CREATE INDEX idx_estado ON solicitudes_adopcion(estado);
CREATE INDEX idx_fecha_solicitud ON solicitudes_adopcion(fecha_solicitud);
CREATE INDEX idx_compatibilidad ON solicitudes_adopcion(puntuacion_compatibilidad);
CREATE INDEX idx_usuario ON solicitudes_adopcion(usuario_id);
CREATE INDEX idx_perrito ON solicitudes_adopcion(perrito_id);

-- ============================================================================
-- TABLA DE RESULTADOS DE MATCHING (CACHE)
-- ============================================================================
CREATE TABLE matching_resultados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    perrito_id INT NOT NULL,
    
    -- Puntuaciones detalladas
    diferencia_actividad DECIMAL(4,2) DEFAULT 0,
    diferencia_experiencia DECIMAL(4,2) DEFAULT 0,
    diferencia_tiempo DECIMAL(4,2) DEFAULT 0,
    diferencia_entrenamiento DECIMAL(4,2) DEFAULT 0,
    diferencia_ruido DECIMAL(4,2) DEFAULT 0,
    diferencia_sociabilidad DECIMAL(4,2) DEFAULT 0,
    diferencia_independencia DECIMAL(4,2) DEFAULT 0,
    diferencia_cuidados DECIMAL(4,2) DEFAULT 0,
    
    -- Puntuación total
    puntuacion_total DECIMAL(6,2) NOT NULL,
    porcentaje_compatibilidad DECIMAL(5,2) NOT NULL,
    
    -- Factores de incompatibilidad crítica
    incompatible_tamaño BOOLEAN DEFAULT FALSE,
    incompatible_niños BOOLEAN DEFAULT FALSE,
    incompatible_mascotas BOOLEAN DEFAULT FALSE,
    incompatible_vivienda BOOLEAN DEFAULT FALSE,
    
    -- Metadatos
    fecha_calculo TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    valido_hasta TIMESTAMP NOT NULL,
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (perrito_id) REFERENCES perritos(id) ON DELETE CASCADE,
    UNIQUE KEY unique_matching (usuario_id, perrito_id)
);

-- Índices para matching
CREATE INDEX idx_usuario_compatibilidad ON matching_resultados(usuario_id, porcentaje_compatibilidad);
CREATE INDEX idx_perrito_compatibilidad ON matching_resultados(perrito_id, porcentaje_compatibilidad);
CREATE INDEX idx_valido_hasta ON matching_resultados(valido_hasta);

-- ============================================================================
-- TABLA DE PREGUNTAS DEL QUIZ
-- ============================================================================
CREATE TABLE quiz_preguntas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria VARCHAR(50) NOT NULL,
    pregunta TEXT NOT NULL,
    tipo_respuesta ENUM('escala', 'multiple', 'booleana') NOT NULL,
    orden_display TINYINT NOT NULL,
    activa BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_categoria_orden ON quiz_preguntas(categoria, orden_display);
CREATE INDEX idx_activa ON quiz_preguntas(activa);

-- ============================================================================
-- TABLA DE OPCIONES DE RESPUESTA DEL QUIZ
-- ============================================================================
CREATE TABLE quiz_opciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pregunta_id INT NOT NULL,
    texto_opcion TEXT NOT NULL,
    valor_numerico TINYINT NOT NULL,
    orden_display TINYINT NOT NULL,
    
    FOREIGN KEY (pregunta_id) REFERENCES quiz_preguntas(id) ON DELETE CASCADE
);

CREATE INDEX idx_pregunta_orden ON quiz_opciones(pregunta_id, orden_display);

-- ============================================================================
-- TABLA DE RESPUESTAS DEL USUARIO AL QUIZ
-- ============================================================================
CREATE TABLE quiz_respuestas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    pregunta_id INT NOT NULL,
    opcion_id INT NOT NULL,
    valor_calculado TINYINT NOT NULL,
    fecha_respuesta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (pregunta_id) REFERENCES quiz_preguntas(id) ON DELETE CASCADE,
    FOREIGN KEY (opcion_id) REFERENCES quiz_opciones(id) ON DELETE CASCADE,
    UNIQUE KEY unique_respuesta (usuario_id, pregunta_id)
);

CREATE INDEX idx_usuario_quiz ON quiz_respuestas(usuario_id);
