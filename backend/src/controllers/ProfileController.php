<?php
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/UbicacionServicio.php';
require_once __DIR__ . '/../models/Suscripcion.php';
// WIP: require_once __DIR__ . '/../models/Zona.php';

class ProfileController {
    private $usuarioModel;
    private $ubicacionModel;

    public function __construct() {
        $this->usuarioModel = new Usuario();
        $this->ubicacionModel = new UbicacionServicio();
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
        
        // Antes era Rutas, ahora son Ubicaciones de Servicio
        $ubicaciones = $this->ubicacionModel->findByUsuarioId($userId);

        // [WIP] Cargar Zonas de recolección para el selector del cliente
        // require_once __DIR__ . '/../models/Zona.php';
        // $zonaModel = new Zona();
        // $zonas = $zonaModel->getAllZonas();
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
            // NOTA: El correo electrónico idealmente no se debería modificar directamente si es manejado por Supabase,
            // pero para esta demo, mantendremos la actualización interna de los otros campos.

            try {
                if (empty($nombre) || empty($apellido)) {
                    throw new Exception("El nombre y apellido son requeridos.");
                }

                // Guardar cambios en BD
                $this->usuarioModel->updateProfile($userId, $nombre, $apellido, $telefono, $direccion);
                
                // Actualizar sesión
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
     * Agrega una nueva ubicación de servicio.
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
            
            // zona_id ya no aplica directamente en la inserción de ubicación, 
            // la asignación a una zona se hará mediante rutas de camión (WIP)

            try {
                if (empty($nombreReferencia)) {
                    throw new Exception("El nombre de la referencia es obligatorio.");
                }
                if ($latitud === false || $latitud === null || $longitud === false || $longitud === null) {
                    throw new Exception("Debes marcar una ubicación válida en el mapa.");
                }

                // Crear ubicación
                $ubicacionId = $this->ubicacionModel->create($userId, $nombreReferencia, $descripcion, $latitud, $longitud);
                if (!$ubicacionId) {
                    throw new Exception("Error al guardar la nueva dirección.");
                }

                // Activar suscripción incondicionalmente para la nueva ubicación
                $suscripcionModel = new Suscripcion();
                $suscripciones = $suscripcionModel->findByUsuarioId($userId);
                
                $suscripcionModel->create($userId, $ubicacionId, 1, 'moroso');
                
                // Si es la primera ubicación, actualizar el estado de verificación
                if (empty($suscripciones)) {
                    $this->usuarioModel->updateVerificationStatus($userId, 'activo');
                }

                $_SESSION['success'] = "Ubicación de servicio registrada correctamente. La suscripción se procesará acorde a tu estado de verificación.";
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

