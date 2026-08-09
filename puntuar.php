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
$puntuacion = (int) ($_POST["puntuacion"] ?? 0);

if ($id_pelicula > 0 && $puntuacion >= 1 && $puntuacion <= 5) {
    insertarOActualizarPuntuacion($conexion, $id_pelicula, $_SESSION["id_usuario"], $puntuacion);
}

header("Location: pelicula.php?id=" . $id_pelicula);
exit();
