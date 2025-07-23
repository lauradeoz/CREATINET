<?php
// Configuración de errores
    ini_set('display_errors', 0); // No mostrar errores en pantalla
    ini_set('log_errors', 1); // Habilitar el registro de errores
    ini_set('error_log', 'errores.log'); // Guardar errores en un archivo llamado errores.log
    error_reporting(E_ALL); // Reportar todos los errores
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

// Obtener la URI de la solicitud
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = '/laura/'; // Asegúrate de que esto coincida con tu RewriteBase en .htaccess

// Eliminar el prefijo del directorio base si existe
if (strpos($requestUri, $basePath) === 0) {
    $requestUri = substr($requestUri, strlen($basePath));
}

// Si la solicitud es para la API, incluir api/index.php y salir
if (strpos($requestUri, 'api/') === 0) {
    require __DIR__ . '/api/index.php';
    exit();
} else {
    // Si no es una solicitud de API, iniciar el buffering de salida para la página principal
    ob_start();
}

// Si no es una solicitud de API, continuar con la lógica normal de index.php
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/data/trabajoDB.php';

$database = new Database();
$trabajoDB = new TrabajoDB($database);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['archivo'])) {
    $titulo = filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_STRING);
    $descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_STRING);
    $id_usuario = $_SESSION['usuario_id'];
    $uploadOk = 1;
    $errorMessage = "";

    // Validar título y descripción
    if (empty($titulo) || empty($descripcion)) {
        $errorMessage = "El título y la descripción no pueden estar vacíos.";
        $uploadOk = 0;
    }

    // Manejo de la subida de archivos
    $target_dir = "img/trabajos/"; // Ruta relativa a index.php
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $imageFileType = strtolower(pathinfo($_FILES["archivo"]["name"], PATHINFO_EXTENSION));
    $uniqueFileName = uniqid() . "." . $imageFileType;
    $target_file = $target_dir . $uniqueFileName;

    // Validar tipo de archivo
    $check = getimagesize($_FILES["archivo"]["tmp_name"]);
    if($check !== false) {
        // Es una imagen
    } else {
        $errorMessage = "El archivo no es una imagen.";
        $uploadOk = 0;
    }

    // Permitir ciertos formatos de archivo
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
    && $imageFileType != "gif" ) {
        $errorMessage = "Solo se permiten archivos JPG, JPEG, PNG y GIF.";
        $uploadOk = 0;
    }

    // Limitar tamaño de archivo (ej. 5MB)
    if ($_FILES["archivo"]["size"] > 5000000) {
        $errorMessage = "El archivo es demasiado grande. Máximo 5MB.";
        $uploadOk = 0;
    }

    // Si todo está bien, intentar subir el archivo
    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["archivo"]["tmp_name"], $target_file)) {
            $programas_usados = isset($_POST['programas_usados']) ? $_POST['programas_usados'] : [];
            $trabajoDB->create($id_usuario, $titulo, $descripcion, $uniqueFileName, $programas_usados);
            header('Location: index.php');
            exit();
        } else {
            $errorMessage = "Hubo un error al subir tu archivo.";
        }
    }
}

