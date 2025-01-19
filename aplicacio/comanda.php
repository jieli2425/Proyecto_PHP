<?php
session_start();

require_once '../vendor/autoload.php';

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

// Función para crear o actualizar el archivo de la cesta para el usuario
function crearComandaUsuario($usuario, $productosSeleccionats, $totals)
{
    // Definir la ruta del archivo de la comanda del usuario
    $comandaFile = "../comandes/$usuario.txt";  // Asegúrate de tener esta carpeta "comandes" creada

    // Crear el contenido de la comanda
    $contenido = "Factura per al usuari: $usuario\n";
    $contenido .= "Data: " . date("Y-m-d H:i:s") . "\n\n";

    $contenido .= "Productes seleccionats:\n";
    foreach ($productosSeleccionats as $producto) {
        $contenido .= "Nom: {$producto['nombre']}, ID: {$producto['id']}, Quantitat: {$producto['quantitat']}, ";
        $contenido .= "Preu sense IVA: {$producto['preuSenseIVA']} €, IVA: {$producto['valorIVA']} €, Preu amb IVA: {$producto['preuAmbIVA']} €\n";
    }

    $contenido .= "\nTotal sense IVA: " . number_format($totals['senseIVA'], 2) . " €\n";
    $contenido .= "IVA: " . number_format($totals['IVA'], 2) . " €\n";
    $contenido .= "Total amb IVA: " . number_format($totals['ambIVA'], 2) . " €\n";

    // Guardar el archivo
    file_put_contents($comandaFile, $contenido);
}

function borrar($usuario) {
    $comandaFile = "../comandes/$usuario.txt"; // Ruta del archivo de la comanda
    if (file_exists($comandaFile)) {
        return unlink($comandaFile); // Eliminar el archivo y devolver true si tiene éxito
    }
    return false; // Si el archivo no existe, devolver false
}

if (isset($_POST['borrarComanda'])) {
    $usuario = $_SESSION['usuario'];
    $borrado = borrar($usuario);

    // Redirigir a la página "cistella.php" después de borrar la comanda
    header('Location: cistella.php');
    exit;
}

// Generar PDF si se hace clic en "Visualitzar Comanda (PDF)"
if (isset($_POST['generarPDF'])|| isset($_POST['crear_comanda'])) {
    // Crear el archivo de la comanda para el usuario
    crearComandaUsuario($usuario, $productosSeleccionats, $totals);

    $html = "<h1>Factura</h1>";
    $html .= "<p><strong>Usuari:</strong> $usuario</p>";
    $html .= "<p><strong>Data:</strong> " . date("Y-m-d H:i:s") . "</p>";

    $html .= "<table border='1' cellpadding='5' cellspacing='0'>";
    $html .= "<thead><tr><th>Nom del producte</th><th>ID</th><th>Quantitat</th><th>Preu sense IVA</th><th>IVA</th><th>Preu amb IVA</th></tr></thead><tbody>";

    foreach ($productosSeleccionats as $producto) {
        $html .= "<tr>
            <td>{$producto['nombre']}</td>
            <td>{$producto['id']}</td>
            <td>{$producto['quantitat']}</td>
            <td>{$producto['preuSenseIVA']} €</td>
            <td>{$producto['valorIVA']} €</td>
            <td>{$producto['preuAmbIVA']} €</td>
        </tr>";
    }

    $html .= "</tbody></table>";
    $html .= "<h3>Total de la cistella:</h3>";
    $html .= "<p>Total sense IVA: " . number_format($totalSenseIVA, 2) . " €</p>";
    $html .= "<p>IVA: " . number_format($totalIVA, 2) . " €</p>";
    $html .= "<p>Total amb IVA: " . number_format($totalAmbIVA, 2) . " €</p>";

    // Crear el PDF
    $dompdf = new Dompdf\Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Enviar el PDF al navegador
    $dompdf->stream("comanda.pdf", array("Attachment" => false));
    exit;
}

?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestió de la Comanda</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 20px;
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        table th {
            background-color: #f2f2f2;
        }
        .btn-group {
            text-align: center;
            margin-top: 20px;
        }
        button {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 5px;
            font-size: 16px;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Factura</h1>
        <p><strong>Usuari:</strong> <?php echo htmlspecialchars($usuario); ?></p>
        <p><strong>Data:</strong> <?php echo date("Y-m-d H:i:s"); ?></p>

        <table>
            <thead>
                <tr>
                    <th>Nom del producte</th>
                    <th>ID</th>
                    <th>Quantitat</th>
                    <th>Preu sense IVA</th>
                    <th>IVA</th>
                    <th>Preu amb IVA</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($productosSeleccionats as $producto) {
                    echo "<tr>
                    <td>{$producto['nombre']}</td>
                    <td>{$producto['id']}</td>
                    <td>{$producto['quantitat']}</td>
                    <td>{$producto['preuSenseIVA']} €</td>
                    <td>{$producto['valorIVA']} €</td>
                    <td>{$producto['preuAmbIVA']} €</td>
                  </tr>";
                }
                ?>
            </tbody>
        </table>

        <h3>Total de la cistella:</h3>
        <p>Total sense IVA: <?php echo number_format($totalSenseIVA, 2); ?> €</p>
        <p>IVA: <?php echo number_format($totalIVA, 2); ?> €</p>
        <p>Total amb IVA: <?php echo number_format($totalAmbIVA, 2); ?> €</p>
        <p>Data i hora: <?php echo date("Y-m-d H:i:s"); ?></p>

        <div class="btn-group">
            <form method="POST" style="display: inline;">
                <button type="submit" name="crear_comanda">Crear Comanda</button>
                <button type="submit" name="generarPDF">GenerarPDF</button>
                <button type="submit" name="borrarComanda">Eliminar Comanda</button>
            </form>
            <form method="POST" action="index.php" style="display: inline;">
                <button type="submit">Tornar</button>
            </form>
        </div>
    </div>
</body>
</html>