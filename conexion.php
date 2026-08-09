<?php
$servidor = "localhost";
$usuarioBD = "root";
$passwordBD = "";
$basedatos = "trailers";

$conexion = mysqli_connect(
    $servidor,
    $usuarioBD,
    $passwordBD,
    $basedatos
);

if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

$conexion->set_charset("utf8mb4");