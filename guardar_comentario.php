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

$id_pelicula = (int) ($_POST["id_pelicula"] ?? 0);
$comentario = trim($_POST["comentario"] ?? "");
$spoiler = isset($_POST["spoiler"]) ? 1 : 0;

if ($id_pelicula > 0 && $comentario !== "") {
    insertarComentario($conexion, $id_pelicula, $_SESSION["id_usuario"], $comentario, $spoiler);
}

header("Location: pelicula.php?id=" . $id_pelicula);
exit();
