<?php
require_once __DIR__ . '/../models/Usuario.php';

class AuthController {
    private $usuarioModel;

    public function __construct() {
        $this->usuarioModel = new Usuario();
    }

    /**
     * Procesa el login de usuario.
     */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
            $password = $_POST['password'] ?? '';

            try {
                if (!$email) {
                    throw new Exception("Correo electrónico inválido.");
                }
                if (empty($password)) {
                    throw new Exception("La contraseña es requerida.");
                }

                $user = $this->usuarioModel->findByEmail($email);
                if (!$user || !password_verify($password, $user['password'])) {
                    throw new Exception("Credenciales incorrectas.");
                }

                // Prevenir fijación de sesión regenerando el ID
                session_regenerate_id(true);

                // Guardar datos en la sesión
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nombre'] = $user['nombre'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_rol'] = $user['rol'];

                header("Location: dashboard");
                exit;
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
                header("Location: auth?action=login");
                exit;
            }
        }
    }

    /**
     * Procesa el registro de un nuevo usuario.
     */
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = trim($_POST['nombre'] ?? '');
            $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
            $password = $_POST['password'] ?? '';

            try {
                if (empty($nombre)) {
                    throw new Exception("El nombre completo es requerido.");
                }
                if (!$email) {
                    throw new Exception("Correo electrónico inválido.");
                }
                if (strlen($password) < 6) {
                    throw new Exception("La contraseña debe tener al menos 6 caracteres.");
                }

                // Verificar si el correo ya existe
                if ($this->usuarioModel->findByEmail($email)) {
                    throw new Exception("El correo electrónico ya está registrado.");
                }

                $userId = $this->usuarioModel->create($nombre, $email, $password, 'cliente');
                if (!$userId) {
                    throw new Exception("Error al registrar el usuario.");
                }

                // Iniciar sesión automáticamente
                session_regenerate_id(true);
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_nombre'] = $nombre;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_rol'] = 'cliente';

                $_SESSION['success'] = "¡Cuenta creada exitosamente!";
                header("Location: dashboard");
                exit;
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
                header("Location: auth?action=register");
                exit;
            }
        }
    }

    /**
     * Cierra la sesión del usuario.
     */
    public function logout() {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        header("Location: ./");
        exit;
    }
}
