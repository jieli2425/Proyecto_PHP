<?php
session_start();

if ($_SESSION['tipo'] != 'gestor') {
    header('Location: login.php');
    exit;
}

$directorioComandes = '../comandes/';
$archivoUsuarios = '../usuaris/usuaris.txt';

// Incluir el archivo para enviar correos
require 'enviarcorreotramite.php';

// Función para obtener las comandas de un cliente
function obtenerComandas($directorio, $usuario) {
    $archivoComandas = $directorio . $usuario . '.txt';
    if (file_exists($archivoComandas)) {
        return file($archivoComandas, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    }
    return [];
}

// Función para obtener el correo del cliente
function obtenerCorreoCliente($archivoUsuarios, $usuario) {
    $usuarios = file($archivoUsuarios, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($usuarios as $usuarioData) {
        $campos = explode(';', $usuarioData);
        if ($campos[0] === $usuario) {
            return $campos[6] ?? null; // Retorna el correo del cliente
        }
    }
    return null;
}

// Función para obtener el correo y nombre del gestor
function obtenerDatosGestor($archivoUsuarios, $usuarioGestor) {
    $usuarios = file($archivoUsuarios, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($usuarios as $usuarioData) {
        $campos = explode(';', $usuarioData);
        if ($campos[0] === $usuarioGestor && $campos[3] === 'gestor') {
            return ['correo' => $campos[6], 'nombre' => $campos[4] . ' ' . $campos[5]]; // Retorna correo y nombre
        }
    }
    return null;
}

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuarioCliente = $_POST['usuario_cliente'];
    $accion = $_POST['accion'];

    // Obtener el correo del gestor
    $datosGestor = obtenerDatosGestor($archivoUsuarios, $_SESSION['usuario']);
    $correoGestor = $datosGestor['correo'];  // Correo del gestor
    $nombreGestor = $datosGestor['nombre'];  // Nombre del gestor

    // Obtener el correo del cliente
    $correoCliente = obtenerCorreoCliente($archivoUsuarios, $usuarioCliente);
    $archivoComandas = $directorioComandes . $usuarioCliente . '.txt';

    // Mensajes personalizados para el correo
    $mensajesCorreo = [
        'borrar' => 'Tu comanda ha sido borrada por el gestor.',
        'tramitar' => 'Tu comanda ha sido tramitada por el gestor.',
        'finalizar' => 'Tu comanda ha sido finalizada por el gestor.',
    ];

    // Definir el asunto y mensaje basado en la acción
    $asunto = "";
    $mensaje = "";

    if ($accion === 'borrar') {
        if (file_exists($archivoComandas)) {
            unlink($archivoComandas); // Borrar el archivo completo
        }
        $asunto = "Comanda borrada";
        $mensaje = $mensajesCorreo['borrar'];
    } elseif ($accion === 'tramitar') {
        $asunto = "Comanda tramitada";
        $mensaje = $mensajesCorreo['tramitar'];
    } elseif ($accion === 'finalizar') {
        if (file_exists($archivoComandas)) {
            unlink($archivoComandas); // Borrar el archivo completo
        }
        $asunto = "Comanda finalizada";
        $mensaje = $mensajesCorreo['finalizar'];
    }

    // Enviar el correo al cliente con los datos dinámicos
    $enviado = enviarCorreo($correoGestor, $correoCliente, $asunto, $mensaje);

    // Mostrar mensaje de éxito en verde
    if ($enviado === true) {
        $mensajeExito = "<p style='color: green;'>Correo enviado correctamente.</p>";
   
}
}

// Asegúrate de que el archivo exista y contenga datos
$clientes = [];
if (file_exists($archivoUsuarios)) {
    $clientes = file($archivoUsuarios, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor - Comandas</title>
</head>
<body>
    <h1>Gestión de Comandas</h1>

    <?php if (isset($mensajeExito)) { echo $mensajeExito; } ?>

    <?php if (!empty($clientes)): ?>
        <?php foreach ($clientes as $cliente): ?>
            <?php
            $datos = explode(';', $cliente);
            $usuarioCliente = $datos[0];
            $gestorAsignado = $datos[9] ?? '';

            if ($gestorAsignado === $_SESSION['usuario']) {
                $comandas = obtenerComandas($directorioComandes, $usuarioCliente);
                if (!empty($comandas)) {
            ?>
            <h3>Cliente: <?php echo htmlspecialchars($usuarioCliente); ?></h3>
            <div>
                <?php foreach ($comandas as $comanda): ?>
                    <?php echo htmlspecialchars($comanda) . "<br>"; ?>
                <?php endforeach; ?>
            </div>
            <form method="POST">
                <input type="hidden" name="usuario_cliente" value="<?php echo htmlspecialchars($usuarioCliente); ?>">
                <button type="submit" name="accion" value="borrar">Borrar Comanda</button>
                <button type="submit" name="accion" value="tramitar">Tramitar Comanda</button>
                <button type="submit" name="accion" value="finalizar">Finalizar Comanda</button>
            </form>
            <hr>
            <?php
                }
            }
            ?>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No hay clientes disponibles.</p>
    <?php endif; ?>
</body>
</html>
