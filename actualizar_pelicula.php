<?php
session_start();
require_once "seguridad.php";
exigirLogin();
exigirEditorOAdmin();


if (!isset($_SESSION["id_usuario"])) {
header("Location: login.php");
exit();
}
include "conexion.php";
$id_peliculas = $_POST["id_peliculas"];
$titulo = $_POST["titulo"];
$director = $_POST["director"];
$categoria = $_POST["categoria"];
$anio = $_POST["anio"];
$link = $_POST["link"];
$imagen = $_POST["imagen_actual"];

if (
    isset($_FILES["imagenes"]) &&
    $_FILES["imagenes"]["error"] == 0
) {

    // Nombre del archivo original
    $nombreArchivo = $_FILES["imagenes"]["name"];

    // Obtener extensión
    $extension = strtolower(pathinfo(
        $nombreArchivo,
        PATHINFO_EXTENSION
    ));

    // Extensiones permitidas
    $permitidas = [
        "jpg",
        "jpeg",
        "png",
        "webp"
    ];

    if (!in_array($extension, $permitidas)) {
        die("Formato de imagen no permitido.");
    }

    //tamaño maximo
    $maximo = 2 * 1024 * 1024;
    if ($_FILES["imagenes"]["size"] > $maximo) {
        die("La imagen supera el tamaño permitido.");
    }
    if (getimagesize($_FILES["imagenes"]["tmp_name"]) === false) {
        die("El archivo no es una imagen válida.");
    }

    // Crear nombre único y guardar imagen
    $imagen = uniqid() . "." . $extension;

    move_uploaded_file(
        $_FILES["imagenes"]["tmp_name"],
        "imagenes/" . $imagen
    );
}
// 1. Actualizar los datos principales de la película
$sql = "UPDATE peliculas
SET titulo = ?,
director = ?,
categoria = ?,
anio = ?,
link = ?,
imagenes = ?
WHERE id_peliculas = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param(
"ssssssi",
$titulo,
$director,
$categoria,
$anio,
$link,
$imagen,
$id_peliculas
);
if ($stmt->execute()) {
// 2. Borrar relaciones antiguas
$sql_borrar = "DELETE FROM peliculas_actores WHERE id_peliculas = ?";
$stmt_borrar = $conexion->prepare($sql_borrar);
$stmt_borrar->bind_param("i", $id_peliculas);
$stmt_borrar->execute();
// 3. Insertar relaciones nuevas
if (isset($_POST["actores"])) {
$sql_insertar = "INSERT INTO peliculas_actores
(id_peliculas, id_actor,personaje)
VALUES (?, ?, ?)";
$stmt_insertar = $conexion->prepare($sql_insertar);
foreach ($_POST["actores"] as $id_actor) {

        $personaje = isset($_POST["personajes"][$id_actor])
            ? trim($_POST["personajes"][$id_actor])
            : "";

        $stmt_insertar->bind_param(
            "iis",
            $id_peliculas,
            $id_actor,
            $personaje
        );
$stmt_insertar->execute();
}
}
header("Location: index.php");
exit;
} else {
echo "Error al actualizar la película: " . $conexion->error;
}
?>

