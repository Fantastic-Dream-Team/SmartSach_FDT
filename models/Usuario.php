<?php
require_once __DIR__ . '/../config/database.php';

class Usuario {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Busca un usuario por su correo electrónico.
     */
    public function findByEmail($email) {
        $sql = "SELECT * FROM usuarios WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    /**
     * Busca un usuario por su ID.
     */
    public function findById($id) {
        $sql = "SELECT id, nombre, email, rol, zona_id, foto_perfil, created_at FROM usuarios WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Registra un nuevo usuario en la base de datos con un rol determinado.
     */
    public function create($nombre, $email, $password, $rol = 'cliente', $zonaId = null) {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        
        $sql = "INSERT INTO usuarios (nombre, email, password, rol, zona_id) VALUES (:nombre, :email, :password, :rol, :zona_id) RETURNING id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'nombre' => $nombre,
            'email' => $email,
            'password' => $passwordHash,
            'rol' => $rol,
            'zona_id' => empty($zonaId) ? null : intval($zonaId)
        ]);
        
        $result = $stmt->fetch();
        return $result ? $result['id'] : false;
    }

    /**
     * Actualiza los datos del perfil de usuario.
     */
    public function updateProfile($id, $nombre, $email, $foto_perfil = null) {
        if ($foto_perfil) {
            $sql = "UPDATE usuarios SET nombre = :nombre, email = :email, foto_perfil = :foto_perfil WHERE id = :id";
            $params = [
                'nombre' => $nombre,
                'email' => $email,
                'foto_perfil' => $foto_perfil,
                'id' => $id
            ];
        } else {
            $sql = "UPDATE usuarios SET nombre = :nombre, email = :email WHERE id = :id";
            $params = [
                'nombre' => $nombre,
                'email' => $email,
                'id' => $id
            ];
        }
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Lista todos los conductores del sistema con su zona asignada.
     */
    public function getConductores() {
        $sql = "SELECT u.id, u.nombre, u.email, u.created_at, u.zona_id, z.nombre_zona 
                FROM usuarios u 
                LEFT JOIN zonas z ON u.zona_id = z.id 
                WHERE u.rol = 'conductor' 
                ORDER BY u.nombre ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Actualiza la información y zona de un conductor, aplicando la regla crítica de negocio.
     */
    public function updateConductorZona($id, $nombre, $email, $zonaId) {
        // Consultar el estado operativo actual de la zona del conductor
        $sql = "SELECT u.zona_id, z.estado 
                FROM usuarios u 
                LEFT JOIN zonas z ON u.zona_id = z.id 
                WHERE u.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $current = $stmt->fetch();
        
        if ($current && $current['estado'] === 'en_ruta') {
            throw new Exception("Regla de negocio: No se puede modificar ni reasignar este conductor porque su zona asignada está actualmente activa ('en_ruta'). El conductor debe finalizarla primero.");
        }

        $sql = "UPDATE usuarios SET nombre = :nombre, email = :email, zona_id = :zona_id WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'nombre' => $nombre,
            'email' => $email,
            'zona_id' => empty($zonaId) ? null : intval($zonaId),
            'id' => $id
        ]);
    }

    /**
     * Elimina un conductor del sistema.
     */
    public function deleteConductor($id) {
        // Verificar si su zona asignada está en_ruta antes de eliminar
        $sql = "SELECT u.zona_id, z.estado 
                FROM usuarios u 
                LEFT JOIN zonas z ON u.zona_id = z.id 
                WHERE u.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $current = $stmt->fetch();
        
        if ($current && $current['estado'] === 'en_ruta') {
            throw new Exception("Regla de negocio: No se puede eliminar al conductor porque su zona asignada está actualmente activa ('en_ruta').");
        }

        $sql = "DELETE FROM usuarios WHERE id = :id AND rol = 'conductor'";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
}
