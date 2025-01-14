<?php
session_start();

if (!isset($_SESSION['cliente'])) {
    header('Location: login.php');
    exit;
}

echo "Hola, " . $_SESSION['usuario'] . ".<br>";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Agregar producto a la cesta
    $usuario = $_SESSION['usuario'];
    $producto = $_POST['producto'];

    $cesta = file_exists("cistella/$usuario.txt") ? file_get_contents("cistella/$usuario.txt") : '';
    $cesta .= $producto . "\n";
    file_put_contents("cistella/$usuario.txt", $cesta);
    echo "Producto agregado a tu cesta.<br>";
}

// Mostrar productos del catálogo
$productos = file('productes/productes.txt', FILE_IGNORE_NEW_LINES);

echo "<h3>Catálogo de productos:</h3>";
echo "<ul>";

foreach ($productos as $producto) {
    list($nombre, $id, $precio, $iva, $disponible) = explode(';', $producto);

    echo "<li>$nombre - $precio € (IVA: $iva%) - Disponible: $disponible ";
    
    if ($disponible == 'Sí') {
        echo "<form method='POST' style='display:inline;'>
                <input type='hidden' name='producto' value='$nombre'>
                <button type='submit'>Añadir a la cesta</button>
              </form>";
    } else {
        echo "(No disponible)";
    }
    echo "</li>";
}

echo "</ul>";
?>
