<?php
// Incluir el archivo autoload.php de Composer para cargar PHPMailer
require '../vendor/autoload.php';

// Función para enviar correos
function enviarCorreoCliente($correoCliente, $asunto, $mensaje) {
    // Crear una nueva instancia de PHPMailer
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        // Configuración del servidor SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';  // Servidor SMTP
        $mail->SMTPAuth = true;
        $mail->Username = 'joan2005garcia@gmail.com';  // Tu correo de Gmail
        $mail->Password = 'qgmc iygr itau zhqy';  // Tu contraseña de aplicación de Gmail
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Remitente y destinatario
        $mail->setFrom('joan2005garcia@gmail.com', 'Gestor');
        $mail->addAddress($correoCliente);  // Correo del cliente
        $mail->isHTML(false);  // No enviar como HTML
        $mail->Subject = $asunto;  // Asunto del correo
        $mail->Body = $mensaje;  // Cuerpo del correo

        // Enviar el correo
        $mail->send();
    } catch (Exception $e) {
        echo "Error al enviar correo: " . $mail->ErrorInfo;
    }
}
?>
