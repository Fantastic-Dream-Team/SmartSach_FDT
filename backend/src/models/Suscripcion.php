<?php
require_once __DIR__ . '/../../config/database.php';

class Suscripcion {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Obtiene las suscripciones de un usuario que estén activas.
     */
    public function findByUsuarioId($usuarioId) {
        $sql = "SELECT s.*, u.nombre_referencia, u.descripcion_direccion, r.nombre_ruta 
                FROM public.suscripciones s
                LEFT JOIN public.ubicaciones_servicio u ON s.ubicacion_id = u.ubicacion_id
                LEFT JOIN public.rutas r ON s.ruta_id = r.ruta_id
                WHERE s.usuario_id = :usuario_id 
                AND (s.estado_suscripcion = 'activa' OR s.estado_suscripcion IS NULL)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['usuario_id' => $usuarioId]);
        return $stmt->fetchAll();
    }

    /**
     * Da de baja (soft delete) a una suscripción.
     */
    public function cancel($suscripcionId, $usuarioId) {
        $sql = "UPDATE public.suscripciones 
                SET estado_suscripcion = 'inactiva' 
                WHERE suscripcion_id = :id AND usuario_id = :uid";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $suscripcionId, 'uid' => $usuarioId]);
    }

    /**
     * Obtiene una suscripción por su ID.
     */
    public function findById($id) {
        $sql = "SELECT s.*, u.nombre_referencia, u.descripcion_direccion, r.nombre_ruta 
                FROM public.suscripciones s
                LEFT JOIN public.ubicaciones_servicio u ON s.ubicacion_id = u.ubicacion_id
                LEFT JOIN public.rutas r ON s.ruta_id = r.ruta_id
                WHERE s.suscripcion_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    /**
     * Crea una nueva suscripción asociada a una ubicación.
     */
    public function create($usuarioId, $ubicacionId, $rutaId = 1, $estadoPago = 'moroso') {
        $sql = "INSERT INTO public.suscripciones (usuario_id, ubicacion_id, ruta_id, fecha_activacion, proximo_vencimiento, estado_pago) 
                VALUES (:usuario_id, :ubicacion_id, :ruta_id, CURRENT_DATE, (CURRENT_DATE + INTERVAL '30 days'), :estado_pago)
                RETURNING suscripcion_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'usuario_id' => $usuarioId,
            'ubicacion_id' => $ubicacionId,
            'ruta_id' => $rutaId,
            'estado_pago' => $estadoPago
        ]);
        $result = $stmt->fetch();
        return $result ? $result['suscripcion_id'] : false;
    }
}
