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
$comentario = obtenerComentarioPorId($conexion, $id_comentario);

if (!$comentario) {
    die("Comentario no encontrado.");
}

if ((int) $comentario["id_usuarios"] !== (int) $_SESSION["id_usuario"] && !esAdmin()) {
    header("Location: index.php?acceso=denegado");
    exit();
}

$id_pelicula = (int) $comentario["id_peliculas"];
eliminarComentario($conexion, $id_comentario);

header("Location: pelicula.php?id=" . $id_pelicula);
exit();
