<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['tipus'] != 'admin') {
    header("Location: login.php");
    exit;
}

$gestors = json_decode(file_get_contents('usuaris/gestors.json'), true);
$clients = json_decode(file_get_contents('usuaris/clients.json'), true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panell d'Administrador</title>
</head>
<body>
    <h1>Gestió d'Usuaris</h1>

    <h2>Gestors de la botiga</h2>
    <ul>
        <?php foreach ($gestors as $gestor) { ?>
            <li><?php echo htmlspecialchars($gestor['nom']); ?></li>
        <?php } ?>
    </ul>

    <h2>Clients de la botiga</h2>
    <ul>
        <?php foreach ($clients as $client) { ?>
            <li><?php echo htmlspecialchars($client['nom']); ?></li>
        <?php } ?>
    </ul>

    <a href="logout.php">Tancar sessió</a>
</body>
</html>
