-- PAWTITAS - Datos de Ejemplo para Testing
-- Compatible con MariaDB 10.9.2

USE pawtitas_db;

-- ============================================================================
-- USUARIOS DE EJEMPLO
-- ============================================================================

-- Admin por defecto
INSERT INTO usuarios (nombre, apellidos, email, password, tipo_usuario, telefono, ciudad, activo) VALUES
('Admin', 'Sistema', 'admin@pawtitas.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '5551234567', 'Ciudad de México', 1);

-- Adoptantes de ejemplo
INSERT INTO usuarios (nombre, apellidos, email, password, tipo_usuario, telefono, fecha_nacimiento, ciudad, ocupacion, estado_civil, activo) VALUES
('María', 'González López', 'maria.gonzalez@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'adoptante', '5551111111', '1990-05-15', 'Ciudad de México', 'Ingeniera', 'soltero', 1),
('Carlos', 'Rodríguez Pérez', 'carlos.rodriguez@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'adoptante', '5552222222', '1985-08-22', 'Guadalajara', 'Profesor', 'casado', 1),
('Ana', 'Martínez Silva', 'ana.martinez@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'adoptante', '5553333333', '1992-12-03', 'Monterrey', 'Doctora', 'soltero', 1),
('Luis', 'Hernández Torres', 'luis.hernandez@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'adoptante', '5554444444', '1988-03-10', 'Puebla', 'Diseñador', 'casado', 1);

-- ============================================================================
-- PERFILES DE ADOPTANTES (EJEMPLOS VARIADOS)
-- ============================================================================

-- María: Persona activa, sin experiencia, vive en departamento
INSERT INTO perfil_adoptante (
    usuario_id, nivel_actividad, experiencia_mascotas, tiempo_disponible, 
    paciencia_entrenamiento, tolerancia_ruido, sociabilidad_deseada, 
    independencia_deseada, cuidados_especiales, tipo_vivienda, 
    tiene_niños, tiene_otras_mascotas, tamaño_preferido, quiz_completado, fecha_quiz
) VALUES (
    2, 8, 2, 6, 7, 5, 8, 4, 3, 'departamento', 
    FALSE, FALSE, 'pequeño,mediano', TRUE, NOW()
);

-- Carlos: Familia con niños, experiencia media, casa con jardín
INSERT INTO perfil_adoptante (
    usuario_id, nivel_actividad, experiencia_mascotas, tiempo_disponible, 
    paciencia_entrenamiento, tolerancia_ruido, sociabilidad_deseada, 
    independencia_deseada, cuidados_especiales, tipo_vivienda, 
    tiene_niños, edad_niños_menor, tiene_otras_mascotas, tamaño_preferido, quiz_completado, fecha_quiz
) VALUES (
    3, 6, 6, 5, 8, 7, 9, 3, 5, 'casa_con_jardin', 
    TRUE, 8, FALSE, 'mediano,grande', TRUE, NOW()
);

-- Ana: Profesional ocupada, poca experiencia, quiere perro independiente
INSERT INTO perfil_adoptante (
    usuario_id, nivel_actividad, experiencia_mascotas, tiempo_disponible, 
    paciencia_entrenamiento, tolerancia_ruido, sociabilidad_deseada, 
    independencia_deseada, cuidados_especiales, tipo_vivienda, 
    tiene_niños, tiene_otras_mascotas, tamaño_preferido, quiz_completado, fecha_quiz
) VALUES (
    4, 4, 1, 3, 4, 6, 5, 8, 2, 'casa_sin_jardin', 
    FALSE, FALSE, 'pequeño,mediano', TRUE, NOW()
);

-- Luis: Muy activo, mucha experiencia, puede con cuidados especiales
INSERT INTO perfil_adoptante (
    usuario_id, nivel_actividad, experiencia_mascotas, tiempo_disponible, 
    paciencia_entrenamiento, tolerancia_ruido, sociabilidad_deseada, 
    independencia_deseada, cuidados_especiales, tipo_vivienda, 
    tiene_niños, edad_niños_menor, tiene_otras_mascotas, tipo_otras_mascotas, tamaño_preferido, quiz_completado, fecha_quiz
) VALUES (
    5, 9, 9, 8, 9, 8, 7, 5, 9, 'casa_con_jardin', 
    TRUE, 12, TRUE, 'gatos', 'mediano,grande,gigante', TRUE, NOW()
);

-- ============================================================================
-- PERRITOS DE EJEMPLO
-- ============================================================================

-- Obtener ID del admin
INSERT INTO perritos (nombre, raza, edad_aproximada, tamaño, peso, sexo, color, esterilizado, vacunas_completas, microchip, personalidad, historia, disponible, tipo_especial, admin_id, refugio_origen) VALUES

-- Luna: Perrita tranquila, ideal para principiantes
('Luna', 'Mestizo', 3, 'mediano', 15.5, 'hembra', 'Café y blanco', 1, 1, 1, 
'Muy cariñosa y tranquila. Perfecta para familias primerizas. Le gusta estar cerca de las personas pero no es demandante.', 
'Luna fue rescatada de la calle cuando era cachorra. Ha sido socializada y está lista para encontrar su hogar definitivo.', 
1, 'normal', 1, 'Refugio Municipal'),

-- Max: Perro activo, necesita experiencia
('Max', 'Labrador Retriever', 2, 'grande', 28.0, 'macho', 'Dorado', 1, 1, 1, 
'Muy energético y juguetón. Necesita mucho ejercicio diario. Inteligente pero requiere entrenamiento constante.', 
'Max llegó al refugio porque su familia anterior no podía darle el ejercicio que necesita. Es un perro muy atlético.', 
1, 'normal', 1, 'Refugio Municipal'),

-- Bella: Perra de apoyo emocional
('Bella', 'Pastor Alemán', 4, 'grande', 25.0, 'hembra', 'Negro y café', 1, 1, 1, 
'Muy tranquila y empática. Entrenada como perro de apoyo emocional. Excelente para personas que necesitan compañía especial.', 
'Bella fue entrenada específicamente para apoyo emocional. Busca una persona que necesite su compañía especial.', 
1, 'apoyo_emocional', 1, 'Centro de Entrenamiento Especializado'),

-- Rocky: Perro muy activo, para expertos
('Rocky', 'Border Collie', 1, 'mediano', 20.0, 'macho', 'Negro y blanco', 1, 1, 0, 
'Extremadamente inteligente y energético. Necesita estimulación mental constante. Ideal para personas muy activas.', 
'Rocky es un perro de trabajo que necesita una familia que entienda sus necesidades de ejercicio mental y físico.', 
1, 'normal', 1, 'Refugio Rural'),

-- Coco: Perrita pequeña e independiente
('Coco', 'Chihuahua', 5, 'pequeño', 3.5, 'hembra', 'Blanco', 1, 1, 1, 
'Pequeña pero con gran personalidad. Muy independiente. Perfecta para personas que quieren compañía sin mucha demanda.', 
'Coco llegó al refugio por problemas económicos de su familia anterior. Es muy dulce pero independiente.', 
1, 'normal', 1, 'Refugio Municipal'),

-- Thor: Perro guía
('Thor', 'Golden Retriever', 6, 'grande', 32.0, 'macho', 'Dorado', 1, 1, 1, 
'Muy tranquilo y entrenado profesionalmente. Perro guía para personas con discapacidad visual. Extremadamente obediente.', 
'Thor fue entrenado profesionalmente como perro guía. Está listo para ayudar a alguien que lo necesite.', 
1, 'perro_guia', 1, 'Escuela de Perros Guía'),

-- Milo: Perro con necesidades especiales
('Milo', 'Beagle', 7, 'mediano', 18.0, 'macho', 'Tricolor', 1, 1, 1, 
'Perro mayor muy cariñoso. Necesita medicación diaria para artritis. Perfecto para alguien que busca un compañero tranquilo.', 
'Milo necesita cuidados especiales por su edad, pero tiene mucho amor que dar. Busca un hogar tranquilo.', 
1, 'normal', 1, 'Refugio de Animales Mayores'),

-- Nala: Perra sociable para familias
('Nala', 'Mestizo', 2, 'mediano', 22.0, 'hembra', 'Atigrado', 1, 1, 1, 
'Muy sociable y buena con niños. Le encanta jugar pero también sabe cuándo relajarse. Ideal para familias activas.', 
'Nala es perfecta para familias. Ha convivido con niños y otros perros sin problemas.', 
1, 'normal', 1, 'Refugio Familiar');

-- ============================================================================
-- PERFILES DE PERRITOS
-- ============================================================================

-- Luna: Tranquila, fácil para principiantes
INSERT INTO perfil_perrito (
    perrito_id, nivel_energia, experiencia_necesaria, tiempo_atencion_necesario, 
    dificultad_entrenamiento, nivel_ruido, sociabilidad, independencia, 
    necesita_cuidados_especiales, bueno_con_niños, edad_minima_niños, 
    bueno_con_otros_perros, bueno_con_gatos, necesita_jardin, perfil_completado
) VALUES (
    1, 4, 2, 5, 3, 2, 7, 6, 1, TRUE, 5, TRUE, TRUE, FALSE, TRUE
);

-- Max: Muy energético, necesita experiencia
INSERT INTO perfil_perrito (
    perrito_id, nivel_energia, experiencia_necesaria, tiempo_atencion_necesario, 
    dificultad_entrenamiento, nivel_ruido, sociabilidad, independencia, 
    necesita_cuidados_especiales, bueno_con_niños, edad_minima_niños, 
    bueno_con_otros_perros, bueno_con_gatos, necesita_jardin, perfil_completado
) VALUES (
    2, 9, 7, 8, 6, 5, 8, 3, 2, TRUE, 8, TRUE, FALSE, TRUE, TRUE
);

-- Bella: Apoyo emocional, muy tranquila
INSERT INTO perfil_perrito (
    perrito_id, nivel_energia, experiencia_necesaria, tiempo_atencion_necesario, 
    dificultad_entrenamiento, nivel_ruido, sociabilidad, independencia, 
    necesita_cuidados_especiales, bueno_con_niños, edad_minima_niños, 
    bueno_con_otros_perros, bueno_con_gatos, necesita_jardin, perfil_completado
) VALUES (
    3, 3, 5, 7, 2, 1, 6, 4, 3, TRUE, 10, TRUE, TRUE, FALSE, TRUE
);

-- Rocky: Extremadamente activo, solo para expertos
INSERT INTO perfil_perrito (
    perrito_id, nivel_energia, experiencia_necesaria, tiempo_atencion_necesario, 
    dificultad_entrenamiento, nivel_ruido, sociabilidad, independencia, 
    necesita_cuidados_especiales, bueno_con_niños, edad_minima_niños, 
    bueno_con_otros_perros, bueno_con_gatos, necesita_jardin, perfil_completado
) VALUES (
    4, 10, 9, 10, 8, 6, 5, 2, 1, TRUE, 12, TRUE, FALSE, TRUE, TRUE
);

-- Coco: Independiente, poco mantenimiento
INSERT INTO perfil_perrito (
    perrito_id, nivel_energia, experiencia_necesaria, tiempo_atencion_necesario, 
    dificultad_entrenamiento, nivel_ruido, sociabilidad, independencia, 
    necesita_cuidados_especiales, bueno_con_niños, edad_minima_niños, 
    bueno_con_otros_perros, bueno_con_gatos, necesita_jardin, perfil_completado
) VALUES (
    5, 3, 1, 3, 4, 4, 4, 9, 0, TRUE, 8, FALSE, TRUE, FALSE, TRUE
);

-- Thor: Perro guía, muy entrenado
INSERT INTO perfil_perrito (
    perrito_id, nivel_energia, experiencia_necesaria, tiempo_atencion_necesario, 
    dificultad_entrenamiento, nivel_ruido, sociabilidad, independencia, 
    necesita_cuidados_especiales, bueno_con_niños, edad_minima_niños, 
    bueno_con_otros_perros, bueno_con_gatos, necesita_jardin, perfil_completado
) VALUES (
    6, 4, 8, 6, 1, 1, 5, 7, 2, TRUE, 0, TRUE, TRUE, FALSE, TRUE
);

-- Milo: Cuidados especiales, muy tranquilo
INSERT INTO perfil_perrito (
    perrito_id, nivel_energia, experiencia_necesaria, tiempo_atencion_necesario, 
    dificultad_entrenamiento, nivel_ruido, sociabilidad, independencia, 
    necesita_cuidados_especiales, bueno_con_niños, edad_minima_niños, 
    bueno_con_otros_perros, bueno_con_gatos, necesita_jardin, perfil_completado
) VALUES (
    7, 2, 6, 4, 2, 1, 8, 5, 8, TRUE, 5, TRUE, TRUE, FALSE, TRUE
);

-- Nala: Equilibrada, buena para familias
INSERT INTO perfil_perrito (
    perrito_id, nivel_energia, experiencia_necesaria, tiempo_atencion_necesario, 
    dificultad_entrenamiento, nivel_ruido, sociabilidad, independencia, 
    necesita_cuidados_especiales, bueno_con_niños, edad_minima_niños, 
    bueno_con_otros_perros, bueno_con_gatos, necesita_jardin, perfil_completado
) VALUES (
    8, 6, 4, 6, 4, 3, 9, 4, 2, TRUE, 3, TRUE, TRUE, FALSE, TRUE
);

-- ============================================================================
-- ALGUNAS SOLICITUDES DE EJEMPLO
-- ============================================================================

INSERT INTO solicitudes_adopcion (usuario_id, perrito_id, estado, mensaje_adoptante, puntuacion_compatibilidad, fecha_solicitud) VALUES
(2, 1, 'pendiente', 'Me encantaría adoptar a Luna. Vivo en un departamento pero tengo tiempo para pasearla.', 85.5, NOW() - INTERVAL 2 DAY),
(3, 8, 'en_revision', 'Nala sería perfecta para mi familia. Tenemos niños y buscamos un perro sociable.', 92.3, NOW() - INTERVAL 1 DAY),
(4, 5, 'aprobada', 'Coco parece ideal para mi estilo de vida. Busco un perro independiente.', 88.7, NOW() - INTERVAL 3 DAY);
