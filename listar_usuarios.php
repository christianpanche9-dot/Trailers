<?php
session_start();
require_once "seguridad.php";
exigirLogin();
exigirAdmin();

require_once "conexion.php";
include "includes/header.php";
include "includes/menu.php";
require_once "includes/funciones.php";

$resultado = obtenerUsuarios($conexion);
?>


<main class="contenedor">
    <h1>Listado de Usuarios</h1>

<div class="contenedor">
<div class="grid-peliculas">
    <?php while ($usuario = $resultado->fetch_assoc()) {?>
    <?php if($usuario["foto"]=="")
    {
    $foto="sin-foto.jpg";
    }
    else
    {
    $foto=$usuario["foto"];
    }
    ?>
    <article class="tarjeta">
        <img src="imagenes/<?php echo htmlspecialchars($foto); ?>" class="foto">
        <h3><?php echo htmlspecialchars($usuario["nombre"]); ?></h3>
        <p><strong>Email:</strong>
            <?php echo htmlspecialchars($usuario["email"]); ?>
        </p>

        <p><strong>Rol:</strong> <?php echo htmlspecialchars($usuario["rol"]); ?></p>

        <p><strong>Fecha de Alta:</strong> <?php echo htmlspecialchars($usuario["fecha_alta"]); ?></p>

        <form method="POST" action="eliminar_usuario.php" style="display:inline"
              onsubmit="return confirm('¿Eliminar este usuario?')">
            <input type="hidden" name="id" value="<?php echo (int) $usuario['id_usuarios']; ?>">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generarTokenCSRF()); ?>">
            <button class="boton" type="submit">Eliminar</button>
        </form>
    </article>
    <?php } ?>
</div>
</div>
</main>
<?php
include "includes/footer.php";
?>