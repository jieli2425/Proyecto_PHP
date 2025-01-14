<?php
session_start();

// Verificar si el formulario ha sido enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];

    // Comprobar si el archivo de usuarios existe
    if (file_exists('../usuaris/usuaris.txt')) {
        // Leer el archivo y obtener las líneas
        $usuarios = file('../usuaris/usuaris.txt', FILE_IGNORE_NEW_LINES);

        // Verificar usuario y contraseña
        foreach ($usuarios as $linea) {
            list($nombre, $correo, $pass, $tipo) = explode(';', $linea);

            // Comprobamos si el nombre de usuario y la contraseña coinciden
            if ($nombre == $usuario && password_verify($password, $pass)) {
                $_SESSION['usuario'] = $nombre;
                $_SESSION['tipo'] = $tipo;
                header('Location: index.php');
                exit;
            }
        }
    }

    // Si no se encuentra el usuario o la contraseña es incorrecta
    $error = "Usuario o contraseña incorrectos.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
    <!-- Formulario de login -->
<form method="POST">
    <label for="usuario">Usuario:</label>
    <input type="text" name="usuario" required><br><br>
    
    <label for="password">Contraseña:</label>
    <input type="password" name="password" id="password" required><br><br>

    <button type="submit">Iniciar sesión</button>
</form>

<!-- Mostrar mensaje de error en caso de fallo -->
<?php if (isset($error)) { echo '<p style="color:red;">' . $error . '</p>'; } ?>
</body>
</html>
