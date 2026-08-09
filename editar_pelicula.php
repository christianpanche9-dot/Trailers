<?php
session_start();
require_once "seguridad.php";
exigirLogin();
exigirEditorOAdmin();

include "includes/header.php";
include "includes/menu.php";

if (!isset($_SESSION["id_usuario"])) {
header("Location: login.php");
exit();
}

include "conexion.php";
if (!isset($_GET["id"])) {
die("No se ha indicado ninguna película.");
}

$id_peliculas = $_GET["id"];
// Obtener datos de la película
$sql_pelicula = "SELECT * FROM peliculas WHERE id_peliculas = ?";
$stmt = $conexion->prepare($sql_pelicula);
$stmt->bind_param("i", $id_peliculas);
$stmt->execute();
$resultado_pelicula = $stmt->get_result();
if ($resultado_pelicula->num_rows === 0) {
die("La película no existe.");
}

$pelicula = $resultado_pelicula->fetch_assoc();
// Obtener todos los actores
$sql_actores = "SELECT id_actor, nombre, apellidos FROM actores ORDER BY nombre";
$resultado_actores = $conexion->query($sql_actores);
// Obtener actores asociados a esta película
$sql_actores_pelicula = "
SELECT id_actor,personaje
FROM peliculas_actores
WHERE id_peliculas = ?
";

$stmt_actores = $conexion->prepare($sql_actores_pelicula);
$stmt_actores->bind_param("i", $id_peliculas);
$stmt_actores->execute();
$resultado_actores_pelicula = $stmt_actores->get_result();
$actores_seleccionados = [];
$personajes = [];
while ($fila = $resultado_actores_pelicula->fetch_assoc()) {
$actores_seleccionados[] = $fila["id_actor"];
 $personajes[$fila["id_actor"]] = $fila["personaje"];
}
?>


<body>

<div class="contenedor">
    <h1>Editar película</h1>
<form action="actualizar_pelicula.php" method="POST" enctype="multipart/form-data" class="formulario">
<input type="hidden" name="id_peliculas" value="<?php echo (int) $pelicula['id_peliculas']; ?>">

<div class="campo">
<label for="titulo">Título:</label>
<input
type="text"
name="titulo"
id="titulo"
value="<?php echo htmlspecialchars($pelicula['titulo']); ?>"
required
>
</div>
<div class="campo">
<label for="director">Director:</label>
<input
type="text"
name="director"
id="director"
value="<?php echo htmlspecialchars($pelicula['director']); ?>"
>
</div>
<div class="campo">
<label for="categoria">Género:</label>
<input
type="text"
name="categoria"
id="categoria"
value="<?php echo htmlspecialchars($pelicula['categoria']); ?>"
>
</div>
<div class="campo">
<label for="Anio">Anio:</label>
<input
type="text"
name="anio"
id="anio"
value="<?php echo htmlspecialchars($pelicula['anio']); ?>"
>
</div>
<div class="campo">
<label for="link">Link:</label>
<input
type="url"
name="link"
id="link"
value="<?php echo htmlspecialchars($pelicula['link']); ?>">
</div>
<div class="campo">
<label for="imagenes">Portada:</label>
    <input type="file" name="imagenes">

    <input type="hidden" name="imagen_actual"
           value="<?php echo htmlspecialchars($pelicula['imagenes']); ?>">

    <?php if (!empty($pelicula['imagenes'])) { ?>
        <br>
        <img src="imagenes/<?php echo htmlspecialchars($pelicula['imagenes']); ?>"
             width="120"
             alt="Portada actual">
    <?php } ?>
</div>
<h2>Actores y actrices</h2>
<?php while ($actor = $resultado_actores->fetch_assoc()) { ?>
<label class="checkbox">
<input
type="checkbox"
name="actores[]"
value="<?php echo (int) $actor['id_actor']; ?>"
<?php
if (in_array($actor["id_actor"], $actores_seleccionados)) {
echo "checked";
}
?>
>
<?php echo htmlspecialchars($actor["nombre"] . " " . $actor["apellidos"]); ?>
</label>
<input
    type="text"
    name="personajes[<?php echo (int) $actor['id_actor']; ?>]"
    placeholder="Personaje"
    value="<?php echo isset($personajes[$actor['id_actor']]) ? htmlspecialchars($personajes[$actor['id_actor']]) : ''; ?>"
>

<br><br>
<?php } ?>

<button class="boton-iniciar"type="submit">Actualizar película</button>
</form>
<p>
</p>
</div>
</body>
</html>