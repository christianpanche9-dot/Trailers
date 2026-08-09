<?php

function obtenerCategorias($conexion) {
$sql = "SELECT DISTINCT categoria FROM peliculas ORDER BY categoria ASC";
$resultado = $conexion->query($sql);
$categorias = [];
while ($fila = $resultado->fetch_assoc()) {
$categorias[] = $fila["categoria"];
}
return $categorias;
}


function obtenerPeliculaMasAntigua($conexion) {

    $sql = "SELECT titulo, anio
            FROM peliculas
            WHERE anio = (SELECT MIN(anio) FROM peliculas)";

    $stmt = $conexion->prepare($sql);
    $stmt->execute();

    return $stmt->get_result();
}

function obtenerPeliculaMasReciente($conexion) {

    $sql = "SELECT titulo, anio
            FROM peliculas
            WHERE anio = (SELECT MAX(anio) FROM peliculas)";

    $stmt = $conexion->prepare($sql);
    $stmt->execute();

    return $stmt->get_result();
}

function obtenerTop3PeliculasPopulares($conexion) {

    $sql = "SELECT
                p.id_peliculas,
                p.titulo,
                COUNT(DISTINCT v.id_visualizacion) AS reproducciones,
                AVG(pt.puntuacion) AS media,
                COUNT(DISTINCT pt.id_puntuacion) AS total_votos
            FROM peliculas p
            INNER JOIN visualizacion v
                ON p.id_peliculas = v.id_peliculas
            LEFT JOIN puntuaciones pt
                ON pt.id_peliculas = p.id_peliculas
            GROUP BY p.id_peliculas, p.titulo
            ORDER BY reproducciones DESC
            LIMIT 3";

    $stmt = $conexion->prepare($sql);
    $stmt->execute();

    return $stmt->get_result();
}

function insertarPelicula($conexion, $titulo, $director, $anio, $categoria, $link, $imagenes) {
    $sql = "INSERT INTO peliculas (titulo, director, anio, categoria, link, imagenes)
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ssisss", $titulo, $director, $anio, $categoria, $link, $imagenes);
    if ($stmt->execute()) {
        return $conexion->insert_id;
    }

    return false;
}

function insertarActorPelicula($conexion, $id_pelicula, $id_actor, $personaje) {

    $sql = "INSERT INTO peliculas_actores (id_peliculas, id_actor, personaje)
            VALUES (?, ?, ?)";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("iis", $id_pelicula, $id_actor, $personaje);

    return $stmt->execute();
}

function insertarActor($conexion, $nombre, $apellidos, $nacionalidad, $fecha_nacimiento, $foto) {
$sql = "INSERT INTO actores (nombre, apellidos, nacionalidad, fecha_nacimiento, foto)
VALUES (?, ?, ?, ?, ?)";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("sssss", $nombre, $apellidos, $nacionalidad, $fecha_nacimiento, $foto);
return $stmt->execute();
}

function obtenerActoresConPeliculas($conexion) {

    $sql = "SELECT
                a.id_actor,
                a.nombre,
                a.apellidos,
                a.nacionalidad,
                a.fecha_nacimiento,
                a.foto,
                GROUP_CONCAT(CONCAT(p.titulo, ' (', pa.personaje, ')') SEPARATOR ', ') AS peliculas
            FROM actores a
            LEFT JOIN peliculas_actores pa
                ON a.id_actor = pa.id_actor
            LEFT JOIN peliculas p
                ON pa.id_peliculas = p.id_peliculas
            GROUP BY
                a.id_actor,
                a.nombre,
                a.apellidos,
                a.nacionalidad,
                a.fecha_nacimiento,
                a.foto
            ORDER BY a.nombre";

    $stmt = $conexion->prepare($sql);
    $stmt->execute();

    return $stmt->get_result();
}

function obtenerActorPorId($conexion, $id_actor) {

    $sql = "SELECT
                id_actor,
                nombre,
                apellidos,
                nacionalidad,
                fecha_nacimiento,
                foto
            FROM actores
            WHERE id_actor = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_actor);
    $stmt->execute();

    return $stmt->get_result();
}

function obtenerPeliculasDeActor($conexion, $id_actor) {

    $sql = "SELECT
                p.id_peliculas,
                p.titulo,
                pa.personaje
            FROM peliculas p
            INNER JOIN peliculas_actores pa
                ON p.id_peliculas = pa.id_peliculas
            WHERE pa.id_actor = ?
            ORDER BY p.titulo";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_actor);
    $stmt->execute();

    return $stmt->get_result();
}

function rutaFotoActor($foto) {
    if (empty($foto)) {
        return "imagenes/sin-imagen.jpg";
    }
    if (preg_match('/^https?:\/\//i', $foto)) {
        return $foto;
    }
    return "imagenes/" . $foto;
}

