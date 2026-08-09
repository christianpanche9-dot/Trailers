<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "seguridad.php";
exigirLogin();
exigirAdmin();
include_once "conexion.php";



$sql = "SELECT id_actor, nombre
        FROM actores
        ORDER BY nombre";

$resultado = $conexion->query($sql);

?>


<main class="contenedor">
    <h2 class="titulo-seccion">Añadir nueva Pelicula</h2>

    <form class="formulario" method="POST" enctype="multipart/form-data" action="procesar_nuevo.php">

        <div class="campo">
            <label for="titulo">Título:</label>
            <input type="text" id="titulo" name="titulo" required>
        </div>

        <div class="campo">
            <label for="director">Director:</label>
            <input type="text" id="director" name="director" required>
        </div>

        <div class="campo">
            <label for="anio">Año:</label>
            <input type="number" id="anio" name="anio" required>
        </div>

        <div class="campo">
            <label for="categoria">Categoría:</label>
            <select id="categoria" name="categoria" required>
                <option value="">Selecciona una categoria</option>
                <option value="Acción">Acción</option>
                <option value="Aventura">Aventura</option>
                <option value="Animacion">Animación</option>
                <option value="Biografia">Biografía</option>
                <option value="Comedia">Comedia</option>
                <option value="Crimen">Crimen</option>
                <option value="Documental">Documental</option>
                <option value="Drama">Drama</option>
                <option value="Familiar">Familiar</option>
                <option value="Fantasia">Fantasía</option>
                <option value="Historica">Histórica</option>
                <option value="Terror">Terror</option>
                <option value="Musical">Musical</option>
                <option value="Misterio">Misterio</option>
                <option value="Romance">Romance</option>
                <option value="Ciencia ficción">Ciencia ficción</option>
                <option value="Deporte">Deporte</option>
                <option value="Thriller">Thriller</option>
                <option value="Belica">Bélica</option>
                <option value="Western">Western</option>
            </select>
        </div>

        <div class="campo">
            <label for="link">Link</label>
            <input type="text" id="link" name="link" required>
        </div>
        <div class="campo">
        <label for="imagen">Portada:</label>
        <input type="file" name="imagen">
        </div>
        <h2>Actores y actrices</h2>

        <p>Selecciona los actores que participan en la película:</p>
            <?php while ($actor = $resultado->fetch_assoc()) { ?>
            <div class="campo">
            <label class="checkbox">
            <input type="checkbox" class="checkActor" name="actores[]" value="<?php echo (int) $actor['id_actor']; ?>">
            <?php echo htmlspecialchars($actor['nombre']); ?>
            </label>
            </div>
            <div class="campo personaje">
            <input type="text"
               name="personaje[<?php echo $actor['id_actor']; ?>]"
               placeholder="Nombre del personaje">
            </div>  
            <?php } ?>
        <br>
        <button class="boton" type="submit">Guardar Película</button>
    </form>
</main>
