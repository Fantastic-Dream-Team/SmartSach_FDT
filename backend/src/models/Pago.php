<?php
require_once __DIR__ . '/../../config/database.php';

class Pago {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Obtiene todos los pagos registrados.
     */
    public function getAllPagos() {
        $sql = "SELECT p.*, s.usuario_id, u.nombre, u.apellido 
                FROM public.pagos p
                JOIN public.suscripciones s ON p.suscripcion_id = s.suscripcion_id
                JOIN public.usuarios u ON s.usuario_id = u.usuario_id
                ORDER BY p.fecha_pago DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtiene los pagos realizados por un usuario a través de sus suscripciones.
     */
    public function findByUsuarioId($usuarioId) {
        $sql = "SELECT p.*, s.ruta_id 
                FROM public.pagos p
                JOIN public.suscripciones s ON p.suscripcion_id = s.suscripcion_id
                WHERE s.usuario_id = :usuario_id 
                ORDER BY p.fecha_pago DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['usuario_id' => $usuarioId]);
        return $stmt->fetchAll();
    }

    /**
     * Registra un pago. 
     * Idealmente se debería usar el Procedimiento Almacenado sp_procesar_pago_sach,
     * pero aquí ofrecemos la inserción básica.
     */
    public function create($suscripcionId, $monto, $metodoPago = 'efectivo', $comprobanteUrl = null) {
        $sql = "INSERT INTO public.pagos (suscripcion_id, monto, metodo_pago, comprobante_url) 
                VALUES (:suscripcion_id, :monto, :metodo_pago, :comprobante_url) RETURNING pago_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'suscripcion_id' => $suscripcionId,
            'monto' => $monto,
            'metodo_pago' => $metodoPago,
            'comprobante_url' => $comprobanteUrl
        ]);
        
        $result = $stmt->fetch();
        return $result ? $result['pago_id'] : false;
    }
}
