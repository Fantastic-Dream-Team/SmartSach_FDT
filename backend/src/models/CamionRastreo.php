<?php
require_once __DIR__ . '/../../config/database.php';

class CamionRastreo {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Obtiene la ubicación actual de un camión asociado a una ruta.
     */
    public function findByRutaId($rutaId) {
        $sql = "SELECT camion_id, ruta_id, placa_vehiculo, latitud, longitud, ultima_actualizacion 
                FROM public.camiones_rastreo 
                WHERE ruta_id = :ruta_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['ruta_id' => $rutaId]);
        return $stmt->fetch();
    }

    /**
     * Actualiza la ubicación del camión.
     */
    public function updateUbicacion($camionId, $latitud, $longitud) {
        $sql = "UPDATE public.camiones_rastreo 
                SET latitud = :latitud, longitud = :longitud 
                WHERE camion_id = :camion_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'latitud' => $latitud,
            'longitud' => $longitud,
            'camion_id' => $camionId
        ]);
    }
}
