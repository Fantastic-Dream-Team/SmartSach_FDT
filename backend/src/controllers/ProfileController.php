<?php
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/UbicacionServicio.php';
require_once __DIR__ . '/../models/Suscripcion.php';
require_once __DIR__ . '/../models/Ruta.php'; // <-- AGREGADO

class ProfileController {
    private $usuarioModel;
    private $ubicacionModel;
    private $rutaModel; // <-- AGREGADO

    public function __construct() {
        $this->usuarioModel = new Usuario();
        $this->ubicacionModel = new UbicacionServicio();
        $this->rutaModel = new Ruta(); // <-- AGREGADO
    }

    /**
     * Muestra la página de perfil.
     */
    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: auth");
            exit;
        }

        $userId = $_SESSION['user_id'];
        $user = $this->usuarioModel->findById($userId);
        
        if (!$user) {
            $user = [
                'usuario_id' => $userId,
                'nombre' => $_SESSION['user_nombre'] ?? 'Usuario',
                'apellido' => 'Supabase',
                'correo_electronico' => $_SESSION['user_email'] ?? 'correo@ejemplo.com',
                'telefono' => '6000-0000',
                'direccion' => 'David, Chiriquí',
                'cedula' => '0-000-0000'
            ];
        }
        
        // Obtener ubicaciones del usuario
        $ubicacionesRaw = $this->ubicacionModel->findByUsuarioId($userId);
        
        $suscripcionModel = new Suscripcion();
        $suscripciones = $suscripcionModel->findByUsuarioId($userId);
        $subMap = [];
        foreach ($suscripciones as $sub) {
            $subMap[$sub['ubicacion_id']] = $sub;
        }

        $ubicaciones = [];
        foreach ($ubicacionesRaw as $u) {
            $sub = $subMap[$u['ubicacion_id']] ?? null;
            if ($sub) {
                $u['suscripcion_id'] = $sub['suscripcion_id'];
                $ubicaciones[] = $u;
            }
        }

        // OBTENER TODAS LAS RUTAS DISPONIBLES PARA EL SELECTOR <-- NUEVO
        $rutasDisponibles = $this->rutaModel->getAllRoutes();

        // [WIP] Zonas (se mantiene igual)
        $zonas = [];

        require_once __DIR__ . '/../../../frontend/src/pages/profile.php';
    }

    /**
     * Actualiza la información personal.
     */
    public function update() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: auth");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];
            $nombre = trim($_POST['nombre'] ?? '');
            $apellido = trim($_POST['apellido'] ?? '');
            $telefono = trim($_POST['telefono'] ?? '');
            $direccion = trim($_POST['direccion'] ?? '');

            try {
                if (empty($nombre) || empty($apellido)) {
                    throw new Exception("El nombre y apellido son requeridos.");
                }

                $this->usuarioModel->updateProfile($userId, $nombre, $apellido, $telefono, $direccion);
                
                $_SESSION['user_nombre'] = $nombre;
                $_SESSION['success'] = "Perfil actualizado correctamente.";
                
                header("Location: profile");
                exit;
            } catch (Throwable $e) {
                $_SESSION['error'] = "Error al actualizar: " . $e->getMessage();
                header("Location: profile");
                exit;
            }
        }
    }

    /**
     * Agrega una nueva ubicación de servicio con ruta seleccionada.
     */
    public function addRoute() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: auth");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];
            $nombreReferencia = trim($_POST['nombre'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $latitud = filter_input(INPUT_POST, 'latitud', FILTER_VALIDATE_FLOAT);
            $longitud = filter_input(INPUT_POST, 'longitud', FILTER_VALIDATE_FLOAT);
            
            // RECIBIR LA RUTA SELECCIONADA <-- NUEVO
            $rutaId = filter_input(INPUT_POST, 'ruta_id', FILTER_VALIDATE_INT);

            try {
                if (empty($nombreReferencia)) {
                    throw new Exception("El nombre de la referencia es obligatorio.");
                }
                if ($latitud === false || $latitud === null || $longitud === false || $longitud === null) {
                    throw new Exception("Debes marcar una ubicación válida en el mapa.");
                }
                if (!$rutaId) {
                    throw new Exception("Debes seleccionar una ruta de recolección.");
                }

                // Crear ubicación
                $ubicacionId = $this->ubicacionModel->create($userId, $nombreReferencia, $descripcion, $latitud, $longitud);
                if (!$ubicacionId) {
                    throw new Exception("Error al guardar la nueva dirección.");
                }

                // Crear suscripción CON LA RUTA SELECCIONADA <-- NUEVO
                $suscripcionModel = new Suscripcion();
                $suscripcionModel->create($userId, $ubicacionId, $rutaId, 'moroso');

                $_SESSION['success'] = "Ubicación registrada correctamente. Suscripción creada con la ruta seleccionada.";
                header("Location: profile");
                exit;
            } catch (Throwable $e) {
                $_SESSION['error'] = "Error al guardar ubicación: " . $e->getMessage();
                header("Location: profile");
                exit;
            }
        }
    }
}