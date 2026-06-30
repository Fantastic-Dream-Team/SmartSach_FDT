<?php
/**
 * Configuración de la base de datos y conexión PDO a Supabase (PostgreSQL) en Railway.
 * Utiliza variables de entorno estrictamente.
 */

class Database {
    private static $conn = null;

    public static function getConnection() {
        if (self::$conn === null) {
            try {
                // Leer las variables de entorno inyectadas por Railway
                $host = getenv('DB_HOST') ?: (isset($_ENV['DB_HOST']) ? $_ENV['DB_HOST'] : '');
                $port = getenv('DB_PORT') ?: (isset($_ENV['DB_PORT']) ? $_ENV['DB_PORT'] : '5432');
                $dbname = getenv('DB_NAME') ?: (isset($_ENV['DB_NAME']) ? $_ENV['DB_NAME'] : '');
                $user = getenv('DB_USER') ?: (isset($_ENV['DB_USER']) ? $_ENV['DB_USER'] : '');
                $password = getenv('DB_PASSWORD') ?: (isset($_ENV['DB_PASSWORD']) ? $_ENV['DB_PASSWORD'] : '');
                $sslmode = getenv('DB_SSLMODE') ?: (isset($_ENV['DB_SSLMODE']) ? $_ENV['DB_SSLMODE'] : 'require');
                $sslmode = strtolower($sslmode);

                // Forzar SSL requerido por Supabase
                if (empty($host) || empty($dbname) || empty($user) || empty($password)) {
                    throw new Exception("Faltan variables de entorno para la base de datos.");
                }

                $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=$sslmode";
                
                // Opciones de conexión PDO
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];

                self::$conn = new PDO($dsn, $user, $password, $options);
            } catch (Exception $e) {
                // En lugar de exponer un stack trace, devolvemos un JSON limpio
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Error de conexión a la base de datos.',
                    'detail' => 'Verifique la configuración o el estado del servidor PostgreSQL.'
                ]);
                exit;
            }
        }
        return self::$conn;
    }
}
