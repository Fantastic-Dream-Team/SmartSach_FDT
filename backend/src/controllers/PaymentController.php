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
        
        $suscripcionesRaw = $this->suscripcionModel->findByUsuarioId($userId);
        
        // Agrupar por ubicacion_id para evitar duplicados (mismo comportamiento que Dashboard y Perfil)
        $suscripciones = [];
        foreach ($suscripcionesRaw as $sub) {
            if (!empty($sub['ubicacion_id'])) {
                $suscripciones[$sub['ubicacion_id']] = $sub;
            } else {
                $suscripciones[] = $sub;
            }
        }

        $tieneDeuda = false;
        $suscripcionesMorosas = [];
        $todasLasSuscripciones = []; // Para enviar a la vista con el precio calculado
        $totalDeuda = 0.0;
        
        foreach ($suscripciones as $sub) {
            // Calcular precio dinámico: $10 si es en David, $15 en caso contrario
            $ref = strtolower($sub['nombre_referencia'] ?? '');
            $desc = strtolower($sub['descripcion_direccion'] ?? '');
            $esDavid = (strpos($ref, 'david') !== false) || (strpos($desc, 'david') !== false);
            $monto = $esDavid ? 10.00 : 15.00;
            
            $sub['monto_calculado'] = $monto;
            $todasLasSuscripciones[] = $sub;
            
            if ($sub['estado_pago'] === 'moroso') {
                $tieneDeuda = true;
                $suscripcionesMorosas[] = $sub;
                $totalDeuda += $monto;
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

                // Obtener detalles de la suscripción para el monto
                $subData = $this->suscripcionModel->findById($suscripcionId);
                if (!$subData) {
                    throw new Exception("Suscripción no encontrada.");
                }
                
                $ref = strtolower($subData['nombre_referencia'] ?? '');
                $desc = strtolower($subData['descripcion_direccion'] ?? '');
                $esDavid = (strpos($ref, 'david') !== false) || (strpos($desc, 'david') !== false);
                $monto = $esDavid ? 10.00 : 15.00;

                // Validación estricta de tipos: asegurar que el monto es estrictamente numérico
                if (!is_numeric($monto)) {
                    throw new Exception("El monto calculado no es un valor numérico válido.");
                }

                // Validar opcionalmente monto proveniente de parámetros externos
                $montoExterno = filter_input(INPUT_POST, 'monto', FILTER_VALIDATE_FLOAT) ?? null;
                if ($montoExterno !== null && !is_numeric($montoExterno)) {
                    throw new Exception("El monto recibido no es estrictamente numérico.");
                }

                // Llamar al procedimiento almacenado para procesar el pago
                $db = Database::getConnection();
                $metodoStr = htmlspecialchars(trim($_POST['metodo_pago'] ?? ''), ENT_QUOTES, 'UTF-8');
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

