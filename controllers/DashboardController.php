<?php
require_once __DIR__ . '/../models/Ruta.php';
require_once __DIR__ . '/../models/Pago.php';
require_once __DIR__ . '/../models/Noticia.php';

class DashboardController {
    private $rutaModel;
    private $pagoModel;
    private $noticiaModel;

    public function __construct() {
        $this->rutaModel = new Ruta();
        $this->pagoModel = new Pago();
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
        $rutas = $this->rutaModel->findByUsuarioId($userId);
        
        // Seleccionar ruta activa (por defecto la primera, o por parámetro GET)
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

        // Obtener saldo pendiente para definir "Paz y Salvo" o "Moroso"
        $saldoPendiente = $this->pagoModel->getSaldoPendiente($userId);
        $estadoCuenta = ($saldoPendiente > 0) ? 'Moroso' : 'Paz y Salvo';

        // Obtener todas las viviendas de la misma zona para graficar la ruta continua en el mapa del cliente
        $zonaRutas = [];
        if ($selectedRuta && $selectedRuta['zona_id']) {
            $zonaRutas = $this->rutaModel->findByZonaId($selectedRuta['zona_id']);
        }

        // Obtener noticias de reciclaje y anuncios
        $noticias = $this->noticiaModel->getAllNoticias();

        // Renderizar la vista
        require_once __DIR__ . '/../views/dashboard.php';
    }
}
