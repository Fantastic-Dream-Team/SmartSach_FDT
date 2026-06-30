<?php
// Definir la raíz absoluta del proyecto para inclusiones seguras
define('ROOT_PATH', realpath(__DIR__ . '/..'));
/**
 * Front Controller de Smartsach.
 * Delega el enrutamiento y la lógica de backend al router.
 */
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
if ($uri !== '/' && file_exists(__DIR__ . $uri) && is_file(__DIR__ . $uri)) {
    return false;
}
// Fallback para servir estáticos (como JS modules) desde /frontend si están fuera de public/
$realPath = __DIR__ . '/..' . $uri;
if ($uri !== '/' && str_starts_with($uri, '/frontend/') && file_exists($realPath) && is_file($realPath)) {
    if (str_ends_with($realPath, '.js')) header('Content-Type: application/javascript');
    readfile($realPath);
    exit;
}

// Cargar dependencias de Composer (Autoload PSR-4)
require_once __DIR__ . '/../vendor/autoload.php';

// Delegar al enrutador principal en el backend
require_once __DIR__ . '/../backend/src/routes/web.php';
