<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

// Verificar que el usuario es cliente
if ($_SESSION['tipo'] != 'cliente') {
    echo "<p style='color: red;'>No autorizado.</p>";
    exit;
}

// Funciones para obtener correos
function obtenerCorreoCliente($fitxer, $usuarioCliente) {
    $usuaris = file($fitxer, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($usuaris as $usuari) {
        $camps = explode(';', $usuari);
        if (isset($camps[0]) && $camps[0] == $usuarioCliente && $camps[3] == 'cliente') {
            return $camps[6] ?? null;
        }
    }
    return null;
}

function obtenerCorreoGestorAsignado($fitxer, $usuarioCliente) {
    $usuaris = file($fitxer, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($usuaris as $usuari) {
        $camps = explode(';', $usuari);
        if (isset($camps[0]) && $camps[0] == $usuarioCliente && $camps[3] == 'cliente') {
            $gestorAsignado = $camps[9] ?? null;
            foreach ($usuaris as $gestor) {
                $datosGestor = explode(';', $gestor);
                if (isset($datosGestor[0]) && $datosGestor[0] == $gestorAsignado && $datosGestor[3] == 'gestor') {
                    return $datosGestor[6] ?? null;
                }
            }
        }
    }
    return null;
}

function enviarCorreo($correoCliente, $correoGestor, $asunto, $mensaje) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'joan2005garcia@gmail.com';
        $mail->Password = 'qgmc iygr itau zhqy';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom($correoCliente, 'Cliente');
        $mail->addAddress($correoGestor, 'Gestor Asignado');
        $mail->addReplyTo($correoCliente, 'Cliente');

        $mail->isHTML(false);
        $mail->Subject = $asunto;
        $mail->Body = $mensaje;

        $mail->send();

        $registro = date('Y-m-d H:i:s') . " | Correo enviado desde: $correoCliente | Destinatario: $correoGestor  | Asunto: $asunto | Mensaje: $mensaje\n";
        $archivoRegistro = '../registro_correos.txt';
        $file = fopen($archivoRegistro, 'a');
        fwrite($file, $registro);
        fclose($file);

        return true;
    } catch (Exception $e) {
        return $e->getMessage();
    }
}

// Inicializar variables para mostrar mensajes
$mensajeResultado = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $archivoUsuarios = "../usuaris/usuaris.txt";
    $correoCliente = obtenerCorreoCliente($archivoUsuarios, $_SESSION['usuario']);
    $correoGestor = obtenerCorreoGestorAsignado($archivoUsuarios, $_SESSION['usuario']);
    $mensaje = $_POST['mensaje'] ?? '';
    $tipoPeticion = $_POST['tipo_peticion'] ?? '';

    if ($tipoPeticion === 'modificacion_esborrament') {
        $asunto = "Petició de modificació o esborrament del compte";
    } elseif ($tipoPeticion === 'justificacio_comanda') {
        $asunto = "Petició de justificació de comanda rebutjada";
    } else {
        $asunto = "Petició desconeguda";
    }

    if (!$correoCliente || !$correoGestor) {
        $mensajeResultado = "<p style='color: red;'>No se pudieron obtener los correos.</p>";
    } else {
        $resultado = enviarCorreo($correoCliente, $correoGestor, $asunto, $mensaje);
        if ($resultado === true) {
            $mensajeResultado = "<p style='color: green;'>Correo enviado correctamente.</p>";
        } else {
            $mensajeResultado = "<p style='color: red;'>Error al enviar el correo: $resultado</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar Correo</title>
</head>
<body>
    <h1>Enviar Correo</h1>
    <?php
    if (!empty($mensajeResultado)) {
        echo $mensajeResultado; // Mostrar el mensaje de resultado
    }
    ?>
    <form method="post" action="">
        <label for="tipo_peticion">Tipo de Petición:</label>
        <select name="tipo_peticion" id="tipo_peticion">
            <option value="modificacion_esborrament">Modificación o Esborrament</option>
            <option value="justificacio_comanda">Justificación de Comanda Rechazada</option>
        </select>
        <br><br>
        <label for="mensaje">Mensaje:</label>
        <textarea name="mensaje" id="mensaje" rows="5" cols="40"></textarea>
        <br><br>
        <button type="submit" name="enviar_correo">Enviar Correo</button>
    </form>
</body>
</html>