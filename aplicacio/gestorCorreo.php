<?php
session_start();

if ($_SESSION['tipo'] != 'gestor') {
    header('Location: login.php');
    exit;
}

// Archivos
$archivoUsuarios = '../usuaris/usuaris.txt';

// Obtener el correo del administrador
function obtenirCorreoAdmin($fitxer) {
    $usuaris = file($fitxer, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($usuaris as $usuari) {
        $camps = explode(';', $usuari);
        $rol = $camps[3] ?? null;

        if ($rol === 'admin' && isset($camps[1])) { // Correo en el índice 1
            return $camps[1];
        }
    }
    return null;
}

// Obtener el correo del gestor
function obtenirCorreoGestor($fitxer, $usuarioGestor) {
    $usuaris = file($fitxer, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($usuaris as $usuari) {
        $camps = explode(';', $usuari);
        $rol = $camps[3] ?? null;

        if ($rol === 'gestor' && $camps[0] === $usuarioGestor) {
            return $camps[6];
        }
    }
    return null;
}

$correoAdmin = obtenirCorreoAdmin($archivoUsuarios);
if (!$correoAdmin) {
    echo "<p style='color: red;'>No se encontró el correo del administrador.</p>";
    exit;
}

// Procesar el formulario y enviar el correo
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['enviar_correo'])) {
    $correoGestor = obtenirCorreoGestor($archivoUsuarios, $_SESSION['usuario']);
    $tipoPeticion = $_POST['tipo_peticion']; // Asunto dinámico según selección
    $mensaje = $_POST['mensaje'];

    if (filter_var($correoGestor, FILTER_VALIDATE_EMAIL) && filter_var($correoAdmin, FILTER_VALIDATE_EMAIL)) {
        // Incluir la función de envío de correos
        include_once('enviar_correogestor.php');

        // Llamar a la función para enviar el correo
        $resultado = enviarCorreo($correoGestor, $correoAdmin, $tipoPeticion, $mensaje);
        echo "<p style='color: green;'>$resultado</p>";
    } else {
        echo "<p style='color: red;'>Direcciones de correo no válidas.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Correo</title>
</head>
<body>
    <h3>Enviar correo al administrador</h3>
    <form method="POST">
        <label for="tipo_peticion">Tipo de Petición:</label>
        <select name="tipo_peticion" id="tipo_peticion">
            <option value="addicio">Addició</option>
            <option value="modificacio">Modificació</option>
            <option value="esborrament">Esborrament</option>
        </select>
        <br><br>

        <label for="mensaje">Mensaje:</label><br>
        <textarea id="mensaje" name="mensaje" rows="5" required></textarea><br><br>

        <button type="submit" name="enviar_correo">Enviar</button>
    </form>

    <form method="POST" action="index.php">
        <button type="submit">Volver</button>
    </form>
</body>
</html>
