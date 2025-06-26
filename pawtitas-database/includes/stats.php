<?php
/**
 * PAWTITAS - Funciones de Estadísticas (Corregidas)
 */

require_once __DIR__ . '/../config/database.php';

class StatsManager {
    private $db;
    
    public function __construct() {
        $this->db = obtener_conexion_db();
    }
    
    public function getGeneralStats() {
        try {
            $stats = [
                'adopted_dogs' => $this->getAdoptedDogsCount(),
                'available_dogs' => $this->getAvailableDogsCount(),
                'registered_shelters' => $this->getRegisteredSheltersCount(),
                'happy_families' => $this->getHappyFamiliesCount(),
                'special_dogs' => $this->getSpecialDogsCount()
            ];
            
            return $stats;
        } catch (Exception $e) {
            error_log("Error obteniendo estadísticas: " . $e->getMessage());
            return $this->getDefaultStats();
        }
    }
    
    private function getAdoptedDogsCount() {
        $query = "SELECT COUNT(*) as count FROM solicitudes_adopcion WHERE estado = 'completada'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        return (int) $result['count'];
    }
    
    private function getAvailableDogsCount() {
        $query = "SELECT COUNT(*) as count FROM perritos WHERE disponible = 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        return (int) $result['count'];
    }
    
    private function getRegisteredSheltersCount() {
        $query = "SELECT COUNT(*) as count FROM usuarios WHERE tipo_usuario = 'refugio' AND activo = 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        return (int) $result['count'];
    }
    
    private function getHappyFamiliesCount() {
        $query = "SELECT COUNT(DISTINCT usuario_id) as count FROM solicitudes_adopcion WHERE estado = 'completada'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        return (int) $result['count'];
    }
    
    private function getSpecialDogsCount() {
        $query = "SELECT COUNT(*) as count FROM perritos WHERE tipo_especial IN ('apoyo_emocional', 'perro_guia') AND disponible = 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        return (int) $result['count'];
    }
    
    public function getFeaturedDogs($limit = 3) {
        try {
            $query = "SELECT id, nombre, raza, edad_aproximada, foto_principal, personalidad 
                     FROM perritos 
                     WHERE disponible = 1 AND foto_principal IS NOT NULL 
                     ORDER BY fecha_registro DESC 
                     LIMIT :limit";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error obteniendo perritos destacados: " . $e->getMessage());
            return [];
        }
    }
    
    private function getDefaultStats() {
        return [
            'adopted_dogs' => 150,
            'available_dogs' => 45,
            'registered_shelters' => 25,
            'happy_families' => 89,
            'special_dogs' => 12
        ];
    }
}

/**
 * Función simplificada para obtener estadísticas generales
 * Usa las funciones helper de functions.php
 */
function getGeneralStats() {
    try {
        $stats = [];
        
        // Perritos adoptados
        $sql = "SELECT COUNT(*) as total FROM solicitudes_adopcion WHERE estado = 'completada'";
        $resultado = consultar_db($sql);
        $stats['adopted_dogs'] = $resultado ? $resultado[0]['total'] : 150;
        
        // Perritos disponibles
        $sql = "SELECT COUNT(*) as total FROM perritos WHERE disponible = 1";
        $resultado = consultar_db($sql);
        $stats['available_dogs'] = $resultado ? $resultado[0]['total'] : 45;
        
        // Refugios registrados
        $sql = "SELECT COUNT(*) as total FROM usuarios WHERE tipo_usuario = 'refugio' AND activo = 1";
        $resultado = consultar_db($sql);
        $stats['registered_shelters'] = $resultado ? $resultado[0]['total'] : 25;
        
        // Familias felices
        $sql = "SELECT COUNT(DISTINCT usuario_id) as total FROM solicitudes_adopcion WHERE estado = 'completada'";
        $resultado = consultar_db($sql);
        $stats['happy_families'] = $resultado ? $resultado[0]['total'] : 89;
        
        // Perritos especiales
        $sql = "SELECT COUNT(*) as total FROM perritos WHERE tipo_especial IN ('apoyo_emocional', 'perro_guia') AND disponible = 1";
        $resultado = consultar_db($sql);
        $stats['special_dogs'] = $resultado ? $resultado[0]['total'] : 12;
        
        return $stats;
        
    } catch (Exception $e) {
        error_log("Error obteniendo estadísticas: " . $e->getMessage());
        // Devolver estadísticas por defecto si hay error
        return [
            'adopted_dogs' => 150,
            'available_dogs' => 45,
            'registered_shelters' => 25,
            'happy_families' => 89,
            'special_dogs' => 12
        ];
    }
}

