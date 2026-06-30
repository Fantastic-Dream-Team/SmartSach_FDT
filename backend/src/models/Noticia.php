<?php
require_once __DIR__ . '/../../config/database.php';

class Noticia {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Obtiene todas las noticias ordenadas por fecha de publicación (más recientes primero).
     */
    public function getAllNoticias() {
        try {
            $sql = "SELECT n.*, u.nombre as autor_nombre 
                    FROM public.noticias n
                    LEFT JOIN public.usuarios u ON n.autor_id = u.usuario_id
                    ORDER BY n.fecha_publicacion DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Crea una nueva noticia en el sistema.
     */
    public function create($titulo, $contenido, $autorId) {
        try {
            $sql = "INSERT INTO public.noticias (titulo, contenido, autor_id) 
                    VALUES (:titulo, :contenido, :autor_id) RETURNING id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'titulo' => $titulo,
                'contenido' => $contenido,
                'autor_id' => $autorId
            ]);
            $result = $stmt->fetch();
            return $result ? $result['id'] : false;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Elimina una noticia del sistema.
     */
    public function delete($id) {
        try {
            $sql = "DELETE FROM public.noticias WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
