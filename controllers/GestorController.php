<?php
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Ruta.php';
require_once __DIR__ . '/../models/Reporte.php';
require_once __DIR__ . '/../models/Zona.php';

class GestorController {
    private $usuarioModel;
    private $rutaModel;
    private $reporteModel;
    private $zonaModel;

    public function __construct() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'gestor') {
            header("Location: auth");
            exit;
        }
        $this->usuarioModel = new Usuario();
        $this->rutaModel = new Ruta();
        $this->reporteModel = new Reporte();
        $this->zonaModel = new Zona();
    }

    /**
     * Muestra el panel general de gestor (Mapa con conductores activos por zona).
     */
    public function dashboard() {
        $routes = $this->rutaModel->getAllRoutes();
        require_once __DIR__ . '/../views/gestor_dashboard.php';
    }

    /**
     * Muestra e interactúa con el listado de reportes del sistema.
     */
    public function reportes() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_GET['action'] ?? '';
            
            if ($action === 'mark_seen') {
                $reporteId = filter_input(INPUT_POST, 'reporte_id', FILTER_VALIDATE_INT);
                if ($reporteId) {
                    $this->reporteModel->markAsVisto($reporteId);
                    $_SESSION['success'] = "Reporte marcado como visto.";
                }
                header("Location: help");
                exit;
            }

            if ($action === 'comment') {
                $reporteId = filter_input(INPUT_POST, 'reporte_id', FILTER_VALIDATE_INT);
                $comentario = trim($_POST['comentario'] ?? '');
                
                if ($reporteId && !empty($comentario)) {
                    $this->reporteModel->addComentario($reporteId, $_SESSION['user_id'], $comentario);
                    $_SESSION['success'] = "Respuesta agregada al hilo.";
                }
                header("Location: help");
                exit;
            }
        }

        $reportes = $this->reporteModel->getAllReportes();
        require_once __DIR__ . '/../views/gestor_reportes.php';
    }

    /**
     * Perfil administrativo: Gestión de Zonas, Conductores y Casas.
     */
    public function profile() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_GET['action'] ?? '';

            try {
                // Crear Conductor
                if ($action === 'create_driver') {
                    $nombre = trim($_POST['nombre'] ?? '');
                    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
                    $password = $_POST['password'] ?? '';
                    $zonaId = filter_input(INPUT_POST, 'zona_id', FILTER_VALIDATE_INT) ?: null;

                    if (empty($nombre) || !$email || strlen($password) < 6) {
                        throw new Exception("Datos de conductor inválidos.");
                    }

                    if ($this->usuarioModel->findByEmail($email)) {
                        throw new Exception("El correo ya está registrado.");
                    }

                    $this->usuarioModel->create($nombre, $email, $password, 'conductor', $zonaId);
                    $_SESSION['success'] = "Conductor registrado exitosamente.";
                }

                // Modificar Conductor (cambiar zona y datos)
                if ($action === 'update_driver') {
                    $driverId = filter_input(INPUT_POST, 'driver_id', FILTER_VALIDATE_INT);
                    $nombre = trim($_POST['nombre'] ?? '');
                    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
                    $zonaId = filter_input(INPUT_POST, 'zona_id', FILTER_VALIDATE_INT) ?: null;

                    if ($driverId && !empty($nombre) && $email) {
                        // El modelo aplica la regla crítica de validación si la zona del conductor está en_ruta
                        $this->usuarioModel->updateConductorZona($driverId, $nombre, $email, $zonaId);
                        $_SESSION['success'] = "Conductor actualizado correctamente.";
                    }
                }

                // Dar de baja Conductor
                if ($action === 'delete_driver') {
                    $driverId = filter_input(INPUT_POST, 'driver_id', FILTER_VALIDATE_INT);
                    if ($driverId) {
                        $this->usuarioModel->deleteConductor($driverId);
                        $_SESSION['success'] = "Conductor dado de baja del sistema.";
                    }
                }

                // Crear Zona
                if ($action === 'create_zone') {
                    $nombre = trim($_POST['nombre_zona'] ?? '');
                    $descripcion = trim($_POST['descripcion'] ?? '');

                    if (empty($nombre)) {
                        throw new Exception("El nombre de la zona es requerido.");
                    }

                    $this->zonaModel->create($nombre, $descripcion);
                    $_SESSION['success'] = "Zona de recolección creada.";
                }

                // Registrar Casa de Cliente en una Zona
                if ($action === 'create_route') {
                    $clienteEmail = filter_input(INPUT_POST, 'cliente_email', FILTER_VALIDATE_EMAIL);
                    $nombre = trim($_POST['nombre'] ?? '');
                    $descripcion = trim($_POST['descripcion'] ?? '');
                    $latitud = filter_input(INPUT_POST, 'latitud', FILTER_VALIDATE_FLOAT);
                    $longitud = filter_input(INPUT_POST, 'longitude', FILTER_VALIDATE_FLOAT); // name="longitude" en el HTML
                    $zonaId = filter_input(INPUT_POST, 'zona_id', FILTER_VALIDATE_INT) ?: null;

                    if (!$clienteEmail || empty($nombre) || $latitud === false || $longitud === false) {
                        throw new Exception("Datos requeridos incompletos.");
                    }

                    $cliente = $this->usuarioModel->findByEmail($clienteEmail);
                    if (!$cliente || $cliente['rol'] !== 'cliente') {
                        throw new Exception("No existe ningún cliente registrado con ese correo.");
                    }

                    $this->rutaModel->create($cliente['id'], $nombre, $descripcion, $latitud, $longitud, 15.00, $zonaId);
                    $_SESSION['success'] = "Dirección registrada e integrada a la zona.";
                }

                // Editar Casa de Cliente
                if ($action === 'edit_route') {
                    $routeId = filter_input(INPUT_POST, 'route_id', FILTER_VALIDATE_INT);
                    $nombre = trim($_POST['nombre'] ?? '');
                    $descripcion = trim($_POST['descripcion'] ?? '');
                    $costo = filter_input(INPUT_POST, 'costo', FILTER_VALIDATE_FLOAT) ?: 15.00;
                    $zonaId = filter_input(INPUT_POST, 'zona_id', FILTER_VALIDATE_INT) ?: null;

                    if ($routeId) {
                        // El modelo aplica la validación crítica "en_ruta" de la zona
                        $this->rutaModel->updateRouteDetails($routeId, $nombre, $descripcion, $costo, $zonaId);
                        $_SESSION['success'] = "Dirección modificada correctamente.";
                    }
                }

                // Control Remoto de Zonas (Activar/Desactivar Zona a Distancia)
                if ($action === 'toggle_zone') {
                    $zonaId = filter_input(INPUT_POST, 'zona_id', FILTER_VALIDATE_INT);
                    $estado = trim($_POST['estado'] ?? 'inactiva');

                    if ($zonaId) {
                        $this->zonaModel->updateEstado($zonaId, $estado);
                        $_SESSION['success'] = "Estado de zona cambiado a: $estado.";
                    }
                }

                header("Location: profile");
                exit;
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
                header("Location: profile");
                exit;
            }
        }

        $user = $this->usuarioModel->findById($_SESSION['user_id']);
        $conductores = $this->usuarioModel->getConductores();
        $rutas = $this->rutaModel->getAllRoutes();
        $zonas = $this->zonaModel->getAllZonas();
        
        require_once __DIR__ . '/../views/gestor_profile.php';
    }

    /**
     * Gestión de Noticias (CRUD de blog para el gestor).
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

                if ($action === 'delete') {
                    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
                    if ($id) {
                        $noticiaModel->delete($id);
                        $_SESSION['success'] = "Noticia eliminada correctamente.";
                    }
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
        require_once __DIR__ . '/../views/gestor_noticias.php';
    }
}
