<?php



class PortfolioController {
    private $trabajoDB;
    private $requestMethod;
    private $trabajoId;

    public function __construct($database, $requestMethod, $trabajoId = null) {
        $this->trabajoDB = new TrabajoDB($database);
        $this->requestMethod = $requestMethod;
        $this->trabajoId = $trabajoId;
        error_log("DEBUG: PortfolioController instanciado con requestMethod: " . $this->requestMethod);
    }

    public function processRequest() {
        switch ($this->requestMethod) {
            case 'GET':
                if ($this->trabajoId) {
                    $response = $this->getTrabajo($this->trabajoId);
                } else {
                    $response = $this->getAllTrabajos();
                }
                break;
            case 'POST':
                $response = $this->createTrabajo();
                break;
            case 'PUT':
                $response = $this->updateTrabajo();
                break;
            case 'DELETE':
                $response = $this->deleteTrabajo();
                break;
            default:
                $response = $this->notFoundResponse();
                break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    private function getAllTrabajos() {
        $result = $this->trabajoDB->getAll();
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function getTrabajo($id) {
        $result = $this->trabajoDB->getById($id);
        if (! $result) {
            return $this->notFoundResponse();
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function createTrabajo() {
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        // Aquí deberías validar los datos de entrada
        $result = $this->trabajoDB->create($input);
        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function updateTrabajo() {
        // Leer datos de php://input para PUT requests (JSON)
        $input = json_decode(file_get_contents('php://input'), TRUE);

        // Si no hay datos en php://input, intentar leer de $_POST (para form-data)
        if (empty($input) && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $_POST;
        }

        $titulo = $input['titulo'] ?? null;
        $descripcion = $input['descripcion'] ?? null;
        $programas_usados = isset($input['programas_usados']) ? (array)$input['programas_usados'] : [];

        // Manejo de la subida de archivos para la actualización
        $archivo = null;
        if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
            $target_dir = "../img/trabajos/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $imageFileType = strtolower(pathinfo($_FILES["archivo"]["name"], PATHINFO_EXTENSION));
            $uniqueFileName = uniqid() . "." . $imageFileType;
            $target_file = $target_dir . $uniqueFileName;

            if (move_uploaded_file($_FILES["archivo"]["tmp_name"], $target_file)) {
                $archivo = $target_file;
            } else {
                // Manejar error de subida de archivo
                $response['status_code_header'] = 'HTTP/1.1 500 Internal Server Error';
                $response['body'] = json_encode(['success' => false, 'error' => 'Error al subir el nuevo archivo.']);
                return $response;
            }
        } else {
            // Si no se sube un nuevo archivo, mantener el existente
            // Necesitamos obtener el archivo actual del proyecto
            $existingTrabajo = $this->trabajoDB->getById($this->trabajoId);
            if ($existingTrabajo) {
                $archivo = $existingTrabajo['archivo'];
            }
        }

        $result = $this->trabajoDB->update($this->trabajoId, $titulo, $descripcion, $archivo, $programas_usados);
        if ($result) {
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode(['success' => true, 'message' => 'Proyecto actualizado con éxito.']);
        } else {
            $response['status_code_header'] = 'HTTP/1.1 500 Internal Server Error';
            $response['body'] = json_encode(['success' => false, 'error' => 'Error al actualizar el proyecto en la base de datos.']);
        }
        return $response;
    }

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

    private function notFoundResponse() {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = null;
        return $response;
    }
}