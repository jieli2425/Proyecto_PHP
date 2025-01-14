<?php
session_start();

// Verificar que el usuario está autenticado y es un gestor
if ($_SESSION['tipo'] != 'gestor') {
    header('Location: login.php');
    exit;
}

$usuario = $_SESSION['usuario'];
$comandaFile = "comandes/$usuario.txt"; // Ruta al archivo de la comanda

// Verificar si existe una comanda para el usuario
if (file_exists($comandaFile)) {
    // Leer la comanda
    $comanda = file_get_contents($comandaFile);
} else {
    echo "No tienes ninguna comanda asociada.";
    exit;
}

// Función para enviar el correo
function enviarCorreo($destinatario, $asunto, $mensaje) {
    // Aquí debes configurar tu servidor de correo o usar una librería como PHPMailer.
    // Enviar el correo al cliente
    mail($destinatario, $asunto, $mensaje);
}

// Función para enviar el mensaje de WhatsApp o Telegram
function enviarMensaje($mensaje) {
    // Enviar el mensaje a través de WhatsApp o Telegram (usar API correspondiente)
    // Esta es una función de ejemplo, deberías usar la API adecuada.
    // Ejemplo con WhatsApp:
    $whatsappAPI = "https://api.whatsapp.com/send?phone=123456789&text=" . urlencode($mensaje);
    file_get_contents($whatsappAPI);
    // Ejemplo con Telegram:
    $telegramAPI = "https://api.telegram.org/bot<your_bot_token>/sendMessage?chat_id=<chat_id>&text=" . urlencode($mensaje);
    file_get_contents($telegramAPI);
}

// Acciones para rechazar, tramitar o finalizar la comanda
if (isset($_POST['accion'])) {
    $accion = $_POST['accion'];
    
    switch ($accion) {
        case 'rechazar':
            // Eliminar la comanda
            unlink($comandaFile);
            
            // Enviar correo y mensaje
            $asunto = "Comanda rebutjada";
            $mensaje = "La teva comanda ha estat rebutjada i esborrada. Si tens preguntes, contacta amb nosaltres.";
            enviarCorreo($usuario, $asunto, $mensaje);
            enviarMensaje($mensaje);
            
            echo "Comanda rebutjada i esborrada.";
            break;
        
        case 'tramitar':
            // Enviar correo y mensaje
            $asunto = "Tramitant la comanda";
            $mensaje = "La teva comanda està en procés de tramitació. Rebràs més informació pròximament.";
            enviarCorreo($usuario, $asunto, $mensaje);
            enviarMensaje($mensaje);
            
            echo "Comanda en procés de tramitació.";
            break;
        
        case 'finalizar':
            // Eliminar la comanda
            unlink($comandaFile);
            
            // Enviar correo y mensaje
            $asunto = "Comanda enviada";
            $mensaje = "La teva comanda ha estat enviat i pagada. La comanda ha estat esborrada.";
            enviarCorreo($usuario, $asunto, $mensaje);
            enviarMensaje($mensaje);
            
            echo "Comanda finalitzada i esborrada.";
            break;
        
        default:
            echo "Acció no vàlida.";
    }
}
?>

<h3>Gestió de la comanda:</h3>
<form method="POST">
    <button type="submit" name="accion" value="rechazar">Rebutjar comanda</button>
    <button type="submit" name="accion" value="tramitar">Tramitar comanda</button>
    <button type="submit" name="accion" value="finalizar">Finalitzar comanda</button>
</form>

<!-- Formulari per tornar a la pàgina anterior -->
<form method="POST" action="gestor.php">
    <button type="submit">Volver</button>
</form>
