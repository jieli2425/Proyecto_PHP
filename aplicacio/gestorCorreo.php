<?php
session_start();

if ($_SESSION['tipo'] != 'gestor') {
    header('Location: login.php');
    exit;
}

// Archivos
$archivoProductos = '../productes/productes.txt';
$archivoUsuarios = '../usuaris/usuaris.txt';

// Obtener el correo del administrador
function obtenirCorreoAdmin($fitxer) {
    $usuaris = file($fitxer, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($usuaris as $usuari) {
        $camps = explode(';', $usuari);
        $rol = $camps[3] ?? null;

        // Verificar que haya al menos 7 campos y que el rol sea admin
        if ($rol === 'admin' && isset($camps[1])) { // Correo en el índice 1
            return $camps[1]; // Correo del administrador
        }
    }
    return null; // Si no se encuentra el correo del admin
}

// Obtener el correo del gestor
function obtenirCorreoGestor($fitxer, $usuarioGestor) {
    $usuaris = file($fitxer, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($usuaris as $usuari) {
        $camps = explode(';', $usuari);
        $rol = $camps[3] ?? null;

        if ($rol === 'gestor' && $camps[0] === $usuarioGestor) {
            return $camps[6]; // Correo del gestor
        }
    }
    return null; // Si no se encuentra el correo del gestor
}

$correoAdmin = obtenirCorreoAdmin($archivoUsuarios);
if (!$correoAdmin) {
    echo "<p style='color: red;'>No se encontró el correo del administrador.</p>";
    exit;
}

// Enviar correo
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['enviar_correo'])) {
    $correoGestor = obtenirCorreoGestor($archivoUsuarios, $_SESSION['usuario']); // Obtener correo del gestor desde archivo
    $asunto = "petició d'addició/modificació/esborrament de client"; // Asunto fijo
    $mensaje = $_POST['mensaje'];

    // Validar datos
    if (filter_var($correoGestor, FILTER_VALIDATE_EMAIL) && filter_var($correoAdmin, FILTER_VALIDATE_EMAIL)) {
        // Incluir el archivo externo para enviar el correo
        include('enviar_correogestor.php');

        // Llamar a la función para enviar el correo
        $resultado = enviarCorreo($correoGestor, $correoAdmin, $asunto, $mensaje);
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
    <label for="mensaje">Mensaje:</label><br>
    <textarea id="mensaje" name="mensaje" rows="5" required></textarea><br><br>

    <!-- Botón con el asunto como texto -->
    <button type="submit" name="enviar_correo"><?php echo "petició d'addició/modificació/esborrament de client"; ?></button>
</form>

    <form method="POST" action="index.php">
        <button type="submit">Volver</button>
    </form>
</body>
</html>