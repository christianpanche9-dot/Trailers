<?php
require_once "conexion.php";
header(
"Content-Type: application/json; charset=UTF-8"
);
$buscar = "";
if (isset($_GET["buscar"])) {
$buscar = trim($_GET["buscar"]);
}
if (mb_strlen($buscar) < 2) {
echo json_encode(
[],
JSON_UNESCAPED_UNICODE
);
exit;
}
$sql = "
SELECT DISTINCT
p.id_peliculas,
p.titulo,
p.director,
p.categoria,
p.anio
FROM peliculas p
LEFT JOIN peliculas_actores pa
ON p.id_peliculas = pa.id_peliculas
LEFT JOIN actores a
ON pa.id_actor = a.id_actor
WHERE
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
ORDER BY p.titulo ASC
LIMIT 8
";
$stmt = $conexion->prepare($sql);
if (!$stmt) {
http_response_code(500);
echo json_encode(
[
"error" => "No se pudo preparar la consulta."
],
JSON_UNESCAPED_UNICODE
);
exit;
}
$texto_busqueda = "%" . $buscar . "%";
$stmt->bind_param(
"ssssss",
$texto_busqueda,
$texto_busqueda,
$texto_busqueda,
$texto_busqueda,
$texto_busqueda,
$texto_busqueda
);
$stmt->execute();
$resultado = $stmt->get_result();
$peliculas = [];
while ($fila = $resultado->fetch_assoc()) {
$peliculas[] = [
"id_peliculas" => (int) $fila["id_peliculas"],
"titulo" => $fila["titulo"],
"director" => $fila["director"],
"categoria" => $fila["categoria"],
"anio" => $fila["anio"],
];
}
echo json_encode(
$peliculas,
JSON_UNESCAPED_UNICODE
);
$stmt->close();
$conexion->close();