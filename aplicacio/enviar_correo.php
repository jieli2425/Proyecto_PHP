<?php
if ($_SESSION['tipo'] != 'cliente') {
    header('Location: login.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $asunto = "";
    $mensaje = $_POST['mensaje'];
    
    // Comprovem si és una petició de modificació o esborrament de compte
    if ($_POST['solicitud'] == 'modificar') {
        $asunto = "petició de modificació/esborrament del compte de client";
    } elseif ($_POST['solicitud'] == 'esborrar') {
        $asunto = "petició de modificació/esborrament del compte de client";
    }
    
    // Si és una justificació de comanda rebutjada
    if ($_POST['solicitud'] == 'justificar_comanda') {
        $asunto = "petició de justificació de comanda rebutjada";
    }

    // Enviament de l'email
    $to = "gestor1@botiga.com";  // El correu del gestor de la botiga
    $headers = "From: ".$_SESSION['usuario']."@domini.com" . "\r\n" .
               "Reply-To: ".$_SESSION['usuario']."@domini.com" . "\r\n" .
               "Content-Type: text/plain; charset=UTF-8\r\n";

    if (mail($to, $asunto, $mensaje, $headers)) {
        echo "La teva sol·licitud ha estat enviada correctament.";
    } else {
        echo "Hi ha hagut un error en l'enviament del correu.";
    }
}
?>

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