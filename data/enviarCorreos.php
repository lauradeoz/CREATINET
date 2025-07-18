<?php
/**
 * data/enviarCorreos.php
 *
 * Esta clase `Correo` se encarga de enviar correos electrónicos
 * utilizando la librería PHPMailer. Es utilizada para funcionalidades
 * como la verificación de cuenta y la recuperación de contraseña.
 */

// Incluye el archivo de configuración global (donde se definen las constantes MAIL_HOST, MAIL_USER, etc.).
// Se usa __DIR__ para asegurar que la ruta sea siempre correcta, independientemente de dónde se incluya este archivo.
include_once __DIR__ . '/../config/config.php';

// Importa las clases necesarias de PHPMailer.
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Incluye los archivos de la librería PHPMailer.
// Asegúrate de que estas rutas sean correctas en tu entorno.
require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';


class Correo{

    /**
     * Envía un correo electrónico utilizando PHPMailer.
     *
     * @param string $forEmail  Dirección de correo electrónico del destinatario.
     * @param string $forName   Nombre del destinatario.
     * @param string $asunto    Asunto del correo.
     * @param string $body      Contenido del cuerpo del correo (puede ser HTML).
     * @return array Un array asociativo con el estado de éxito y un mensaje.
     */
    public static function enviarCorreo($forEmail, $forName, $asunto, $body){

        // Crea una nueva instancia de PHPMailer.
        // El parámetro 'true' habilita las excepciones para un manejo de errores más robusto.
        $mail = new PHPMailer(true);

        try {
            // --- Configuración de Depuración (solo para desarrollo) ---
            // Habilita la salida de depuración detallada de PHPMailer.
            // $mail->SMTPDebug = 2; // Nivel de depuración: 2 muestra mensajes del cliente y del servidor.
            // Redirige la salida de depuración a los logs de errores de PHP.
            // $mail->Debugoutput = function($str, $level) {
            //     error_log("PHPMailer: $str");
            // };

            // --- Configuración del Servidor SMTP ---
            $mail->isSMTP();                // Le dice a PHPMailer que use SMTP para enviar.
            $mail->Host       = MAIL_HOST;  // Configura el servidor SMTP (definido en config.php).
            $mail->SMTPAuth   = true;       // Habilita la autenticación SMTP.
            $mail->Username   = MAIL_USER;  // Nombre de usuario SMTP (definido en config.php).
            $mail->Password   = MAIL_PASS;  // Contraseña SMTP (definido en config.php).
            $mail->From       = MAIL_USER;  // Dirección de correo del remitente.
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Habilita el cifrado TLS.
            $mail->Port       = 587;        // Puerto TCP para conectar al servidor SMTP.

            // --- Configuración de Codificación ---
            $mail->CharSet = 'UTF-8';   // Establece la codificación de caracteres a UTF-8.
            $mail->Encoding = 'base64'; // Establece la codificación de transferencia de contenido.

            // --- Configuración del Remitente y Destinatario ---
            // Establece la dirección y el nombre del remitente.
            // base64_encode se usa para codificar el nombre del remitente para compatibilidad con UTF-8 en cabeceras.
            $mail->setFrom(MAIL_USER, '=?UTF-8?B?'.base64_encode('Administración').'?=');
            // Añade un destinatario.
            $mail->addAddress($forEmail, '=?UTF-8?B?'.base64_encode($forName).'?=');

            // --- Contenido del Correo ---
            $mail->isHTML(true); // Habilita el formato HTML en el cuerpo del correo.
            // Codifica el asunto para compatibilidad con UTF-8.
            $mail->Subject = '=?UTF-8?B?'.base64_encode($asunto).'?=';
            // Cuerpo del correo en formato HTML.
            // nl2br convierte saltos de línea a <br /> para HTML.
            // htmlspecialchars previene ataques XSS al escapar caracteres especiales.
            $mail->Body    = '
            <html>
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
            </head>
            <body>
                '.nl2br(htmlspecialchars($body)).'
            </body>
            </html>';

            // Versión en texto plano del cuerpo del correo (para clientes que no soportan HTML).
            $mail->AltBody = $body;

            // --- Envío del Correo ---
            if(!$mail->send()){
                // Si el envío falla, registra el error y devuelve un mensaje de fallo.
                error_log("Error al enviar correo: " . $mail->ErrorInfo);
                return ["success" => false, "message" => 'El correo no pudo ser enviado: ' . $mail->ErrorInfo];
            } else {
                // Si el envío es exitoso, registra el éxito y devuelve un mensaje de éxito.
                error_log("Correo enviado exitosamente a: $forEmail");
                return ["success" =>true, "message" => "Registro exitoso. Por favor, verifica tu correo."];
            }
        } catch(Exception $e) {
            // Captura cualquier excepción lanzada por PHPMailer y devuelve un mensaje de error.
            // error_log("PHPMailer Exception: " . $e->getMessage()); // Descomentar para depuración.
            return ["success" => false, "message" => 'Error al enviar el formulario'];
        }
    }
}
