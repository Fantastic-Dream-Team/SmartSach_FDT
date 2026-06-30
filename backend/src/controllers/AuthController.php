<?php
require_once __DIR__ . '/../models/Usuario.php';

class AuthController {
    private $usuarioModel;

    public function __construct() {
        $this->usuarioModel = new Usuario();
    }

    /**
     * Establece la sesión en el backend una vez que Supabase autenticó al usuario en el frontend.
     * Espera recibir 'auth_id' y 'email' mediante POST.
     */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
            $authId = $_POST['auth_id'] ?? '';

            try {
                if (!$email || empty($authId)) {
                    throw new Exception("Faltan datos de autenticación de Supabase.");
                }

                // Buscar al usuario por correo electrónico (o auth_id)
                $user = $this->usuarioModel->findByEmail($email);
                
                if (!$user) {
                    throw new Exception("Usuario no encontrado en la base de datos interna. Si acabas de registrarte, espera a que el trigger sincronice tus datos.");
                }

                // Prevenir fijación de sesión regenerando el ID
                session_regenerate_id(true);

                // Guardar datos en la sesión
                $_SESSION['user_id'] = $user['usuario_id'];
                $_SESSION['auth_id'] = $user['auth_id'];
                $_SESSION['user_nombre'] = $user['nombre'];
                $_SESSION['user_email'] = $user['correo_electronico'];
                // TODO: Leer rol de metadatos de Supabase. Temporalmente asumimos cliente si no se define.
                $_SESSION['user_rol'] = 'cliente'; 

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
     * El registro local ya no está soportado.
     * Supabase gestiona el registro y dispara un trigger para poblar la BD interna.
     */
    public function register() {
        $_SESSION['error'] = "El registro debe realizarse a través del cliente de Supabase en el frontend.";
        header("Location: auth?action=register");
        exit;
    }

    /**
     * Cierra la sesión del usuario en el backend.
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

