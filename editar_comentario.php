<?php
session_start();
require_once "seguridad.php";
exigirLogin();
require "conexion.php";
require_once "includes/funciones.php";

$id_comentario = (int) ($_GET["id"] ?? 0);
$comentario = obtenerComentarioPorId($conexion, $id_comentario);

if (!$comentario) {
    die("Comentario no encontrado.");
}

if ((int) $comentario["id_usuarios"] !== (int) $_SESSION["id_usuario"] && !esAdmin()) {
    header("Location: index.php?acceso=denegado");
    exit();
}

include "includes/header.php";
include "includes/menu.php";
?>

<main class="contenedor">
    <h2 class="titulo-seccion">Editar comentario</h2>

    <form class="formulario" method="POST" action="actualizar_comentario.php">
        <input type="hidden" name="id_comentario" value="<?php echo (int) $comentario["id_comentario"]; ?>">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generarTokenCSRF()); ?>">

        <div class="campo">
            <label for="comentario">Comentario</label>
            <textarea name="comentario" id="comentario" rows="6" maxlength="2000" required><?php echo htmlspecialchars($comentario["comentario"]); ?></textarea>
        </div>

        <label class="opcion-spoiler">
            <input type="checkbox" name="spoiler" value="1" <?php echo $comentario["spoiler"] ? "checked" : ""; ?>>
            Mi comentario contiene spoilers
        </label>

        <br>
        <button class="boton" type="submit">Guardar cambios</button>
        <a class="boton-secundario" href="pelicula.php?id=<?php echo (int) $comentario["id_peliculas"]; ?>">Cancelar</a>
    </form>
</main>

<?php include "includes/footer.php"; ?>
