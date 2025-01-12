<?php
// Configuració per a la connexió a la base de dades
$host = 'localhost';
$db = 'botiga';
$user = 'root';
$pass = '';

// Connexió
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("No s'ha pogut connectar a la base de dades: " . $e->getMessage());
}
?>