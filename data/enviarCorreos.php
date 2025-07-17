<?php
// Corrección: Usar __DIR__ para asegurar que las rutas sean siempre correctas.
include_once __DIR__ . '/../config/config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';


class Correo{

public static function enviarCorreo($forEmail, $forName, $asunto, $body){

$mail = new PHPMailer(true);


try {
    // Habilitar depuración detallada
    $mail->SMTPDebug = 2; // Habilita la salida de depuración detallada
    $mail->Debugoutput = function($str, $level) {
        error_log("PHPMailer: $str");
    };

    //Server settings
    $mail->isSMTP();                // Send using SMTP
    $mail->Host       = MAIL_HOST;  // Set the SMTP server to send through
    $mail->SMTPAuth   = true;       // Enable SMTP authentication
    $mail->Username   = MAIL_USER;  // SMTP username
    $mail->Password   = MAIL_PASS;  // SMTP password
    $mail->From       = MAIL_USER; 
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;  
    $mail->Port       = 587;  
    
    // Configuración de codificación
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';

    // Configuración del remitente y destinatario
    $mail->setFrom(MAIL_USER, '=?UTF-8?B?'.base64_encode('Administración').'?=');
     $mail->addAddress($forEmail, '=?UTF-8?B?'.base64_encode($forName).'?=');



    
    $mail->isHTML(true); // Permite el formato HTML
    $mail->Subject = '=?UTF-8?B?'.base64_encode($asunto).'?=';
    $mail->Body    = '
    <html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    </head>
    <body>
        '.nl2br(htmlspecialchars($body)).'
    </body>
    </html>';

    $mail->AltBody = $body; // Versión en texto plano

    // Send the email
    if(!$mail->send()){
        error_log("Error al enviar correo: " . $mail->ErrorInfo);
        return ["success" => false, "message" => 'El correo no pudo ser enviado: ' . $mail->ErrorInfo];
    } else {
        error_log("Correo enviado exitosamente a: $forEmail");
        return ["success" =>true, "message" => "Registro exitoso. Por favor, verifica tu correo."];
    }
    }catch(Exception $e){
        return ["success" => false, "message" => 'Error al enviar el formulario'];
    }
    
}

}