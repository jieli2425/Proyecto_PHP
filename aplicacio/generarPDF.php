<?php
require '..//vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

session_start();

if ($_SESSION['tipo'] != 'admin') {
    header('Location: login.php');
    exit;
}

// Verificar si se ha enviado el parámetro 'tipo'
if (!isset($_GET['tipo']) || !in_array($_GET['tipo'], ['gestor', 'cliente'])) {
    die('Tipo de PDF no válido.');
}
$tipo = $_GET['tipo'];

// Configurar Dompdf
$options = new Options();
$dompdf = new Dompdf($options);

// Archivo con los datos de usuarios
$archivoUsuarios = '..//usuaris/usuaris.txt';

// Función para obtener datos según el tipo (gestor o cliente)
function obtenerDatos($fitxer, $tipo) {
    $usuaris = file($fitxer, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $resultado = [];
    
    foreach ($usuaris as $usuari) {
        $camps = explode(';', $usuari);
        $rol = $camps[3] ?? null;

        if ($rol === $tipo) {
            if ($tipo === 'gestor' && count($camps) >= 8) {
                $resultado[] = [
                    'usuario' => $camps[0],
                    'id' => $camps[1],
                    'nom' => $camps[4] ?? '',
                    'cognoms' => $camps[5] ?? '',
                    'correo' => $camps[6] ?? '',
                    'telefon' => $camps[7] ?? ''
                ];
            } elseif ($tipo === 'cliente' && count($camps) >= 9) {
                $resultado[] = [
                    'usuario' => $camps[0],
                    'id' => $camps[1],
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
    return $resultado;
}

// Obtener los datos según el tipo solicitado
$usuarios = obtenerDatos($archivoUsuarios, $tipo);

// Generar contenido HTML para el PDF
$html = '<h1>Lista de ' . ucfirst($tipo) . 's</h1>';
$html .= '<table border="1" cellpadding="10" cellspacing="0">';
$html .= '<thead>
            <tr>
                <th>Usuario</th>
                <th>ID</th>
                <th>Nombre</th>
                <th>Apellidos</th>
                <th>Correo</th>
                <th>Teléfono</th>';
if ($tipo === 'cliente') {
    $html .= '<th>Dirección</th>
              <th>Gestor Asignado</th>';
}
$html .= '</tr>
          </thead>';
$html .= '<tbody>';

foreach ($usuarios as $usuario) {
    $html .= '<tr>
                <td>' . $usuario['usuario'] . '</td>
                <td>' . $usuario['id'] . '</td>
                <td>' . $usuario['nom'] . '</td>
                <td>' . $usuario['cognoms'] . '</td>
                <td>' . $usuario['correo'] . '</td>
                <td>' . $usuario['telefon'] . '</td>';
    if ($tipo === 'cliente') {
        $html .= '<td>' . $usuario['adreça'] . '</td>
                  <td>' . $usuario['gestor_assignat'] . '</td>';
    }
    $html .= '</tr>';
}

$html .= '</tbody>';
$html .= '</table>';

// Cargar el contenido HTML en Dompdf
$dompdf->loadHtml($html);

// Renderizar el PDF
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Forzar descarga del PDF
$nombreArchivo = 'lista_' . $tipo . 's.pdf';
$dompdf->stream($nombreArchivo, ['Attachment' => true]);
exit;
?>