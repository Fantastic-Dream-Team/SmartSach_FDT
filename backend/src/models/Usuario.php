<?php
require_once __DIR__ . '/../../config/database.php';

class Usuario {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Busca un usuario por su correo electrónico.
     */
    public function findByEmail($email) {
        $sql = "SELECT * FROM public.usuarios WHERE correo_electronico = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    /**
     * Busca un usuario por su auth_id (Supabase).
     */
    public function findByAuthId($authId) {
        $sql = "SELECT * FROM public.usuarios WHERE auth_id = :auth_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['auth_id' => $authId]);
        return $stmt->fetch();
    }

    /**
     * Busca un usuario por su ID interno.
     */
    public function findById($id) {
        $sql = "SELECT * FROM public.usuarios WHERE usuario_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Actualiza los datos del perfil de usuario.
     */
    public function updateProfile($id, $nombre, $apellido, $telefono, $direccion) {
        $sql = "UPDATE public.usuarios 
                SET nombre = :nombre, 
                    apellido = :apellido, 
                    telefono = :telefono, 
                    direccion = :direccion 
                WHERE usuario_id = :id";
        $params = [
            'nombre' => $nombre,
            'apellido' => $apellido,
            'telefono' => $telefono,
            'direccion' => $direccion,
            'id' => $id
        ];
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
}
