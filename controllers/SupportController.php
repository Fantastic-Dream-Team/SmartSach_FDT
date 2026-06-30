<?php
require_once __DIR__ . '/../models/Reporte.php';
require_once __DIR__ . '/../models/Ruta.php';

class SupportController {
    private $reporteModel;
    private $rutaModel;

    public function __construct() {
        $this->reporteModel = new Reporte();
        $this->rutaModel = new Ruta();
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
        $rutas = $this->rutaModel->findByUsuarioId($userId);
        $reportes = $this->reporteModel->getPublicReportes();

        require_once __DIR__ . '/../views/help.php';
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
            $rutaId = $_POST['ruta_id'] ?? null;
            $descripcion = trim($_POST['descripcion'] ?? '');
            $fotoPath = null;

            try {
                if (empty($descripcion)) {
                    throw new Exception("La descripción del problema es obligatoria.");
                }

                // Procesar subida de foto de reporte
                if (isset($_FILES['foto_reporte']) && $_FILES['foto_reporte']['error'] !== UPLOAD_ERR_NO_FILE) {
                    $file = $_FILES['foto_reporte'];
                    
                    if ($file['error'] !== UPLOAD_ERR_OK) {
                        throw new Exception("Error al cargar el archivo de imagen.");
                    }

                    // Validar tipo
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                    $fileType = mime_content_type($file['tmp_name']);
                    if (!in_array($fileType, $allowedTypes)) {
                        throw new Exception("Solo se permiten imágenes JPG, JPEG y PNG.");
                    }

                    // Validar tamaño (máximo 3MB)
                    if ($file['size'] > 3 * 1024 * 1024) {
                        throw new Exception("La imagen no debe superar los 3MB.");
                    }

                    // Crear directorio si no existe
                    $uploadDir = __DIR__ . '/../public/uploads/reports/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }

                    // Nombre único
                    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $fileName = 'report_' . $userId . '_' . time() . '.' . $extension;
                    $destPath = $uploadDir . $fileName;

                    if (move_uploaded_file($file['tmp_name'], $destPath)) {
                        $fotoPath = 'uploads/reports/' . $fileName;
                    } else {
                        throw new Exception("No se pudo guardar el archivo adjunto.");
                    }
                }

                $reportId = $this->reporteModel->create($userId, $rutaId, $descripcion, $fotoPath);
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

    /**
     * Agrega un comentario a un reporte existente (Cliente).
     */
    public function addComment() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: auth");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $reporteId = filter_input(INPUT_POST, 'reporte_id', FILTER_VALIDATE_INT);
            $comentario = trim($_POST['comentario'] ?? '');

            try {
                if (!$reporteId || empty($comentario)) {
                    throw new Exception("El comentario no puede estar vacío.");
                }

                $this->reporteModel->addComentario($reporteId, $_SESSION['user_id'], $comentario);
                $_SESSION['success'] = "Comentario publicado.";
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