function buscarActoresPeliculas($conexion, $texto){

    $sql = "SELECT
                a.id_actor,
                a.nombre,
                a.apellidos,
                a.nacionalidad,
                a.fecha_nacimiento,
                a.foto,
                GROUP_CONCAT(DISTINCT CONCAT(p.titulo, ' (', pa.personaje, ')')SEPARATOR ', ') AS peliculas
            FROM actores a
            LEFT JOIN peliculas_actores pa
                ON a.id_actor = pa.id_actor
            LEFT JOIN peliculas p
                ON pa.id_peliculas = p.id_peliculas
            WHERE
                a.nombre LIKE ?
                OR a.apellidos LIKE ?
                OR p.titulo LIKE ?
            GROUP BY a.id_actor
            ORDER BY a.nombre ASC";

    $stmt = $conexion->prepare($sql);

    $buscar = "%" . $texto . "%";

    $stmt->bind_param("sss", $buscar, $buscar, $buscar);

    $stmt->execute();

    return $stmt->get_result();
}

function obtenerPeliculasConActores($conexion) {

    $sql = "SELECT
                p.id_peliculas,
                p.titulo,
                p.director,
                p.categoria,
                GROUP_CONCAT(
                    CONCAT(a.nombre, ' ', a.apellidos, ' (', pa.personaje, ')')
                    SEPARATOR ', '
                ) AS actores
            FROM peliculas p
            LEFT JOIN peliculas_actores pa
                ON p.id_peliculas = pa.id_peliculas
            LEFT JOIN actores a
                ON pa.id_actor = a.id_actor
            GROUP BY
                p.id_peliculas,
                p.titulo,
                p.director,
                p.categoria
            ORDER BY p.titulo ASC";

    $stmt = $conexion->prepare($sql);
    $stmt->execute();

    return $stmt->get_result();
}

function buscarPeliculas($conexion, $buscar = "", $categoria = "", $orden = "anio_desc", $limite = null, $offset = null)
{
    switch ($orden) {
        case "anio_asc":
            $orden_sql = "p.anio ASC";
            break;

        case "titulo_asc":
            $orden_sql = "p.titulo ASC";
            break;

        case "titulo_desc":
            $orden_sql = "p.titulo DESC";
            break;

        case "director_asc":
            $orden_sql = "p.director ASC";
            break;

        case "categoria_asc":
            $orden_sql = "p.categoria ASC";
            break;

        case "anio_desc":
        default:
            $orden_sql = "p.anio DESC";
            break;
    }

    $sql = "
        SELECT
            p.id_peliculas,
            p.titulo,
            p.director,
            p.anio,
            p.categoria,
            p.link,
            p.imagenes,
            GROUP_CONCAT(
                CONCAT(a.nombre,' ',a.apellidos,' (',pa.personaje,')')
                SEPARATOR ', '
            ) AS actores
        FROM peliculas p
        LEFT JOIN peliculas_actores pa
            ON p.id_peliculas = pa.id_peliculas
        LEFT JOIN actores a
            ON pa.id_actor = a.id_actor
        WHERE 1=1
    ";

    $tipos = "";
    $parametros = [];

    if ($buscar != "") {

        $sql .= "
            AND (
                p.titulo LIKE ?
                OR p.director LIKE ?
                OR p.categoria LIKE ?
                OR a.nombre LIKE ?
                OR a.apellidos LIKE ?
                OR CONCAT(
                a.nombre,
                ' ',
                a.apellidos
                ) LIKE ?
                )
                ";

        $texto = "%".$buscar."%";

        $tipos .= "ssssss";

        $parametros[] = $texto;
        $parametros[] = $texto;
        $parametros[] = $texto;
        $parametros[] = $texto;
        $parametros[] = $texto;
        $parametros[] = $texto;
    }

    if ($categoria != "") {

        $sql .= " AND p.categoria = ? ";

        $tipos .= "s";

        $parametros[] = $categoria;
    }

    $sql .= "
        GROUP BY
            p.id_peliculas,
            p.titulo,
            p.director,
            p.anio,
            p.categoria,
            p.link,
            p.imagenes

        ORDER BY $orden_sql
    ";

    if ($limite !== null) {
        $sql .= " LIMIT ? OFFSET ?";
        $tipos .= "ii";
        $parametros[] = $limite;
        $parametros[] = $offset;
    }

    $stmt = $conexion->prepare($sql);

    if (!empty($parametros)) {
        $stmt->bind_param($tipos, ...$parametros);
    }

    $stmt->execute();

    return $stmt->get_result();
}

