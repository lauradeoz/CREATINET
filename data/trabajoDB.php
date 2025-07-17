<?php
require_once __DIR__ . '/../config/database.php';

class TrabajoDB {
    private $db;

    public function __construct($database) {
        $this->db = $database->getConexion();
    }

    public function create($id_usuario, $titulo, $descripcion, $archivo, $programas_usados) {
        $programas_str = implode(", ", $programas_usados);
        $stmt = $this->db->prepare("INSERT INTO proyectos (ususario_id, titulo, descripcion, archivo, programas_usados) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $id_usuario, $titulo, $descripcion, $archivo, $programas_str);
        $stmt->execute();
        $stmt->close();
        return ['id' => $this->db->insert_id];
    }

    public function getAll() {
        $sql = "
            SELECT t.*, u.nombre as nombre_usuario, COUNT(l.id) as favorito
            FROM proyectos t
            JOIN usuarios u ON t.ususario_id = u.id
            LEFT JOIN likes l ON t.id = l.id_trabajo
            GROUP BY t.id
            ORDER BY t.fecha_publicacion DESC
        ";
        $result = $this->db->query($sql);
        $trabajos = [];
        while ($row = $result->fetch_assoc()) {
            $trabajos[] = $row;
        }
        return $trabajos;
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM proyectos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $trabajo = $result->fetch_assoc();
        $stmt->close();
        return $trabajo;
    }

    public function update($id, $titulo, $descripcion, $archivo, $programas_usados) {
        $programas_str = implode(", ", $programas_usados);
        $sql = "UPDATE proyectos SET titulo = ?, descripcion = ?, archivo = ?, programas_usados = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssssi", $titulo, $descripcion, $archivo, $programas_str, $id);
        $stmt->execute();
        $stmt->close();
        return $stmt->affected_rows > 0;
    }

    public function delete($id) {
        error_log("DEBUG: Ejecutando DELETE para proyecto con ID: " . $id);
        $stmt = $this->db->prepare("DELETE FROM proyectos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $affectedRows = $stmt->affected_rows;
        $stmt->close();
        error_log("DEBUG: Filas afectadas por DELETE: " . $affectedRows);
        return $affectedRows > 0;
    }

    public function darLike($id_usuario, $id_trabajo) {
        //verificar si ya existe el like
        $stmt = $this->db->prepare("SELECT id FROM likes WHERE id_usuario = ? AND id_trabajo = ?");
        $stmt->bind_param("ii", $id_usuario, $id_trabajo);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            //si ya existe, lo borramos
            $stmt->close();
            $stmt = $this->db->prepare("DELETE FROM likes WHERE id_usuario = ? AND id_trabajo = ?");
            $stmt->bind_param("ii", $id_usuario, $id_trabajo);
            $stmt->execute();
            $stmt->close();
            return -1; //indica que se quito el like
        } else {
            //si no existe, lo insertamos
            $stmt->close();
            $stmt = $this->db->prepare("INSERT INTO likes (id_usuario, id_trabajo) VALUES (?, ?)");
            $stmt->bind_param("ii", $id_usuario, $id_trabajo);
            $stmt->execute();
            $stmt->close();
            return 1; //indica que se dio el like
        }
    }

    public function getProyectosUsuario($id_usuario) {
        $sql = "
            SELECT t.*, u.nombre as nombre_usuario, COUNT(l.id) as favorito
            FROM proyectos t
            JOIN usuarios u ON t.ususario_id = u.id
            LEFT JOIN likes l ON t.id = l.id_trabajo
            WHERE t.ususario_id = ?
            GROUP BY t.id
            ORDER BY t.fecha_publicacion DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $trabajos = [];
        while ($row = $result->fetch_assoc()) {
            $trabajos[] = $row;
        }
        $stmt->close();
        return $trabajos;
    }

    public function getProyectosOtros($id_usuario) {
        $sql = "
            SELECT t.*, u.nombre as nombre_usuario, COUNT(l.id) as favorito
            FROM proyectos t
            JOIN usuarios u ON t.ususario_id = u.id
            LEFT JOIN likes l ON t.id = l.id_trabajo
            WHERE t.ususario_id != ?
            GROUP BY t.id
            ORDER BY t.fecha_publicacion DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $trabajos = [];
        while ($row = $result->fetch_assoc()) {
            $trabajos[] = $row;
        }
        $stmt->close();
        return $trabajos;
    }
}