<?php
require_once __DIR__ . '/../models/ReporteIncidencia.php';
require_once __DIR__ . '/../models/UbicacionServicio.php';

class SupportController {
    private $reporteModel;
    private $ubicacionModel;

    public function __construct() {
        $this->reporteModel = new ReporteIncidencia();
        $this->ubicacionModel = new UbicacionServicio();
    }

    /**
     * Muestra la página de ayuda y reportes.
     */
    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: auth");
            exit;
        }

        $userId = $_SESSION['user_id'];
        $ubicaciones = $this->ubicacionModel->findByUsuarioId($userId);
        
        // Asumimos que los reportes a mostrar son los del usuario actual o públicos
        // Para el cliente, mostramos sus propios reportes
        $reportes = $this->reporteModel->findByUsuarioId($userId);

        require_once __DIR__ . '/../../../frontend/src/pages/help.php';
    }

    /**
     * Procesa la creación de un nuevo reporte de servicio.
     */
    public function submitReport() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: auth");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];
            $ubicacionId = $_POST['ubicacion_id'] ?? null;
            $tipoIncidencia = $_POST['tipo_incidencia'] ?? 'otro';
            $descripcion = trim($_POST['descripcion'] ?? '');

            try {
                if (empty($descripcion)) {
                    throw new Exception("La descripción del problema es obligatoria.");
                }
                
                if (!$ubicacionId) {
                    throw new Exception("Debe seleccionar una ubicación de servicio relacionada.");
                }

                // Tipos permitidos por el esquema: 'no_paso_camion', 'mala_atencion', 'desperdicio_en_via', 'otro'
                $validTipos = ['no_paso_camion', 'mala_atencion', 'desperdicio_en_via', 'otro'];
                if (!in_array($tipoIncidencia, $validTipos)) {
                    $tipoIncidencia = 'otro';
                }

                $reportId = $this->reporteModel->create($userId, $ubicacionId, $tipoIncidencia, $descripcion);
                if (!$reportId) {
                    throw new Exception("Error al guardar el reporte.");
                }

                $_SESSION['success'] = "Reporte enviado exitosamente. Estaremos atendiendo su caso a la brevedad.";
                header("Location: help");
                exit;
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
                header("Location: help");
                exit;
            }
        }
    }
}

