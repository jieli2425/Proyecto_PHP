<?php
require '../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

session_start();

// Verificar si el usuario tiene acceso
if ($_SESSION['tipo'] != 'gestor') {
    header('Location: login.php');
    exit;
}

// Archivo de productos
$archivoProductos = '../../productes/productes.txt';

// Leer los productos
$productos = file_exists($archivoProductos) ? file($archivoProductos, FILE_IGNORE_NEW_LINES) : [];

// Configurar Dompdf
$options = new Options();
$dompdf = new Dompdf($options);

// Crear contenido HTML para el PDF
$html = '<h1>Lista de Productos</h1>';
$html .= '<table border="1" cellpadding="10" cellspacing="0">';
$html .= '<thead>
            <tr>
                <th>Nombre</th>
                <th>ID</th>
                <th>Precio (€)</th>
                <th>IVA (%)</th>
                <th>Disponible</th>
            </tr>
          </thead>';
$html .= '<tbody>';

foreach ($productos as $producto) {
    list($nombre, $id, $precio, $iva, $disponible) = explode('|', $producto);
    $html .= "<tr>
                <td>" . htmlspecialchars($nombre) . "</td>
                <td>" . htmlspecialchars($id) . "</td>
                <td>" . htmlspecialchars($precio) . "</td>
                <td>" . htmlspecialchars($iva) . "</td>
                <td>" . htmlspecialchars($disponible) . "</td>
              </tr>";
}

$html .= '</tbody>';
$html .= '</table>';

// Cargar el contenido HTML en Dompdf
$dompdf->loadHtml($html);

// Configurar tamaño de papel y orientación
$dompdf->setPaper('A4', 'portrait');

// Renderizar el PDF
$dompdf->render();

// Descargar el archivo PDF
$dompdf->stream('lista_productos.pdf', ['Attachment' => true]);
exit;
?>