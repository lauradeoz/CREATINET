<?php
// Configuración de errores
    ini_set('display_errors', 0); // No mostrar errores en pantalla
    ini_set('log_errors', 1); // Habilitar el registro de errores
    ini_set('error_log', 'errores.log'); // Guardar errores en un archivo llamado errores.log
    error_reporting(E_ALL); // Reportar todos los errores
/**
 * data/trabajoDB.php
 *
 * Esta clase `TrabajoDB` se encarga de todas las operaciones de la base de datos
 * relacionadas con los "trabajos" o "proyectos" del portfolio.
 * Utiliza MySQLi para interactuar con la tabla `proyectos` y `likes`.
 */

// Incluye el archivo de configuración de la base de datos.
require_once __DIR__ . '/../config/database.php';

class TrabajoDB {
    private $db; // Propiedad para almacenar la conexión a la base de datos.

    /**
     * Constructor de la clase TrabajoDB.
     *
     * @param Database $database Una instancia de la clase Database que proporciona la conexión.
     */
    public function __construct($database) {
        // Obtiene la conexión mysqli del objeto Database.
        $this->db = $database->getConexion();
    }

    /**
     * Crea un nuevo trabajo (proyecto) en la base de datos.
     *
     * @param int    $id_usuario       ID del usuario que sube el trabajo.
     * @param string $titulo           Título del trabajo.
     * @param string $descripcion      Descripción del trabajo.
     * @param string $archivo          Ruta del archivo de imagen del trabajo.
     * @param array  $programas_usados Array de programas utilizados en el trabajo.
     * @return array Un array asociativo con el ID del trabajo insertado.
     */
    public function create($id_usuario, $titulo, $descripcion, $archivo, $programas_usados) {
        // Convierte el array de programas usados en una cadena separada por comas.
        $programas_str = implode(", ", $programas_usados);
        
        // Prepara la consulta SQL para insertar un nuevo proyecto.
        $stmt = $this->db->prepare("INSERT INTO proyectos (usuario_id, titulo, descripcion, imagen, programas_usados) VALUES (?, ?, ?, ?, ?)");
        
        // Vincula los parámetros a la consulta preparada.
        // 'i' para entero (id_usuario), 's' para cadena (titulo, descripcion, imagen, programas_str).
        $stmt->bind_param("issss", $id_usuario, $titulo, $descripcion, $archivo, $programas_str);
        
        // Ejecuta la consulta.
        $stmt->execute();
        
        // Cierra la declaración preparada.
        $stmt->close();
        
        // Devuelve el ID del último insertado.
        return ['id' => $this->db->insert_id];
    }

    /**
     * Obtiene todos los trabajos de la base de datos, incluyendo el nombre del usuario
     * y el conteo de likes.
     *
     * @return array Un array de arrays asociativos, donde cada uno representa un trabajo.
     */
    public function getAll() {
        $sql = "
            SELECT t.*, u.nombre as nombre_usuario, COUNT(l.id) as favorito
            FROM proyectos t
            JOIN usuarios u ON t.usuario_id = u.id
            LEFT JOIN likes l ON t.id = l.id_trabajo
            GROUP BY t.id
            ORDER BY t.fecha_publicacion DESC
        ";
        // Ejecuta la consulta SQL.
        $result = $this->db->query($sql);
        
        $trabajos = [];
        // Itera sobre los resultados y los añade al array de trabajos.
        while ($row = $result->fetch_assoc()) {
            $trabajos[] = $row;
        }
        return $trabajos;
    }

    /**
     * Obtiene un trabajo específico por su ID.
     *
     * @param int $id ID del trabajo a buscar.
     * @return array|null Un array asociativo con los datos del trabajo, o null si no se encuentra.
     */
    public function getById($id) {
        // Prepara la consulta para seleccionar un trabajo por su ID.
        $stmt = $this->db->prepare("SELECT * FROM proyectos WHERE id = ?");
        
        // Vincula el ID como parámetro entero.
        $stmt->bind_param("i", $id);
        
        // Ejecuta la consulta.
        $stmt->execute();
        
        // Obtiene el resultado de la consulta.
        $result = $stmt->get_result();
        
        // Obtiene la fila como un array asociativo.
        $trabajo = $result->fetch_assoc();
        
        // Cierra la declaración.
        $stmt->close();
        
        return $trabajo;
    }

