<?php
require_once __DIR__ . '/../config/database.php';

class Reporte {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Crea un nuevo reporte de servicio.
     */
    public function create($usuarioId, $rutaId, $descripcion, $fotoUrl = null) {
        $rutaIdVal = empty($rutaId) ? null : intval($rutaId);
        
        $sql = "INSERT INTO reportes (usuario_id, ruta_id, descripcion, foto_url, estado, visto_gestor) 
                VALUES (:usuario_id, :ruta_id, :descripcion, :foto_url, 'Pendiente', FALSE) RETURNING id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'usuario_id' => $usuarioId,
            'ruta_id' => $rutaIdVal,
            'descripcion' => $descripcion,
            'foto_url' => $fotoUrl
        ]);
        
        $result = $stmt->fetch();
        return $result ? $result['id'] : false;
    }

    /**
     * Obtiene los reportes creados por un usuario.
     */
    public function findByUsuarioId($usuarioId) {
        $sql = "SELECT r.*, rt.nombre as ruta_nombre 
                FROM reportes r 
                LEFT JOIN rutas rt ON r.ruta_id = rt.id 
                WHERE r.usuario_id = :usuario_id 
                ORDER BY r.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['usuario_id' => $usuarioId]);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene todos los reportes del sistema (para uso del gestor).
     */
    public function getAllReportes() {
        $sql = "SELECT r.*, u.nombre as cliente_nombre, rt.nombre as ruta_nombre 
                FROM reportes r
                JOIN usuarios u ON r.usuario_id = u.id
                LEFT JOIN rutas rt ON r.ruta_id = rt.id
                ORDER BY r.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtiene el listado de reportes públicos de la comunidad.
     */
    public function getPublicReportes() {
        $sql = "SELECT r.*, u.nombre as cliente_nombre, u.foto_perfil as cliente_foto, rt.nombre as ruta_nombre 
                FROM reportes r
                JOIN usuarios u ON r.usuario_id = u.id
                LEFT JOIN rutas rt ON r.ruta_id = rt.id
                ORDER BY r.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Marca un reporte como visto por el Gestor.
     */
    public function markAsVisto($id) {
        $sql = "UPDATE reportes SET visto_gestor = TRUE WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Obtiene la cantidad de reportes creados por un usuario.
     */
    public function getReportesCountByUsuario($usuarioId) {
        $sql = "SELECT COUNT(*) as total FROM reportes WHERE usuario_id = :usuario_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['usuario_id' => $usuarioId]);
        $row = $stmt->fetch();
        return $row ? intval($row['total']) : 0;
    }

    /**
     * Obtiene la cantidad de respuestas de otros usuarios no leídas.
     * En este modelo contamos los comentarios hechos por otros en los reportes del usuario.
     */
    public function getUnreadRepliesCount($usuarioId) {
        $sql = "SELECT COUNT(c.id) as total 
                FROM comentarios_reportes c
                JOIN reportes r ON c.reporte_id = r.id
                WHERE r.usuario_id = :usuario_id AND c.usuario_id != :usuario_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['usuario_id' => $usuarioId]);
        $row = $stmt->fetch();
        return $row ? intval($row['total']) : 0;
    }

    // ==========================================
    // SECCIÓN DE COMENTARIOS / HILOS
    // ==========================================

    /**
     * Obtiene los comentarios de un reporte específico.
     */
    public function getComentarios($reporteId) {
        $sql = "SELECT c.*, u.nombre as usuario_nombre, u.rol as usuario_rol, u.foto_perfil as usuario_foto 
                FROM comentarios_reportes c
                JOIN usuarios u ON c.usuario_id = u.id
                WHERE c.reporte_id = :reporte_id
                ORDER BY c.fecha ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['reporte_id' => $reporteId]);
        return $stmt->fetchAll();
    }

    /**
     * Agrega un nuevo comentario a un reporte.
     */
    public function addComentario($reporteId, $usuarioId, $comentario) {
        $sql = "INSERT INTO comentarios_reportes (reporte_id, usuario_id, comentario) 
                VALUES (:reporte_id, :usuario_id, :comentario)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'reporte_id' => $reporteId,
            'usuario_id' => $usuarioId,
            'comentario' => $comentario
        ]);
    }
}
