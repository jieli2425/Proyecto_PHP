<?php
session_start();

if ($_SESSION['tipo'] != 'admin') {
    header('Location: login.php');
    exit;
}

$archivoUsuarios = '../usuaris/usuaris.txt';
$archivoMensajes = '../registro_correos.txt'; // Ruta al archivo de mensajes

// Función para obtener usuarios según el tipo
function obtenirUsuaris($fitxer, $tipo) {
    $usuaris = file($fitxer, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $resultat = [];
    
    foreach ($usuaris as $usuari) {
        $camps = explode(';', $usuari);
        $rol = $camps[3] ?? null;

        if ($rol === $tipo) {
            if ($tipo === 'admin') {
                $resultat[] = [
                    'usuario' => $camps[0],
                    'correo' => $camps[1],
                    'password' => $camps[2]
                ];
            } elseif ($tipo === 'gestor' && count($camps) >= 8) {
                $resultat[] = [
                    'usuario' => $camps[0],
                    'id' => $camps[1],
                    'password' => $camps[2],
                    'nom' => $camps[4] ?? '',
                    'cognoms' => $camps[5] ?? '',
                    'correo' => $camps[6] ?? '',
                    'telefon' => $camps[7] ?? ''
                ];
            } elseif ($tipo === 'cliente' && count($camps) >= 9) {
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

// Función para mostrar los mensajes enviados
function mostrarMensajes($archivoMensajes) {
    if (file_exists($archivoMensajes)) {
        $mensajes = file($archivoMensajes, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($mensajes as $mensaje) {
            $datosMensaje = explode(' | ', $mensaje);
            
            // Extraer datos específicos
            $correoEnviado = isset($datosMensaje[1]) ? explode(': ', $datosMensaje[1])[1] : 'Desconocido';
            $destinatario = isset($datosMensaje[2]) ? explode(': ', $datosMensaje[2])[1] : 'Desconocido';
            $asunto = isset($datosMensaje[3]) ? explode(': ', $datosMensaje[3])[1] : 'Sin asunto';
            $contenido = isset($datosMensaje[4]) ? explode(': ', $datosMensaje[4])[1] : 'No hay contenido';

            echo "<p><strong>Correo Enviado:</strong> $correoEnviado</p>";
            echo "<p><strong>Destinatario:</strong> $destinatario</p>";
            echo "<p><strong>Asunto:</strong> $asunto</p>";
            echo "<p><strong>Mensaje:</strong> $contenido</p>";
            echo "<hr>";
        }
    } else {
        echo "<p>No se han encontrado mensajes.</p>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accio'])) {
        // Aquí van las acciones para crear/editar/eliminar gestores y clientes, etc.
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Mostrar datos del admin
$admin = obtenirUsuaris($archivoUsuarios, 'admin')[0] ?? null;
if ($admin) {
    echo "<h3>Administrador</h3>";
    echo "Usuario: {$admin['usuario']}<br>";
    echo "Correo: {$admin['correo']}<br>";
    echo "<hr>";
}

// Mostrar lista de gestores
$gestors = obtenirUsuaris($archivoUsuarios, 'gestor');
echo "<h3>Lista de Gestores</h3>";
foreach ($gestors as $gestor) {
    echo "Usuario: {$gestor['usuario']}<br>";
    echo "ID: {$gestor['id']}<br>";
    echo "Nombre: {$gestor['nom']}<br>";
    echo "Apellidos: {$gestor['cognoms']}<br>";
    echo "Correo: {$gestor['correo']}<br>";
    echo "Teléfono: {$gestor['telefon']}<br>";
    echo "<hr>";
}

// Mostrar lista de clientes
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

echo "<h3>Mensajes Enviados</h3>";
mostrarMensajes($archivoMensajes);
?>
