<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

if ($_SESSION['tipo'] != 'cliente') {
    header('Location: login.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require '../vendor/autoload.php';

    $archivoUsuarios = "../usuaris/usuaris.txt";
    $usuarioCliente = $_SESSION['usuario'];
    $correoCliente = null;
    $correoGestor = null;

    // Leer datos del archivo
    $usuarios = file($archivoUsuarios, FILE_IGNORE_NEW_LINES);

    foreach ($usuarios as $usuarioData) {
        $campos = explode(';', $usuarioData);

        // Identificar al cliente y obtener su correo
        if ($campos[0] == $usuarioCliente) {
            $correoCliente = $campos[6]; // Correo del cliente
            $gestorAsignado = $campos[9]; // Identificador del gestor asignado
        }

        // Identificar al gestor asignado y obtener su correo
        if (isset($gestorAsignado) && $gestorAsignado == $campos[0]) {
            $correoGestor = $campos[4]; // Correo del gestor
        }
    }

    // Validar que se encontraron los correos
    if (!$correoCliente || !$correoGestor) {
        echo "No s'han pogut obtenir les dades del client o del gestor assignat.";
        exit;
    }

    // Obtener el mensaje y la solicitud del formulario
    $solicitud = trim(strtolower($_POST['solicitud'] ?? ''));
    $mensaje = $_POST['mensaje'] ?? '';
    $asunto = '';

    // Depuración
    echo "Valor de solicitud: " . htmlspecialchars($solicitud) . "<br>";

    switch ($solicitud) {
        case 'modificar':
            $asunto = "Petició de modificació del compte";
            break;
        case 'esborrar':
            $asunto = "Petició d'esborrament del compte";
            break;
        case 'justificar_comanda':
            $asunto = "Petició de justificació de comanda rebutjada";
            break;
        default:
            echo "Acció desconeguda.";
            exit;
    }

    // Configuración de PHPMailer
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'joan2005garcia@gmail.com';
        $mail->Password = 'qgmc iygr itau zhqy';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom($correoCliente, $usuarioCliente);
        $mail->addAddress($correoGestor, 'Gestor Assignat');
        $mail->addReplyTo($correoCliente, 'Cliente');

        $mail->isHTML(false);
        $mail->Subject = $asunto;
        $mail->Body = $mensaje;

        $mail->send();
        echo "La teva sol·licitud ha estat enviada correctament.";
    } catch (Exception $e) {
        echo "Hi ha hagut un error en l'enviament del correu. Error: {$mail->ErrorInfo}";
    }
}
