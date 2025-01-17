<?php
session_start();

if ($_SESSION['tipo'] != 'gestor') {
    header('Location: login.php');
    exit;
}

$directorioComandes = '../comandes/';
$archivoUsuarios = '../usuaris/usuaris.txt';

// Incluir el archivo para enviar correos
require 'enviarcorreotramite.php';  // Asegúrate de que la ruta sea correcta

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

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuarioCliente = $_POST['usuario_cliente'];
    $accion = $_POST['accion'];

    $correoCliente = obtenerCorreoCliente($archivoUsuarios, $usuarioCliente);
    $archivoComandas = $directorioComandes . $usuarioCliente . '.txt';

    if ($accion === 'borrar') {
        if (file_exists($archivoComandas)) {
            unlink($archivoComandas); // Borrar el archivo completo
            enviarCorreoCliente($correoCliente, "Comanda borrada", "Tu comanda ha sido borrada por el gestor.");
        }
    } elseif ($accion === 'tramitar') {
        enviarCorreoCliente($correoCliente, "Comanda tramitada", "Tu comanda ha sido tramitada por el gestor.");
    } elseif ($accion === 'finalizar') {
        enviarCorreoCliente($correoCliente, "Comanda finalizada", "Tu comanda ha sido finalizada por el gestor.");
    }
}

$clientes = file($archivoUsuarios, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
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
</body>
</html>
