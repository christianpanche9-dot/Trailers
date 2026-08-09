<?php
session_start();
require_once "seguridad.php";
exigirLogin();
exigirAdmin();
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

publicarComentario($conexion, $id_comentario);

header("Location: pelicula.php?id=" . (int) $comentario["id_peliculas"]);
exit();
