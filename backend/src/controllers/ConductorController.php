<?php
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Ruta.php';
require_once __DIR__ . '/../models/UbicacionServicio.php';
require_once __DIR__ . '/../models/CamionRastreo.php';

class ConductorController {
    private $usuarioModel;
    private $rutaModel;
    private $ubicacionModel;
    private $camionModel;

    public function __construct() {
        // Verificar autenticación y rol
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
     * Muestra el dashboard del conductor con el mapa y selector de rutas.
     */
    public function dashboard() {
        try {
            $conductorId = $_SESSION['user_id'];
            
            // Obtener todas las rutas disponibles para el selector
            $rutasDisponibles = $this->rutaModel->findAll();
            
            // Obtener la ruta seleccionada (por GET o la primera disponible)
            $rutaId = isset($_GET['ruta_id']) ? (int)$_GET['ruta_id'] : null;
            if (!$rutaId && !empty($rutasDisponibles)) {
                $rutaId = $rutasDisponibles[0]['ruta_id'];
            }
            
            // Obtener los clientes de la ruta seleccionada
            $clientes = [];
            $selectedRuta = null;
            if ($rutaId) {
                $clientes = $this->rutaModel->getClientesByRuta($rutaId);
                $selectedRuta = $this->rutaModel->findById($rutaId);
            }

            // Procesar acciones POST (Iniciar/Finalizar ruta)
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $action = $_GET['action'] ?? '';
                
                if ($action === 'start' && $rutaId) {
                    $this->rutaModel->updateEstado($rutaId, 'activa');
                    $_SESSION['success'] = "¡Ruta iniciada! La recolección ha comenzado.";
                } elseif ($action === 'finish' && $rutaId) {
                    $this->rutaModel->updateEstado($rutaId, 'inactiva');
                    $_SESSION['success'] = "Servicio de recolección finalizado.";
                }
                header("Location: conductor/dashboard" . ($rutaId ? "?ruta_id=" . $rutaId : ""));
                exit;
            }

            // Cargar la vista
            require_once __DIR__ . '/../../../frontend/src/pages/conductor_dashboard.php';
            
        } catch (Exception $e) {
            $_SESSION['error'] = "Error al cargar el dashboard: " . $e->getMessage();
            header("Location: dashboard");
            exit;
        }
    }

    /**
     * Endpoint API: Obtiene los clientes de una ruta específica (formato JSON).
     */
    public function getClientesPorRuta() {
        try {
            header('Content-Type: application/json');
            
            $rutaId = isset($_GET['ruta_id']) ? (int)$_GET['ruta_id'] : 0;
            if ($rutaId <= 0) {
                echo json_encode(['error' => 'ID de ruta inválido']);
                return;
            }
            
            $clientes = $this->rutaModel->getClientesByRuta($rutaId);
            echo json_encode(['success' => true, 'clientes' => $clientes]);
            
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Muestra el perfil de solo lectura del conductor.
     */
    public function profile() {
        try {
            $user = $this->usuarioModel->findById($_SESSION['user_id']);
            require_once __DIR__ . '/../../../frontend/src/pages/conductor_profile.php';
        } catch (Exception $e) {
            $_SESSION['error'] = "Error al cargar el perfil: " . $e->getMessage();
            header("Location: dashboard");
            exit;
        }
    }
}