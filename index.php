
<?php
// cache-bust deploy check
session_start();
require_once "seguridad.php";

include_once "includes/funciones.php";
include "conexion.php";



$pagina = isset($_GET["pagina"]) ? (int)$_GET["pagina"] : 1;

if ($pagina < 1) {
    $pagina = 1;
}
$porPagina = 6;

$offset = ($pagina - 1) * $porPagina;

$categoriaSeleccionada = $_GET["categoria"] ?? "";
$buscar = trim($_GET["buscar"] ?? "");
$orden = $_GET["orden"] ?? "anio_desc";

$totalPeliculas = contarPeliculasFiltradas($conexion, $buscar, $categoriaSeleccionada);
$totalPaginas = max(1, ceil($totalPeliculas / $porPagina));

$peliculasMostradas = buscarPeliculas($conexion, $buscar, $categoriaSeleccionada, $orden, $porPagina, $offset);

$categorias = obtenerCategorias($conexion);


include "includes/header.php";
include "includes/menu.php";

?>

<main class="contenedor">
    <h2 class="titulo-seccion">Listado de Películas</h2>

    <form class="formulario" method="GET" action="index.php">
        <div class="campo campo-busqueda buscador-dinamico">
            <label for="buscar">Buscar:</label>
            <input
                type="text"
                id="buscar"
                name="buscar"
                placeholder="Título, director, actor o actriz"
                autocomplete="off"
                value="<?php echo htmlspecialchars($buscar); ?>">
            <div id="sugerencias" class="sugerencias" hidden></div>
        </div>

        <div class="campo">
            <label for="categoria">Categoría:</label>
            <select name="categoria" id="categoria">
                <option value="">Todas las categorías</option>
                <?php foreach ($categorias as $categoria): ?>
                    <option value="<?php echo htmlspecialchars($categoria); ?>" <?php echo ($categoria == $categoriaSeleccionada) ? "selected" : ""; ?>>
                        <?php echo htmlspecialchars($categoria); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="campo">
            <label for="orden">Ordenar por:</label>
            <select name="orden" id="orden">
                <option value="anio_desc" <?php echo ($orden == "anio_desc") ? "selected" : ""; ?>>Año (recientes primero)</option>
                <option value="anio_asc" <?php echo ($orden == "anio_asc") ? "selected" : ""; ?>>Año (antiguas primero)</option>
                <option value="titulo_asc" <?php echo ($orden == "titulo_asc") ? "selected" : ""; ?>>Título A-Z</option>
                <option value="titulo_desc" <?php echo ($orden == "titulo_desc") ? "selected" : ""; ?>>Título Z-A</option>
                <option value="director_asc" <?php echo ($orden == "director_asc") ? "selected" : ""; ?>>Director A-Z</option>
                <option value="categoria_asc" <?php echo ($orden == "categoria_asc") ? "selected" : ""; ?>>Categoría A-Z</option>
            </select>
        </div>

        <button class="boton" type="submit">Filtrar</button>
    </form>

    <br>

    <div class="grid-peliculas">
       <?php while ($pelicula = $peliculasMostradas->fetch_assoc()) { ?>
            <article class="tarjeta">
                <?php if($pelicula["imagenes"]=="")
                    {
                    $imagenes="sin-imagen.jpg";
                    }
                    else
                    {
                    $imagenes=$pelicula["imagenes"];
                    }
                    ?>
                    <img src="imagenes/<?php echo htmlspecialchars($imagenes); ?>" width="160" class="cartel">
                <h3><?php echo htmlspecialchars($pelicula["titulo"]); ?></h3>
                <p><strong>Autor:</strong> <?php echo htmlspecialchars($pelicula["director"]); ?></p>
                <p><strong>Año:</strong> <?php echo (int) $pelicula["anio"]; ?></p>
                <span class="etiqueta"><?php echo htmlspecialchars($pelicula["categoria"]); ?></span>
                <h4>Reparto</h4>

                <?php if (!empty($pelicula["actores"])) { ?>
                    <ul>
                        <?php
                        $reparto = explode(", ", $pelicula["actores"]);

                        foreach ($reparto as $actor) {
                            echo "<li>" . htmlspecialchars($actor) . "</li>";
                        }
                        ?>
                    </ul>
                <?php } else { ?>
                    <p>Sin actores asignados.</p>
                <?php } ?>

                <br>

                <?php echo "<a class='boton-iniciar' href='pelicula.php?id=" . (int) $pelicula["id_peliculas"] . "'>";
                    echo "Ver ficha Completa";
                    echo "</a>"?>

                <br>

                <button class="btnTrailer boton"
                data-target="trailer<?= $pelicula['id_peliculas'] ?>"
                data-pelicula="<?= $pelicula['id_peliculas'] ?>"
                data-usuario="<?= $_SESSION['id_usuario'] ?? '' ?>">
                Ver tráiler
                 </button>

                <br>
                <iframe id="trailer<?= $pelicula['id_peliculas'] ?>"
                        class="trailer"
                        width="90%"
                        height="45%"
                        src="<?= htmlspecialchars($pelicula['link']) ?>"
                        data-src="<?= htmlspecialchars($pelicula['link']) ?>"
                        style="display:none;"
                        title="YouTube video player"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        referrerpolicy="strict-origin-when-cross-origin"
                        allowfullscreen>
                </iframe>

                <?php if (puedeEditarContenido() || esAdmin()) { ?>
                <div class="acciones-pelicula">
                    <?php if (puedeEditarContenido()) { ?>
                        <a class="boton-secundario" href="editar_pelicula.php?id=<?php echo (int) $pelicula['id_peliculas']; ?>">Editar</a>
                    <?php } ?>
                    <?php if (esAdmin()) { ?>
                        <form method="POST" action="eliminar_pelicula.php"
                              onsubmit="return confirm('¿Eliminar esta película?')">
                            <input type="hidden" name="id" value="<?php echo (int) $pelicula['id_peliculas']; ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generarTokenCSRF()); ?>">
                            <button class="boton" type="submit">Eliminar</button>
                        </form>
                    <?php } ?>
                </div>
                <?php } ?>
            </article>
            <?php } ?>
    </div>
    <?php
        $queryExtra = "";

        if ($categoriaSeleccionada != "") {
            $queryExtra .= "&categoria=" . urlencode($categoriaSeleccionada);
        }
        if ($buscar != "") {
            $queryExtra .= "&buscar=" . urlencode($buscar);
        }
        if ($orden != "anio_desc") {
            $queryExtra .= "&orden=" . urlencode($orden);
        }
        $rango = 2;

        $inicio = max(1, $pagina - $rango);
        $fin = min($totalPaginas, $pagina + $rango);
