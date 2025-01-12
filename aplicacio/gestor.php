<?php
session_start();

// Comprovar si l'usuari és gestor
if (!isset($_SESSION['user']) || $_SESSION['user']['tipus'] != 'gestor') {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];

// Carregar clients des del fitxer
$clients = json_decode(file_get_contents('usuaris/clients.json'), true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panell del Gestor</title>
</head>
<body>
    <h1>Panell del Gestor</h1>
    <p>Benvingut, <?php echo htmlspecialchars($user['nom']); ?>!</p>

    <h2>Llista de Clients</h2>
    <ul>
        <?php foreach ($clients as $client) { ?>
            <li><?php echo htmlspecialchars($client['nom_complet']); ?></li>
        <?php } ?>
    </ul>

    <h2>Envia una petició a l'administrador</h2>
    <form action="enviar_peticio.php" method="POST">
        <label for="peticio">Descripció de la petició:</label><br>
        <textarea name="peticio" rows="4" cols="50" required></textarea><br>
        <input type="submit" value="Enviar petició">
    </form>

    <a href="logout.php">Tancar sessió</a>
</body>
</html>