<?php
/**
 * Front Controller de Smartsach.
 * Delega el enrutamiento y la lógica de backend al router.
 */

// Cargar dependencias de Composer (Autoload PSR-4)
require_once __DIR__ . '/../../vendor/autoload.php';

// Delegar al enrutador principal en el backend
require_once __DIR__ . '/../src/routes/web.php';
