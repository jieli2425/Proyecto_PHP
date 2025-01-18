<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

// Función para enviar correo y registrar
function enviarCorreo($correoGestor, $correoAdmin, $asunto, $mensaje) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'joan2005garcia@gmail.com'; // Cambia esto con tu correo
        $mail->Password = 'qgmc iygr itau zhqy'; // Cambia esto con tu contraseña o app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom($correoGestor, 'Gestor');
        $mail->addAddress($correoAdmin, 'Administrador');
        $mail->addReplyTo($correoGestor, 'Gestor');

        $mail->isHTML(false);
        $mail->Subject = $asunto;
        $mail->Body = $mensaje;

        $mail->send();

        // Guardar registro
        $registro = date('Y-m-d H:i:s') . " | Correo enviado desde: $correoGestor | Destinatario: $correoAdmin | Asunto: $asunto | Mensaje: $mensaje\n";
        $archivoRegistro = '../registro_correos.txt';
        file_put_contents($archivoRegistro, $registro, FILE_APPEND);

        return "Correo enviado correctamente y registrado.";
    } catch (Exception $e) {
        return "Error al enviar el correo. {$mail->ErrorInfo}";
    }
}
?>
