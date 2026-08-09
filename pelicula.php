<?php
session_start();
require_once "seguridad.php";
exigirLogin();
if (!isset($_SESSION["id_usuario"])) {
header("Location: login.php");
exit();
}
require_once "conexion.php";
include_once "includes/funciones.php";

if (!isset($_GET["id"])) {
die("No se ha indicado ninguna película.");
}
$id = intval($_GET["id"]);
if ($id <= 0) {
die("El identificador de la película no es válido.");
}

/* CONSULTA DE LA PELÍCULA PRINCIPAL */
$sql = "
SELECT
id_peliculas,
titulo,
director,
categoria,
anio,
link
FROM peliculas
WHERE id_peliculas = ?
";
$stmt = $conexion->prepare($sql);
if (!$stmt) {
die(
"Error al preparar la consulta: "
. $conexion->error
);
}
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
if ($resultado->num_rows == 0) {
die("Película no encontrada.");
}
$pelicula = $resultado->fetch_assoc();

/* CONSULTA DEL REPARTO */
$sqlActores = "
SELECT
actores.id_actor,
actores.nombre,
actores.apellidos,
peliculas_actores.personaje
FROM actores
INNER JOIN peliculas_actores
ON actores.id_actor =
peliculas_actores.id_actor
WHERE peliculas_actores.id_peliculas = ?
ORDER BY
actores.apellidos,
actores.nombre
";
$stmtActores = $conexion->prepare($sqlActores);
if (!$stmtActores) {
die(
"Error al preparar la consulta del reparto: "
. $conexion->error
);
}
$stmtActores->bind_param("i", $id);
$stmtActores->execute();
$resultadoActores =
$stmtActores->get_result();

/* CONSULTA DE PELÍCULAS RELACIONADAS */
$categoriaActual = $pelicula["categoria"];
$sqlRelacionadas = "
SELECT
id_peliculas,
titulo,
director,
anio
FROM peliculas
WHERE categoria = ?
AND id_peliculas != ?
ORDER BY titulo
LIMIT 3
";
$stmtRelacionadas =
$conexion->prepare($sqlRelacionadas);
if (!$stmtRelacionadas) {
die(
"Error al preparar las recomendaciones: "
. $conexion->error
);
}
$stmtRelacionadas->bind_param(
"si",
$categoriaActual,
$id
);
$stmtRelacionadas->execute();
$resultadoRelacionadas =
$stmtRelacionadas->get_result();

/* CONSULTA DE COMENTARIOS */
$comentariosVisibles = [];
$resultadoComentarios = obtenerComentariosPelicula($conexion, $id);
while ($fila = $resultadoComentarios->fetch_assoc()) {
    $esPropio = (int) $fila["id_usuarios"] === (int) $_SESSION["id_usuario"];
    if ($fila["estado"] === "publicado" || $esPropio || esAdmin()) {
        $comentariosVisibles[] = $fila;
    }
}

/* CONSULTA DE PUNTUACIÓN */
$resumenPuntuacion = obtenerResumenPuntuacion($conexion, $id);
$miPuntuacion = obtenerPuntuacionUsuario($conexion, $id, $_SESSION["id_usuario"]);

/* PREPARACIÓN DEL TRÁILER */
$trailer = "";
if (!empty($pelicula["link"])) {
$trailer = convertirYoutubeEmbed(
$pelicula["link"]
);
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta
name="viewport"
content="width=device-width, initial-scale=1.0"
>
<link rel="stylesheet" href="css/estilos.css">
</head>
<title>
<?php
echo htmlspecialchars(
$pelicula["titulo"]
);
?>
</title>
<link rel="stylesheet" href="estilos.css">
</head>
<body>
<header>
    <h1> Ficha de película</h1>
<p>
    Información completa y recomendaciones.
</p>
</header>
<?php include "includes/menu.php";?>
<main class="contenedor-ficha">
<article class="ficha">
<h2>
<?php
echo htmlspecialchars(
$pelicula["titulo"]);?>
</h2>
<div class="datos-pelicula">
<p> <strong>Director:</strong> <?php echo htmlspecialchars( $pelicula["director"]);?></p>
<p> <strong>Género:</strong> <?php echo htmlspecialchars($pelicula["categoria"]);?></p>
<p> <strong>Año de estreno:</strong> <?php echo htmlspecialchars( $pelicula["anio"]);?></p>
</div>
<h3>Reparto</h3>
<?php if ($resultadoActores->num_rows > 0) {?>
<ul class="lista-reparto">
<?php
while ($actor =$resultadoActores->fetch_assoc()) {?><li><a href="listar_actores.php?id_actor=<?php echo (int) $actor["id_actor"]; ?>"><?php echo htmlspecialchars($actor["nombre"]. " " . $actor["apellidos"] . " como " . $actor["personaje"]);?></a>
</li><?php
}
?>
</ul>
<?php
} else {
?>
<p>Esta película todavía no tiene
actores asociados.
</p>
<?php
}
?>

<?php
if ($trailer != "") {
?>
<h3>Tráiler</h3>
<div class="trailer">
<iframe
src="<?php
echo htmlspecialchars($trailer);
?>"
title="Tráiler de <?php
echo htmlspecialchars(
$pelicula["titulo"]
);
?>"
allow="accelerometer;
autoplay;
clipboard-write;
encrypted-media;
gyroscope;
picture-in-picture;
web-share"
allowfullscreen
>
</iframe>
</div>
<?php
}
?>

<section class="puntuacion">
<h2>Puntuación</h2>
<?php if ((int) $resumenPuntuacion["total"] > 0) { ?>
<p>
<strong><?= number_format((float) $resumenPuntuacion["media"], 1) ?></strong> / 5
(<?= (int) $resumenPuntuacion["total"] ?> <?= (int) $resumenPuntuacion["total"] === 1 ? "voto" : "votos" ?>)
</p>
<?php } else { ?>
<p>Todavía no hay puntuaciones para esta película.</p>
<?php } ?>
<form action="puntuar.php" method="POST" class="formulario-puntuacion">
<input type="hidden" name="id_pelicula" value="<?= (int) $pelicula["id_peliculas"] ?>">
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generarTokenCSRF()) ?>">
<div class="estrellas">
<?php for ($i = 5; $i >= 1; $i--) { ?>
<input type="radio" id="estrella<?= $i ?>" name="puntuacion" value="<?= $i ?>"
    <?= $miPuntuacion === $i ? "checked" : "" ?> required>
<label for="estrella<?= $i ?>" title="<?= $i ?> estrellas">&#9733;</label>
<?php } ?>
</div>
<button type="submit" class="boton">
<?= $miPuntuacion !== null ? "Actualizar puntuación" : "Puntuar" ?>
</button>
</form>
</section>