function contarPeliculasFiltradas($conexion, $buscar = "", $categoria = "")
{
    $sql = "
        SELECT COUNT(DISTINCT p.id_peliculas) AS total
        FROM peliculas p
        LEFT JOIN peliculas_actores pa
            ON p.id_peliculas = pa.id_peliculas
        LEFT JOIN actores a
            ON pa.id_actor = a.id_actor
        WHERE 1=1
    ";

    $tipos = "";
    $parametros = [];

    if ($buscar != "") {
        $sql .= "
            AND (
                p.titulo LIKE ?
                OR p.director LIKE ?
                OR p.categoria LIKE ?
                OR a.nombre LIKE ?
                OR a.apellidos LIKE ?
                OR CONCAT(a.nombre, ' ', a.apellidos) LIKE ?
            )
        ";

        $texto = "%" . $buscar . "%";
        $tipos .= "ssssss";
        $parametros = [$texto, $texto, $texto, $texto, $texto, $texto];
    }

    if ($categoria != "") {
        $sql .= " AND p.categoria = ? ";
        $tipos .= "s";
        $parametros[] = $categoria;
    }

    $stmt = $conexion->prepare($sql);

    if (!empty($parametros)) {
        $stmt->bind_param($tipos, ...$parametros);
    }

    $stmt->execute();

    return (int) $stmt->get_result()->fetch_assoc()["total"];
}

function convertirYoutubeEmbed($url)
{
    return str_replace("watch?v=", "embed/", $url);
}

function obtenerUsuarios($conexion)
{
    $sql = "SELECT id_usuarios, nombre, email, rol, fecha_alta, foto
            FROM usuarios";

    $stmt = $conexion->prepare($sql);
    $stmt->execute();

    return $stmt->get_result();
}

function eliminarPelicula($conexion, $id)
{
    $sql = "DELETE FROM peliculas WHERE id_peliculas = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id);

    return $stmt->execute();
}

function eliminarUsuario($conexion, $id)
{
    $sql = "DELETE FROM usuarios WHERE id_usuarios = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id);

    return $stmt->execute();
}

function insertarOActualizarPuntuacion($conexion, $id_pelicula, $id_usuario, $puntuacion)
{
    $sql = "INSERT INTO puntuaciones (id_usuarios, id_peliculas, puntuacion)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE puntuacion = VALUES(puntuacion), fecha_puntuacion = NOW()";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("iii", $id_usuario, $id_pelicula, $puntuacion);

    return $stmt->execute();
}

function obtenerPuntuacionUsuario($conexion, $id_pelicula, $id_usuario)
{
    $sql = "SELECT puntuacion
            FROM puntuaciones
            WHERE id_peliculas = ? AND id_usuarios = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ii", $id_pelicula, $id_usuario);
    $stmt->execute();

    $fila = $stmt->get_result()->fetch_assoc();

    return $fila ? (int) $fila["puntuacion"] : null;
}

function obtenerResumenPuntuacion($conexion, $id_pelicula)
{
    $sql = "SELECT AVG(puntuacion) AS media, COUNT(*) AS total
            FROM puntuaciones
            WHERE id_peliculas = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_pelicula);
    $stmt->execute();

    return $stmt->get_result()->fetch_assoc();
}

function insertarComentario($conexion, $id_pelicula, $id_usuario, $comentario, $spoiler)
{
    $sql = "INSERT INTO comentarios (id_usuarios, id_peliculas, comentario, spoiler)
            VALUES (?, ?, ?, ?)";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("iisi", $id_usuario, $id_pelicula, $comentario, $spoiler);

    return $stmt->execute();
}

function obtenerComentariosPelicula($conexion, $id_pelicula)
{
    $sql = "SELECT
                c.id_comentario,
                c.id_usuarios,
                c.id_peliculas,
                c.comentario,
                c.spoiler,
                c.estado,
                c.fecha_comentario,
                c.fecha_modificacion,
                u.nombre AS autor
            FROM comentarios c
            INNER JOIN usuarios u ON u.id_usuarios = c.id_usuarios
            WHERE c.id_peliculas = ?
            ORDER BY c.fecha_comentario DESC";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_pelicula);
    $stmt->execute();

    return $stmt->get_result();
}

function obtenerComentarioPorId($conexion, $id_comentario)
{
    $sql = "SELECT * FROM comentarios WHERE id_comentario = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_comentario);
    $stmt->execute();

    return $stmt->get_result()->fetch_assoc();
}

function actualizarComentario($conexion, $id_comentario, $comentario, $spoiler)
{
    $sql = "UPDATE comentarios
            SET comentario = ?, spoiler = ?, fecha_modificacion = NOW()
            WHERE id_comentario = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sii", $comentario, $spoiler, $id_comentario);

    return $stmt->execute();
}

function eliminarComentario($conexion, $id_comentario)
{
    $sql = "DELETE FROM comentarios WHERE id_comentario = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_comentario);

    return $stmt->execute();
}

function publicarComentario($conexion, $id_comentario)
{
    $sql = "UPDATE comentarios SET estado = 'publicado' WHERE id_comentario = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_comentario);

    return $stmt->execute();
}

function obtenerComentariosPorUsuario($conexion, $id_usuario)
{
    $sql = "SELECT
                c.id_comentario,
                c.comentario,
                c.spoiler,
                c.estado,
                c.fecha_comentario,
                c.fecha_modificacion,
                p.id_peliculas,
                p.titulo
            FROM comentarios c
            INNER JOIN peliculas p ON p.id_peliculas = c.id_peliculas
            WHERE c.id_usuarios = ?
            ORDER BY c.fecha_comentario DESC";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();

    return $stmt->get_result();
}