<?php
session_start();
require_once "seguridad.php";
exigirLogin();
require "conexion.php";
require_once "includes/funciones.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST" || !validarTokenCSRF($_POST["csrf_token"] ?? null)) {
    header("Location: index.php");
    exit();
}

$id_comentario = (int) ($_POST["id_comentario"] ?? 0);
$comentarioActual = obtenerComentarioPorId($conexion, $id_comentario);

if (!$comentarioActual) {
    die("Comentario no encontrado.");
}

if ((int) $comentarioActual["id_usuarios"] !== (int) $_SESSION["id_usuario"] && !esAdmin()) {
    header("Location: index.php?acceso=denegado");
    exit();
}

$texto = trim($_POST["comentario"] ?? "");
$spoiler = isset($_POST["spoiler"]) ? 1 : 0;

if ($texto !== "") {
    actualizarComentario($conexion, $id_comentario, $texto, $spoiler);
}

header("Location: pelicula.php?id=" . (int) $comentarioActual["id_peliculas"]);
exit();
