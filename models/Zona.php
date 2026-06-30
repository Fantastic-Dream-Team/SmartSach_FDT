<?php
require_once __DIR__ . '/../config/database.php';

class Zona {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Obtiene todas las zonas registradas.
     */
    public function getAllZonas() {
        $sql = "SELECT z.*, u.nombre as conductor_nombre 
                FROM zonas z
                LEFT JOIN usuarios u ON u.zona_id = z.id AND u.rol = 'conductor'
                ORDER BY z.nombre_zona ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtiene los detalles de una zona específica.
     */
    public function findById($id) {
        $sql = "SELECT * FROM zonas WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Registra una nueva zona.
     */
    public function create($nombre, $descripcion) {
        $sql = "INSERT INTO zonas (nombre_zona, descripcion) VALUES (:nombre_zona, :descripcion) RETURNING id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'nombre_zona' => $nombre,
            'descripcion' => $descripcion
        ]);
        $result = $stmt->fetch();
        return $result ? $result['id'] : false;
    }

    /**
     * Actualiza el estado operativo de la zona ('inactiva', 'en_ruta', 'finalizada').
     */
    public function updateEstado($id, $estado) {
        $sql = "UPDATE zonas SET estado = :estado WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'estado' => $estado,
            'id' => $id
        ]);
    }
}
