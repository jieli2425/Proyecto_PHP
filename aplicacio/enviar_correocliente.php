<?php
session_start(); // Asegúrate de que session_start() esté aquí para trabajar con la sesión

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

// Verificar que el usuario es cliente
if ($_SESSION['tipo'] != 'cliente') {
    header('Location: login.php');
    exit;
}

// Funciones para obtener correos
function obtenerCorreoCliente($fitxer, $usuarioCliente) {
    $usuaris = file($fitxer, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($usuaris as $usuari) {
        $camps = explode(';', $usuari);

        // El índice 0 es para el nombre de usuario y el índice 6 para el correo del cliente
        if (isset($camps[0]) && $camps[0] == $usuarioCliente && $camps[3] == 'cliente') {
            return $camps[6] ?? null; // Correo del cliente
        }
    }
    return null; // Si no se encuentra el correo del cliente
}

function obtenerCorreoGestorAsignado($fitxer, $usuarioCliente) {
    $usuaris = file($fitxer, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($usuaris as $usuari) {
        $camps = explode(';', $usuari);

        // Buscar el gestor asignado al cliente
        if (isset($camps[0]) && $camps[0] == $usuarioCliente && $camps[3] == 'cliente') {
            $gestorAsignado = $camps[9] ?? null; // Gestor asignado al cliente

            // Buscar el correo del gestor asignado
            foreach ($usuaris as $gestor) {
                $datosGestor = explode(';', $gestor);
                if (isset($datosGestor[0]) && $datosGestor[0] == $gestorAsignado && $datosGestor[3] == 'gestor') {
                    return $datosGestor[6] ?? null; // Correo del gestor asignado
                }
            }
        }
    }
    return null; // Si no se encuentra el correo del gestor
}

// Función para enviar correo y registrar
function enviarCorreo($correoCliente, $correoGestor, $asunto, $mensaje) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'joan2005garcia@gmail.com'; // Cambia esto con tu correo
        $mail->Password = 'qgmc iygr itau zhqy'; // Cambia esto con tu contraseña o app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom($correoCliente, 'Cliente'); // El correo del cliente
        $mail->addAddress($correoGestor, 'Gestor Asignado'); // El correo del gestor asignado
        $mail->addReplyTo($correoCliente, 'Cliente'); // Responder al correo del cliente

        $mail->isHTML(false); // Enviar como texto plano
        $mail->Subject = $asunto;
        $mail->Body = $mensaje;

        $mail->send();

        // Guardar el registro en un archivo de texto
        $registro = date('Y-m-d H:i:s') . " | Correo enviado desde: $correoCliente | Destinatario: $correoGestor  | Asunto: $asunto | Mensaje: $mensaje\n";
        
        // Abrir el archivo de registro (o crear uno si no existe)
        $archivoRegistro = '../registro_correos.txt'; // Ruta del archivo de registro
        $file = fopen($archivoRegistro, 'a'); // 'a' para añadir contenido al final del archivo
        fwrite($file, $registro);
        fclose($file);

        return "Correo enviado correctamente y registrado.";
    } catch (Exception $e) {
        return "Error al enviar el correo. {$mail->ErrorInfo}";
    }
}

// Código para enviar correo
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['enviar_correo'])) {
    $archivoUsuarios = "../usuaris/usuaris.txt";
    $correoCliente = obtenerCorreoCliente($archivoUsuarios, $_SESSION['usuario']); // Obtener el correo del cliente desde el archivo
    $correoGestor = obtenerCorreoGestorAsignado($archivoUsuarios, $_SESSION['usuario']); // Obtener el correo del gestor asignado desde el archivo
    $mensaje = $_POST['mensaje'];
    $asunto = "Petició d'adicció/modificació/esborrament del client";

    // Verificar si los correos se obtuvieron correctamente
    if (!$correoCliente || !$correoGestor) {
        echo "<p style='color: red;'>No se pudieron obtener los correos.</p>";
        exit;
    }

    // Llamar a la función para enviar el correo
    $resultado = enviarCorreo($correoCliente, $correoGestor, $asunto, $mensaje);
    echo "<p style='color: green;'>$resultado</p>";
}
?>
