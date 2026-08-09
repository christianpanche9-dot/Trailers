<?php
session_start();
require_once "seguridad.php";
exigirLogin();
exigirAdmin();
require "conexion.php";
include_once "includes/funciones.php";

$titulo = $_POST["titulo"] ?? "";
$director = $_POST["director"] ?? "";
$anio = $_POST["anio"] ?? "";
$categoria = $_POST["categoria"] ?? "";
$link = convertirYoutubeEmbed($_POST["link"] ?? "");

$imagen = "";
$errorImagen = false;

if (!empty($_FILES["imagen"]["name"]) && $_FILES["imagen"]["error"] == 0) {
    $extension = strtolower(pathinfo($_FILES["imagen"]["name"], PATHINFO_EXTENSION));
    $permitidas = ["jpg", "jpeg", "png", "webp"];
    $maximo = 2 * 1024 * 1024;

    if (!in_array($extension, $permitidas)) {
        $errorImagen = true;
    } elseif ($_FILES["imagen"]["size"] > $maximo) {
        $errorImagen = true;
    } elseif (getimagesize($_FILES["imagen"]["tmp_name"]) === false) {
        $errorImagen = true;
    } else {
        $imagen = uniqid() . "." . $extension;
    }
}

$error = false;
$guardado = false;

if (!$errorImagen && $titulo != "" && $director != "" && $anio != "" && $categoria != "" && $link != "" && $imagen != "") {
    $id_pelicula = insertarPelicula($conexion, $titulo, $director, $anio, $categoria, $link, $imagen);
    move_uploaded_file(
    $_FILES["imagen"]["tmp_name"],
    "imagenes/" . $imagen
    );
    if ($id_pelicula) {
        $guardado = true;

        if (isset($_POST["actores"])) {
            foreach ($_POST["actores"] as $id_actor) {
                $personaje = $_POST["personaje"][$id_actor] ?? "";
                insertarActorPelicula($conexion, $id_pelicula, $id_actor,$personaje);
            }
        }
    }

} else {
    $error = true;

}


include "includes/header.php";
include "includes/menu.php";
?>

<main class="contenedor">
    <h2 class="titulo-seccion">Pelicula recibida</h2>
    
    <?php if ($error): ?>
    
    <div class="alerta">
            <p>Faltan datos en el formulario. Vuelve atrás y revisa los campos.</p>
        </div>

        <a class="boton" href="index.php">Volver al formulario</a>

    <?php elseif ($guardado): ?>

        <div class="exito">
            <p>La película se ha recibido correctamente.</p>
        </div>

        <article class="tarjeta">
            <h3><?php echo htmlspecialchars($titulo); ?></h3>
            <p><strong>Director:</strong> <?php echo htmlspecialchars($director); ?></p>
            <p><strong>Anio:</strong> <?php echo htmlspecialchars($anio); ?></p>
            <p><strong>Categoría:</strong> <?php echo htmlspecialchars($categoria); ?></p>
            <p><strong>Link:</strong> <?php echo htmlspecialchars($link); ?></p>
            <p><strong>imagen:</strong> <?php echo htmlspecialchars($imagen); ?></p>
        </article>

        <a class="boton" href="index.php">Inicio</a>
        <a class="boton" href="nuevo.php">Añadir otra película</a>

        <?php else: ?>
        <div class="alerta">
            <p>No se ha podido guardar la película.</p>
        </div>

        

    <?php endif; ?>
</main>

<?php include "includes/footer.php"; ?>
