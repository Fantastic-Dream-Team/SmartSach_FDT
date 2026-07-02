<?php
require_once __DIR__ . '/../models/Pago.php';
require_once __DIR__ . '/../models/Suscripcion.php';
require_once __DIR__ . '/../../config/database.php';

class PaymentController {
    private $pagoModel;
    private $suscripcionModel;

    public function __construct() {
        $this->pagoModel = new Pago();
        $this->suscripcionModel = new Suscripcion();
    }

    /**
     * Muestra el historial y estado de cuenta de pagos.
     */
    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: auth");
            exit;
        }

        $userId = $_SESSION['user_id'];
        $historial = $this->pagoModel->findByUsuarioId($userId);
        
        $suscripciones = $this->suscripcionModel->findByUsuarioId($userId);
        $tieneDeuda = false;
        $suscripcionesMorosas = [];
        $totalDeuda = 0.0;
        
        foreach ($suscripciones as $sub) {
            if ($sub['estado_pago'] === 'moroso') {
                $tieneDeuda = true;
                $suscripcionesMorosas[] = $sub;
                $totalDeuda += 15.00; // Asumiendo $15.00 por ubicación
            }
        }

        require_once __DIR__ . '/../../../frontend/src/pages/payments.php';
    }

    /**
     * Procesa la simulación de pago.
     */
    public function pay() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: auth");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];
            $suscripcionId = filter_input(INPUT_POST, 'suscripcion_id', FILTER_VALIDATE_INT);
            
            try {
                if (!$suscripcionId) {
                    throw new Exception("ID de suscripción inválido.");
                }

                // Llamar al procedimiento almacenado para procesar el pago
                $db = Database::getConnection();
                $monto = 15.00; // Monto por defecto
                $metodoStr = $_POST['metodo_pago'] ?? '';
                $metodo = $metodoStr ? 'simulacion_' . strtolower($metodoStr) : 'simulacion_web';
                
                $sql = "CALL public.sp_procesar_pago_sach(:suscripcion_id, :monto, :metodo)";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    'suscripcion_id' => $suscripcionId,
                    'monto' => $monto,
                    'metodo' => $metodo
                ]);

                $_SESSION['success'] = "¡Pago realizado con éxito! Tu cuenta está ahora al día.";
                header("Location: payments");
                exit;
            } catch (Exception $e) {
                $_SESSION['error'] = "Error al procesar el pago: " . $e->getMessage();
                header("Location: payments");
                exit;
            }
        }
    }
}

