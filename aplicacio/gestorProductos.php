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
function obtenirUsuaris($fitxer, $tipo) {
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

$clients = obtenirUsuaris($archivoUsuarios, 'cliente');
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
    <h3>Enviar correo al administrador</h3>
    <form method="POST">
    <label for="mensaje">Mensaje:</label><br>
    <textarea id="mensaje" name="mensaje" rows="5" required></textarea><br><br>

    <!-- Botón con el asunto como texto -->
    <button type="submit" name="enviar_correo"><?php echo "petició d'addició/modificació/esborrament de client"; ?></button>
</form>

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