<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

echo "Hola, " . $_SESSION['usuario'] . " ";

if ($_SESSION['tipo'] == 'admin') {
    echo "<a href='admin.php'>Administración</a>", " ";
    echo "<a href='../usuaris/usuaris.txt'>Ver Txt</a>", " ";
    echo "<a href='logout.php'>Cerrar sesión</a>";
} elseif ($_SESSION['tipo'] == 'gestor') {
    echo "<a href='gestorProductos.php'>Gestionar productos</a>", " ";
    echo "<a href='gestorCorreo.php'>Gestionar Correos</a>", " ";
    echo "<a href='gestortramitacion.php'>Gestionar Comandas</a>", " ";
    echo "<a href='logout.php'>Cerrar sesión</a>";
} elseif ($_SESSION['tipo'] == 'cliente') {
    echo "<a href='cliente.php'>Mi cuenta</a> ";
    echo "<a href='logout.php'>Cerrar sesión</a>";
    // Mostrar productos directamente si el usuario es cliente
    $productos = file('../productes/productes.txt', FILE_IGNORE_NEW_LINES);

    echo "<h3>Selecciona los productos que quieres comprar:</h3>";
    echo "<form method='POST' action='cistella.php'>";

    echo "<table border='1'>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>ID</th>
                    <th>Preu</th>
                    <th>Quantitat</th>
                    <th>Afegir a la cistella</th>
                </tr>
            </thead>
            <tbody>";

    foreach ($productos as $producto) {
        list($nombre, $id, $precio, $iva, $disponible) = explode('|', $producto);
        echo "<tr>";
        echo "<td>$nombre</td>";
        echo "<td>$id</td>";
        echo "<td>$precio €</td>";

        if ($disponible == 'Sí') {
            echo "<td><input type='number' name='quantitat[$id]' min='1' value='1'></td>";
            echo "<td><input type='checkbox' name='producto[]' value='$id'></td>";
        } else {
            echo "<td>No disponible</td><td></td>";
        }
        echo "</tr>";
    }

    echo "</tbody></table> <br>";
    echo "<button type='submit'>Ir a la cesta</button>";
    echo "</form>";
}
?>
