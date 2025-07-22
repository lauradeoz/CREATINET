<?php
// Configuración de errores
    ini_set('display_errors', 0); // No mostrar errores en pantalla
    ini_set('log_errors', 1); // Habilitar el registro de errores
    ini_set('error_log', 'errores.log'); // Guardar errores en un archivo llamado errores.log
    error_reporting(E_ALL); // Reportar todos los errores
/**
 * Script para cerrar la sesión del usuario.
 *
 * Este archivo se encarga de finalizar la sesión activa de un usuario,
 * eliminando todos los datos de sesión y redirigiendo al usuario a la
 * página de inicio de sesión.
 */

// Inicia la sesión. Es crucial llamar a session_start() antes de
// manipular cualquier variable de sesión, incluso para destruirla.
session_start();

// Borra todas las variables de sesión.
// Esto elimina todos los datos almacenados en el array superglobal $_SESSION.
session_unset();

// Destruye la sesión actual.
// Esto elimina el archivo de sesión del servidor y el ID de sesión de la cookie del usuario.
session_destroy();

// Redirige al usuario a la página de inicio de sesión (login.php).
// La función header() debe ser llamada antes de que se envíe cualquier salida al navegador.
header('Location: login.php');
// Termina la ejecución del script para asegurar que la redirección se realice inmediatamente.
exit();