<section class="nuevo-comentario">
<h2>Escribe tu opinión</h2>
<form
action="guardar_comentario.php"
method="POST"
>
<input
type="hidden"
name="id_pelicula"
value="<?= (int) $pelicula["id_peliculas"] ?>"
>
<input
type="hidden"
name="csrf_token"
value="<?= htmlspecialchars(generarTokenCSRF()) ?>"
>
<label for="comentario">
Comentario
</label>
<textarea
name="comentario"
id="comentario"
rows="6"
maxlength="2000"
required
></textarea>
<label class="opcion-spoiler">
<input
type="checkbox"
name="spoiler"
value="1"
>
Mi comentario contiene spoilers
</label>
<button class="boton" type="submit">
Publicar comentario
</button>
</form>
</section>

<section class="comentarios">
<h2>Comentarios (<?= count($comentariosVisibles) ?>)</h2>
<?php if (count($comentariosVisibles) > 0) { ?>
<ul class="lista-comentarios">
<?php foreach ($comentariosVisibles as $comentario) { ?>
<li class="comentario">
<div class="comentario-avatar"><?= htmlspecialchars(mb_strtoupper(mb_substr($comentario["autor"], 0, 1))) ?></div>
<div class="comentario-cuerpo">
<div class="comentario-cabecera">
<strong><?= htmlspecialchars($comentario["autor"]) ?></strong>
<small><?= htmlspecialchars($comentario["fecha_comentario"]) ?></small>
<?php if ($comentario["fecha_modificacion"]) { ?>
<small>(editado)</small>
<?php } ?>
</div>
<?php if ($comentario["estado"] !== "publicado") { ?>
<span class="etiqueta etiqueta-pendiente">Pendiente de aprobación</span>
<?php } ?>
<?php if ($comentario["spoiler"]) { ?>
<span class="etiqueta etiqueta-spoiler">Contiene spoilers</span>
<?php } ?>
<p class="comentario-texto"><?= nl2br(htmlspecialchars($comentario["comentario"])) ?></p>
<?php if ((int) $comentario["id_usuarios"] === (int) $_SESSION["id_usuario"] || esAdmin()) { ?>
<div class="acciones-comentario">
<?php if (esAdmin() && $comentario["estado"] !== "publicado") { ?>
<form method="POST" action="publicar_comentario.php">
<input type="hidden" name="id_comentario" value="<?= (int) $comentario["id_comentario"] ?>">
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generarTokenCSRF()) ?>">
<button class="boton-secundario" type="submit">Publicar</button>
</form>
<?php } ?>
<a class="boton-secundario" href="editar_comentario.php?id=<?= (int) $comentario["id_comentario"] ?>">Editar</a>
<form method="POST" action="borrar_comentario.php"
      onsubmit="return confirm('¿Borrar este comentario?')">
<input type="hidden" name="id_comentario" value="<?= (int) $comentario["id_comentario"] ?>">
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generarTokenCSRF()) ?>">
<button class="boton" type="submit">Borrar</button>
</form>
</div>
<?php } ?>
</div>
</li>
<?php } ?>
</ul>
<?php } else { ?>
<p class="mensaje-vacio">Todavía no hay comentarios. ¡Sé el primero en opinar!</p>
<?php } ?>
</section>
</article>
<?php
if ($resultadoRelacionadas->num_rows > 0) {
?>
<section class="relacionadas">
<h3>
También te pueden interesar
</h3>
<p class="texto-relacionadas">
Otras películas del género
<strong>
<?php
echo htmlspecialchars(
$categoriaActual
);
?>
</strong>
</p>
<div class="rejilla-relacionadas">
    <?php
while ($relacionada =$resultadoRelacionadas->fetch_assoc()
) {
?>
<article
class="pelicula-relacionada"
>
<h4>
<?php
echo htmlspecialchars(
$relacionada["titulo"]
);
?>
</h4>
<p>
<strong>Director:</strong>
<?php
echo htmlspecialchars(
$relacionada["director"]
);
?>
</p>
<p>
<strong>Estreno:</strong>
<?php
echo htmlspecialchars(
$relacionada[
"anio"
]
);
?>
</p>
<a
class="boton-iniciar"
href="pelicula.php?id=<?php
echo $relacionada[
"id_peliculas"
];
?>"
>
Ver película
</a>
</article>
<?php
}
?>
</div>
</section>
<?php
}
?>

</main>

</body>
<?php include "includes/footer.php"?>
</html>
<?php
$stmt->close();
$stmtActores->close();
$stmtRelacionadas->close();
$conexion->close();
?>