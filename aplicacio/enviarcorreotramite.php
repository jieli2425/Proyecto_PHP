<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

// Eliminar session_start() ya que ya se ha iniciado en el archivo principal

if ($_SESSION['tipo'] != 'gestor') {
    header('Location: login.php');
    exit;
}

// Funciones para obtener correos
function obtenerCorreoGestor($fitxer, $usuarioGestor) {
    $usuaris = file($fitxer, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($usuaris as $usuari) {
        $camps = explode(';', $usuari);

        // El índice 3 es para el rol y el índice 6 para el correo del gestor
        if (isset($camps[0]) && isset($camps[3]) && $camps[0] == $usuarioGestor && $camps[3] == 'gestor') {
            return $camps[6] ?? null; // Correo del gestor
        }
    }
    return null; // Si no se encuentra el correo del gestor
}



// Función para enviar correo y registrar
function enviarCorreo($correoGestor, $correoCliente, $asunto, $mensaje) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'joan2005garcia@gmail.com'; // Cambia esto con tu correo
        $mail->Password = 'qgmc iygr itau zhqy'; // Cambia esto con tu contraseña o app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom($correoGestor, 'Gestor'); // El correo del gestor
        $mail->addAddress($correoCliente, 'Cliente'); // El correo del cliente
        $mail->addReplyTo($correoGestor, 'Gestor'); // Responder al correo del gestor

        $mail->isHTML(false); // Enviar como texto plano
        $mail->Subject = $asunto;
        $mail->Body = $mensaje;

        $mail->send();

        // Guardar el registro en un archivo de texto
        $registro = date('Y-m-d H:i:s') . " | Correo enviado desde: $correoGestor | Destinatario: $correoCliente  | Asunto: $asunto | Mensaje: $mensaje\n";
        
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
    $correoGestor = obtenerCorreoGestor($archivoUsuarios, $_SESSION['usuario']); // Obtener el correo del gestor desde el archivo
    $usuarioCliente = $_POST['usuario_cliente']; // Asumir que el nombre del cliente se pasa como un dato en el formulario
    $correoCliente = obtenerCorreoCliente($archivoUsuarios, $usuarioCliente); // Obtener el correo del cliente desde el archivo
    $mensaje = $_POST['mensaje'];

    // Verificar si los correos se obtuvieron correctamente
    if (!$correoGestor || !$correoCliente) {
        echo "<p style='color: red;'>No se pudieron obtener los correos.</p>";
        exit;
    }

    // Definir el asunto
    $asunto = "Actualización sobre tu comanda";

    // Llamar a la función para enviar el correo
    $resultado = enviarCorreo($correoGestor, $correoCliente, $asunto, $mensaje);
    echo "<p style='color: green;'>$resultado</p>";
}
?>
