<?php

function estaLogueado(): bool
{
return isset($_SESSION["id_usuario"]);
}

function tieneRol(string $rol): bool
{
return isset($_SESSION["rol"])
&& $_SESSION["rol"] === $rol;
}

function esAdmin(): bool
{
return tieneRol("Admin");
}

function esEditor(): bool
{
return tieneRol("Editor");
}

function puedeEditarContenido(): bool
{
return esAdmin() || esEditor();
}

function exigirLogin(): void
{
if (!estaLogueado()) {
header("Location: login.php");
exit();
}
}

function exigirAdmin(): void
{
exigirLogin();
if (!esAdmin()) {
header("Location: index.php?acceso=denegado");
exit();
}
}

function exigirEditorOAdmin(): void
{
exigirLogin();
if (!puedeEditarContenido()) {
header("Location: index.php?acceso=denegado");
exit();
}
}

function generarTokenCSRF(): string
{
if (empty($_SESSION["csrf_token"])) {
$_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}
return $_SESSION["csrf_token"];
}

function validarTokenCSRF(?string $token): bool
{
return !empty($token)
&& !empty($_SESSION["csrf_token"])
&& hash_equals($_SESSION["csrf_token"], $token);
}

?>