<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/data/trabajoDB.php';

$database = new Database();
$trabajoDB = new ProyectoDB($database);

$id_usuario = 9; // ID del usuario actual
$titulo = "Mi Primer Proyecto Ficticio";
$descripcion = "Este es un proyecto de ejemplo creado automáticamente por el agente para probar la funcionalidad de subida.";
$archivo = "img/trabajos/proyecto_ficticio_ejemplo.jpg";
$programas_usados = ["img/LOGOS/PS.png", "img/LOGOS/AI.png"];

$result = $trabajoDB->create($id_usuario, $titulo, $descripcion, $archivo, $programas_usados);

if ($result && isset($result['id'])) {
    echo "Proyecto ficticio creado exitosamente con ID: " . $result['id'] . "\n";
} else {
    echo "Error al crear el proyecto ficticio.\n";
}

// Crear el archivo HTML para el proyecto ficticio
$proyecto_html = '
<div class="trabajo-card">
    <h3>' . htmlspecialchars($titulo) . '</h3>
    <p>Por: Usuario Ficticio</p>
    <img src="' . htmlspecialchars($archivo) . '" alt="' . htmlspecialchars($titulo) . '">
    <p>' . htmlspecialchars($descripcion) . '</p>
    <div class="programas-usados">
        <strong>Programas Usados:</strong>
        <img src="img/LOGOS/PS.png" alt="PS" class="programa-logo">
        <img src="img/LOGOS/AI.png" alt="AI" class="programa-logo">
    </div>
    <div class="likes">
        <button class="like-btn" data-id="ficticio">Like</button>
        <span class="likes-count">0</span>
    </div>
</div>';

file_put_contents('fictitious_project.html', $proyecto_html);

$database->close();
?>