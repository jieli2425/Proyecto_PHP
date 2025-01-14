<?php
session_start();

// Verificar si l'usuari és admin
if ($_SESSION['tipo'] != 'admin') {
    header('Location: login.php');
    exit;
}

// Ruta del fitxer d'usuaris
$archivoUsuarios = '../usuaris/usuaris.txt';

// Obtenir l'usuari actual
$usuarioActual = $_SESSION['usuario'];
$usuarios = file($archivoUsuarios, FILE_IGNORE_NEW_LINES);

// Si el formulari es va enviar, processar la modificació
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nouUsuario = $_POST['usuario'];
    $nouEmail = $_POST['correo'];
    $nouPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Actualitzar l'usuari al fitxer
    foreach ($usuarios as $index => $usuari) {
        list($nom, $email, $password, $tipo) = explode(';', $usuari);

        if ($nom == $usuarioActual) {
            $usuarios[$index] = "$nouUsuario;$nouEmail;$nouPassword;admin";
            $_SESSION['usuario'] = $nouUsuario; // Actualitzar sessió
            break;
        }
    }

    // Escriure les dades actualitzades al fitxer
    file_put_contents($archivoUsuarios, implode("\n", $usuarios) . "\n");
    echo "Perfil actualitzat correctament.";
}

?>

<h3>Modificar el meu perfil</h3>
<form method="POST">
    <label for="usuario">Nou nom d'usuari:</label>
    <input type="text" name="usuario" required><br>

    <label for="correo">Nou correu electrònic:</label>
    <input type="email" name="correo" required><br>

    <label for="password">Nova contrasenya:</label>
    <input type="password" name="password" required><br>

    <button type="submit">Modificar</button>
</form>

<form method="POST" action="index.php">
    <button type="submit"></button>
</form>

<form method="POST" action="index.php">
    <button type="submit">Tornar</button>
</form>