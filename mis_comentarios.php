<?php
session_start();
require_once "seguridad.php";
exigirLogin();
require "conexion.php";
require_once "includes/funciones.php";

$resultado = obtenerComentariosPorUsuario($conexion, $_SESSION["id_usuario"]);

include "includes/header.php";
include "includes/menu.php";
?>

<main class="contenedor">
    <h2 class="titulo-seccion">Mis comentarios</h2>

    <div class="grid-peliculas">
        <?php while ($comentario = $resultado->fetch_assoc()) { ?>
            <article class="tarjeta">
                <h3><?php echo htmlspecialchars($comentario["titulo"]); ?></h3>

                <?php if ($comentario["estado"] !== "publicado") { ?>
                    <span class="etiqueta etiqueta-pendiente">Pendiente de aprobación</span>
                <?php } ?>

                <?php if ($comentario["spoiler"]) { ?>
                    <span class="etiqueta etiqueta-spoiler">Contiene spoilers</span>
                <?php } ?>

                <p class="comentario-texto"><?php echo nl2br(htmlspecialchars($comentario["comentario"])); ?></p>

                <p>
                    <small>Publicado: <?php echo htmlspecialchars($comentario["fecha_comentario"]); ?></small>
                    <?php if ($comentario["fecha_modificacion"]) { ?>
                        <small>(editado)</small>
                    <?php } ?>
                </p>

                <div class="acciones-comentario">
                    <a class="boton-secundario" href="pelicula.php?id=<?php echo (int) $comentario["id_peliculas"]; ?>">Ver película</a>
                    <a class="boton-secundario" href="editar_comentario.php?id=<?php echo (int) $comentario["id_comentario"]; ?>">Editar</a>

                    <form method="POST" action="borrar_comentario.php"
                          onsubmit="return confirm('¿Borrar este comentario?')">
                        <input type="hidden" name="id_comentario" value="<?php echo (int) $comentario["id_comentario"]; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generarTokenCSRF()); ?>">
                        <button class="boton" type="submit">Borrar</button>
                    </form>
                </div>
            </article>
        <?php } ?>
    </div>
</main>

<?php include "includes/footer.php"; ?>
