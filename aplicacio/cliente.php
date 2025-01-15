<?php
session_start();

if ($_SESSION['tipo'] != 'cliente') {
    header('Location: login.php');
    exit;
}

// Ruta del archivo donde se guardan los datos de los usuarios
$archivoUsuarios = "../usuaris/usuaris.txt";
$usuario = $_SESSION['usuario'];

// Leer todos los usuarios desde el archivo
$usuarios = file($archivoUsuarios, FILE_IGNORE_NEW_LINES);

// Buscar los datos del usuario actual
$datosUsuario = null;
foreach ($usuarios as $usuarioData) {
    $campos = explode(';', $usuarioData);
    if ($usuario == $campos[0]) {
        $datosUsuario = [
            'usuario' => $campos[0],
            'id' => $campos[1],
            'nom' => $campos[4],
            'cognoms' => $campos[5],
            'correo' => $campos[6],
            'telefon' => $campos[7],
            'adreça' => $campos[8],
            'gestor_assignat' => $campos[9]
        ];
        break;
    }
}

if ($datosUsuario) {
    // Mostrar los datos personales del usuario
    echo "<h3>Les teves dades personals:</h3>";
    echo "Nom d'usuari: " . $datosUsuario['usuario'] . "<br>";
    echo "ID: " . $datosUsuario['id'] . "<br>";
    echo "Nom: " . $datosUsuario['nom'] . "<br>";
    echo "Cognoms: " . $datosUsuario['cognoms'] . "<br>";
    echo "Correu electrònic: " . $datosUsuario['correo'] . "<br>";
    echo "Telèfon: " . $datosUsuario['telefon'] . "<br>";
    echo "Adreça: " . $datosUsuario['adreça'] . "<br>";
    echo "Gestor assignat: " . $datosUsuario['gestor_assignat'] . "<br><br>";
} else {
    echo "No es van trobar els teus dades.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
        <!-- Formulari per demanar la modificació o l'esborrament del compte -->
<h3>Modificar o esborrar el teu compte</h3>
<form method="POST" action="enviar_correo.php">
    <textarea name="contenido" placeholder="Escriu el teu missatge aquí..." required></textarea><br>
    <button type="submit" name="accion" value="modificar">Petició de modificació/esborrament del compte</button>
</form>
    
    <!-- Formulari per demanar la justificació de comanda rebutjada -->
<h3>Justificació de comanda rebutjada</h3>
<form method="POST" action="enviar_correo.php">
    <textarea name="contenido" placeholder="Escriu el teu missatge aquí..." required></textarea><br>
    <button type="submit" name="accion" value="justificacio">Petició de justificació de comanda rebutjada</button>
</form>

<form method="POST" action="index.php">
    <button type="submit">Volver</button>
</form>
</body>
</html>
