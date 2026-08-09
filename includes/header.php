
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movie Trailers</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>

<header>
    <h1> 🎬Movie Trailers</h1>
    <p>Conoce los últimos trailers de nuestra App</p>
    <?php if (isset($_SESSION["id_usuario"])): ?>
    <p>Hola <?php echo htmlspecialchars($_SESSION["nombre"]); ?> (<?php echo htmlspecialchars($_SESSION["rol"]); ?>)</p>
    <?php else: ?>
        <?php $paginaActual = basename($_SERVER["PHP_SELF"]); ?>
        <?php if (!in_array($paginaActual, ["login.php", "registro_usuarios.php"])): ?>
        <div class="auth-botones">
            <a href="login.php" class="boton-login">Iniciar sesión</a>
            <a href="registro_usuarios.php" class="boton-registro">Registrarse</a>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</header>
