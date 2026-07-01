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
        
        // Obtener ubicaciones y mapear a $rutas
        $ubicaciones = $this->ubicacionModel->findByUsuarioId($userId);
        $rutas = [];
        
        foreach ($ubicaciones as $u) {
            $rutas[] = [
                'id' => $u['ubicacion_id'],
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
        $estadoCuenta = 'Paz y Salvo';

        // Obtener estado de cuenta a partir de la suscripción si existe
        $suscripciones = $this->suscripcionModel->findByUsuarioId($userId);
        if ($suscripciones) {
            foreach ($suscripciones as $sub) {
                if ($sub['estado_pago'] === 'moroso') {
                    $estadoCuenta = 'Moroso';
                    break;
                }
            }
        }

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
}

