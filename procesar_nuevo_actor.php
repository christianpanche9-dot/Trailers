<?php
session_start();
require_once "seguridad.php";
exigirLogin();
exigirAdmin();
require "conexion.php";
require_once "includes/funciones.php";

$nombre = $_POST["nombre"] ?? "";
$apellidos = $_POST["apellidos"] ?? "";
$nacionalidad = $_POST["nacionalidad"] ?? "";
$fecha_nacimiento = $_POST["fecha_nacimiento"] ?? "";

$foto = "sin-imagen.jpg";
$errorImagen = false;

if (!empty($_FILES["foto"]["name"]) && $_FILES["foto"]["error"] == 0) {
    $extension = strtolower(pathinfo($_FILES["foto"]["name"], PATHINFO_EXTENSION));
    $permitidas = ["jpg", "jpeg", "png", "webp"];
    $maximo = 2 * 1024 * 1024;

    if (!in_array($extension, $permitidas)) {
        $errorImagen = true;
    } elseif ($_FILES["foto"]["size"] > $maximo) {
        $errorImagen = true;
    } elseif (getimagesize($_FILES["foto"]["tmp_name"]) === false) {
        $errorImagen = true;
    } else {
        $foto = uniqid() . "." . $extension;
        move_uploaded_file($_FILES["foto"]["tmp_name"], "imagenes/" . $foto);
    }
}

$error = false;
$guardado = false;
if (!$errorImagen && $nombre != "" && $apellidos != "" && $nacionalidad != "" && $fecha_nacimiento != "") {
$guardado = insertarActor($conexion, $nombre, $apellidos, $nacionalidad, $fecha_nacimiento, $foto);
} else {
$error = true;
}
include "includes/header.php";
include "includes/menu.php";
?>

<main class="contenedor">
    <h2 class="titulo-seccion">Actor Recibido</h2>
    
    <?php if ($error): ?>
    
    <div class="alerta">
            <p>Faltan datos en el formulario. Vuelve atrás y revisa los campos.</p>
        </div>

        <a class="boton" href="listar_actores.php">Volver al formulario</a>

    <?php elseif ($guardado): ?>

        <div class="exito">
            <p>La información del actor/actriz se ha recibido correctamente.</p>
        </div>

        <article class="tarjeta">
            <img src="imagenes/<?php echo htmlspecialchars($foto); ?>" class="foto">
            <h2><?php echo htmlspecialchars($nombre); ?></h2>
            <h3><?php echo htmlspecialchars($apellidos); ?></h3>
            <p><strong>Nacionalidad:</strong> <?php echo htmlspecialchars($nacionalidad); ?></p>
            <p><strong>Fecha de Nacimiento:</strong> <?php echo htmlspecialchars($fecha_nacimiento); ?></p>
        </article>

        <a class="boton" href="index.php">Inicio</a>
        <a class="boton" href="listar_actores.php">Añadir otros Actores</a>

        <?php else: ?>
        <div class="alerta">
            <p>No se ha podido guardar la película.</p>
        </div>

        

    <?php endif; ?>
</main>

<?php include "includes/footer.php"; ?>
