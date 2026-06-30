<?php
/**
 * Front Controller de Smartsach.
 * Delega el enrutamiento y la lógica de backend al router.
 */

// Delegar al enrutador principal en el backend
require_once __DIR__ . '/../../backend/src/routes/web.php';