    /**
     * Actualiza un trabajo existente en la base de datos.
     *
     * @param int    $id               ID del trabajo a actualizar.
     * @param string $titulo           Nuevo título del trabajo.
     * @param string $descripcion      Nueva descripción del trabajo.
     * @param string $archivo          Nueva ruta del archivo de imagen del trabajo.
     * @param array  $programas_usados Array de programas utilizados en el trabajo.
     * @return bool True si la actualización fue exitosa, false en caso contrario.
     */
    public function update($id, $titulo, $descripcion, $archivo, $programas_usados) {
        // Convierte el array de programas usados en una cadena separada por comas.
        $programas_str = implode(", ", $programas_usados);
        
        // Consulta SQL para actualizar un proyecto.
        $sql = "UPDATE proyectos SET titulo = ?, descripcion = ?, imagen = ?, programas_usados = ? WHERE id = ?";
        
        // Prepara la consulta.
        $stmt = $this->db->prepare($sql);
        
        // Verifica si la preparación de la consulta falló.
        if ($stmt === false) {
            error_log("Error al preparar la consulta UPDATE: " . $this->db->error);
            return false;
        }
        
        // Vincula los parámetros.
        $stmt->bind_param("ssssi", $titulo, $descripcion, $archivo, $programas_str, $id);
        
        // Ejecuta la consulta.
        $execute_success = $stmt->execute();
        
        // Verifica si la ejecución de la consulta falló.
        if ($execute_success === false) {
            error_log("Error al ejecutar la consulta UPDATE: " . $stmt->error);
            $stmt->close();
            return false;
        }
        
        // Obtiene el número de filas afectadas por la operación.
        $affectedRows = $stmt->affected_rows;
        
        // Cierra la declaración.
        $stmt->close();
        
        error_log("DEBUG: Filas afectadas por UPDATE: " . $affectedRows);
        
        // Devuelve true si se afectó al menos una fila (indicando éxito).
        return $affectedRows > 0;
    }

    /**
     * Elimina un trabajo de la base de datos.
     *
     * @param int $id ID del trabajo a eliminar.
     * @return bool True si la eliminación fue exitosa, false en caso contrario.
     */
    public function delete($id) {
        error_log("DEBUG: Ejecutando DELETE para proyecto con ID: " . $id);
        
        // Prepara la consulta para eliminar un proyecto por su ID.
        $stmt = $this->db->prepare("DELETE FROM proyectos WHERE id = ?");
        
        // Vincula el ID como parámetro entero.
        $stmt->bind_param("i", $id);
        
        // Ejecuta la consulta.
        $stmt->execute();
        
        // Obtiene el número de filas afectadas.
        $affectedRows = $stmt->affected_rows;
        
        // Cierra la declaración.
        $stmt->close();
        
        error_log("DEBUG: Filas afectadas por DELETE: " . $affectedRows);
        
        // Devuelve true si se afectó al menos una fila.
        return $affectedRows > 0;
    }

    /**
     * Maneja la lógica de "me gusta" para un trabajo.
     * Si el usuario ya dio "me gusta", lo quita; de lo contrario, lo añade.
     *
     * @param int $id_usuario ID del usuario.
     * @param int $id_trabajo ID del trabajo.
     * @return int 1 si se añadió el like, -1 si se quitó, 0 si hubo un error.
     */
    public function darLike($id_usuario, $id_trabajo) {
        // Verificar si ya existe el like
        $stmt = $this->db->prepare("SELECT id FROM likes WHERE id_usuario = ? AND id_trabajo = ?");
        if ($stmt === false) {
            error_log("Error al preparar SELECT en darLike: " . $this->db->error);
            return 0;
        }
        $stmt->bind_param("ii", $id_usuario, $id_trabajo);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            // Si ya existe, lo borramos
            $stmt->close();
            $stmt = $this->db->prepare("DELETE FROM likes WHERE id_usuario = ? AND id_trabajo = ?");
            if ($stmt === false) {
                error_log("Error al preparar DELETE en darLike: " . $this->db->error);
                return 0;
            }
            $stmt->bind_param("ii", $id_usuario, $id_trabajo);
            if (!$stmt->execute()) {
                error_log("Error al ejecutar DELETE en darLike: " . $stmt->error);
                $stmt->close();
                return 0;
            }
            $stmt->close();
            return -1; // Indica que se quitó el like
        } else {
            // Si no existe, lo insertamos
            $stmt->close();
            $stmt = $this->db->prepare("INSERT INTO likes (id_usuario, id_trabajo) VALUES (?, ?)");
            if ($stmt === false) {
                error_log("Error al preparar INSERT en darLike: " . $this->db->error);
                return 0;
            }
            $stmt->bind_param("ii", $id_usuario, $id_trabajo);
            if (!$stmt->execute()) {
                error_log("Error al ejecutar INSERT en darLike: " . $stmt->error);
                $stmt->close();
                return 0;
            }
            $stmt->close();
            return 1; // Indica que se dio el like
        }
    }

    /**
     * Obtiene los proyectos subidos por un usuario específico.
     *
     * @param int $id_usuario ID del usuario.
     * @return array Un array de arrays asociativos, donde cada uno representa un trabajo del usuario.
     */
    public function getProyectosUsuario($id_usuario) {
        $sql = "
            SELECT t.*, u.nombre as nombre_usuario, COUNT(l.id) as favorito
            FROM proyectos t
            JOIN usuarios u ON t.usuario_id = u.id
            LEFT JOIN likes l ON t.id = l.id_trabajo
            WHERE t.usuario_id = ?
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

    /**
     * Obtiene proyectos de otros usuarios (excluyendo los del usuario actual).
     *
     * @param int $id_usuario ID del usuario actual.
     * @return array Un array de arrays asociativos, donde cada uno representa un trabajo de otros usuarios.
     */
    public function getProyectosOtros($id_usuario) {
        $sql = "
            SELECT t.*, u.nombre as nombre_usuario, COUNT(l.id) as favorito
            FROM proyectos t
            JOIN usuarios u ON t.usuario_id = u.id
            LEFT JOIN likes l ON t.id = l.id_trabajo
            WHERE t.usuario_id != ?
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
