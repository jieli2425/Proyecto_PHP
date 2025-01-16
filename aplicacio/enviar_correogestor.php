<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

if ($_SESSION['tipo'] != 'gestor') {
    header('Location: login.php');
    exit;
}

// Funciones para obtener correos
function obtenerCorreoGestor($fitxer, $usuarioGestor) {
    $usuaris = file($fitxer, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($usuaris as $usuari) {
        $camps = explode(';', $usuari);

        if (isset($camps[0]) && isset($camps[3]) && $camps[0] == $usuarioGestor && $camps[3] == 'gestor') {
            return $camps[6] ?? null; 
        }
    }
    return null; 
}

function obtenerCorreoAdmin($fitxer) {
    $usuaris = file($fitxer, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($usuaris as $usuari) {
        $camps = explode(';', $usuari);

        if (isset($camps[3]) && $camps[3] == 'admin') {
            return $camps[1] ?? null; 
        }
    }
    return null; 
}

// Función para enviar correo y registrar
function enviarCorreo($correoGestor, $correoAdmin, $asunto, $mensaje) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'joan2005garcia@gmail.com';
        $mail->Password = 'qgmc iygr itau zhqy';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom($correoGestor, 'Gestor');
        $mail->addAddress($correoAdmin, 'Administrador');
        $mail->addReplyTo($correoGestor, 'Gestor');

        $mail->isHTML(false);
        $mail->Subject = $asunto;
        $mail->Body = $mensaje;

        $mail->send();

        // Guardar el registro
        $registro = date('Y-m-d H:i:s') . " | Correo enviado desde: $correoGestor | Destinatario: $correoAdmin | Asunto: $asunto | Mensaje: $mensaje\n";
        $archivoRegistro = '../registro_correos.txt';
        $file = fopen($archivoRegistro, 'a');
        fwrite($file, $registro);
        fclose($file); 

        return "Correo enviado correctamente y registrado.";
    } catch (Exception $e) {
        return "Error al enviar el correo. {$mail->ErrorInfo}";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['enviar_correo'])) {
    $archivoUsuarios = "../usuaris/usuaris.txt";
    $correoGestor = obtenerCorreoGestor($archivoUsuarios, $_SESSION['usuario']);
    $correoAdmin = obtenerCorreoAdmin($archivoUsuarios);
    $mensaje = $_POST['mensaje'];

    if (!$correoGestor || !$correoAdmin) {
        echo "<p style='color: red;'>No se pudieron obtener los correos.</p>";
        exit;
    }

    $asunto = "Petició d'addició/modificació/esborrament de client";
    $resultado = enviarCorreo($correoGestor, $correoAdmin, $asunto, $mensaje);
    echo "<p style='color: green;'>$resultado</p>";
}
?>
