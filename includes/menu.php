<nav>
    <a href="index.php">Inicio</a>
    <a href="listar_actores.php">Actores</a>

     <?php if (estaLogueado()): ?>
    <a href="mis_comentarios.php">Mis comentarios</a>
     <?php endif; ?>

     <?php if (esAdmin()): ?>
    <a href="estadisticas.php">Estadísticas</a>
    <a href="listar_usuarios.php">Usuarios</a>
    <?php endif; ?>
</nav>
