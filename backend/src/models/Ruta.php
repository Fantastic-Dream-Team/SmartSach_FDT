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
}

