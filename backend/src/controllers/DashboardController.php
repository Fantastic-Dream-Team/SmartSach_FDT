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
        
        // Obtener ubicaciones (antiguas rutas/viviendas)
        $ubicaciones = $this->ubicacionModel->findByUsuarioId($userId);
        
        $selectedUbicacion = null;
        $ubicacionId = filter_input(INPUT_GET, 'ubicacion_id', FILTER_VALIDATE_INT);
        
        if ($ubicacionId) {
            foreach ($ubicaciones as $u) {
                if (intval($u['ubicacion_id']) === $ubicacionId) {
                    $selectedUbicacion = $u;
                    break;
                }
            }
        }
        
        if (!$selectedUbicacion && !empty($ubicaciones)) {
            $selectedUbicacion = $ubicaciones[0];
        }

        // Obtener estado de cuenta a partir de la suscripción
        $suscripciones = $this->suscripcionModel->findByUsuarioId($userId);
        $estadoCuenta = 'Paz y Salvo';
        foreach ($suscripciones as $sub) {
            if ($sub['estado_pago'] === 'moroso') {
                $estadoCuenta = 'Moroso';
                break;
            }
        }

        // [WIP] - Zona de Rutas y Noticias se mantienen por compatibilidad futura
        $zonaRutas = []; // TODO: Implementar lógica de agrupación de rutas por zonas
        
        // Obtener noticias de reciclaje y anuncios
        $noticias = $this->noticiaModel->getAllNoticias();

        // Renderizar la vista
        require_once __DIR__ . '/../../../frontend/src/pages/dashboard.php';
    }
}

