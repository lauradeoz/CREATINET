<?php
/**
 * Script para verificar la cuenta de un usuario.
 *
 * Este archivo procesa el token de verificación enviado por correo electrónico
 * para activar la cuenta de un nuevo usuario.
 */

// Incluye la clase UsuarioDB para interactuar con la base de datos de usuarios.
include_once __DIR__ . '/data/ususarioDB.php';
// Incluye la configuración de la base de datos.
require_once __DIR__ . '/config/database.php';

// Instancia la conexión a la base de datos.
$database = new Database();
// Instancia la clase UsuarioDB, pasándole la conexión a la base de datos.
$usuarioDB = new UsuarioDB($database);

$mensaje = ""; // Variable para almacenar el mensaje de resultado de la verificación.

// Comprueba si se ha recibido un token de verificación en la URL.
if(isset($_GET['token'])){
    $token = $_GET['token'];
    // Registra el token recibido para depuración.
    error_log("DEBUG: Token recibido en verificar.php: " . $token);
    // Llama al método verificarToken de UsuarioDB para validar y procesar el token.
    $resultado = $usuarioDB->verificarToken($token);
    // Almacena el mensaje devuelto por la operación (éxito o error).
    $mensaje = $resultado['mensaje'];
    // Registra el resultado completo de la verificación para depuración.
    error_log("DEBUG: Resultado de verificacion de token: " . print_r($resultado, true));
} else {
    // Si no se recibe un token, registra un mensaje y redirige al usuario a la página de login.
    error_log("DEBUG: No se recibio token en verificar.php. Redirigiendo a login.php");
    header("Location: login.php");
    exit(); // Termina la ejecución del script.
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de cuenta</title>
    <!-- Enlace a la hoja de estilos CSS para el login (reutilizada para esta página) -->
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <!-- Contenedor principal de la página de verificación -->
    <div class="container">
        <h1>Verificación de cuenta</h1>
        <!-- Muestra el mensaje de resultado de la verificación -->
        <p class="mensaje"><?php echo $mensaje; ?></p>
        <!-- Enlace para que el usuario pueda ir a la página de inicio de sesión -->
        <a href="login.php" class="volver">Ir a Iniciar Sesión</a>
    </div>
</body>
</html>
