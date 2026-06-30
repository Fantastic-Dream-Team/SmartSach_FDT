<?php
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Ruta.php';
require_once __DIR__ . '/../models/ReporteIncidencia.php';
require_once __DIR__ . '/../models/UbicacionServicio.php';
// WIP: require_once __DIR__ . '/../models/Zona.php';

class GestorController {
    private $usuarioModel;
    private $rutaModel;
    private $reporteModel;
    private $ubicacionModel;

    public function __construct() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'gestor') {
            header("Location: auth");
            exit;
        }
        $this->usuarioModel = new Usuario();
        $this->rutaModel = new Ruta();
        $this->reporteModel = new ReporteIncidencia();
        $this->ubicacionModel = new UbicacionServicio();
    }

    /**
     * Muestra el panel general de gestor.
     */
    public function dashboard() {
        $routes = $this->rutaModel->getAllRoutes();
        require_once __DIR__ . '/../../../frontend/src/pages/gestor_dashboard.php';
    }

    /**
     * Muestra e interactúa con el listado de reportes del sistema.
     */
    public function reportes() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_GET['action'] ?? '';
            
            if ($action === 'update_status') {
                $reporteId = filter_input(INPUT_POST, 'reporte_id', FILTER_VALIDATE_INT);
                $estado = trim($_POST['estado'] ?? 'en_proceso');
                if ($reporteId) {
                    $this->reporteModel->updateEstado($reporteId, $estado);
                    $_SESSION['success'] = "Estado del reporte actualizado a $estado.";
                }
                header("Location: help");
                exit;
            }
        }

        $reportes = $this->reporteModel->getAllReportes();
        require_once __DIR__ . '/../../../frontend/src/pages/gestor_reportes.php';
    }

    /**
     * Perfil administrativo: Gestión de Zonas, Conductores y Ubicaciones.
     */
    public function profile() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_GET['action'] ?? '';

            try {
                // [WIP] Crear Conductor: En Supabase Auth el registro se hace en frontend. 
                // Aquí solo deberíamos asignar el rol o mapear.

                // Crear Ubicación de Cliente (antes create_route)
                if ($action === 'create_ubicacion') {
                    $clienteEmail = filter_input(INPUT_POST, 'cliente_email', FILTER_VALIDATE_EMAIL);
                    $nombre = trim($_POST['nombre'] ?? '');
                    $descripcion = trim($_POST['descripcion'] ?? '');
                    $latitud = filter_input(INPUT_POST, 'latitud', FILTER_VALIDATE_FLOAT);
                    $longitud = filter_input(INPUT_POST, 'longitude', FILTER_VALIDATE_FLOAT);

                    if (!$clienteEmail || empty($nombre) || $latitud === false || $longitud === false) {
                        throw new Exception("Datos requeridos incompletos.");
                    }

                    $cliente = $this->usuarioModel->findByEmail($clienteEmail);
                    if (!$cliente) {
                        throw new Exception("No existe ningún cliente registrado con ese correo.");
                    }

                    $this->ubicacionModel->create($cliente['usuario_id'], $nombre, $descripcion, $latitud, $longitud);
                    $_SESSION['success'] = "Ubicación de servicio registrada.";
                }

                // [WIP] Control Remoto de Zonas, Crear Zonas, etc. en desarrollo.

                header("Location: profile");
                exit;
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
                header("Location: profile");
                exit;
            }
        }

        $user = $this->usuarioModel->findById($_SESSION['user_id']);
        
        // [WIP] Recuperar conductores y zonas cuando se defina su estructura 
        $conductores = [];
        $zonas = [];
        
        $rutas = $this->rutaModel->getAllRoutes();
        
        require_once __DIR__ . '/../../../frontend/src/pages/gestor_profile.php';
    }

    /**
     * Gestión de Noticias (WIP).
     */
    public function noticias() {
        require_once __DIR__ . '/../models/Noticia.php';
        $noticiaModel = new Noticia();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_GET['action'] ?? '';
            try {
                if ($action === 'create') {
                    $titulo = trim($_POST['titulo'] ?? '');
                    $contenido = trim($_POST['contenido'] ?? '');
                    if (empty($titulo) || empty($contenido)) {
                        throw new Exception("El título y contenido son requeridos.");
                    }
                    $noticiaModel->create($titulo, $contenido, $_SESSION['user_id']);
                    $_SESSION['success'] = "Noticia publicada exitosamente.";
                }
                header("Location: news");
                exit;
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
                header("Location: news");
                exit;
            }
        }

        $noticias = $noticiaModel->getAllNoticias();
        require_once __DIR__ . '/../../../frontend/src/pages/gestor_noticias.php';
    }
}

