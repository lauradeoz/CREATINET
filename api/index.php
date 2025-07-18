<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);
session_start();

// Configuración de cabeceras
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Inclusión de archivos necesarios
require_once __DIR__ . '/../config/database.php';
require_once 'C:/xampp/htdocs/CREATINET/data/trabajoDB.php';
require_once __DIR__ . '/../controllers/portfolioController.php';

// Enrutamiento
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Verificar si hay una cabecera X-HTTP-Method-Override
if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
    $requestMethod = strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
    error_log("DEBUG: X-HTTP-Method-Override detectado. Método ajustado a: " . $requestMethod);
}
error_log("DEBUG: Método de solicitud final para API: " . $requestMethod);

// Eliminar el prefijo del directorio base si existe
$basePath = '/CREATINET/'; // Asegúrate de que esto coincida con tu RewriteBase en .htaccess
if (strpos($requestUri, $basePath) === 0) {
    $requestUri = substr($requestUri, strlen($basePath));
}

$segments = explode('/', trim($requestUri, '/'));

// El primer segmento debería ser 'api', el segundo el endpoint
if (empty($segments[0]) || $segments[0] !== 'api') {
    header('HTTP/1.1 404 Not Found');
    echo json_encode(['success' => false, 'error' => 'Endpoint de API no encontrado']);
    exit();
}

$endpoint = isset($segments[1]) ? $segments[1] : null; // 'trabajos', 'like', etc.
$trabajoId = isset($segments[2]) ? (int)$segments[2] : null; // Obtener el ID si está presente en la URL

$database = new Database();

switch ($endpoint) {
    case 'trabajos':
        // Determine the actual method to use for the controller
        // Prioritize X-HTTP-Method-Override if set, otherwise use the actual request method
        $actualMethod = $requestMethod; // This already considers X-HTTP-Method-Override

        // If it's a PUT request (either direct or overridden), use the ID from the URL
        if ($actualMethod === 'PUT') {
            $controller = new PortfolioController($database, 'PUT', $trabajoId);
        } else if ($actualMethod === 'POST') {
            // If it's a POST request, it could be a creation or an update via form submission
            // If trabajoId is present in the URL, it's an update
            if ($trabajoId) {
                $controller = new PortfolioController($database, 'PUT', $trabajoId); // Treat as PUT for controller
            } else {
                // Otherwise, it's a creation
                $controller = new PortfolioController($database, 'POST', null);
            }
        } else {
            // For GET and DELETE, use the determined actual method and URL ID
            $controller = new PortfolioController($database, $actualMethod, $trabajoId);
        }
        try {
            $controller->processRequest();
        } catch (Exception $e) {
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    case 'like':
        error_log("DEBUG: Endpoint 'like' alcanzado.");
        error_log("DEBUG: SESSION[\"usuario_id\"] = " . (isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : 'NO SET'));
        if ($requestMethod == 'POST') {
            $data = json_decode(file_get_contents('php://input'));
            error_log("DEBUG: Datos recibidos para like: " . print_r($data, true));
            if (!isset($_SESSION['usuario_id']) || !isset($data->id_trabajo)) {
                header('HTTP/1.1 400 Bad Request');
                echo json_encode(['success' => false, 'error' => 'Datos incompletos o sesión no iniciada.']);
                exit();
            }
            $trabajoDB = new TrabajoDB($database);
            $resultado = $trabajoDB->darLike($_SESSION['usuario_id'], $data->id_trabajo);
            echo json_encode(['success' => true, 'resultado' => $resultado]);
        } else {
            header('HTTP/1.1 405 Method Not Allowed');
        }
        break;

    default:
        header('HTTP/1.1 404 Not Found');
        echo json_encode(['success' => false, 'error' => 'Endpoint no válido']);
        break;
}

$database->close();