<?php
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Ruta.php';
require_once __DIR__ . '/../models/Zona.php';

class ConductorController {
    private $usuarioModel;
    private $rutaModel;
    private $zonaModel;

    public function __construct() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'conductor') {
            header("Location: auth");
            exit;
        }
        $this->usuarioModel = new Usuario();
        $this->rutaModel = new Ruta();
        $this->zonaModel = new Zona();
    }

    /**
     * Muestra las viviendas y el estado de la zona de recolección asignada.
     */
    public function dashboard() {
        $conductorId = $_SESSION['user_id'];
        
        // Obtener detalles del conductor para saber su zona_id
        $conductor = $this->usuarioModel->findById($conductorId);
        $zonaId = $conductor['zona_id'];

        $selectedZona = null;
        $rutas = [];

        if ($zonaId) {
            $selectedZona = $this->zonaModel->findById($zonaId);
            // Obtener paradas/casas de la zona con su estado de cuenta
            $rutas = $this->rutaModel->findByZonaId($zonaId);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $zonaId) {
            $action = $_GET['action'] ?? '';
            
            if ($action === 'start') {
                $this->zonaModel->updateEstado($zonaId, 'en_ruta');
                $_SESSION['success'] = "¡Zona iniciada! La recolección de basura ha comenzado en esta zona.";
            } elseif ($action === 'finish') {
                $this->zonaModel->updateEstado($zonaId, 'finalizada');
                $_SESSION['success'] = "Servicio de recolección finalizado para esta zona.";
            } elseif ($action === 'reset') {
                $this->zonaModel->updateEstado($zonaId, 'inactiva');
                $_SESSION['success'] = "Estado de zona restablecido a inactiva.";
            }
            header("Location: dashboard");
            exit;
        }

        require_once __DIR__ . '/../views/conductor_dashboard.php';
    }

    /**
     * Muestra el perfil de solo lectura del conductor.
     */
    public function profile() {
        $user = $this->usuarioModel->findById($_SESSION['user_id']);
        require_once __DIR__ . '/../views/conductor_profile.php';
    }
}
