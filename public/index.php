<?php
/**
 * Front Controller & Enrutador Central de Smartsach.
 */

// Configuración de cookies de sesión seguras
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
}

// Iniciar sesión
session_start();

// Autocarga de clases básica (si fuera necesaria, pero incluiremos manualmente por simplicidad y robustez)
require_once __DIR__ . '/../config/database.php';

// Enrutador inteligente
$request_uri = $_SERVER['REQUEST_URI'];
$base_path = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));

if (substr($request_uri, 0, strlen($base_path)) === $base_path) {
    $route = substr($request_uri, strlen($base_path));
} else {
    $route = $request_uri;
}

// Limpiar la ruta para remover parámetros query (e.g. ?action=login)
$route = parse_url($route, PHP_URL_PATH);

// Normalizar la ruta final
if ($route === '' || $route === '/' || $route === '/index.php') {
    $route = '/';
} else {
    $route = '/' . trim($route, '/');
}

// Despacho de rutas
switch ($route) {
    case '/migrate':
        try {
            $db = Database::getConnection();
            $sqlFile = __DIR__ . '/../database/database.sql';
            if (!file_exists($sqlFile)) {
                throw new Exception("No se encontró el archivo SQL de la base de datos.");
            }
            $sql = file_get_contents($sqlFile);
            $db->exec($sql);
            echo "<div style='font-family: sans-serif; padding: 40px; text-align: center;'>
                    <h1 style='color: #2d5a46;'>¡Migración Exitosa!</h1>
                    <p>Las tablas e índices han sido creados y los datos de prueba han sido insertados correctamente en la base de datos de Railway.</p>
                    <br><a href='./' style='background: #2d5a46; color: white; padding: 10px 20px; text-decoration: none; border-radius: 20px;'>Ir al Inicio / Iniciar Sesión</a>
                  </div>";
        } catch (Exception $e) {
            echo "<div style='font-family: sans-serif; padding: 40px; text-align: center; color: #ba1a1a;'>
                    <h1>Error de Migración</h1>
                    <p>" . htmlspecialchars($e->getMessage()) . "</p>
                  </div>";
        }
        break;

    case '/':
    case '/home':
        // Vista estática de presentación
        require_once __DIR__ . '/../views/home.php';
        break;

    case '/auth':
        require_once __DIR__ . '/../controllers/AuthController.php';
        $controller = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_GET['action'] ?? '';
            if ($action === 'register') {
                $controller->register();
            } else {
                $controller->login();
            }
        } else {
            // Renderizar formulario de login/registro
            require_once __DIR__ . '/../views/auth.php';
        }
        break;

    case '/logout':
        require_once __DIR__ . '/../controllers/AuthController.php';
        $controller = new AuthController();
        $controller->logout();
        break;

    case '/dashboard':
        $rol = $_SESSION['user_rol'] ?? 'cliente';
        if ($rol === 'gestor') {
            require_once __DIR__ . '/../controllers/GestorController.php';
            $controller = new GestorController();
            $controller->dashboard();
        } elseif ($rol === 'conductor') {
            require_once __DIR__ . '/../controllers/ConductorController.php';
            $controller = new ConductorController();
            $controller->dashboard();
        } else {
            require_once __DIR__ . '/../controllers/DashboardController.php';
            $controller = new DashboardController();
            $controller->index();
        }
        break;

    case '/payments':
        $rol = $_SESSION['user_rol'] ?? 'cliente';
        if ($rol !== 'cliente') {
            header("Location: dashboard");
            exit;
        }
        require_once __DIR__ . '/../controllers/PaymentController.php';
        $controller = new PaymentController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->pay();
        } else {
            $controller->index();
        }
        break;

    case '/profile':
        $rol = $_SESSION['user_rol'] ?? 'cliente';
        if ($rol === 'gestor') {
            require_once __DIR__ . '/../controllers/GestorController.php';
            $controller = new GestorController();
            $controller->profile();
        } elseif ($rol === 'conductor') {
            require_once __DIR__ . '/../controllers/ConductorController.php';
            $controller = new ConductorController();
            $controller->profile();
        } else {
            require_once __DIR__ . '/../controllers/ProfileController.php';
            $controller = new ProfileController();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $action = $_GET['action'] ?? '';
                if ($action === 'add_route') {
                    $controller->addRoute();
                } else {
                    $controller->update();
                }
            } else {
                $controller->index();
            }
        }
        break;

    case '/help':
    case '/report':
        $rol = $_SESSION['user_rol'] ?? 'cliente';
        if ($rol === 'gestor') {
            require_once __DIR__ . '/../controllers/GestorController.php';
            $controller = new GestorController();
            $controller->reportes();
        } elseif ($rol === 'conductor') {
            header("Location: dashboard");
            exit;
        } else {
            require_once __DIR__ . '/../controllers/SupportController.php';
            $controller = new SupportController();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $action = $_GET['action'] ?? '';
                if ($action === 'comment') {
                    $controller->addComment();
                } else {
                    $controller->submitReport();
                }
            } else {
                $controller->index();
            }
        }
        break;

    case '/notifications':
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'cliente') {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit;
        }
        require_once __DIR__ . '/../models/Reporte.php';
        $reporteModel = new Reporte();
        $unread = $reporteModel->getUnreadRepliesCount($_SESSION['user_id']);
        echo json_encode(['status' => 'success', 'unread_count' => $unread]);
        exit;

    case '/news':
        $rol = $_SESSION['user_rol'] ?? 'cliente';
        if ($rol !== 'gestor') {
            header("Location: dashboard");
            exit;
        }
        require_once __DIR__ . '/../controllers/GestorController.php';
        $controller = new GestorController();
        $controller->noticias();
        break;

    default:
        // Redirigir a Home en caso de ruta no encontrada
        header("Location: ./");
        exit;
}
