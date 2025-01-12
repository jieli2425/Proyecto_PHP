<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>
<body>
    <h1>Benvingut, <?php echo htmlspecialchars($user['nom']); ?>!</h1>
    <p>Tipus d'usuari: <?php echo htmlspecialchars($user['tipus']); ?></p>

    <a href="logout.php">Tancar sessió</a>

    <?php if ($user['tipus'] == 'admin') { ?>
        <a href="admin-dashboard.php">Accedir al panell d'administrador</a>
    <?php } elseif ($user['tipus'] == 'gestor') { ?>
        <a href="gestor-dashboard.php">Accedir al panell del gestor</a>
    <?php } elseif ($user['tipus'] == 'client') { ?>
        <a href="client-dashboard.php">Accedir al panell del client</a>
    <?php } ?>
</body>
</html>
