<?php

 // Configuración de errores
    ini_set('display_errors', 0); // No mostrar errores en pantalla
    ini_set('log_errors', 1); // Habilitar el registro de errores
    ini_set('error_log', 'errores.log'); // Guardar errores en un archivo llamado errores.log
    error_reporting(E_ALL); // Reportar todos los errores
    ini_set('log_errors', 1); // Habilitar el registro de errores
    ini_set('error_log', 'errores.log'); // Guardar errores en un archivo llamado errores.log
    error_reporting(E_ALL); // Reportar todos los errores
/**
 * controllers/usuarioController.php
 *
 * Este controlador maneja todas las operaciones relacionadas con los usuarios,
 * incluyendo el inicio de sesión, el registro y la recuperación de contraseña.
 * Actúa como un punto central para procesar las solicitudes de formularios
 * y comunicarse con la capa de datos (UsuarioDB).
 */

// Inicia la sesión si no ha sido iniciada ya.
// Esto es crucial para poder usar las variables de sesión como $_SESSION.
if(session_status() == PHP_SESSION_NONE){
    session_start();
}

// Incluye las clases necesarias para la conexión a la base de datos y la lógica de usuario.
require_once __DIR__ . '/../config/database.php'; // Clase para la conexión a la base de datos.
require_once __DIR__ . '/../data/ususarioDB.php'; // Clase para las operaciones CRUD de usuarios.

// Crea instancias de las clases Database y UsuarioDB.
$database = new Database(); // Establece la conexión a la base de datos.
$usuariodb = new UsuarioDB($database); // Objeto para interactuar con la tabla de usuarios.

// --- Lógica para el Inicio de Sesión (Login) ---
// Comprueba si la solicitud es de tipo POST y si se han enviado los campos
// 'login', 'email' y 'password' desde el formulario de inicio de sesión.
if($_SERVER['REQUEST_METHOD'] == 'POST'
&& isset($_POST['login'])
&& isset($_POST['email'])
&& isset($_POST['password'])
) {
    // El usuario ha intentado iniciar sesión.
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Verifica las credenciales del usuario llamando al método de UsuarioDB.
    $resultado = $usuariodb->verificarCredenciales($email, $password);
    
    // Almacena el estado de logueo en la sesión.
    $_SESSION['logueado'] = $resultado['success'];

    if($resultado['success'] == true){
        // Si el login es exitoso, guarda el ID y el nombre del usuario en la sesión.
        $_SESSION['usuario_id'] = $resultado['usuario']['id'];
        $_SESSION['usuario_nombre'] = $resultado['usuario']['nombre'];
        $_SESSION['usuario_nick'] = $resultado['usuario']['nick']; // <-- Añadido
        $ruta = '../index.php'; // Redirige a la página principal.
    } else {
        $ruta = '../login.php'; // Si falla, redirige de nuevo a la página de login.
    }
    // Redirige al usuario con un mensaje (éxito o error).
    redirigirConMensaje($ruta, $resultado['success'], $resultado['mensaje']);
}

// --- Lógica para el Registro de Usuario ---
// Comprueba si la solicitud es de tipo POST y si se han enviado los campos
// 'registro', 'nombre', 'email' y 'password' desde el formulario de registro.
if($_SERVER['REQUEST_METHOD'] == 'POST'
&& isset($_POST['registro'])
&& isset($_POST['nombre'])
&& isset($_POST['email'])
&& isset($_POST['password'])
) {
    // El usuario ha intentado registrar una nueva cuenta.
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Registra el nuevo usuario llamando al método de UsuarioDB.
    $resultado = $usuariodb->registrarUsuario($nombre, $email, $password);
    
    // Redirige al usuario a la página de login con un mensaje.
    redirigirConMensaje('../login.php', $resultado['success'], $resultado['mensaje']);
}

// --- Lógica para la Recuperación de Contraseña ---
// Comprueba si la solicitud es de tipo POST y si se han enviado los campos
// 'recuperar', 'email' desde el formulario de recuperación de contraseña.
if($_SERVER['REQUEST_METHOD'] == "POST" 
    && isset($_POST['recuperar'])
    && isset($_POST['email'])
    ) {

    $email = $_POST['email'];

    // Inicia el proceso de recuperación de contraseña llamando al método de UsuarioDB.
    $resultado = $usuariodb->recuperarPassword($email);
    // Redirige al usuario a la página de login con un mensaje.
    redirigirConMensaje('../login.php', $resultado['success'], $resultado['mensaje']);

}

// --- Lógica para la Actualización de Perfil ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editar_perfil'])) {
    // 1. Verificar que el usuario está logueado
    if (!isset($_SESSION['usuario_id'])) {
        redirigirConMensaje('../login.php', false, 'Debes iniciar sesión para editar tu perfil.');
    }

    $id_usuario = $_SESSION['usuario_id'];

    // 2. Recoger los datos del formulario
    $datos_perfil = [
        'nombre_completo' => $_POST['nombre_completo'] ?? '',
        'nick'            => $_POST['nick'] ?? '',
        'biografia'       => $_POST['biografia'] ?? '',
        'especialidades'  => $_POST['especialidades'] ?? '',
        'sitio_web'       => $_POST['sitio_web'] ?? ''
    ];

    // 3. Gestionar la subida de la foto de perfil
    $foto_perfil_file = null;
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $foto_perfil_file = $_FILES['foto_perfil'];
    }

    // 4. Llamar al método de la base de datos para actualizar
    $resultado = $usuariodb->updateProfile($id_usuario, $datos_perfil, $foto_perfil_file);

    // 5. Redirigir con el resultado
    // Si el nick se actualizó correctamente, redirigimos a la nueva URL del perfil
    if ($resultado['success']) {
        $nick = $datos_perfil['nick'];
        redirigirConMensaje("../perfil.php?nick=$nick", true, $resultado['mensaje']);
    } else {
        // Si hay un error, redirigimos de vuelta al formulario de edición
        redirigirConMensaje('../editar_perfil.php', false, $resultado['mensaje']);
    }
}

/**
 * Función auxiliar para redirigir al usuario con un mensaje en la sesión.
 *
 * @param string $url     La URL a la que se redirigirá al usuario.
 * @param bool   $success Indica si la operación fue exitosa (true) o no (false).
 * @param string $mensaje El mensaje a mostrar al usuario.
 */
function redirigirConMensaje($url, $success, $mensaje = ''){
    // Almacena el estado de éxito y el mensaje en variables de sesión.
    $_SESSION['success'] = $success;
    $_SESSION['mensaje'] = $mensaje;

    // Realiza la redirección HTTP.
    header("Location: $url");
    // Termina la ejecución del script para asegurar la redirección.
    exit();
}