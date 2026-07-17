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
                // Usar ruta relativa para evitar ERR_TOO_MANY_REDIRECTS
                header("Location: ?ruta_id=" . $rutaId);
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

    /**
     * Endpoint API: Actualiza la ubicación del camión en tiempo real (POST).
     * Envuelve la lógica en try-catch y realiza validación estricta de tipos.
     */
    public function updateLocation() {
        header('Content-Type: application/json');
        
        try {
            // Leer el cuerpo de la petición (JSON)
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'No se recibieron datos JSON válidos.'
                ]);
                return;
            }

            // Validar ruta_id
            $rutaId = isset($input['ruta_id']) ? $input['ruta_id'] : null;
            if ($rutaId === null || !filter_var($rutaId, FILTER_VALIDATE_INT)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'El campo ruta_id es inválido o no provisto.'
                ]);
                return;
            }
            $rutaId = (int)$rutaId;

            // Validar latitud y longitud (validación estricta de tipos)
            $latitud = isset($input['latitud']) ? $input['latitud'] : null;
            $longitud = isset($input['longitud']) ? $input['longitud'] : null;

            if ($latitud === null || $longitud === null || !is_numeric($latitud) || !is_numeric($longitud)) {
                // Si no se envían coordenadas (inicialización), establecer por defecto
                $latitud = 8.444901056161243;
                $longitud = -82.42998653395294;
            } else {
                $latitud = floatval($latitud);
                $longitud = floatval($longitud);
                
                // Validar límites geográficos de David, Chiriquí (Geo-fencing)
                if ($latitud < 8.3800 || $latitud > 8.4800 || $longitud < -82.4800 || $longitud > -82.4000) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'error' => 'Coordenadas fuera de rango. El servicio solo opera en David, Chiriquí.'
                    ]);
                    return;
                }
            }

            // Verificar si ya existe un registro de camión para la ruta
            $camion = $this->camionModel->findByRutaId($rutaId);
            
            if ($camion) {
                // Sentencia preparada por PDO encapsulada en el modelo
                $resultado = $this->camionModel->updateUbicacion($camion['camion_id'], $latitud, $longitud);
                if (!$resultado) {
                    throw new Exception("Error al actualizar las coordenadas en la base de datos.");
                }
            } else {
                // Si no existe, insertar un registro inicial con placa simulada
                $db = Database::getConnection();
                $sql = "INSERT INTO public.camiones_rastreo (ruta_id, placa_vehiculo, latitud, longitud) 
                        VALUES (:ruta_id, :placa, :latitud, :longitud)";
                $stmt = $db->prepare($sql);
                $placa = 'CAM-' . $rutaId;
                $resultado = $stmt->execute([
                    'ruta_id' => $rutaId,
                    'placa' => $placa,
                    'latitud' => $latitud,
                    'longitud' => $longitud
                ]);
                if (!$resultado) {
                    throw new Exception("Error al insertar la ubicación inicial del camión.");
                }
            }

            echo json_encode([
                'success' => true,
                'message' => 'Ubicación actualizada con éxito.',
                'posicion' => [
                    'latitud' => $latitud,
                    'longitud' => $longitud
                ]
            ]);

        } catch (Throwable $e) {
            // Manejo robusto capturando cualquier Throwable
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error interno en el servidor.',
                'message' => $e->getMessage()
            ]);
        }
    }
}