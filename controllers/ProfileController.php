<?php
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Ruta.php';
require_once __DIR__ . '/../models/Pago.php';

class ProfileController {
    private $usuarioModel;
    private $rutaModel;
    private $pagoModel;

    public function __construct() {
        $this->usuarioModel = new Usuario();
        $this->rutaModel = new Ruta();
        $this->pagoModel = new Pago();
    }

    /**
     * Muestra la página de perfil.
     */
    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: auth");
            exit;
        }

        $userId = $_SESSION['user_id'];
        $user = $this->usuarioModel->findById($userId);
        $rutas = $this->rutaModel->findByUsuarioId($userId);

        // Cargar Zonas de recolección para el selector del cliente
        require_once __DIR__ . '/../models/Zona.php';
        $zonaModel = new Zona();
        $zonas = $zonaModel->getAllZonas();

        require_once __DIR__ . '/../views/profile.php';
    }

    /**
     * Actualiza la información personal y/o la foto de perfil.
     */
    public function update() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: auth");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];
            $nombre = trim($_POST['nombre'] ?? '');
            $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
            $fotoPath = null;

            try {
                if (empty($nombre)) {
                    throw new Exception("El nombre es requerido.");
                }
                if (!$email) {
                    throw new Exception("Correo electrónico inválido.");
                }

                // Verificar si el email ya lo usa otro usuario
                $existingUser = $this->usuarioModel->findByEmail($email);
                if ($existingUser && intval($existingUser['id']) !== intval($userId)) {
                    throw new Exception("El correo electrónico ya está registrado por otro usuario.");
                }

                // Procesar subida de foto de perfil
                if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] !== UPLOAD_ERR_NO_FILE) {
                    $file = $_FILES['foto_perfil'];
                    
                    if ($file['error'] !== UPLOAD_ERR_OK) {
                        throw new Exception("Error al cargar el archivo de imagen.");
                    }

                    // Validar tipo de archivo
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                    $fileType = mime_content_type($file['tmp_name']);
                    if (!in_array($fileType, $allowedTypes)) {
                        throw new Exception("Solo se permiten imágenes JPG, JPEG y PNG.");
                    }

                    // Validar tamaño (máximo 2MB)
                    if ($file['size'] > 2 * 1024 * 1024) {
                        throw new Exception("La imagen no debe superar los 2MB.");
                    }

                    // Crear directorio si no existe
                    $uploadDir = __DIR__ . '/../public/uploads/profile_photos/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }

                    // Generar nombre único
                    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $fileName = 'profile_' . $userId . '_' . time() . '.' . $extension;
                    $destPath = $uploadDir . $fileName;

                    if (move_uploaded_file($file['tmp_name'], $destPath)) {
                        $fotoPath = 'uploads/profile_photos/' . $fileName;
                    } else {
                        throw new Exception("No se pudo guardar la imagen en el servidor.");
                    }
                }

                // Guardar cambios en BD
                $this->usuarioModel->updateProfile($userId, $nombre, $email, $fotoPath);
                
                // Actualizar sesión
                $_SESSION['user_nombre'] = $nombre;
                $_SESSION['user_email'] = $email;

                $_SESSION['success'] = "Perfil actualizado correctamente.";
                header("Location: profile");
                exit;
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
                header("Location: profile");
                exit;
            }
        }
    }

    /**
     * Agrega una nueva ruta y suma el cargo al estado de cuenta.
     */
    public function addRoute() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: auth");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];
            $nombre = trim($_POST['nombre'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $latitud = filter_input(INPUT_POST, 'latitud', FILTER_VALIDATE_FLOAT);
            $longitud = filter_input(INPUT_POST, 'longitud', FILTER_VALIDATE_FLOAT);
            $zonaId = filter_input(INPUT_POST, 'zona_id', FILTER_VALIDATE_INT) ?: null;

            try {
                if (empty($nombre)) {
                    throw new Exception("El nombre de la ruta/casa es obligatorio.");
                }
                if ($latitud === false || $latitud === null || $longitud === false || $longitud === null) {
                    throw new Exception("Debes marcar una ubicación válida en el mapa.");
                }

                // Costo mensual base
                $costo = 15.00;

                // Crear ruta vinculada a la zona
                $rutaId = $this->rutaModel->create($userId, $nombre, $descripcion, $latitud, $longitud, $costo, $zonaId);
                if (!$rutaId) {
                    throw new Exception("Error al guardar la nueva dirección.");
                }

                // Sumar al estado de cuenta del cliente
                $this->pagoModel->agregarCargo($userId, $costo);

                $_SESSION['success'] = "Dirección registrada y cargo de $" . number_format($costo, 2) . " sumado a su estado de cuenta.";
                header("Location: profile");
                exit;
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
                header("Location: profile");
                exit;
            }
        }
    }
}
