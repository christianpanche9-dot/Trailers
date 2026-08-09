<?php
session_start();

require_once "seguridad.php";
exigirLogin();
exigirAdmin();

require "conexion.php";
require_once "includes/funciones.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST" || !validarTokenCSRF($_POST["csrf_token"] ?? null)) {
    header("Location: listar_usuarios.php");
    exit();
}

$id = (int) ($_POST["id"] ?? 0);

if ($id > 0) {
    eliminarUsuario($conexion, $id);
}

header("Location: listar_usuarios.php");
exit();