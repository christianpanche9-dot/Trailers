<?php
include "includes/header.php";
$errores = [];

if (isset($_POST["registrar"])) {

    $nombre = trim($_POST["nombre"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $repetir_password = trim($_POST["repetir_password"]);

  
    if (empty($nombre)) {
        $errores[] = "Debe introducir el nombre.";
    }

    if (empty($email)) {
        $errores[] = "Debe introducir el correo electrónico.";
    }

    if (empty($password)) {
        $errores[] = "Debe introducir una contraseña.";
    }

    if (empty($repetir_password)) {
        $errores[] = "Debe repetir la contraseña.";
    }

    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El correo electrónico no es válido.";
    }

    if (!empty($password) && !empty($repetir_password) && $password != $repetir_password) {
        $errores[] = "Las contraseñas no coinciden.";
    }


    if (count($errores) == 0) {

        require_once "conexion.php";

        $sql = "SELECT id_usuarios FROM usuarios WHERE email = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $errores[] = "Ya existe un usuario registrado con ese correo electrónico.";
        } else {

            $password_segura = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO usuarios (nombre, email, password)
                    VALUES (?, ?, ?)";

            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("sss", $nombre, $email, $password_segura);

            if ($stmt->execute()) {
                echo "Usuario registrado correctamente.";
            } else {
                $errores[] = "Error al registrar el usuario.";
            }
        }
    }
}

if (!empty($errores)) {
    foreach ($errores as $error) {
        echo "<p style='color:red;'>$error</p>";
    }
}
?>

<body>
    <div class="contenedor">
    <form action="registro_usuarios.php" method="POST" class="formulario">
        <h1>Registro de Usuarios</h1>
            <div class="campo">
            <label>Nombre y Apellidos</label>
            <input type="text" name="nombre" required>
        </div>
        <div class="campo">
            <label>Correo electrónico</label>
            <input type="email" name="email" required>
        </div>
        <div class="campo">
            <label>Contraseña</label>
            <input type="password" name="password" required>
        </div>
        <div class="campo">
            <label>Repetir Contraseña</label>
            <input type="password" name="repetir_password" required>
        </div>
        <button type="submit" name="registrar" class="boton">Registrarse</button>
    </form>
     <a href="login.php" class="boton-iniciar">iniciar sesión</a>
</div>
</body>

</html>
