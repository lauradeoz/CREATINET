<?php
require_once 'database.php';

$db = new Database();
$conexion = $db->getConexion();

$sql_usuarios = "
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    token VARCHAR(255) NULL,
    token_recuperacion VARCHAR(255) NULL,
    verificado TINYINT(1) DEFAULT 0,
    bloqueado TINYINT(1) DEFAULT 0,
    ultima_conexion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
";

if ($conexion->query($sql_usuarios) === TRUE) {
    echo "Tabla 'usuarios' creada con éxito.\n";
} else {
    echo "Error al crear la tabla 'usuarios': " . $conexion->error . "\n";
}


$sql_trabajos = "
CREATE TABLE IF NOT EXISTS trabajos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT,
    archivo VARCHAR(255) NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
);
";

$sql_likes = "
CREATE TABLE IF NOT EXISTS likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_trabajo INT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id),
    FOREIGN KEY (id_trabajo) REFERENCES trabajos(id)
);
";

if ($conexion->query($sql_trabajos) === TRUE) {
    echo "Tabla 'trabajos' creada con éxito.\n";
} else {
    echo "Error al crear la tabla 'trabajos': " . $conexion->error . "\n";
}

if ($conexion->query($sql_likes) === TRUE) {
    echo "Tabla 'likes' creada con éxito.\n";
} else {
    echo "Error al crear la tabla 'likes': " . $conexion->error . "\n";
}

$db->close();