<?php
/**
 * api/index.php
 *
 * Este es el punto de entrada principal para todas las solicitudes de la API.
 * Actúa como un enrutador central, dirigiendo las solicitudes a los controladores
 * adecuados basándose en la URL y el método de la solicitud.
 */

// Configuración de errores para depuración.
// ini_set('display_errors', 0); // Deshabilita la visualización de errores en la salida.
// ini_set('log_errors', 1);     // Habilita el registro de errores en el log del servidor.
// error_reporting(E_ALL);      // Reporta todos los errores de PHP.

// Inicia la sesión. Es crucial para mantener el estado del usuario (ej. usuario logueado).
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Configuración de cabeceras HTTP para permitir CORS (Cross-Origin Resource Sharing)
// y definir el tipo de contenido de la respuesta.
header("Access-Control-Allow-Origin: *"); // Permite solicitudes desde cualquier origen.
header("Content-Type: application/json; charset=UTF-8"); // Establece el tipo de contenido como JSON.
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE"); // Define los métodos HTTP permitidos.
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With"); // Define las cabeceras permitidas.

// Inclusión de archivos necesarios para la funcionalidad de la API.
require_once __DIR__ . '/../config/database.php'; // Configuración de la base de datos.
require_once 'C:/xampp/htdocs/CREATINET/data/trabajoDB.php'; // Clase para interactuar con la tabla de trabajos.
require_once __DIR__ . '/../controllers/portfolioController.php'; // Controlador para la lógica de negocio de trabajos.

// --- Lógica de Enrutamiento ---

// Obtiene la URI de la solicitud (la parte de la URL después del dominio).
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// Obtiene el método HTTP de la solicitud (GET, POST, PUT, DELETE, etc.).
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Verifica si existe una cabecera 'X-HTTP-Method-Override'.
// Esta cabecera se usa comúnmente para simular métodos PUT o DELETE
// cuando el cliente (ej. un formulario HTML) solo puede enviar POST.
if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
    $requestMethod = strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
    error_log("DEBUG: X-HTTP-Method-Override detectado. Método ajustado a: " . $requestMethod);
}
error_log("DEBUG: Método de solicitud final para API: " . $requestMethod);

// Elimina el prefijo del directorio base de la URI si existe.
// Esto es necesario si la aplicación no está en la raíz del servidor web.
$basePath = '/CREATINET/'; // Debe coincidir con RewriteBase en .htaccess.
if (strpos($requestUri, $basePath) === 0) {
    $requestUri = substr($requestUri, strlen($basePath));
}

// Divide la URI en segmentos para identificar el endpoint y los parámetros.
// Ejemplo: /api/trabajos/123 -> ['api', 'trabajos', '123']
$segments = explode('/', trim($requestUri, '/'));

// El primer segmento debe ser 'api' para que sea una solicitud válida a la API.
if (empty($segments[0]) || $segments[0] !== 'api') {
    header('HTTP/1.1 404 Not Found');
    echo json_encode(['success' => false, 'error' => 'Endpoint de API no encontrado']);
    exit();
}

// El segundo segmento es el nombre del endpoint (ej. 'trabajos', 'like').
$endpoint = isset($segments[1]) ? $segments[1] : null;
// El tercer segmento (si existe) es el ID de un recurso (ej. ID de trabajo).
$trabajoId = isset($segments[2]) ? (int)$segments[2] : null;

// Instancia la conexión a la base de datos.
$database = new Database();

// --- Enrutamiento de Endpoints ---
// Utiliza un switch para dirigir la solicitud al controlador o lógica adecuada
// basándose en el endpoint identificado.
switch ($endpoint) {
    case 'trabajos':
        // Lógica para el endpoint 'trabajos' (creación, lectura, actualización, eliminación de proyectos).

        // Determina el método HTTP real a usar para el controlador.
        // Se prioriza el método de la cabecera X-HTTP-Method-Override si está presente.
        $actualMethod = $requestMethod; // Ya considera X-HTTP-Method-Override.

        // Si el método es PUT (directo o simulado), se pasa el ID del trabajo de la URL.
        if ($actualMethod === 'PUT') {
            $controller = new PortfolioController($database, 'PUT', $trabajoId);
        } else if ($actualMethod === 'POST') {
            // Si es POST, podría ser una creación o una actualización (vía envío de formulario).
            // Si hay un ID en la URL, se trata como una actualización (PUT para el controlador).
            if ($trabajoId) {
                $controller = new PortfolioController($database, 'PUT', $trabajoId); // Tratar como PUT.
            } else {
                // Si no hay ID, es una creación.
                $controller = new PortfolioController($database, 'POST', null);
            }
        } else {
            // Para GET y DELETE, se usa el método determinado y el ID de la URL.
            $controller = new PortfolioController($database, $actualMethod, $trabajoId);
        }

        // Procesa la solicitud utilizando el controlador de Portfolio.
        try {
            $controller->processRequest();
        } catch (Exception $e) {
            // Captura cualquier excepción y devuelve un error 500.
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    case 'like':
        // Lógica para el endpoint 'like' (dar o quitar "me gusta" a un trabajo).
        error_log("DEBUG: Endpoint 'like' alcanzado.");
        error_log("DEBUG: SESSION[\"usuario_id\"] = " . (isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : 'NO SET'));

        if ($requestMethod == 'POST') {
            // Lee el cuerpo de la solicitud (espera JSON).
            $data = json_decode(file_get_contents('php://input'));
            error_log("DEBUG: Datos recibidos para like: " . print_r($data, true));

            // Valida que el usuario esté logueado y que se haya proporcionado el ID del trabajo.
            if (!isset($_SESSION['usuario_id']) || !isset($data->id_trabajo)) {
                header('HTTP/1.1 400 Bad Request');
                echo json_encode(['success' => false, 'error' => 'Datos incompletos o sesión no iniciada.']);
                exit();
            }
            // Instancia TrabajoDB y llama al método darLike.
            $trabajoDB = new TrabajoDB($database);
            $resultado = $trabajoDB->darLike($_SESSION['usuario_id'], $data->id_trabajo);
            // Devuelve la respuesta en formato JSON.
            echo json_encode(['success' => true, 'resultado' => $resultado]);
        } else {
            // Si el método no es POST, devuelve un error 405 (Método no permitido).
            header('HTTP/1.1 405 Method Not Allowed');
        }
        break;

    default:
        // Si el endpoint no coincide con ninguno de los casos definidos, devuelve un error 404.
        header('HTTP/1.1 404 Not Found');
        echo json_encode(['success' => false, 'error' => 'Endpoint no válido']);
        break;
}

// Cierra la conexión a la base de datos al finalizar la solicitud.
$database->close();