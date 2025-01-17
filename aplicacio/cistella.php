<?php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] != 'cliente') {
    header('Location: login.php');
    exit;
}

$productos = file('../productes/productes.txt', FILE_IGNORE_NEW_LINES);
$usuario = $_SESSION['usuario'];

// Definir la ruta del archivo de la cesta
$cestaFile = "../cistelles/$usuario.txt";

// Verificar si el archivo de la cesta existe, sino, crearlo vacío
if (!file_exists($cestaFile)) {
    file_put_contents($cestaFile, "");
}

// Cargar la cesta de productos desde el archivo
$cestaArray = file($cestaFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

// Variables de totales
$totalSenseIVA = 0;
$totalIVA = 0;
$totalAmbIVA = 0;
$productosSeleccionats = [];

// Función para calcular los totales y los productos seleccionados
function calcularTotals($productos, $cestaArray)
{
    $totals = ['senseIVA' => 0, 'IVA' => 0, 'ambIVA' => 0];
    $productosSeleccionats = [];

    foreach ($cestaArray as $linea) {
        $parts = explode('|', $linea);
        if (count($parts) < 5) continue;  // Asegurarse de que hay suficientes datos

        list($nombre, $idProducto, $precio, $iva, $quantitat) = $parts;

        foreach ($productos as $producto) {
            list($nombre, $id, $precioProducto, $ivaProducto, $disponible) = explode('|', $producto);
            if ($id == $idProducto) {
                $preuSenseIVA = $precioProducto * $quantitat;
                $valorIVA = ($ivaProducto / 100) * $preuSenseIVA;
                $preuAmbIVA = $preuSenseIVA + $valorIVA;

                $totals['senseIVA'] += $preuSenseIVA;
                $totals['IVA'] += $valorIVA;
                $totals['ambIVA'] += $preuAmbIVA;

                $productosSeleccionats[] = [
                    'nombre' => $nombre,
                    'id' => $id,
                    'quantitat' => $quantitat,
                    'preuSenseIVA' => number_format($preuSenseIVA, 2),
                    'valorIVA' => number_format($valorIVA, 2),
                    'preuAmbIVA' => number_format($preuAmbIVA, 2),
                ];
            }
        }
    }

    return [$productosSeleccionats, $totals];
}

// Procesar la cesta (añadir productos)
if (isset($_POST['producto'])) {
    foreach ($_POST['producto'] as $idProducto) {
        $quantitat = isset($_POST['quantitat'][$idProducto]) ? intval($_POST['quantitat'][$idProducto]) : 1;
        $productoEncontrado = false;

        // Buscar producto en la lista de productos disponibles
        foreach ($productos as $producto) {
            list($nombre, $id, $precio, $iva, $disponible) = explode('|', $producto);
            if ($id == $idProducto) {
                $precio = floatval($precio);
                $iva = floatval($iva);
                $productoEncontrado = true;
                break;
            }
        }

        if (!$productoEncontrado) {
            continue; // Si el producto no se encuentra en la lista, saltarlo
        }

        // Añadir o actualizar el producto en la cesta
        $productoEnCesta = false;
        foreach ($cestaArray as &$linea) {
            $parts = explode('|', $linea);
            if (count($parts) < 5) continue;  // Asegurarse de que hay suficientes datos

            list($nombreExistente, $idExistente, $precioExistente, $ivaExistente, $cantidadExistente) = $parts;
            
            if ($idExistente == $idProducto) {
                // Si el producto ya está en la cesta, actualizar la cantidad
                $nuevaCantidad = intval($cantidadExistente) + $quantitat;
                $nuevoPrecioTotal = $precio * $nuevaCantidad;
                $linea = "$nombre|$idProducto|$nuevoPrecioTotal|$iva|$nuevaCantidad";
                $productoEnCesta = true;
                break;
            }
        }

        // Si no se encontró el producto, añadirlo como nuevo
        if (!$productoEnCesta) {
            $precio = $precio * $quantitat;
            // Se añaden todos los detalles del producto al guardar en la cesta
            $cestaArray[] = "$nombre|$idProducto|$precio|$iva|$quantitat";
        }
    }

    // Guardar la cesta actualizada en el archivo de usuario
    file_put_contents($cestaFile, implode("\n", $cestaArray));

    // Redirigir para evitar resubir el formulario
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}



// Función para eliminar un producto de la cesta
function eliminarProducto($idProducto, $cestaArray, $cestaFile)
{
    $nuevaCestaArray = [];

    // Recorremos la cesta y eliminamos el producto con el id correspondiente
    foreach ($cestaArray as $linea) {
        $parts = explode('|', $linea);
        if (count($parts) < 5) continue; // Asegurarse de que hay suficientes datos

        list($nombre, $id, $precio, $iva, $cantidad) = $parts;

        if ($id == $idProducto) {
            $nuevaCantidad = intval($cantidad) - 1; // Reducir la cantidad en 1
            if ($nuevaCantidad > 0) {
                // Si la cantidad sigue siendo mayor a 0, actualizamos la línea
                $nuevoPrecio = floatval($precio) / intval($cantidad) * $nuevaCantidad; // Recalcular el precio
                $nuevaCestaArray[] = "$nombre|$id|$nuevoPrecio|$iva|$nuevaCantidad";
            }
            // Si la cantidad llega a 0, simplemente no se añade al array
        } else {
            // Si el ID no coincide, mantenemos el producto en la cesta
            $nuevaCestaArray[] = $linea;
        }
    }

    // Guardamos la nueva cesta sin el producto eliminado
    file_put_contents($cestaFile, implode("\n", $nuevaCestaArray));

    return $nuevaCestaArray;
}

// Procesar la eliminación de un producto
if (isset($_POST['eliminar']) && isset($_POST['idProducto'])) {
    $idProducto = $_POST['idProducto'];
    $cestaArray = eliminarProducto($idProducto, $cestaArray, $cestaFile);
}

// Borrar la cesta
if (isset($_POST['esborrar'])) {
    if (file_exists($cestaFile)) {
        unlink($cestaFile);
        $cestaArray = [];
    }
}

// Recálculo final de totales
list($productosSeleccionats, $totals) = calcularTotals($productos, $cestaArray);
$totalSenseIVA = $totals['senseIVA'];
$totalIVA = $totals['IVA'];
$totalAmbIVA = $totals['ambIVA'];

// Mostrar la tabla con los productos seleccionados
echo "<h3>Resum de la cistella: </h3>" . $_SESSION['usuario'];
echo "<table border='1'>
        <thead>
            <tr>
                <th>Nom del producte</th>
                <th>ID</th>
                <th>Quantitat</th>
                <th>Preu sense IVA</th>
                <th>IVA</th>
                <th>Preu amb IVA</th>
                <th>Acció</th>
            </tr>
        </thead>
        <tbody>";

foreach ($productosSeleccionats as $producto) {
    echo "<tr>
            <td>{$producto['nombre']}</td>
            <td>{$producto['id']}</td>
            <td>{$producto['quantitat']}</td>
            <td>{$producto['preuSenseIVA']} €</td>
            <td>{$producto['valorIVA']} €</td>
            <td>{$producto['preuAmbIVA']} €</td>
            <td>
                <form method='POST'>
                    <input type='hidden' name='idProducto' value='{$producto['id']}'>
                    <button type='submit' name='eliminar'>Eliminar</button>
                </form>
            </td>
          </tr>";
}

echo "</tbody></table>";

echo "<h3>Total de la cistella:</h3>";
echo "Total sense IVA: " . number_format($totalSenseIVA, 2) . " €<br>";
echo "IVA: " . number_format($totalIVA, 2) . " €<br>";
echo "Total amb IVA: " . number_format($totalAmbIVA, 2) . " €<br>";
echo "Data i hora: " . date("Y-m-d H:i:s") . "<br>";
?>
<form method='POST' action='comanda.php'>
    <button type='submit' <?php echo empty($productosSeleccionats) ? 'disabled' : ''; ?>>Pagar</button>
</form>

<form method='POST'>
    <button type='submit' name="esborrar">Esborrar cistella</button>
</form>

<form method="POST" action="index.php">
    <button type="submit">Volver</button>
</form>