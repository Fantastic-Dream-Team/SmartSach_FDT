<?php
require_once __DIR__ . '/../../config/database.php';

class Suscripcion {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Obtiene las suscripciones de un usuario.
     */
    public function findByUsuarioId($usuarioId) {
        $sql = "SELECT s.*, u.nombre_referencia, r.nombre_ruta 
                FROM public.suscripciones s
                LEFT JOIN public.ubicaciones_servicio u ON s.ubicacion_id = u.ubicacion_id
                LEFT JOIN public.rutas r ON s.ruta_id = r.ruta_id
                WHERE s.usuario_id = :usuario_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['usuario_id' => $usuarioId]);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene una suscripción por su ID.
     */
    public function findById($id) {
        $sql = "SELECT s.*, u.nombre_referencia, r.nombre_ruta 
                FROM public.suscripciones s
                LEFT JOIN public.ubicaciones_servicio u ON s.ubicacion_id = u.ubicacion_id
                LEFT JOIN public.rutas r ON s.ruta_id = r.ruta_id
                WHERE s.suscripcion_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
}
