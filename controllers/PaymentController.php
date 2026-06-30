<?php
require_once __DIR__ . '/../models/Pago.php';

class PaymentController {
    private $pagoModel;

    public function __construct() {
        $this->pagoModel = new Pago();
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
        $historial = $this->pagoModel->findHistoryByUsuarioId($userId);
        $saldoPendiente = $this->pagoModel->getSaldoPendiente($userId);

        require_once __DIR__ . '/../views/payments.php';
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
            
            try {
                $saldo = $this->pagoModel->getSaldoPendiente($userId);
                if ($saldo <= 0) {
                    throw new Exception("No tienes deudas pendientes de pago.");
                }

                // Simular referencia de pago
                $referencia = "REF-" . strtoupper(bin2hex(random_bytes(4)));
                
                $result = $this->pagoModel->pagarDeudas($userId, $referencia);
                if (!$result) {
                    throw new Exception("Ocurrió un error al procesar el pago.");
                }

                $_SESSION['success'] = "¡Pago realizado con éxito! Código de referencia: $referencia";
                header("Location: payments");
                exit;
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
                header("Location: payments");
                exit;
            }
        }
    }
}
