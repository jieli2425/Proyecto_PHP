<?php
session_start();

if ($_SESSION['tipo'] != 'gestor') {
    header('Location: login.php');
    exit;
}

// Archivos
$archivoProductos = '../productes/productes.txt';
$archivoUsuarios = '../usuaris/usuaris.txt';

// Función para obtener usuarios de un tipo específico (gestor o cliente)
function obtenerUsuarios($fitxer, $tipo) {
    $usuaris = file($fitxer, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $resultat = [];

    foreach ($usuaris as $usuari) {
        $camps = explode(';', $usuari);
        $rol = $camps[3] ?? null;

        if ($rol === $tipo) {
            if ($tipo === 'cliente' && count($camps) >= 9) {
                $resultat[] = [
                    'usuario' => $camps[0],
                    'id' => $camps[1],
                    'password' => $camps[2],
                    'nom' => $camps[4],
                    'cognoms' => $camps[5],
                    'correo' => $camps[6],
                    'telefon' => $camps[7],
                    'adreça' => $camps[8],
                    'gestor_assignat' => $camps[9]
                ];
            }
        }
    }
    return $resultat;
}

$clients = obtenerUsuarios($archivoUsuarios, 'cliente');
echo "<h3>Lista de Clientes</h3>";
foreach ($clients as $cliente) {
    echo "Usuario: {$cliente['usuario']}<br>";
    echo "ID: {$cliente['id']}<br>";
    echo "Nombre: {$cliente['nom']}<br>";
    echo "Apellidos: {$cliente['cognoms']}<br>";
    echo "Correo: {$cliente['correo']}<br>";
    echo "Teléfono: {$cliente['telefon']}<br>";
    echo "Dirección: {$cliente['adreça']}<br>";
    echo "Gestor Asignado: {$cliente['gestor_assignat']}<br>";
    echo "<hr>";
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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion'])) {
    // añadir producto
    if ($_POST['accion'] == 'agregar') {
    $nombre = $_POST['nombre'];
    $id = $_POST['id'];
    $precio = $_POST['precio'];
    $iva = $_POST['iva'];
    $disponible = $_POST['disponible'];

    // Crear entrada del producto
    $producto = "$nombre|$id|$precio|$iva|$disponible\n";
    file_put_contents($archivoProductos, $producto, FILE_APPEND);
    $mensaje = "Producto agregado correctamente.";
    // eliminar producto
    }elseif ($_POST['accion'] == 'eliminar' && isset($_POST['id'])) {
        $productos = file($archivoProductos, FILE_IGNORE_NEW_LINES);
    
        $productosFiltrados = array_filter($productos, function ($producto) {
            list($nombre, $id, $precio, $iva, $disponible) = explode('|', $producto);
            return $id != $_POST['id']; // Excluir el producto con ese ID
        });
    
        // Reescribir el archivo con los productos restantes
        file_put_contents($archivoProductos, implode("\n", $productosFiltrados) . "\n");
        
        // Mensaje de éxito o error
        $mensaje = "Producto eliminado correctamente.";
    }

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Leer y mostrar productos
$productos = file_exists($archivoProductos) ? file($archivoProductos, FILE_IGNORE_NEW_LINES) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor</title>
</head>
<body>
    <br><br>
    <form method="POST">
        <label for="nombre">Nombre del producto:</label><br>
        <input type="text" name="nombre" required><br><br>
        <label for="id">ID del producto:</label><br>
        <input type="number" name="id" min="0" required><br><br>
        <label for="precio">Precio:</label><br>
        <input type="number" name="precio" step="0.01" required><br><br>
        <label for="iva">IVA:</label><br>
        <input type="number" name="iva" step="0.01" required><br><br>
        <label for="disponible">Disponible:</label><br>
        <select name="disponible">
            <option value="Sí">Sí</option>
            <option value="No">No</option>
        </select><br><br>
        <input type="hidden" name="accion" value="agregar">
        <button type="submit">Agregar producto</button>
    </form>

    <form method="POST">
        <h3>Eliminar un producto</h3>
        <label for="identificador">ID del producto a eliminar:</label><br>
        <input type="number" name="id" min="0" required><br><br>
        <input type="hidden" name="accion" value="eliminar">
        <button type="submit">Eliminar Producto</button>
    </form>

    <h2>Lista de productos</h2>
    <table border="1">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>ID</th>
                <th>Precio</th>
                <th>IVA</th>
                <th>Disponible</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($productos as $producto): ?>
                <?php list($nombre, $id, $precio, $iva, $disponible) = explode('|', $producto); ?>
                <tr>
                    <td><?php echo htmlspecialchars($nombre); ?></td>
                    <td><?php echo htmlspecialchars($id); ?></td>
                    <td><?php echo htmlspecialchars($precio); ?> €</td>
                    <td><?php echo htmlspecialchars($iva); ?>%</td>
                    <td><?php echo htmlspecialchars($disponible); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <br>

    <form method="POST" action="index.php">
        <button type="submit">Volver</button>
    </form>
</body>
</html>