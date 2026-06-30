<?php
/**
 * Configuración de la base de datos y conexión PDO.
 * Soporta variables de entorno locales y DATABASE_URL de Railway.
 */

class Database {
    private static $conn = null;

    public static function getConnection() {
        if (self::$conn === null) {
            try {
                // Obtener la URL de conexión de Railway si está definida
                $databaseUrl = getenv('DATABASE_URL') ?: (isset($_ENV['DATABASE_URL']) ? $_ENV['DATABASE_URL'] : null);

                if ($databaseUrl) {
                    // Parsear DATABASE_URL (formato: postgresql://user:pass@host:port/dbname)
                    $dbparts = parse_url($databaseUrl);

                    $host = $dbparts['host'];
                    $port = isset($dbparts['port']) ? $dbparts['port'] : '5432';
                    $user = $dbparts['user'];
                    $password = $dbparts['pass'];
                    $dbname = ltrim($dbparts['path'], '/');
                } else {
                    // Cargar variables individuales de entorno o usar valores por defecto
                    $host = getenv('DB_HOST') ?: 'localhost';
                    $port = getenv('DB_PORT') ?: '5432';
                    $dbname = getenv('DB_NAME') ?: 'smartsach';
                    $user = getenv('DB_USER') ?: 'postgres';
                    $password = getenv('DB_PASSWORD') ?: 'postgres';
                }

                $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
                
                // Opciones de conexión PDO
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];

                self::$conn = new PDO($dsn, $user, $password, $options);
            } catch (PDOException $e) {
                // En producción deberíamos registrar el error y mostrar un mensaje amigable
                throw new Exception("Error de conexión a la base de datos: " . $e->getMessage());
            }
        }
        return self::$conn;
    }
}
