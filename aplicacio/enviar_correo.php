<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start(); // Asegúrate de que la sesión está iniciada

if ($_SESSION['tipo'] != 'cliente') {
    header('Location: login.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require '../vendor/autoload.php'; // Ajusta la ruta a autoload.php

    // Verificar si las claves 'mensaje' y 'solicitud' existen en $_POST
    $asunto = "";
    $mensaje = isset($_POST['mensaje']) ? $_POST['mensaje'] : '';
    $solicitud = isset($_POST['solicitud']) ? $_POST['solicitud'] : '';

    // Determinar el asunto según la solicitud
    if ($solicitud == 'modificar') {
        $asunto = "Petició de modificació del compte de client";
    } elseif ($solicitud == 'esborrar') {
        $asunto = "Petició d'esborrament del compte de client";
    } elseif ($solicitud == 'justificar_comanda') {
        $asunto = "Petició de justificació de comanda rebutjada";
    }

    // Configuración de PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor SMTP de Gmail
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = '448255.clot@fje.edu'; // Tu correo de Gmail
        $mail->Password = '5d833d99'; // Tu contraseña de Gmail o clave de aplicación
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Configuración del correo
        $mail->setFrom('joan2005garcia@gmail.com', 'Joan'); // Remitente
        $mail->addAddress('gestor1@botiga.com', 'Gestor de la Botiga'); // Destinatario
        $mail->addReplyTo($_SESSION['usuario'] . '@domini.com', 'Cliente'); // Email del cliente

        // Contenido del correo
        $mail->isHTML(false); // Configurar como texto plano
        $mail->Subject = $asunto;
        $mail->Body = $mensaje;

        // Enviar el correo
        $mail->send();
        echo "La teva sol·licitud ha estat enviada correctament.";
    } catch (Exception $e) {
        echo "Hi ha hagut un error en l'enviament del correu. Error: {$mail->ErrorInfo}";
    }
}
?>

<!-- Formularios -->
<form method="POST" action="enviar_correo.php">
    <label for="mensaje">Explica el motiu per què vols la justificació:</label><br>
    <textarea name="mensaje" id="mensaje" rows="4" cols="50" required></textarea><br>
    <button type="submit" name="solicitud" value="justificar_comanda">Petició de justificació de comanda rebutjada</button>
</form>

<form method="POST" action="enviar_correo.php">
    <label for="mensaje">Explica la teva petició:</label><br>
    <textarea name="mensaje" id="mensaje" rows="4" cols="50" required></textarea><br>
    <button type="submit" name="solicitud" value="modificar">Petició de modificació del compte</button>
    <button type="submit" name="solicitud" value="esborrar">Petició d'esborrament del compte</button>
</form>
