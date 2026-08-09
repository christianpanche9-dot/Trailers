<?php
session_start();
include "includes/header.php";

if (isset($_SESSION["id_usuario"])) {
    header("Location: index.php");
    exit();
}
?>

<body>
    <div class="contenedor">
    <form action="validar_login.php" method="POST" class="formulario">
        <h1>Iniciar sesión</h1>
        <div class="campo">
            <label>Correo electrónico</label>
            <input type="email" name="email" required>
        </div>
        <div class="campo">
            <label>Contraseña</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit" class="boton">Iniciar sesión</button>
        <a href="registro_usuarios.php" class="boton-iniciar">Registrarse</a>
    </form>
    
</div>
</body>
</html>
