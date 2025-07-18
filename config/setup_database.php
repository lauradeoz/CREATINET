<?php
/**
 * config/setup_database.php
 *
 * Este script se encarga de configurar la estructura inicial de la base de datos,
 * creando las tablas necesarias si no existen.
 * Debe ejecutarse una vez para inicializar la base de datos.
 */

// Incluye el archivo que contiene la clase Database para gestionar la conexión.
require_once 'database.php';

// Crea una nueva instancia de la clase Database para establecer la conexión.
$db = new Database();
// Obtiene el objeto de conexión mysqli.
$conexion = $db->getConexion();

// --- Definición y creación de la tabla 'usuarios' ---
$sql_usuarios = "
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    token VARCHAR(255) NULL,                 -- Token para verificación de cuenta
    token_recuperacion VARCHAR(255) NULL,   -- Token para restablecimiento de contraseña
    verificado TINYINT(1) DEFAULT 0,        -- 0: no verificado, 1: verificado
    bloqueado TINYINT(1) DEFAULT 0,         -- 0: no bloqueado, 1: bloqueado
    ultima_conexion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
";

// Ejecuta la consulta SQL para crear la tabla 'usuarios'.
if ($conexion->query($sql_usuarios) === TRUE) {
    echo "Tabla 'usuarios' creada con éxito.\n";
} else {
    echo "Error al crear la tabla 'usuarios': " . $conexion->error . "\n";
}

// --- Definición y creación de la tabla 'trabajos' ---
$sql_trabajos = "
CREATE TABLE IF NOT EXISTS trabajos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT,
    archivo VARCHAR(255) NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
);
";

// --- Definición y creación de la tabla 'likes' ---
$sql_likes = "
CREATE TABLE IF NOT EXISTS likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_trabajo INT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_trabajo) REFERENCES trabajos(id) ON DELETE CASCADE
);
";

// Ejecuta la consulta SQL para crear la tabla 'trabajos'.
if ($conexion->query($sql_trabajos) === TRUE) {
    echo "Tabla 'trabajos' creada con éxito.\n";
} else {
    echo "Error al crear la tabla 'trabajos': " . $conexion->error . "\n";
}

// Ejecuta la consulta SQL para crear la tabla 'likes'.
if ($conexion->query($sql_likes) === TRUE) {
    echo "Tabla 'likes' creada con éxito.\n";
} else {
    echo "Error al crear la tabla 'likes': " . $conexion->error . "\n";
}

// Cierra la conexión a la base de datos.
$db->close();
