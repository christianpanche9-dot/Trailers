<?php

$errores = [];
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = trim($_POST["email"]);
        $password = trim($_POST["password"]);
    if (empty($email)) {
        $errores[] = "Debe introducir el correo electrónico.";
    }
    if (empty($password)) {
        $errores[] = "Debe introducir la contraseña.";
    }
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El correo electrónico no tiene un formato válido.";
    }
    } else {
        $errores[] = "Acceso no válido.";
    }
    if (count($errores) > 0) {
        echo "<h1>No se ha podido iniciar sesión</h1>";
        echo "<ul>";
        foreach ($errores as $error) {
        echo "<li>$error</li>";
        }
        echo "</ul>";
        echo "<a href='login.php'>Volver al login</a>";
        exit;
        } else { 

        require_once "conexion.php";
    $sql = "SELECT *
    FROM usuarios
    WHERE email = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows == 0) {
    $errores[] = "No existe ningún usuario registrado con ese correo.";
    } else {
    $usuario = $resultado->fetch_assoc();
         if(
        password_verify($password,$usuario["password"])){
        session_start();
        $_SESSION["id_usuario"] = $usuario["id_usuarios"];
        $_SESSION["nombre"] = $usuario["nombre"];
        $_SESSION["email"] = $usuario["email"];
        $_SESSION["rol"] = $usuario["rol"];
        header("Location: index.php");
        exit();
    }
    else
    {
        $errores[] = "La contraseña es incorrecta.";
    }
    }
    if (count($errores) > 0) {
    echo "<h1>No se ha podido iniciar sesión</h1>";
    echo "<ul>";
    foreach ($errores as $error) {
        echo "<li>$error</li>";
    }
    echo "</ul>";
    echo "<a href='login.php'>Volver al login</a>";
    exit;
    }
    }
?>
