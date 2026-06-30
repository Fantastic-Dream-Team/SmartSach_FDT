<?php
require_once __DIR__ . '/../../config/database.php';

class Notificacion {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Obtiene notificaciones de un usuario.
     */
    public function findByUsuarioId($usuarioId, $soloNoLeidas = false) {
        $sql = "SELECT * FROM public.notificaciones WHERE usuario_id = :usuario_id ";
        if ($soloNoLeidas) {
            $sql .= " AND leido = FALSE ";
        }
        $sql .= " ORDER BY fecha_envio DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['usuario_id' => $usuarioId]);
        return $stmt->fetchAll();
    }

    /**
     * Marca una notificación como leída.
     */
    public function markAsRead($id) {
        $sql = "UPDATE public.notificaciones SET leido = TRUE WHERE notificacion_id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Crea una notificación para un usuario
     */
    public function create($usuarioId, $titulo, $mensaje, $tipo = 'sistema') {
        $sql = "INSERT INTO public.notificaciones (usuario_id, titulo, mensaje, tipo_notificacion) 
                VALUES (:usuario_id, :titulo, :mensaje, :tipo)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'usuario_id' => $usuarioId,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'tipo' => $tipo
        ]);
    }
}
