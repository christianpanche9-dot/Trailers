<?php
session_start();
require "conexion.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $id_peliculas = $_POST["id_peliculas"];
    $id_usuarios  = $_POST["id_usuarios"];

    $sql = "INSERT INTO visualizacion (id_peliculas, id_usuarios)
            VALUES (?, ?)";

    $stmt = $conexion->prepare($sql);

    $stmt->bind_param("ii", $id_peliculas, $id_usuarios);

    if ($stmt->execute()) {
        echo "OK";
    } else {
        echo $stmt->error;
    }
}