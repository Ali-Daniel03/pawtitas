-- PAWTITAS - Datos del Quiz para Matching
-- Compatible con MariaDB 10.9.2

USE pawtitas_db;

-- ============================================================================
-- PREGUNTAS DEL QUIZ
-- ============================================================================

-- Categoría: Actividad
INSERT INTO quiz_preguntas (categoria, pregunta, tipo_respuesta, orden_display) VALUES
('actividad', '¿Qué tan activo/a te consideras físicamente?', 'escala', 1),
('actividad', '¿Con qué frecuencia haces ejercicio o actividades al aire libre?', 'multiple', 2);

-- Categoría: Experiencia
INSERT INTO quiz_preguntas (categoria, pregunta, tipo_respuesta, orden_display) VALUES
('experiencia', '¿Cuánta experiencia tienes cuidando mascotas?', 'multiple', 1),
('experiencia', '¿Has entrenado a un perro antes?', 'multiple', 2);

-- Categoría: Tiempo
INSERT INTO quiz_preguntas (categoria, pregunta, tipo_respuesta, orden_display) VALUES
('tiempo', '¿Cuántas horas al día podrías dedicar a tu mascota?', 'multiple', 1),
('tiempo', '¿Trabajas desde casa o tienes horarios flexibles?', 'multiple', 2);

-- Categoría: Entrenamiento
INSERT INTO quiz_preguntas (categoria, pregunta, tipo_respuesta, orden_display) VALUES
('entrenamiento', '¿Qué tanta paciencia tienes para entrenar a una mascota?', 'escala', 1),
('entrenamiento', '¿Estarías dispuesto/a a tomar clases de entrenamiento?', 'booleana', 2);

-- Categoría: Ruido
INSERT INTO quiz_preguntas (categoria, pregunta, tipo_respuesta, orden_display) VALUES
('ruido', '¿Qué tan tolerante eres a los ladridos y ruidos de mascotas?', 'escala', 1),
('ruido', '¿Vives en un lugar donde el ruido podría ser un problema?', 'booleana', 2);

-- Categoría: Sociabilidad
INSERT INTO quiz_preguntas (categoria, pregunta, tipo_respuesta, orden_display) VALUES
('sociabilidad', '¿Prefieres una mascota que sea muy sociable y cariñosa?', 'escala', 1),
('sociabilidad', '¿Te gusta que las mascotas interactúen con visitantes?', 'multiple', 2);

-- Categoría: Independencia
INSERT INTO quiz_preguntas (categoria, pregunta, tipo_respuesta, orden_display) VALUES
('independencia', '¿Prefieres una mascota independiente o que necesite mucha atención?', 'escala', 1),
('independencia', '¿Te molesta si tu mascota te sigue a todas partes?', 'multiple', 2);

-- Categoría: Cuidados Especiales
INSERT INTO quiz_preguntas (categoria, pregunta, tipo_respuesta, orden_display) VALUES
('cuidados', '¿Estarías dispuesto/a a cuidar una mascota con necesidades médicas especiales?', 'multiple', 1),
('cuidados', '¿Tienes experiencia administrando medicamentos a mascotas?', 'booleana', 2);

-- ============================================================================
-- OPCIONES DE RESPUESTA
-- ============================================================================

-- Pregunta 1: Nivel de actividad física
INSERT INTO quiz_opciones (pregunta_id, texto_opcion, valor_numerico, orden_display) VALUES
(1, 'Muy sedentario/a - Prefiero actividades tranquilas', 1, 1),
(1, 'Poco activo/a - Ocasionalmente hago ejercicio', 3, 2),
(1, 'Moderadamente activo/a - Ejercicio regular', 6, 3),
(1, 'Muy activo/a - Ejercicio diario', 8, 4),
(1, 'Extremadamente activo/a - Deportista/atleta', 10, 5);

-- Pregunta 2: Frecuencia de ejercicio
INSERT INTO quiz_opciones (pregunta_id, texto_opcion, valor_numerico, orden_display) VALUES
(2, 'Rara vez o nunca', 1, 1),
(2, '1-2 veces por semana', 3, 2),
(2, '3-4 veces por semana', 6, 3),
(2, '5-6 veces por semana', 8, 4),
(2, 'Todos los días', 10, 5);

-- Pregunta 3: Experiencia con mascotas
INSERT INTO quiz_opciones (pregunta_id, texto_opcion, valor_numerico, orden_display) VALUES
(3, 'Ninguna experiencia', 0, 1),
(3, 'Muy poca - Solo he convivido ocasionalmente', 2, 2),
(3, 'Algo de experiencia - He cuidado mascotas de familiares/amigos', 4, 3),
(3, 'Experiencia moderada - He tenido mascotas antes', 7, 4),
(3, 'Mucha experiencia - He cuidado múltiples mascotas', 10, 5);

-- Pregunta 4: Experiencia entrenando
INSERT INTO quiz_opciones (pregunta_id, texto_opcion, valor_numerico, orden_display) VALUES
(4, 'Nunca he entrenado un perro', 0, 1),
(4, 'He intentado entrenar pero sin mucho éxito', 2, 2),
(4, 'He entrenado con ayuda de otros', 5, 3),
(4, 'He entrenado exitosamente por mi cuenta', 8, 4),
(4, 'Tengo experiencia entrenando múltiples perros', 10, 5);

