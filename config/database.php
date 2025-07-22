<?php
// Configuración de errores
    ini_set('display_errors', 0); // No mostrar errores en pantalla
    ini_set('log_errors', 1); // Habilitar el registro de errores
    ini_set('error_log', 'errores.log'); // Guardar errores en un archivo llamado errores.log
    error_reporting(E_ALL); // Reportar todos los errores
/**
 * config/database.php
 *
 * Esta clase `Database` se encarga de gestionar la conexión a la base de datos.
 * Proporciona métodos para establecer la conexión, obtener la instancia de la conexión
 * y cerrar la conexión.
 */
include_once('config.php');
class Database {
    // Propiedades privadas para almacenar los detalles de la conexión a la base de datos.
    private $host = DB_HOST; // Dirección del servidor de la base de datos.
    private $user = DB_USER;      // Nombre de usuario para la conexión a la base de datos.
    private $password = DB_PASS;      // Contraseña para el usuario de la base de datos.
    private $database = DB_NAME; // Nombre de la base de datos a la que conectarse.
    private $conexion;           // Objeto de conexión a la base de datos (mysqli).

    /**
     * Constructor de la clase Database.
     * Se llama automáticamente cuando se crea una nueva instancia de Database.
     * Inicia la conexión a la base de datos.
     */
    public function __construct() {
        $this->connect();
    }

    /**
     * Método privado para establecer la conexión a la base de datos.
     * Utiliza la extensión MySQLi para conectar.
     * Si la conexión falla, termina la ejecución del script y muestra un mensaje de error.
     */
    private function connect() {
        // Crea una nueva instancia de mysqli para conectar a la base de datos.
        $this->conexion = new mysqli($this->host, $this->user, $this->password, $this->database);

        // Verifica si hubo un error en la conexión.
        if ($this->conexion->connect_error) {
            // Si hay un error, termina el script y muestra el mensaje de error.
            die("Error de conexión: " . $this->conexion->connect_error);
        }
    }

    /**
     * Método público para obtener la instancia de la conexión a la base de datos.
     * Permite que otras clases interactúen con la base de datos a través de esta conexión.
     *
     * @return mysqli La instancia de la conexión a la base de datos.
     */
    public function getConexion() {
        return $this->conexion;
    }

    /**
     * Método público para cerrar la conexión a la base de datos.
     * Es buena práctica cerrar la conexión cuando ya no se necesita para liberar recursos.
     */
    public function close() {
        $this->conexion->close();
    }
}
