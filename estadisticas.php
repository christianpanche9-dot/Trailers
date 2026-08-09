<?php
session_start();

require_once "seguridad.php";
exigirLogin();
exigirAdmin();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit();
}

require "conexion.php";
require_once "includes/funciones.php";

$resultadoTop = obtenerTop3PeliculasPopulares($conexion);
$resultadoReciente = obtenerPeliculaMasReciente($conexion);
$resultadoAntigua = obtenerPeliculaMasAntigua($conexion);

include "includes/header.php";
include "includes/menu.php";
?>

<main class="contenedor">
    <h2 class="titulo-seccion">Estadísticas de los Trailers</h2>

    <section class="estadisticas">

        <article class="caja-estadistica">
            <h3>Película más antigua</h3>
            <?php if ($antigua = $resultadoAntigua->fetch_assoc()) { ?>
                <strong><?php echo (int) $antigua["anio"]; ?></strong>
                <p><?php echo htmlspecialchars($antigua["titulo"]); ?></p>
            <?php } ?>
        </article>

        <article class="caja-estadistica">
            <h3>Película más reciente</h3>
            <?php if ($reciente = $resultadoReciente->fetch_assoc()) { ?>
                <strong><?php echo (int) $reciente["anio"]; ?></strong>
                <p><?php echo htmlspecialchars($reciente["titulo"]); ?></p>
            <?php } ?>
        </article>

        <article class="caja-estadistica caja-estadistica-ancha">
            <h3>Top 3 películas más populares</h3>
            <?php if ($resultadoTop->num_rows > 0) { ?>
                <ol class="lista-top">
                    <?php while ($top = $resultadoTop->fetch_assoc()) { ?>
                        <li>
                            <span class="lista-top-titulo"><?php echo htmlspecialchars($top["titulo"]); ?></span>
                            <span class="lista-top-datos">
                                <?php echo (int) $top["reproducciones"]; ?> reproducciones
                                <?php if ((int) $top["total_votos"] > 0) { ?>
                                    &middot; <?php echo number_format((float) $top["media"], 1); ?>/5
                                    (<?php echo (int) $top["total_votos"]; ?> <?php echo (int) $top["total_votos"] === 1 ? "voto" : "votos"; ?>)
                                <?php } else { ?>
                                    &middot; sin puntuaciones aún
                                <?php } ?>
                            </span>
                        </li>
                    <?php } ?>
                </ol>
            <?php } else { ?>
                <p>Todavía no hay reproducciones registradas.</p>
            <?php } ?>
        </article>

    </section>
</main>

<?php include "includes/footer.php"; ?>
