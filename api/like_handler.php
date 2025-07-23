<?php
// --- Habilitar errores para depuración ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Incluir la configuración de la base de datos de forma segura
require_once $_SERVER['DOCUMENT_ROOT'] . '/laura/config/database.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Simulación de login para pruebas (puedes cambiar esto por tu sistema real)
if (!isset($_SESSION['id_usuario'])) {
    // En un sistema real, aquí devolverías un error si el usuario no está logueado.
    // Para la prueba, asignamos un usuario fijo.
    $_SESSION['id_usuario'] = 1; 
}

// --- Obtener datos de la solicitud ---
$id_usuario = $_SESSION['id_usuario'];
$input = json_decode(file_get_contents('php://input'), true);
$id_trabajo = isset($input['id_trabajo']) ? (int)$input['id_trabajo'] : 0;

if ($id_trabajo === 0) {
    echo json_encode(['success' => false, 'error' => 'ID de trabajo no válido.']);
    exit;
}

// --- Lógica del Like ---
try {
    $db = new Database();
    $conn = $db->getConexion();

    // 1. Verificar si el like ya existe
    $stmt = $conn->prepare("SELECT id FROM likes WHERE id_usuario = ? AND id_trabajo = ?");
    $stmt->bind_param("ii", $id_usuario, $id_trabajo);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // 2a. Si existe, se elimina (unlike)
        $stmt_delete = $conn->prepare("DELETE FROM likes WHERE id_usuario = ? AND id_trabajo = ?");
        $stmt_delete->bind_param("ii", $id_usuario, $id_trabajo);
        $stmt_delete->execute();
        $stmt_delete->close();
    } else {
        // 2b. Si no existe, se inserta (like)
        $stmt_insert = $conn->prepare("INSERT INTO likes (id_usuario, id_trabajo) VALUES (?, ?)");
        $stmt_insert->bind_param("ii", $id_usuario, $id_trabajo);
        $stmt_insert->execute();
        $stmt_insert->close();
    }
    $stmt->close();

    // 3. Obtener el nuevo conteo de likes para ese trabajo
    $stmt_count = $conn->prepare("SELECT COUNT(*) as like_count FROM likes WHERE id_trabajo = ?");
    $stmt_count->bind_param("i", $id_trabajo);
    $stmt_count->execute();
    $result = $stmt_count->get_result();
    $row = $result->fetch_assoc();
    $new_like_count = $row['like_count'];
    $stmt_count->close();

    // 4. Devolver una respuesta exitosa con el nuevo conteo
    echo json_encode(['success' => true, 'new_like_count' => $new_like_count]);

    $db->close();

} catch (Exception $e) {
    // En caso de un error de base de datos, registrarlo y devolver un error genérico
    error_log("Error en like_handler.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Ocurrió un error en el servidor.']);
}