/**
 * Función simplificada para obtener perritos destacados
 */
function getFeaturedDogs($limit = 3) {
    try {
        $sql = "SELECT id, nombre, raza, edad_aproximada, foto_principal, personalidad 
                FROM perritos 
                WHERE disponible = 1 
                ORDER BY fecha_registro DESC 
                LIMIT ?";
        
        $resultado = consultar_db($sql, [$limit]);
        return $resultado ? $resultado : [];
        
    } catch (Exception $e) {
        error_log("Error obteniendo perritos destacados: " . $e->getMessage());
        return [];
    }
}

/**
 * Función para obtener estadísticas de un refugio específico
 */
function getRefugioStats($refugio_id) {
    try {
        $stats = [];
        
        // Total de perritos del refugio
        $sql = "SELECT COUNT(*) as total FROM perritos WHERE refugio_id = ?";
        $resultado = consultar_db($sql, [$refugio_id]);
        $stats['total_perritos'] = $resultado ? $resultado[0]['total'] : 0;
        
        // Perritos disponibles
        $sql = "SELECT COUNT(*) as total FROM perritos WHERE refugio_id = ? AND disponible = 1";
        $resultado = consultar_db($sql, [$refugio_id]);
        $stats['disponibles'] = $resultado ? $resultado[0]['total'] : 0;
        
        // Solicitudes recibidas
        $sql = "SELECT COUNT(*) as total FROM solicitudes_adopcion sa 
                JOIN perritos p ON sa.perrito_id = p.id 
                WHERE p.refugio_id = ?";
        $resultado = consultar_db($sql, [$refugio_id]);
        $stats['solicitudes_recibidas'] = $resultado ? $resultado[0]['total'] : 0;
        
        // Adopciones completadas
        $sql = "SELECT COUNT(*) as total FROM solicitudes_adopcion sa 
                JOIN perritos p ON sa.perrito_id = p.id 
                WHERE p.refugio_id = ? AND sa.estado = 'completada'";
        $resultado = consultar_db($sql, [$refugio_id]);
        $stats['adopciones_completadas'] = $resultado ? $resultado[0]['total'] : 0;
        
        return $stats;
        
    } catch (Exception $e) {
        error_log("Error obteniendo estadísticas del refugio: " . $e->getMessage());
        return [
            'total_perritos' => 0,
            'disponibles' => 0,
            'solicitudes_recibidas' => 0,
            'adopciones_completadas' => 0
        ];
    }
}

/**
 * Función para obtener estadísticas de un adoptante específico
 */
function getAdoptanteStats($usuario_id) {
    try {
        $stats = [];
        
        // Total de solicitudes enviadas
        $sql = "SELECT COUNT(*) as total FROM solicitudes_adopcion WHERE usuario_id = ?";
        $resultado = consultar_db($sql, [$usuario_id]);
        $stats['solicitudes_enviadas'] = $resultado ? $resultado[0]['total'] : 0;
        
        // Solicitudes aprobadas
        $sql = "SELECT COUNT(*) as total FROM solicitudes_adopcion WHERE usuario_id = ? AND estado = 'aprobada'";
        $resultado = consultar_db($sql, [$usuario_id]);
        $stats['solicitudes_aprobadas'] = $resultado ? $resultado[0]['total'] : 0;
        
        // Solicitudes pendientes
        $sql = "SELECT COUNT(*) as total FROM solicitudes_adopcion WHERE usuario_id = ? AND estado = 'pendiente'";
        $resultado = consultar_db($sql, [$usuario_id]);
        $stats['solicitudes_pendientes'] = $resultado ? $resultado[0]['total'] : 0;
        
        // Adopciones completadas
        $sql = "SELECT COUNT(*) as total FROM solicitudes_adopcion WHERE usuario_id = ? AND estado = 'completada'";
        $resultado = consultar_db($sql, [$usuario_id]);
        $stats['adopciones_completadas'] = $resultado ? $resultado[0]['total'] : 0;
        
        // Verificar si tiene currículum
        $stats['tiene_curriculum'] = usuario_tiene_curriculum($usuario_id);
        
        return $stats;
        
    } catch (Exception $e) {
        error_log("Error obteniendo estadísticas del adoptante: " . $e->getMessage());
        return [
            'solicitudes_enviadas' => 0,
            'solicitudes_aprobadas' => 0,
            'solicitudes_pendientes' => 0,
            'adopciones_completadas' => 0,
            'tiene_curriculum' => false
        ];
    }
}
?>
