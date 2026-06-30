<?php
require_once __DIR__ . '/../../config/database.php';

class ReporteIncidencia {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Obtiene todos los reportes de incidencias.
     */
    public function getAllReportes() {
        $sql = "SELECT r.*, u.nombre, u.apellido, us.nombre_referencia 
                FROM public.reportes_incidencias r
                JOIN public.usuarios u ON r.usuario_id = u.usuario_id
                JOIN public.ubicaciones_servicio us ON r.ubicacion_id = us.ubicacion_id
                ORDER BY r.fecha_reporte DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtiene los reportes de un usuario específico.
     */
    public function findByUsuarioId($usuarioId) {
        $sql = "SELECT r.*, us.nombre_referencia 
                FROM public.reportes_incidencias r
                JOIN public.ubicaciones_servicio us ON r.ubicacion_id = us.ubicacion_id
                WHERE r.usuario_id = :usuario_id 
                ORDER BY r.fecha_reporte DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['usuario_id' => $usuarioId]);
        return $stmt->fetchAll();
    }

    /**
     * Crea un nuevo reporte de incidencia.
     */
    public function create($usuarioId, $ubicacionId, $tipoIncidencia, $descripcion) {
        $sql = "INSERT INTO public.reportes_incidencias (usuario_id, ubicacion_id, tipo_incidencia, descripcion) 
                VALUES (:usuario_id, :ubicacion_id, :tipo_incidencia, :descripcion) RETURNING reporte_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'usuario_id' => $usuarioId,
            'ubicacion_id' => $ubicacionId,
            'tipo_incidencia' => $tipoIncidencia,
            'descripcion' => $descripcion
        ]);
        
        $result = $stmt->fetch();
        return $result ? $result['reporte_id'] : false;
    }

    /**
     * Actualiza el estado del reporte.
     */
    public function updateEstado($reporteId, $estado) {
        $sql = "UPDATE public.reportes_incidencias 
                SET estado_reporte = :estado 
                WHERE reporte_id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'estado' => $estado,
            'id' => $reporteId
        ]);
    }
}