-- Pregunta 5: Tiempo disponible diario
INSERT INTO quiz_opciones (pregunta_id, texto_opcion, valor_numerico, orden_display) VALUES
(5, 'Menos de 1 hora', 1, 1),
(5, '1-2 horas', 3, 2),
(5, '3-4 horas', 5, 3),
(5, '5-6 horas', 7, 4),
(5, 'Más de 6 horas', 10, 5);

-- Pregunta 6: Flexibilidad horaria
INSERT INTO quiz_opciones (pregunta_id, texto_opcion, valor_numerico, orden_display) VALUES
(6, 'Trabajo tiempo completo fuera de casa', 2, 1),
(6, 'Trabajo medio tiempo', 5, 2),
(6, 'Trabajo desde casa ocasionalmente', 7, 3),
(6, 'Trabajo desde casa la mayoría del tiempo', 9, 4),
(6, 'Estoy en casa todo el tiempo', 10, 5);

-- Pregunta 7: Paciencia para entrenamiento
INSERT INTO quiz_opciones (pregunta_id, texto_opcion, valor_numerico, orden_display) VALUES
(7, 'Muy poca paciencia (1-2)', 1, 1),
(7, 'Poca paciencia (3-4)', 3, 2),
(7, 'Paciencia moderada (5-6)', 5, 3),
(7, 'Mucha paciencia (7-8)', 7, 4),
(7, 'Paciencia extrema (9-10)', 10, 5);

-- Pregunta 8: Disposición a clases de entrenamiento
INSERT INTO quiz_opciones (pregunta_id, texto_opcion, valor_numerico, orden_display) VALUES
(8, 'Sí, definitivamente', 8, 1),
(8, 'No, prefiero hacerlo solo/a', 3, 2);

-- Pregunta 9: Tolerancia al ruido
INSERT INTO quiz_opciones (pregunta_id, texto_opcion, valor_numerico, orden_display) VALUES
(9, 'Muy baja tolerancia (1-2)', 1, 1),
(9, 'Baja tolerancia (3-4)', 3, 2),
(9, 'Tolerancia moderada (5-6)', 5, 3),
(9, 'Alta tolerancia (7-8)', 7, 4),
(9, 'Muy alta tolerancia (9-10)', 10, 5);

-- Pregunta 10: Problema con ruido en el hogar
INSERT INTO quiz_opciones (pregunta_id, texto_opcion, valor_numerico, orden_display) VALUES
(10, 'Sí, podría ser problemático', 2, 1),
(10, 'No, no hay problema con ruido', 8, 2);

-- Pregunta 11: Preferencia por sociabilidad
INSERT INTO quiz_opciones (pregunta_id, texto_opcion, valor_numerico, orden_display) VALUES
(11, 'Prefiero mascotas reservadas (1-2)', 1, 1),
(11, 'Algo reservadas (3-4)', 3, 2),
(11, 'Equilibrio (5-6)', 5, 3),
(11, 'Bastante sociables (7-8)', 7, 4),
(11, 'Muy sociables (9-10)', 10, 5);

-- Pregunta 12: Interacción con visitantes
INSERT INTO quiz_opciones (pregunta_id, texto_opcion, valor_numerico, orden_display) VALUES
(12, 'Prefiero que sea reservado con extraños', 2, 1),
(12, 'Me da igual', 5, 2),
(12, 'Me gusta que sea amigable con visitantes', 8, 3);

-- Pregunta 13: Independencia vs Atención
INSERT INTO quiz_opciones (pregunta_id, texto_opcion, valor_numerico, orden_display) VALUES
(13, 'Muy independiente (1-2)', 9, 1),
(13, 'Algo independiente (3-4)', 7, 2),
(13, 'Equilibrio (5-6)', 5, 3),
(13, 'Necesita atención (7-8)', 3, 4),
(13, 'Necesita mucha atención (9-10)', 1, 5);

-- Pregunta 14: Mascota que sigue a todas partes
INSERT INTO quiz_opciones (pregunta_id, texto_opcion, valor_numerico, orden_display) VALUES
(14, 'Me encantaría', 1, 1),
(14, 'No me molestaría', 3, 2),
(14, 'Prefiero algo de espacio personal', 7, 3),
(14, 'Me molestaría mucho', 9, 4);

-- Pregunta 15: Cuidados médicos especiales
INSERT INTO quiz_opciones (pregunta_id, texto_opcion, valor_numerico, orden_display) VALUES
(15, 'No, prefiero una mascota sin complicaciones', 0, 1),
(15, 'Solo cuidados menores', 3, 2),
(15, 'Sí, si no es muy complicado', 6, 3),
(15, 'Sí, estoy preparado/a para cualquier cuidado', 10, 4);

-- Pregunta 16: Experiencia con medicamentos
INSERT INTO quiz_opciones (pregunta_id, texto_opcion, valor_numerico, orden_display) VALUES
(16, 'Sí, tengo experiencia', 8, 1),
(16, 'No, pero estaría dispuesto/a a aprender', 5, 2),
(16, 'No, me daría miedo', 1, 3);
