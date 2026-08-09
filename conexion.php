<?php
// Valores por defecto para desarrollo local (XAMPP).
$servidor = "localhost";
$usuarioBD = "root";
$passwordBD = "";
$basedatos = "trailers";

// En el servidor de producción (InfinityFree), crea manualmente
// conexion.local.php con las credenciales reales. Ese archivo nunca
// se sube a git ni se despliega desde el repositorio (ver .gitignore),
// así que las credenciales de producción nunca quedan expuestas.
if (file_exists(__DIR__ . "/conexion.local.php")) {
    require __DIR__ . "/conexion.local.php";
}

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
