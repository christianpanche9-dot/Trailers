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

$id = (int) ($_POST["id"] ?? 0);

if ($id > 0) {
    eliminarPelicula($conexion, $id);
}

header("Location: index.php");
exit();