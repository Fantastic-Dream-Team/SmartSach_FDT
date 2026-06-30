<?php
require_once __DIR__ . '/../../config/database.php';

class UbicacionServicio {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Obtiene todas las ubicaciones de un usuario.
     */
    public function findByUsuarioId($usuarioId) {
        // Obtenemos lat y lng extrayéndolos del tipo GEOGRAPHY de PostGIS
        $sql = "SELECT ubicacion_id, usuario_id, nombre_referencia, 
                       ST_Y(coordenadas_gps::geometry) AS latitud, 
                       ST_X(coordenadas_gps::geometry) AS longitud, 
                       descripcion_direccion, foto_url, fecha_creacion 
                FROM public.ubicaciones_servicio 
                WHERE usuario_id = :usuario_id 
                ORDER BY fecha_creacion DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['usuario_id' => $usuarioId]);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene una ubicación específica.
     */
    public function findById($id) {
        $sql = "SELECT ubicacion_id, usuario_id, nombre_referencia, 
                       ST_Y(coordenadas_gps::geometry) AS latitud, 
                       ST_X(coordenadas_gps::geometry) AS longitud, 
                       descripcion_direccion, foto_url, fecha_creacion 
                FROM public.ubicaciones_servicio 
                WHERE ubicacion_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Crea una nueva ubicación con coordenadas geográficas (PostGIS).
     */
    public function create($usuarioId, $nombreReferencia, $descripcion, $latitud, $longitud, $fotoUrl = null) {
        // Usamos ST_SetSRID y ST_MakePoint(longitud, latitud) para insertar en GEOGRAPHY
        $sql = "INSERT INTO public.ubicaciones_servicio 
                (usuario_id, nombre_referencia, descripcion_direccion, coordenadas_gps, foto_url) 
                VALUES (:usuario_id, :nombre_referencia, :descripcion_direccion, 
                        ST_SetSRID(ST_MakePoint(:longitud, :latitud), 4326), :foto_url) 
                RETURNING ubicacion_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'usuario_id' => $usuarioId,
            'nombre_referencia' => $nombreReferencia,
            'descripcion_direccion' => $descripcion,
            'latitud' => $latitud,
            'longitud' => $longitud,
            'foto_url' => $fotoUrl
        ]);
        
        $result = $stmt->fetch();
        return $result ? $result['ubicacion_id'] : false;
    }

    /**
     * Modifica los detalles de la ubicación.
     */
    public function update($id, $nombreReferencia, $descripcion, $latitud, $longitud, $fotoUrl = null) {
        $sql = "UPDATE public.ubicaciones_servicio 
                SET nombre_referencia = :nombre_referencia, 
                    descripcion_direccion = :descripcion_direccion, 
                    coordenadas_gps = ST_SetSRID(ST_MakePoint(:longitud, :latitud), 4326),
                    foto_url = COALESCE(:foto_url, foto_url)
                WHERE ubicacion_id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'nombre_referencia' => $nombreReferencia,
            'descripcion_direccion' => $descripcion,
            'latitud' => $latitud,
            'longitud' => $longitud,
            'foto_url' => $fotoUrl,
            'id' => $id
        ]);
    }
}
