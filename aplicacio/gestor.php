<?php
session_start();

if ($_SESSION['tipo'] != 'gestor') {
    header('Location: login.php');
    exit;
}

// Archivo de productos
$archivoProductos = '../productes/productes.txt';

// Verificar si el formulario ha sido enviado
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
    }elseif($_POST['accion'] == 'eliminar' && isset($_POST['id'])) {
        $productos = file($archivoProductos, FILE_IGNORE_NEW_LINES);
        $productosFiltrados = array_filter($productos, function ($producto) {
            list($nombre, $id, $precio, $iva, $disponible) = explode('|', $producto);
            return $id != $_POST['id'];
        });
    }

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Leer y mostrar productos
$productos = file_exists($archivoProductos) ? file($archivoProductos, FILE_IGNORE_NEW_LINES) : [];
?>

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
    <input type="number" name="identificador" min="0" required><br><br>
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