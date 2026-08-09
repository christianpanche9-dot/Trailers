<?php

session_start();
require_once "seguridad.php";
exigirLogin();

require_once "conexion.php";
include "includes/header.php";
include "includes/menu.php";
require_once "includes/funciones.php";

$idActor = isset($_GET["id_actor"]) ? (int) $_GET["id_actor"] : 0;
$busqueda = "";

if ($idActor > 0) {
    $resultado = obtenerActorPorId($conexion, $idActor);
    $peliculasActor = obtenerPeliculasDeActor($conexion, $idActor);
} else {
    if (isset($_GET["buscador"])) {
        $busqueda = trim($_GET["buscador"]);
    }

    if ($busqueda != "") {
        $resultado = buscarActoresPeliculas($conexion, $busqueda);
    } else {
        $resultado = obtenerActoresConPeliculas($conexion);
    }
}

?>

<main class="contenedor">
    <h1><?php echo $idActor > 0 ? "Ficha del actor" : "Listado de Actores"; ?></h1>

    <?php if ($idActor > 0) { ?>
        <a class="boton-secundario" href="listar_actores.php">&laquo; Ver todos los actores</a>
        <br><br>
    <?php } else { ?>
    <form class="formulario" method="GET" action="listar_actores.php">
        <div class="campo">
            <label for="buscador">Buscar por película o Actor:</label>
            <input id="buscador" name="buscador">
        </div>
        <button class="boton" type="submit">Filtrar</button>
    </form>
    <?php } ?>

<div class="contenedor">
<div class="<?php echo $idActor > 0 ? "ficha-actor-contenedor" : "grid-peliculas"; ?>">
    <?php if ($resultado->num_rows === 0) { ?>
        <p>No se ha encontrado ningún actor.</p>
    <?php } ?>
    <?php while ($actor = $resultado->fetch_assoc()) {?>
    <article class="tarjeta<?php echo $idActor > 0 ? " ficha-actor" : ""; ?>">
        <img src="<?php echo htmlspecialchars(rutaFotoActor($actor["foto"])); ?>" class="<?php echo $idActor > 0 ? "foto-ficha" : "foto"; ?>">
        <h3><?php echo htmlspecialchars($actor["nombre"] . " " . $actor["apellidos"]); ?></h3>

        <?php if ($idActor > 0) { ?>
        <div class="peliculas-actor">
            <h4>Películas</h4>
            <?php if ($peliculasActor->num_rows > 0) { ?>
            <ul class="lista-peliculas-actor">
                <?php while ($peliculaActor = $peliculasActor->fetch_assoc()) { ?>
                <li>
                    <a href="pelicula.php?id=<?php echo (int) $peliculaActor["id_peliculas"]; ?>">
                        <?php echo htmlspecialchars($peliculaActor["titulo"]); ?>
                    </a>
                    <?php if (!empty($peliculaActor["personaje"])) { ?>
                    <span class="personaje-actor">como <?php echo htmlspecialchars($peliculaActor["personaje"]); ?></span>
                    <?php } ?>
                </li>
                <?php } ?>
            </ul>
            <?php } else { ?>
            <p>Sin películas asociadas.</p>
            <?php } ?>
        </div>
        <?php } ?>

        <p><strong>Nacionalidad:</strong> <?php echo htmlspecialchars($actor["nacionalidad"]); ?></p>

        <p><strong>Fecha de nacimiento:</strong> <?php echo htmlspecialchars($actor["fecha_nacimiento"]); ?></p>

        <?php if ($idActor === 0) { ?>
        <a class="boton-secundario" href="listar_actores.php?id_actor=<?php echo (int) $actor['id_actor']; ?>">Ver ficha</a>
        <?php } ?>
    </article>
    <?php } ?>
</div>
</div>

<?php
if ($idActor === 0 && esAdmin()) {
    include "alta_actores.php";
}
?>
</main>
<?php
include "includes/footer.php";
?>