$proyectosUsuario = $trabajoDB->getProyectosUsuario($_SESSION['usuario_id']);
$proyectosOtros = $trabajoDB->getProyectosOtros($_SESSION['usuario_id']);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Portfolio de Creadores</title>
    <link rel="icon" href="img/LOGO_CREATINET.png" type="image/png">
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <div id="user-info-top-right">
        <p>
            <strong>Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></strong> | 
            <a href="logout.php" class="btn-logout"><strong>Cerrar sesión</strong></a>
        </p>
    </div>
    <div class="container">
        <img src="img/LOGO_CREATINET.png" alt="Logo Creatinet" style="display: block; margin: 0 auto 20px auto; max-width: 150px;">
        <h1>Portfolio de Creadores</h1>
        <?php if (!empty($errorMessage)): ?>
            <div style="color: red; margin-bottom: 10px;"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>

        <button id="toggleUploadForm">Subir Nuevo Proyecto</button>

        <div id="uploadFormContainer" style="display: none;">
            <h2>Sube tu trabajo</h2>
            <form action="index.php" method="post" enctype="multipart/form-data">
                <input type="text" name="titulo" placeholder="Título" required>
                <textarea name="descripcion" placeholder="Descripción"></textarea>
                <div class="form-group">
                    <label for="programas">Programas Usados:</label>
                    <div class="logo-selector">
                        <?php
                        $logos = glob('img/LOGOS/*.png');
                        foreach ($logos as $logo) {
                            $program_name = basename($logo, '.png');
                            echo '<label class="logo-option">';
                            echo '<input type="checkbox" name="programas_usados[]" value="' . htmlspecialchars($logo) . '">';
                            echo '<img src="' . htmlspecialchars($logo) . '" alt="' . htmlspecialchars($program_name) . '">';
                            echo '<span>' . htmlspecialchars($program_name) . '</span>';
                            echo '</label>';
                        }
                        ?>
                    </div>
                </div>
                <input type="file" name="archivo">
                <div id="currentImageContainer" style="display: none; margin-top: 15px;">
                    <p>Imagen actual:</p>
                    <img id="currentProjectImage" src="" alt="Imagen actual del proyecto" style="max-width: 200px; height: auto; border-radius: 8px; margin-top: 5px;">
                </div>
                <input type="hidden" name="trabajos">
                <button type="submit">Subir</button>
            </form>
        </div>

        <h2>Mis Proyectos</h2>
        <div class="trabajos-grid">
            <?php if (is_array($proyectosUsuario)): ?>
            <?php foreach ($proyectosUsuario as $proyecto): ?>
                <div class="trabajo-card">
                    <h3><?php echo htmlspecialchars($proyecto['titulo']); ?></h3>
                    <p>Por: <?php echo htmlspecialchars($proyecto['nombre_usuario']); ?></p>
                    <img src="img/trabajos/<?php echo htmlspecialchars($proyecto['imagen']); ?>" alt="<?php echo htmlspecialchars($proyecto['titulo']); ?>">
                    <p><?php echo htmlspecialchars($proyecto['descripcion']); ?></p>
                    <div class="programas-usados">
                        <strong>Programas Usados:</strong>
                        <?php
                        if (!empty($proyecto['programas_usados'])) {
                            $programas = explode(', ', $proyecto['programas_usados']);
                            foreach ($programas as $programa) {
                                echo '<img src="' . htmlspecialchars($programa) . '" alt="' . htmlspecialchars(basename($programa, '.png')) . '" class="programa-logo">';
                            }
                        }
                        ?>
                    </div>
                    <div class="project-actions">
                        <button class="edit-btn" data-id="<?php echo $proyecto['id']; ?>" data-titulo="<?php echo htmlspecialchars($proyecto['titulo']); ?>" data-descripcion="<?php echo htmlspecialchars($proyecto['descripcion']); ?>" data-archivo="<?php echo htmlspecialchars($proyecto['imagen']); ?>" data-programas="<?php echo htmlspecialchars($proyecto['programas_usados']); ?>">Editar</button>
                        <button class="delete-btn" data-id="<?php echo $proyecto['id']; ?>">Eliminar</button>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <h2>Trabajos de la comunidad</h2>
        <div class="trabajos-grid">
            <?php if (is_array($proyectosOtros)): ?>
            <?php foreach ($proyectosOtros as $proyecto): ?>
                <div class="trabajo-card">
                    <h3><?php echo htmlspecialchars($proyecto['titulo']); ?></h3>
                    <p>Por: <?php echo htmlspecialchars($proyecto['nombre_usuario']); ?></p>
                    <img src="img/trabajos/<?php echo htmlspecialchars($proyecto['imagen']); ?>" alt="<?php echo htmlspecialchars($proyecto['titulo']); ?>">
                    <p><?php echo htmlspecialchars($proyecto['descripcion']); ?></p>
                    <div class="programas-usados">
                        <strong>Programas Usados:</strong>
                        <?php
                        if (!empty($proyecto['programas_usados'])) {
                            $programas = explode(', ', $proyecto['programas_usados']);
                            foreach ($programas as $programa) {
                                echo '<img src="' . htmlspecialchars($programa) . '" alt="' . htmlspecialchars(basename($programa, '.png')) . '" class="programa-logo">';
                            }
                        }
                        ?>
                    </div>
                    <div class="likes">
                        <button class="like-btn" data-id="<?php echo $proyecto['id']; ?>">Like</button>
                        <span class="likes-count"><?php echo $proyecto['favorito']; ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <div id="projectModal" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <img id="modalImage" src="" alt="Project Image">
            <h2 id="modalTitle"></h2>
            <p id="modalDescription"></p>
        </div>
    </div>
    <script src="js/portfolio.js"></script>
    <footer class="main-footer">
        <div class="footer-content">
            <div class="footer-section about">
                <h3>CREATINET</h3>
                <p>Conectando mentes creativas. Tu portfolio, tu comunidad.</p>
            </div>
            <div class="footer-section links">
                <h3>Enlaces Rápidos</h3>
                <ul>
                    <li><a href="index.php">Inicio</a></li>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="privacidad.html">Privacidad</a></li>
                    <li><a href="terminos.html">Términos</a></li>
                </ul>
            </div>
            <div class="footer-section social">
                <h3>Síguenos</h3>
                <p>
                    <a href="#">Facebook</a> |
                    <a href="#">Twitter</a> |
                    <a href="#">Instagram</a>
                </p>
            </div>
        </div>
        <div class="footer-bottom">
            &copy; <?php echo date("Y"); ?> CREATINET. Todos los derechos reservados.
        </div>
    </footer>
</body>
</html>