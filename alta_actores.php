<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "seguridad.php";
exigirLogin();
exigirAdmin();
include_once "conexion.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Alta de Actores</title>
</head>
<body>
<h1>Añadir nuevo actor</h1>
<form action="procesar_nuevo_actor.php" method="POST" class="formulario" enctype="multipart/form-data">
    <div class="campo">
        <label for="nombre">Nombre:</label><br>
        <input type="text" name="nombre" id="nombre" required><br><br>
    </div>
    <div class="campo">
        <label for="apellidos">Apellidos:</label><br>
        <input type="text" name="apellidos" id="apellidos"><br><br>
    </div>
    <div class="campo">
        <label for="nacionalidad">Nacionalidad:</label><br>
        <input type="text" name="nacionalidad" id="genero"><br><br>
    </div>
    <div class="campo">
        <label for="fecha_nacimiento">Fecha de nacimiento:</label><br>
        <input type="date" name="fecha_nacimiento" id="fecha_nacimiento"><br><br>
    </div>
    <div class="campo">
        <label for="foto">Foto:</label><br>
        <input type="file" name="foto" id="foto"><br><br>
    </div>
    <button type="submit" class="boton">Guardar actor</button>
</form>
</body>

</html>