<?php
/**
 * controllers/portfolioController.php
 *
 * Este controlador maneja la lógica de negocio relacionada con los trabajos (proyectos)
 * del portfolio. Actúa como intermediario entre las solicitudes de la API y la
 * interacción con la base de datos a través de la clase TrabajoDB.
 */

class PortfolioController {
    private $trabajoDB;      // Instancia de la clase TrabajoDB para operaciones de base de datos.
    private $requestMethod;  // Método HTTP de la solicitud (GET, POST, PUT, DELETE).
    private $trabajoId;      // ID del trabajo, si la solicitud se refiere a un trabajo específico.

    /**
     * Constructor del controlador.
     *
     * @param object $database      Instancia de la conexión a la base de datos.
     * @param string $requestMethod Método HTTP de la solicitud.
     * @param int|null $trabajoId   ID del trabajo (opcional).
     */
    public function __construct($database, $requestMethod, $trabajoId = null) {
        // Inicializa TrabajoDB con la conexión a la base de datos.
        $this->trabajoDB = new TrabajoDB($database);
        $this->requestMethod = $requestMethod;
        $this->trabajoId = $trabajoId;
        // Registra el método de solicitud para depuración.
        error_log("DEBUG: PortfolioController instanciado con requestMethod: " . $this->requestMethod);
    }

    /**
     * Procesa la solicitud entrante.
     * Dirige la solicitud a la función privada correspondiente según el método HTTP.
     */
    public function processRequest() {
        switch ($this->requestMethod) {
            case 'GET':
                // Si hay un ID de trabajo, obtiene un trabajo específico; de lo contrario, obtiene todos.
                if ($this->trabajoId) {
                    $response = $this->getTrabajo($this->trabajoId);
                } else {
                    $response = $this->getAllTrabajos();
                }
                break;
            case 'POST':
                // Crea un nuevo trabajo.
                $response = $this->createTrabajo();
                break;
            case 'PUT':
                // Actualiza un trabajo existente.
                $response = $this->updateTrabajo();
                break;
            case 'DELETE':
                // Elimina un trabajo.
                $response = $this->deleteTrabajo();
                break;
            default:
                // Método no soportado o endpoint no encontrado.
                $response = $this->notFoundResponse();
                break;
        }
        // Establece la cabecera de estado HTTP de la respuesta.
        header($response['status_code_header']);
        // Imprime el cuerpo de la respuesta si existe.
        if ($response['body']) {
            echo $response['body'];
        }
    }

