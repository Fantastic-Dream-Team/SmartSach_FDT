<?php
require_once __DIR__ . '/../../config/database.php';

class Ruta {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Obtiene todas las rutas de recolección.
     */
    public function getAllRoutes() {
        $sql = "SELECT * FROM public.rutas ORDER BY ruta_id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Alias de getAllRoutes() para compatibilidad con el controlador.
     */
    public function findAll() {
        return $this->getAllRoutes();
    }

    /**
     * Obtiene una ruta por ID.
     */
    public function findById($id) {
        $sql = "SELECT * FROM public.rutas WHERE ruta_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Crea una nueva ruta de camión.
     */
    public function create($nombre, $zonaSector, $horarioEstimado) {
        $sql = "INSERT INTO public.rutas (nombre_ruta, zona_sector, horario_estimado) 
                VALUES (:nombre, :zona_sector, :horario_estimado) RETURNING ruta_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'nombre' => $nombre,
            'zona_sector' => $zonaSector,
            'horario_estimado' => $horarioEstimado
        ]);
        
        $result = $stmt->fetch();
        return $result ? $result['ruta_id'] : false;
    }

    /**
     * Actualiza el estado de la ruta.
     */
    public function updateEstado($id, $estado) {
        $sql = "UPDATE public.rutas SET estado_ruta = :estado WHERE ruta_id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'estado' => $estado,
            'id' => $id
        ]);
    }

    /**
     * Obtiene todos los clientes (con ubicación) suscritos a una ruta específica.
     * Solo devuelve clientes con estado 'al_dia' o 'moroso' para que el conductor vea ambos.
     */
    public function getClientesByRuta($rutaId) {
        $sql = "SELECT 
                    u.usuario_id,
                    u.nombre || ' ' || u.apellido AS cliente_nombre,
                    u.direccion,
                    u.telefono,
                    us.ubicacion_id,
                    us.descripcion_direccion,
                    ST_Y(us.coordenadas_gps::geometry) AS latitud,
                    ST_X(us.coordenadas_gps::geometry) AS longitud,
                    s.estado_pago,
                    CASE 
                        WHEN s.proximo_vencimiento < CURRENT_DATE THEN 'moroso'
                        ELSE s.estado_pago
                    END AS estado_financiero
                FROM public.suscripciones s
                JOIN public.usuarios u ON s.usuario_id = u.usuario_id
                JOIN public.ubicaciones_servicio us ON s.ubicacion_id = us.ubicacion_id
                WHERE s.ruta_id = :ruta_id
                  AND u.rol = 'Cliente'
                  AND (s.estado_suscripcion = 'activa' OR s.estado_suscripcion IS NULL)
                ORDER BY s.estado_pago DESC, u.nombre ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['ruta_id' => $rutaId]);
        return $stmt->fetchAll();
    }
}