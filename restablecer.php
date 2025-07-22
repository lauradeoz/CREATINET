<?php
// Configuración de errores
    ini_set('display_errors', 0); // No mostrar errores en pantalla
    ini_set('log_errors', 1); // Habilitar el registro de errores
    ini_set('error_log', 'errores.log'); // Guardar errores en un archivo llamado errores.log
    error_reporting(E_ALL); // Reportar todos los errores
/**
 * Script para restablecer la contraseña de un usuario.
 *
 * Este archivo maneja la interfaz y la lógica para que un usuario pueda
 * establecer una nueva contraseña utilizando un token de restablecimiento
 * enviado previamente a su correo electrónico.
 */

// Incluye la clase UsuarioDB para interactuar con la base de datos de usuarios.
include_once __DIR__ . '/../data/ususarioDB.php';
// Incluye la configuración de la base de datos.
require_once __DIR__ . '/../config/database.php';

// Instancia la conexión a la base de datos.
$database = new Database();
// Instancia la clase UsuarioDB, pasándole la conexión a la base de datos.
$usuariobd = new UsuarioDB($database);

$mensaje = ""; // Variable para almacenar mensajes al usuario.
$resultado = ['success' => false]; // Variable para el resultado de la operación.

// Verifica si se ha proporcionado un token de restablecimiento en la URL.
if(isset($_GET['token'])){
    $token = $_GET['token'];

    // Si la solicitud es de tipo POST y se ha enviado una nueva contraseña,
    // significa que el usuario está intentando restablecer su contraseña.
    if($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['nueva_password'])){
        // Llama al método restablecerPassword de UsuarioDB para actualizar la contraseña.
        $resultado = $usuariobd->restablecerPassword($token, $_POST['nueva_password']);
        // Almacena el mensaje devuelto por la operación.
        $mensaje = $resultado['mensaje'];
    }
} else {
    // Si no se proporciona un token, redirige al usuario a la página de login.
    header("Location: login.php");
    exit(); // Termina la ejecución del script.
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña</title>
    <!-- Enlace a la hoja de estilos CSS para el login (reutilizada para esta página) -->
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <!-- Contenedor principal de la página -->
    <div class="container">
        <h1>Restablecer Contraseña</h1>
        <?php
        // Muestra el mensaje de la operación (éxito o error).
        if(!empty($mensaje)): ?>
            <p class="mensaje"><?php echo $mensaje; ?></p>
            <?php
            // Si el restablecimiento fue exitoso, muestra un enlace para ir al login.
            if($resultado['success']): ?>
                <a href="login.php" class="volver">Ir a Iniciar Sesión</a>
            <?php endif;
        else:
            // Si no hay mensaje (primera carga de la página con token), muestra el formulario.
        ?>
            <!-- Formulario para que el usuario ingrese la nueva contraseña -->
            <form method="POST" id="formRestablecer">
                <!-- Campo para la nueva contraseña -->
                <input type="password" name="nueva_password" id="nuevaPassword" required placeholder="Nueva Contraseña">
                <!-- Campo para confirmar la nueva contraseña -->
                <input type="password" name="confirmar_password" id="confirmarPassword" required placeholder="Confirmar Nueva Contraseña">
                <!-- Botón para enviar el formulario y restablecer la contraseña -->
                <input type="submit" value="Restablecer Contraseña">
            </form>
            <!-- Párrafo para mostrar mensajes de error de validación del lado del cliente (JavaScript) -->
            <p class="error" id="mensaje_cliente"></p>
            <!-- Incluye el script JavaScript para la validación del formulario del lado del cliente -->
            <script src="js/restablecer.js"></script>
        <?php endif; ?>
    </div>
</body>
</html>