    /**
     * Obtiene todos los trabajos del portfolio.
     *
     * @return array Respuesta HTTP con el estado y los datos de los trabajos.
     */
    private function getAllTrabajos() {
        $result = $this->trabajoDB->getAll(); // Llama al método getAll de TrabajoDB.
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result); // Codifica los resultados a JSON.
        return $response;
    }

    /**
     * Obtiene un trabajo específico por su ID.
     *
     * @param int $id ID del trabajo a obtener.
     * @return array Respuesta HTTP con el estado y los datos del trabajo.
     */
    private function getTrabajo($id) {
        $result = $this->trabajoDB->getById($id); // Llama al método getById de TrabajoDB.
        if (! $result) {
            return $this->notFoundResponse(); // Si no se encuentra, devuelve 404.
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    /**
     * Crea un nuevo trabajo.
     *
     * @return array Respuesta HTTP con el estado y los datos del nuevo trabajo.
     */
    private function createTrabajo() {
        // Decodifica los datos de entrada JSON del cuerpo de la solicitud.
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        // TODO: Aquí se debería añadir una validación robusta de los datos de entrada.
        $result = $this->trabajoDB->create($input); // Llama al método create de TrabajoDB.
        $response['status_code_header'] = 'HTTP/1.1 201 Created'; // 201 Created para creación exitosa.
        $response['body'] = json_encode($result);
        return $response;
    }

    /**
     * Actualiza un trabajo existente.
     *
     * @return array Respuesta HTTP con el estado y el mensaje de la operación.
     */
    private function updateTrabajo() {
        // Intenta leer los datos de entrada como JSON (para solicitudes PUT/PATCH).
        $input = json_decode(file_get_contents('php://input'), TRUE);

        // Si no hay datos en php://input (ej. formulario enviado con POST),
        // intenta leer de la superglobal $_POST.
        if (empty($input) && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $_POST;
        }

        // Extrae los datos del input, usando el operador null coalescing (??) para valores predeterminados.
        $titulo = $input['titulo'] ?? null;
        $descripcion = $input['descripcion'] ?? null;
        // Asegura que programas_usados sea un array, incluso si está vacío.
        $programas_usados = isset($input['programas_usados']) ? (array)$input['programas_usados'] : [];

        $archivo = null; // Variable para almacenar la ruta del archivo.

        // Manejo de la subida de archivos para la actualización.
        // Verifica si se ha subido un nuevo archivo y si no hay errores.
        if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
            $target_dir = "../img/trabajos/"; // Directorio de destino para las imágenes.
            // Crea el directorio si no existe.
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            // Obtiene la extensión del archivo.
            $imageFileType = strtolower(pathinfo($_FILES["archivo"]["name"], PATHINFO_EXTENSION));
            // Genera un nombre de archivo único para evitar colisiones.
            $uniqueFileName = uniqid() . "." . $imageFileType;
            $target_file = $target_dir . $uniqueFileName;

            // Mueve el archivo subido al directorio de destino.
            if (move_uploaded_file($_FILES["archivo"]["tmp_name"], $target_file)) {
                $archivo = $target_file; // Almacena la nueva ruta del archivo.
            } else {
                // Si falla la subida, devuelve un error.
                $response['status_code_header'] = 'HTTP/1.1 500 Internal Server Error';
                $response['body'] = json_encode(['success' => false, 'error' => 'Error al subir el nuevo archivo.']);
                return $response;
            }
        } else {
            // Si no se sube un nuevo archivo, se mantiene el archivo existente.
            // Primero, se obtiene la información del trabajo existente para recuperar la ruta del archivo actual.
            $existingTrabajo = $this->trabajoDB->getById($this->trabajoId);
            if ($existingTrabajo) {
                $archivo = $existingTrabajo['archivo']; // Usa la ruta del archivo existente.
            }
        }

        // Llama al método update de TrabajoDB para actualizar el trabajo en la base de datos.
        $result = $this->trabajoDB->update($this->trabajoId, $titulo, $descripcion, $archivo, $programas_usados);

        // Prepara la respuesta basada en el resultado de la actualización.
        if ($result) {
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode(['success' => true, 'message' => 'Proyecto actualizado con éxito.']);
        } else {
            $response['status_code_header'] = 'HTTP/1.1 500 Internal Server Error';
            $response['body'] = json_encode(['success' => false, 'error' => 'Error al actualizar el proyecto en la base de datos.']);
        }
        return $response;
    }

    /**
     * Elimina un trabajo específico por su ID.
     *
     * @return array Respuesta HTTP con el estado y el mensaje de la operación.
     */
    private function deleteTrabajo() {
        error_log("DEBUG: Intentando eliminar trabajo con ID: " . $this->trabajoId);
        $result = $this->trabajoDB->delete($this->trabajoId);
        if ($result) {
            error_log("DEBUG: Eliminación de trabajo exitosa en DB para ID: " . $this->trabajoId);
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode(['success' => true, 'message' => 'Proyecto eliminado con éxito.']);
        } else {
            error_log("DEBUG: Fallo en la eliminación de trabajo en DB para ID: " . $this->trabajoId);
            $response['status_code_header'] = 'HTTP/1.1 500 Internal Server Error';
            $response['body'] = json_encode(['success' => false, 'message' => 'Error al eliminar el proyecto en la base de datos.']);
        }
        return $response;
    }

    /**
     * Prepara una respuesta HTTP 404 Not Found.
     *
     * @return array Respuesta HTTP para un recurso no encontrado.
     */
    private function notFoundResponse() {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = null; // No hay cuerpo para una respuesta 404 simple.
        return $response;
    }
}
