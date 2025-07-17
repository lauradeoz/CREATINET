<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'];
    
    // Tu lógica de eliminación aquí
    // Ejemplo: $query = "DELETE FROM trabajos WHERE id = ?";
    
    echo json_encode(['success' => true]);
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
}
?>