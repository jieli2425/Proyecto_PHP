<?php
session_start();

if (isset($_SESSION['user'])) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST['nom'];
    $contrasenya = $_POST['contrasenya'];

    $usuaris = json_decode(file_get_contents('usuaris/usuaris.json'), true);

    foreach ($usuaris as $usuari) {
        if ($usuari['nom'] == $nom && $usuari['contrasenya'] == $contrasenya) {
            $_SESSION['user'] = $usuari;
            header("Location: dashboard.php");
            exit;
        }
    }

    $error = "Nom d'usuari o contrasenya incorrecte";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <div class="form-login">
        <form method="POST" action="">
            <h1>LOGIN</h1>
            <div class="caja-input">
                <input type="text" name="nom" placeholder="Nom d'usuari" required>
                <img src="./img/usuario.png" alt="Usuari">
            </div>
            <div class="caja-input">
                <input type="password" name="contrasenya" placeholder="Contrasenya" required>
                <img src="./img/cerradura.png" alt="Contrasenya">
            </div>
            <?php if (isset($error)) { echo "<p style='color:red;'>$error</p>"; } ?>
            <button type="submit" class="btn">Accedir</button>
        </form>
    </div>
</body>
</html>