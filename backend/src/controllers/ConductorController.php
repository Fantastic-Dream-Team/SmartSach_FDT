<?php
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Ruta.php';
require_once __DIR__ . '/../models/UbicacionServicio.php';
require_once __DIR__ . '/../models/CamionRastreo.php';
// WIP: require_once __DIR__ . '/../models/Zona.php';

class ConductorController {
    private $usuarioModel;
    private $rutaModel;
    private $ubicacionModel;
    private $camionModel;

    public function __construct() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'conductor') {
            header("Location: auth");
            exit;
        }
        $this->usuarioModel = new Usuario();
        $this->rutaModel = new Ruta();
        $this->ubicacionModel = new UbicacionServicio();
        $this->camionModel = new CamionRastreo();
    }

    /**
     * Muestra las ubicaciones y el estado de la ruta asignada al camión.
     */
    public function dashboard() {
        $conductorId = $_SESSION['user_id'];
        
        // TODO: Mapear conductor a su ruta y camión de forma dinámica
        $rutaId = 1; // Fijo temporalmente hasta que se asigne en BD
        $rutasCamion = $this->rutaModel->findById($rutaId);
        
        // [WIP] Agrupación por zonas en desarrollo
        $selectedZona = null;
        $zonaId = null;

        // Obtener todas las ubicaciones a recolectar (clientes)
        // Por ahora obtenemos todas, idealmente se filtrarían por la ruta o zona asignada.
        // Simularemos obteniendo todas.
        $ubicaciones = []; 

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $rutaId) {
            $action = $_GET['action'] ?? '';
            
            if ($action === 'start') {
                $this->rutaModel->updateEstado($rutaId, 'activa');
                $_SESSION['success'] = "¡Ruta iniciada! La recolección ha comenzado.";
            } elseif ($action === 'finish') {
                $this->rutaModel->updateEstado($rutaId, 'inactiva');
                $_SESSION['success'] = "Servicio de recolección finalizado.";
            } elseif ($action === 'update_location') {
                $lat = $_POST['latitud'] ?? 0;
                $lng = $_POST['longitud'] ?? 0;
                // $this->camionModel->updateUbicacion(camion_id, $lat, $lng);
            }
            header("Location: dashboard");
            exit;
        }

        require_once __DIR__ . '/../../../frontend/src/pages/conductor_dashboard.php';
    }

    /**
     * Muestra el perfil de solo lectura del conductor.
     */
    public function profile() {
        $user = $this->usuarioModel->findById($_SESSION['user_id']);
        require_once __DIR__ . '/../../../frontend/src/pages/conductor_profile.php';
    }
}

