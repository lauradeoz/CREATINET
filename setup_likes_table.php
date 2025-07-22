<?php
require_once 'config/database.php';

$db = new Database();
$conexion = $db->getConexion();

$sql = "
CREATE TABLE likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_trabajo INT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id),
    FOREIGN KEY (id_trabajo) REFERENCES proyectos(id)
);";

if ($conexion->query($sql) === TRUE) {
    echo "Table 'likes' created successfully";
} else {
    echo "Error creating table: " . $conexion->error;
}

$conexion->close();
?>