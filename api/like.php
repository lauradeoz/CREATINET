<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

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
    // CONEXIÓN
    $db = new Database();
    $pdo = $db->getConexion();

    // Verificar si el like ya existe
    $stmt = $pdo->prepare("SELECT id FROM likes WHERE id_usuario = ? AND id_trabajo = ?");
    $stmt->execute([$id_usuario, $id_trabajo]);
    $likeExistente = $stmt->fetch();

    if ($likeExistente) {
        // Ya existe: quitar like
        $stmt = $pdo->prepare("DELETE FROM likes WHERE id_usuario = ? AND id_trabajo = ?");
        $stmt->execute([$id_usuario, $id_trabajo]);
        echo json_encode(['success' => true, 'resultado' => -1]);
    } else {
        // No existe: agregar like
        $stmt = $pdo->prepare("INSERT INTO likes (id_usuario, id_trabajo) VALUES (?, ?)");
        $stmt->execute([$id_usuario, $id_trabajo]);
        echo json_encode(['success' => true, 'resultado' => 1]);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'DB error: ' . $e->getMessage()]);
}
