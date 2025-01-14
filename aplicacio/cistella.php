<?php
session_start();

if ($_SESSION['tipo'] != 'cliente') {
    header('Location: login.php');
    exit;
}

$productos = file('..//productes/productes.txt', FILE_IGNORE_NEW_LINES);
$usuario = $_SESSION['usuario'];

$totalSenseIVA = 0;
$totalIVA = 0;
$totalAmbIVA = 0;
$productosSeleccionats = [];
$cestaFile = "cistella/$usuario.txt";

// Comprovem si el fitxer de la cistella existeix
if (file_exists($cestaFile)) {
    // Llegim el contingut de la cistella
    $cesta = file_get_contents($cestaFile);
} else {
    $cesta = "La teva cistella està buida.";
}

// Si s'ha enviat la sol·licitud per esborrar la cistella
if (isset($_POST['esborrar'])) {
    if (file_exists($cestaFile)) {
        // Esborrem el fitxer de la cistella
        unlink($cestaFile);
        $cesta = "La teva cistella ha estat esborrada correctament.";
    } else {
        $cesta = "No es pot esborrar, la cistella ja està buida.";
    }
}
if (isset($_POST['producto'])) {
    foreach ($_POST['producto'] as $idProducto) {
        $quantitat = isset($_POST['quantitat'][$idProducto]) ? $_POST['quantitat'][$idProducto] : 1;
        foreach ($productos as $producto) {
            list($nombre, $id, $precio, $iva, $disponible) = explode('|', $producto);
            if ($id == $idProducto) {
                $preuSenseIVA = $precio * $quantitat;
                $valorIVA = ($iva / 100) * $preuSenseIVA;
                $preuAmbIVA = $preuSenseIVA + $valorIVA;
                
                $totalSenseIVA += $preuSenseIVA;
                $totalIVA += $valorIVA;
                $totalAmbIVA += $preuAmbIVA;

                $productosSeleccionats[] = [
                    'nombre' => $nombre,
                    'quantitat' => $quantitat,
                    'preuSenseIVA' => $preuSenseIVA,
                    'valorIVA' => $valorIVA,
                    'preuAmbIVA' => $preuAmbIVA
                ];
            }
        }
    }
}

echo "<h3>Resum de la cistella:</h3>";
echo "<table border='1'>
        <thead>
            <tr>
                <th>Nom del producte</th>
                <th>Quantitat</th>
                <th>Preu sense IVA</th>
                <th>IVA</th>
                <th>Preu amb IVA</th>
            </tr>
        </thead>
        <tbody>";

foreach ($productosSeleccionats as $producto) {
    echo "<tr>
            <td>{$producto['nombre']}</td>
            <td>{$producto['quantitat']}</td>
            <td>{$producto['preuSenseIVA']} €</td>
            <td>{$producto['valorIVA']} €</td>
            <td>{$producto['preuAmbIVA']} €</td>
          </tr>";
}

echo "</tbody></table>";

echo "<h3>Total de la cistella:</h3>";
echo "Total sense IVA: $totalSenseIVA €<br>";
echo "IVA: $totalIVA €<br>";
echo "Total amb IVA: $totalAmbIVA €<br>";
echo "Data i hora: " . date("Y-m-d H:i:s") . "<br>";
?>
<form method='POST' action='comanda.php'>
    <button type='submit'>Pagar</button>
</form>

<form method='POST'>
    <button type='submit' name="esborrar">Esborrar cistella</button>
</form>

<form method="POST" action="index.php">
    <button type="submit">Volver</button>
</form>