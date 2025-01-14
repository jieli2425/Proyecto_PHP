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
    list($nombre, $email, $password, $tipo) = explode(';', $usuarioData);
    if ($usuario == $nombre) {
        $datosUsuario = [
            'nombre' => $nombre,
            'email' => $email,
            'tipo' => $tipo
        ];
        break;
    }
}

if ($datosUsuario) {
    // Mostrar los datos personales del usuario
    echo "<h3>Les teves dades personals:</h3>";
    echo "Nom: " . $datosUsuario['nombre'] . "<br>";
    echo "Correu electrònic: " . $datosUsuario['email'] . "<br>";
    echo "Tipus d'usuari: " . ucfirst($datosUsuario['tipo']) . "<br><br>";
?>
    
<?php
} else {
    echo "No es van trobar els teus dades.";
}

?>
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
