<?php
// --- Bloque de depuración temporal ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// --- Fin del bloque de depuración ---

header('Content-Type: application/json');

session_start();

// Simular login (si no tenés login real)
if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['id_usuario'] = 1; // Usuario fijo para pruebas
}

$id_usuario = $_SESSION['id_usuario'];
$data = json_decode(file_get_contents("php://input"), true);
$id_trabajo = intval($data['id_trabajo'] ?? 0);

if (!$id_trabajo || !$id_usuario) {
    echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
    exit;
}

require_once '../config/database.php';

try {
    $db = new Database();
    $conn = $db->getConexion();

    // Verificar si el like ya existe
    $stmt = $conn->prepare("SELECT id FROM likes WHERE id_usuario = ? AND id_trabajo = ?");
    $stmt->bind_param("ii", $id_usuario, $id_trabajo);
    $stmt->execute();
    $result = $stmt->get_result();
    $likeExistente = $result->fetch_assoc();
    $stmt->close();

    if ($likeExistente) {
        // Ya existe: quitar like
        $stmt = $conn->prepare("DELETE FROM likes WHERE id_usuario = ? AND id_trabajo = ?");
        $stmt->bind_param("ii", $id_usuario, $id_trabajo);
        $stmt->execute();
        $success = $stmt->affected_rows > 0;
        echo json_encode(['success' => $success, 'resultado' => -1]);
    } else {
        // No existe: agregar like
        $stmt = $conn->prepare("INSERT INTO likes (id_usuario, id_trabajo) VALUES (?, ?)");
        $stmt->bind_param("ii", $id_usuario, $id_trabajo);
        $stmt->execute();
        $success = $stmt->affected_rows > 0;
        echo json_encode(['success' => $success, 'resultado' => 1]);
    }
    $stmt->close();
    $db->close();

} catch (Exception $e) {
    error_log('Like API Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Error en la base de datos al procesar el like.']);
}
