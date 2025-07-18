<?php
/**
 * Manejo de Sesiones en PHP
 *
 * La variable superglobal $_SESSION es un array asociativo que permite
 * almacenar datos de sesión para un usuario específico a través de múltiples
 * solicitudes de página.
 *
 * Para poder utilizar $_SESSION, es necesario iniciar la sesión con la función
 * session_start() al principio de cada script donde se vayan a usar variables de sesión.
 */

// Inicia la sesión si no ha sido iniciada ya.
// Esto previene errores si session_start() se llama varias veces.
if(session_status() == PHP_SESSION_NONE){
    session_start();
}

// Comprueba si el usuario ya está logueado.
// Si la variable de sesión 'logueado' existe y es verdadera,
// significa que el usuario ya ha iniciado sesión.
if(isset($_SESSION['logueado']) && $_SESSION['logueado'] == true){
    // Registra un mensaje de depuración en el log de errores del servidor.
    error_log("DEBUG: Usuario logueado. Redirigiendo a index.php desde login.php");
    // Redirige al usuario a la página principal (index.php).
    header("location: index.php");
    // Termina la ejecución del script para asegurar la redirección.
    exit();
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Icono de la página (favicon) -->
    <link rel="icon" href="img/LOGO_CREATINET.png" type="image/png">
    <!-- Enlace a la hoja de estilos CSS específica para el login -->
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <!-- Contenedor principal de la página de login -->
    <div class="container">
        <!-- Logo de la aplicación -->
        <img src="img/LOGO_CREATINET.png" alt="Logo Creatinet" style="display: block; margin: 0 auto 20px auto; max-width: 150px;">
        <!-- Título de la sección de login -->
        <h2>Login</h2>
        <!-- Formulario de inicio de sesión -->
        <!-- El action apunta al controlador PHP que procesará el login -->
        <form method="post" action="controllers/usuarioController.php">
            <!-- Campo para el correo electrónico del usuario -->
            <input type="email" name="email" placeholder="Correo electrónico" required>
            <!-- Campo para la contraseña del usuario -->
            <input type="password" name="password" placeholder="Contraseña" required>
            <!-- Botón para enviar el formulario de login -->
            <input type="submit" name="login" value="Iniciar Sesión">
        </form>
        <!-- Enlace para recuperar la contraseña, que abre un modal -->
        <div class="olvido-password">
            <a class="abrir-modal-recuperar">Recuperar contraseña</a>
        </div>
        
        <!-- Enlace para crear una nueva cuenta, que abre otro modal -->
        <div class="crear-cuenta">
            <a class="abrir-modal-registro">Crear Nueva cuenta</a>
        </div>

        <?php
        // Muestra mensajes de error o éxito almacenados en la sesión.
        // Por ejemplo, si el login falló, el controlador podría haber guardado un mensaje aquí.
        if(isset($_SESSION['mensaje'])){
            // Si hay un mensaje, lo muestra dentro de un div con la clase 'error'.
            echo "<div class='error'>" . $_SESSION['mensaje'] . "</div>";
            // Una vez mostrado, el mensaje se elimina de la sesión para que no aparezca de nuevo.
            unset($_SESSION['mensaje']);
        }
        ?>

        <!-- Modal para la recuperación de contraseña -->
        <div id="modalRecuperar" class="modal">
            <div class="modal-contenido">
                <!-- Botón para cerrar el modal -->
                <span class="cerrarRecuperar">&times;</span>
                <h2>Recuperar contraseña</h2>
                <!-- Formulario de recuperación de contraseña -->
                <form method="POST" action="controllers/usuarioController.php">
                    <!-- Campo para el correo electrónico para recuperar la contraseña -->
                    <input type="email" name="email" placeholder="Correo electrónico" required>
                    <!-- Botón para enviar la solicitud de recuperación -->
                    <input type="submit" name="recuperar" value="Recuperar Contraseña">
                </form>
            </div>
        </div>

        <!-- Modal para el registro de nueva cuenta -->
        <div id="modalRegistro" class="modal">
            <div class="modal-contenido">
                <!-- Botón para cerrar el modal -->
                <span class="cerrarRegistro">&times;</span>
                <h2>Registro Cuenta Nueva</h2>
                <!-- Formulario de registro de nueva cuenta -->
                <form method="POST" action="controllers/usuarioController.php">
                    <!-- Campo para el nombre del nuevo usuario -->
                    <input type="text" name="nombre" placeholder="Nick" required>
                    <!-- Campo para el correo electrónico del nuevo usuario -->
                    <input type="email" name="email" placeholder="Correo electrónico" required>
                    <!-- Campo para la contraseña del nuevo usuario -->
                    <input type="password" name="password" placeholder="Contraseña" required>
                    <!-- Botón para enviar el formulario de registro -->
                    <input type="submit" name="registro" value="Registrarse">
                </form>
            </div>
        </div>
    </div>
    <!-- Enlace al archivo JavaScript que maneja la lógica de los modales y otros comportamientos del login -->
    <script src="js/login.js"></script>
</body>
</html>