?>

    <div class="paginacion">

    <?php if ($pagina > 1) { ?>
        <a href="index.php?pagina=<?= $pagina - 1 ?><?= $queryExtra ?>">
            &laquo; Anterior
        </a>
    <?php } ?>

<?php if ($inicio > 1) { ?>

    <a href="index.php?pagina=1<?= $queryExtra ?>">1</a>

<?php if ($inicio > 2) { ?>

    <span>...</span>

<?php } ?>

<?php } ?>
    <?php for ($i = $inicio; $i <= $fin; $i++) { ?>

        <a
            href="index.php?pagina=<?= $i ?><?= $queryExtra ?>"
            class="<?= ($i == $pagina) ? 'activo' : '' ?>">
            <?= $i ?>
        </a>

    <?php } ?>
    <?php if ($fin < $totalPaginas) { ?>

    <?php if ($fin < $totalPaginas - 1) { ?>
        <span>...</span>
    <?php } ?>

    <a href="index.php?pagina=<?= $totalPaginas ?><?= $queryExtra ?>">
        <?= $totalPaginas ?>
    </a>

<?php } ?>

    <?php if ($pagina < $totalPaginas) { ?>
        <a href="index.php?pagina=<?= $pagina + 1 ?><?= $queryExtra ?>">
            Siguiente &raquo;
        </a>
    <?php } ?>

</div>
<?php
if (esAdmin()) {
    include "nuevo.php";
}
?>

</main>
 <script src="scriptvs2.js"></script>
<?php include "includes/footer.php"; ?>
