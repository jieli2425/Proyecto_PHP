<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

if ($_SESSION['tipo'] != 'cliente') {
    header('Location: login.php');
    exit;
}

// Ruta del archivo donde se guardan los datos de los usuarios
$archivoUsuarios = "../usuaris/usuaris.txt";
$usuario = $_SESSION['usuario'];

// Leer todos los usuarios desde el archivo
$usuarios = file($archivoUsuarios, FILE_IGNORE_NEW_LINES);

// Buscar los datos del usuario actual
$datosUsuario = null;
foreach ($usuarios as $usuarioData) {
    $campos = explode(';', $usuarioData);
    if ($usuario == $campos[0]) {
        $datosUsuario = [
            'usuario' => $campos[0],
            'id' => $campos[1],
            'nom' => $campos[4],
            'cognoms' => $campos[5],
            'correo' => $campos[6],
            'telefon' => $campos[7],
            'adreça' => $campos[8],
            'gestor_assignat' => $campos[9]
        ];
        break;
    }
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

// Procesar el formulario si se envía
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
    <title>Client Panel</title>
</head>
<body>
    <h1>Panel del Cliente</h1>

    <?php if ($datosUsuario): ?>
        <h3>Les teves dades personals:</h3>
        <p>Nom d'usuari: <?= $datosUsuario['usuario'] ?></p>
        <p>ID: <?= $datosUsuario['id'] ?></p>
        <p>Nom: <?= $datosUsuario['nom'] ?></p>
        <p>Cognoms: <?= $datosUsuario['cognoms'] ?></p>
        <p>Correu electrònic: <?= $datosUsuario['correo'] ?></p>
        <p>Telèfon: <?= $datosUsuario['telefon'] ?></p>
        <p>Adreça: <?= $datosUsuario['adreça'] ?></p>
        <p>Gestor assignat: <?= $datosUsuario['gestor_assignat'] ?></p>
    <?php else: ?>
        <p>No es van trobar els teus dades.</p>
    <?php endif; ?>

    <?php if (!empty($mensajeResultado)) echo $mensajeResultado; ?>

    <h3>Enviar Petició</h3>
    <form method="post" action="">
        <label for="tipo_peticion">Selecciona el tipus de petició:</label>
        <select name="tipo_peticion" id="tipo_peticion" required>
            <option value="modificacion_esborrament">Modificació o Esborrament</option>
            <option value="justificacio_comanda">Justificació de Comanda Rebutjada</option>
        </select>
        <br><br>
        <label for="mensaje">Escriu el teu missatge:</label>
        <textarea name="mensaje" id="mensaje" rows="5" cols="40" required></textarea>
        <br><br>
        <button type="submit">Enviar</button>
    </form>

    <form method="POST" action="index.php">
        <button type="submit">Volver</button>
    </form>
</body>
</html>
