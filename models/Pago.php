<?php
require_once __DIR__ . '/../config/database.php';

class Pago {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Obtiene el historial de pagos (tanto Pagados como Pendientes) de un usuario.
     */
    public function findHistoryByUsuarioId($usuarioId) {
        $sql = "SELECT * FROM pagos WHERE usuario_id = :usuario_id ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['usuario_id' => $usuarioId]);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene el monto total de pagos pendientes para saber el estado de cuenta.
     */
    public function getSaldoPendiente($usuarioId) {
        $sql = "SELECT SUM(monto) as total_pendiente FROM pagos WHERE usuario_id = :usuario_id AND estado = 'Pendiente'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['usuario_id' => $usuarioId]);
        $row = $stmt->fetch();
        return $row['total_pendiente'] ? floatval($row['total_pendiente']) : 0.00;
    }

    /**
     * Simula el pago de todos los cargos pendientes de un usuario.
     * Actualiza el estado a 'Pagado' e inserta los datos de fecha y referencia.
     */
    public function pagarDeudas($usuarioId, $referencia) {
        $sql = "UPDATE pagos 
                SET estado = 'Pagado', fecha_pago = CURRENT_TIMESTAMP, referencia = :referencia 
                WHERE usuario_id = :usuario_id AND estado = 'Pendiente'";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'usuario_id' => $usuarioId,
            'referencia' => $referencia
        ]);
    }

    /**
     * Agrega un nuevo cargo pendiente. Si ya existe una cuenta pendiente
     * en el mes en curso, se puede sumar o insertar un nuevo registro.
     * Insertar un nuevo registro pendiente es lo más limpio para llevar el control.
     */
    public function agregarCargo($usuarioId, $monto) {
        $sql = "INSERT INTO pagos (usuario_id, monto, estado) VALUES (:usuario_id, :monto, 'Pendiente')";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'usuario_id' => $usuarioId,
            'monto' => $monto
        ]);
    }
}
