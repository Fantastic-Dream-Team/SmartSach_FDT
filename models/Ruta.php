<?php
require_once __DIR__ . '/../config/database.php';

class Ruta {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Obtiene todas las viviendas de clientes en el sistema.
     */
    public function getAllRoutes() {
        $sql = "SELECT r.*, u.nombre as cliente_nombre, z.nombre_zona, z.estado as zona_estado,
                COALESCE((SELECT COUNT(*) FROM pagos p WHERE p.usuario_id = r.usuario_id AND p.estado = 'Pendiente'), 0) as deudas_pendientes
                FROM rutas r
                LEFT JOIN usuarios u ON r.usuario_id = u.id
                LEFT JOIN zonas z ON r.zona_id = z.id
                ORDER BY r.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtiene todas las viviendas de un usuario.
     */
    public function findByUsuarioId($usuarioId) {
        $sql = "SELECT r.*, z.nombre_zona, z.estado as zona_estado 
                FROM rutas r 
                LEFT JOIN zonas z ON r.zona_id = z.id 
                WHERE r.usuario_id = :usuario_id 
                ORDER BY r.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['usuario_id' => $usuarioId]);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene las viviendas de una zona específica, cruzando datos de pago 
     * para saber si el cliente está Moroso o Paz y Salvo.
     */
    public function findByZonaId($zonaId) {
        $sql = "SELECT r.*, u.nombre as cliente_nombre,
                COALESCE((SELECT COUNT(*) FROM pagos p WHERE p.usuario_id = r.usuario_id AND p.estado = 'Pendiente'), 0) as deudas_pendientes
                FROM rutas r
                JOIN usuarios u ON r.usuario_id = u.id
                WHERE r.zona_id = :zona_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['zona_id' => $zonaId]);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene los detalles de una vivienda.
     */
    public function findById($id) {
        $sql = "SELECT r.*, z.estado as zona_estado FROM rutas r LEFT JOIN zonas z ON r.zona_id = z.id WHERE r.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Registra una nueva vivienda de un cliente asociada a una zona.
     */
    public function create($usuarioId, $nombre, $descripcion, $latitud, $longitud, $costo = 15.00, $zonaId = null) {
        $sql = "INSERT INTO rutas (usuario_id, zona_id, nombre, descripcion, latitud, longitud, costo) 
                VALUES (:usuario_id, :zona_id, :nombre, :descripcion, :latitud, :longitud, :costo) RETURNING id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'usuario_id' => $usuarioId,
            'zona_id' => empty($zonaId) ? null : intval($zonaId),
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'latitud' => $latitud,
            'longitud' => $longitud,
            'costo' => $costo
        ]);
        
        $result = $stmt->fetch();
        return $result ? $result['id'] : false;
    }

    /**
     * Modifica los detalles de la vivienda del cliente.
     * Regla de negocio crítica: no se puede editar una ruta/vivienda si la zona está 'en_ruta'.
     */
    public function updateRouteDetails($id, $nombre, $descripcion, $costo, $zonaId) {
        $current = $this->findById($id);
        if ($current && $current['zona_estado'] === 'en_ruta') {
            throw new Exception("Regla de negocio: No se puede modificar esta vivienda porque la zona a la que pertenece está actualmente activa ('en_ruta'). El conductor debe finalizarla primero.");
        }

        $sql = "UPDATE rutas SET nombre = :nombre, descripcion = :descripcion, costo = :costo, zona_id = :zona_id WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'costo' => $costo,
            'zona_id' => empty($zonaId) ? null : intval($zonaId),
            'id' => $id
        ]);
    }
}
