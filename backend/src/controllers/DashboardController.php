<?php
require_once __DIR__ . '/../models/UbicacionServicio.php';
require_once __DIR__ . '/../models/Suscripcion.php';
require_once __DIR__ . '/../models/Noticia.php';

class DashboardController {
    private $ubicacionModel;
    private $suscripcionModel;
    private $noticiaModel;

    public function __construct() {
        $this->ubicacionModel = new UbicacionServicio();
        $this->suscripcionModel = new Suscripcion();
        $this->noticiaModel = new Noticia();
    }

    /**
     * Muestra la pantalla principal del panel de usuario.
     */
    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: auth");
            exit;
        }

        $userId = $_SESSION['user_id'];
        
        // Obtener suscripciones para mapear el ruta_id correspondiente y el estado de cuenta
        $suscripciones = $this->suscripcionModel->findByUsuarioId($userId);
        $subMap = [];
        $estadoCuenta = 'Paz y Salvo';
        
        if ($suscripciones) {
            foreach ($suscripciones as $sub) {
                $subMap[$sub['ubicacion_id']] = $sub;
                if ($sub['estado_pago'] === 'moroso') {
                    $estadoCuenta = 'Moroso';
                }
            }
        }

        // Obtener ubicaciones y mapear a $rutas
        $ubicaciones = $this->ubicacionModel->findByUsuarioId($userId);
        $rutas = [];
        
        foreach ($ubicaciones as $u) {
            $sub = $subMap[$u['ubicacion_id']] ?? null;
            $rutas[] = [
                'id' => $u['ubicacion_id'],
                'ruta_id' => $sub ? (int)$sub['ruta_id'] : 1, // Asignar ruta_id de la suscripción
                'nombre' => $u['nombre_referencia'],
                'descripcion' => $u['descripcion_direccion'],
                'latitud' => $u['latitud'],
                'longitud' => $u['longitud'],
                'costo' => '10.00',
                'conductor_nombre' => 'SACH - Conductor Turno Mañana',
                'estado' => 'Activa',
                'zona_estado' => 'en_ruta'
            ];
        }

        $selectedRuta = null;
        $rutaId = filter_input(INPUT_GET, 'ruta_id', FILTER_VALIDATE_INT);
        
        if ($rutaId) {
            foreach ($rutas as $r) {
                if (intval($r['id']) === $rutaId) {
                    $selectedRuta = $r;
                    break;
                }
            }
        }
        
        if (!$selectedRuta && !empty($rutas)) {
            $selectedRuta = $rutas[0];
        }

        // Definir variables predeterminadas para evitar warnings
        $saldoPendiente = 0.00;

        // Simular zonaRutas para Leaflet Routing Machine
        $zonaRutas = [];
        if ($selectedRuta) {
            $baseLat = floatval($selectedRuta['latitud']);
            $baseLng = floatval($selectedRuta['longitud']);
            $zonaRutas = [
                ['latitud' => $baseLat + 0.001, 'longitud' => $baseLng + 0.001],
                ['latitud' => $baseLat - 0.001, 'longitud' => $baseLng - 0.001],
                ['latitud' => $baseLat + 0.002, 'longitud' => $baseLng - 0.001]
            ];
        }
        
        // Obtener noticias de reciclaje y anuncios
        $noticias = $this->noticiaModel->getAllNoticias();
        if (!is_array($noticias)) {
            $noticias = [];
        }

        // Renderizar la vista
        require_once __DIR__ . '/../../../frontend/src/pages/dashboard.php';
    }

    /**
     * Endpoint API: Obtiene la última posición del camión filtrando por ruta_id (GET).
     * Retorna un JSON limpio con la latitud y longitud.
     */
    public function getTruckPosition() {
        header('Content-Type: application/json');
        
        try {
            $rutaId = isset($_GET['ruta_id']) ? $_GET['ruta_id'] : null;
            
            // Validar que ruta_id sea un entero numérico válido
            if ($rutaId === null || !filter_var($rutaId, FILTER_VALIDATE_INT)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'El parámetro ruta_id es inválido o no numérico.'
                ]);
                return;
            }
            $rutaId = (int)$rutaId;

            require_once __DIR__ . '/../models/CamionRastreo.php';
            $camionModel = new CamionRastreo();
            $posicion = $camionModel->findByRutaId($rutaId);

            if ($posicion) {
                $lat = floatval($posicion['latitud']);
                $lon = floatval($posicion['longitud']);

                // Geo-fencing de lectura: Ignorar coordenadas antiguas fuera del rango de David
                if ($lat < 8.3800 || $lat > 8.4800 || $lon < -82.4800 || $lon > -82.4000) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'El camión se encuentra fuera de la zona operativa actual.'
                    ]);
                    return;
                }

                echo json_encode([
                    'success' => true,
                    'posicion' => [
                        'latitud' => $lat,
                        'longitud' => $lon,
                        'ultima_actualizacion' => $posicion['ultima_actualizacion']
                    ]
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'No hay ubicación activa de camión registrada para esta ruta.'
                ]);
            }

        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error interno en el servidor.',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Da de baja a una suscripción (soft delete).
     */
    public function cancelSubscription() {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'No autorizado']);
                return;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $suscripcionId = $input['suscripcion_id'] ?? null;
            
            if (!$suscripcionId || !filter_var($suscripcionId, FILTER_VALIDATE_INT)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'ID de suscripción inválido']);
                return;
            }
            
            $resultado = $this->suscripcionModel->cancel((int)$suscripcionId, $_SESSION['user_id']);
            
            if ($resultado) {
                echo json_encode(['success' => true, 'message' => 'Suscripción dada de baja con éxito']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Error al dar de baja la suscripción']);
            }
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error interno', 'message' => $e->getMessage()]);
        }
    }
}

