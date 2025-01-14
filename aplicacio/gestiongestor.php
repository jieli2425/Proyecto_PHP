<?php
session_start();

if ($_SESSION['tipo'] != 'admin') {
    header('Location: login.php');
    exit;
}

$archivoUsuarios = '../usuaris/usuaris.txt';

// Lògica per gestionar accions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $accion = $_POST['accion'];

    if ($accion == 'crear') {
        $nouGestor = $_POST['usuario'] . ";" . $_POST['id'] . ";" . password_hash($_POST['password'], PASSWORD_DEFAULT) . ";" . $_POST['nom'] . ";" . $_POST['email'] . ";" . $_POST['telefon'] . ";gestor\n";
        file_put_contents($archivoUsuarios, $nouGestor, FILE_APPEND);
        echo "Gestor creat correctament.";
    } elseif ($accion == 'esborrar') {
        $gestorABorrar = $_POST['usuario'];
        $gestors = file($archivoUsuarios, FILE_IGNORE_NEW_LINES);
        $gestorsActualitzats = array_filter($gestors, function($gestor) use ($gestorABorrar) {
            return strpos($gestor, $gestorABorrar . ";") !== 0;
        });
        file_put_contents($archivoUsuarios, implode("\n", $gestorsActualitzats) . "\n");
        echo "Gestor esborrat correctament.";
    }
}

// Llistar gestors
$gestors = file($archivoUsuarios, FILE_IGNORE_NEW_LINES);
usort($gestors, function($a, $b) {
    $nomA = explode(';', $a)[3];
    $nomB = explode(';', $b)[3];
    return strcmp($nomA, $nomB);
});
?>

<h3>Llista de gestors</h3>
<ul>
    <?php foreach ($gestors as $gestor): ?>
        <li><?php echo htmlspecialchars($gestor); ?></li>
    <?php endforeach; ?>
</ul>

<h3>Crear un gestor</h3>
<form method="POST">
    <input type="hidden" name="accion" value="crear">
    <label for="usuario">Nom d'usuari:</label>
    <input type="text" name="usuario" required><br>
    <label for="id">Identificador numèric:</label>
    <input type="number" name="id" required><br>
    <label for="password">Contrasenya:</label>
    <input type="password" name="password" required><br>
    <label for="nom">Nom i cognoms:</label>
    <input type="text" name="nom" required><br>
    <label for="email">Correu electrònic:</label>
    <input type="email" name="email" required><br>
    <label for="telefon">Telèfon:</label>
    <input type="text" name="telefon" required><br>
    <button type="submit">Crear gestor</button>
</form>

<h3>Esborrar un gestor</h3>
<form method="POST">
    <input type="hidden" name="accion" value="esborrar">
    <label for="usuario">Nom d'usuari:</label>
    <input type="text" name="usuario" required><br>
    <button type="submit">Esborrar gestor</button>
</form>
