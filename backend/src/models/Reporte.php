<?php
require_once __DIR__ . '/../../config/database.php';

class Reporte {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Obtiene el total de incidencias activas (abiertas o en proceso) para un usuario.
     * Utilizado en header.php para mostrar notificaciones.
     */
    public function getReportesCountByUsuario($usuarioId) {
        try {
            $sql = "SELECT COUNT(*) as total 
                    FROM public.reportes_incidencias 
                    WHERE usuario_id = :usuario_id 
                    AND estado_reporte NOT IN ('cerrado', 'resuelto')";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['usuario_id' => $usuarioId]);
            $result = $stmt->fetch();
            return $result ? (int)$result['total'] : 0;
        } catch (PDOException $e) {
            // Manejo seguro: retornar 0 en lugar de bloquear la interfaz
            return 0;
        }
    }

    /**
     * Obtiene el conteo de respuestas no leídas en los reportes.
     * Stub seguro ya que las respuestas no están implementadas todavía en la BD.
     */
    public function getUnreadRepliesCount($usuarioId) {
        // WIP: Se podría enlazar a public.notificaciones más adelante.
        return 0;
    }

    /**
     * Obtiene todos los reportes de un usuario
     */
    public function getAllByUsuarioId($usuarioId) {
        try {
            $sql = "SELECT r.*, u.nombre_referencia as ubicacion_nombre
                    FROM public.reportes_incidencias r
                    LEFT JOIN public.ubicaciones_servicio u ON r.ubicacion_id = u.ubicacion_id
                    WHERE r.usuario_id = :usuario_id
                    ORDER BY r.fecha_reporte DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['usuario_id' => $usuarioId]);
            return $stmt->fetchAll() ?: [];
        } catch (PDOException $e) {
            return [];
        }
    }
}
