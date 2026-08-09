document.addEventListener("DOMContentLoaded", function () {
    const botones = document.querySelectorAll(".btnTrailer");

    botones.forEach(function(boton){
        boton.addEventListener("click", function(){
            // 1. Mostrar/Ocultar el tráiler (Tu lógica original)
            const id = this.dataset.target;
            mostrarTrailer(id);

            // 2. Capturar datos para la base de datos
            const idPeliculas = this.getAttribute('data-pelicula');
            const idUsuarios = this.getAttribute('data-usuario');

            console.log("Datos capturados - Película:", idPeliculas, "Usuario:", idUsuarios);

            if (!idUsuarios || idUsuarios.trim() === "") {
                console.log("Atención: No se enviará el clic porque no has iniciado sesión.");
                return;
            }

            // Enviamos los datos a index.php`
            fetch('registrar_visualizacion.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id_peliculas=${encodeURIComponent(idPeliculas)}&id_usuarios=${encodeURIComponent(idUsuarios)}`
            })
            .then(response => response.text())
            .then(data => {
                console.log("Respuesta del servidor:", data);
            })
            .catch(error => {

                console.error("Error en la petición AJAX:", error);
            });
        });
    });
    console.log("Script cargado");

        const checks = document.querySelectorAll(".checkActor");

        console.log(checks.length);

        checks.forEach(function(check){

            check.addEventListener("change", function(){

        const personaje = this.closest(".campo").nextElementSibling;

        if(this.checked){
            personaje.style.display = "block";
        }else{
            personaje.style.display = "none";
            personaje.querySelector("input").value = "";
        }

    });

});
});

function mostrarTrailer(id){
    const trailers = document.querySelectorAll(".trailer");

    trailers.forEach(function(trailer){
        if(trailer.id !== id){
            trailer.style.display = "none";
            trailer.src = trailer.dataset.src;
        }
    });

    const actual = document.getElementById(id);

    if(actual.style.display === "none" || actual.style.display === ""){
        actual.style.display = "block";
        actual.src = actual.dataset.src +
            (actual.dataset.src.includes("?") ? "&" : "?") +
            "autoplay=1";
    } else {
        actual.style.display = "none";
        actual.src = actual.dataset.src;
    }
}
const campoBuscar = document.querySelector("#buscar");
const cajaSugerencias = document.querySelector("#sugerencias");

if (campoBuscar) {

let temporizadorBusqueda = null;
let controladorPeticion = null;

campoBuscar.addEventListener("input", function () {
const texto = campoBuscar.value.trim();
clearTimeout(temporizadorBusqueda);

if (texto.length < 2) {
ocultarSugerencias();
return;
}
temporizadorBusqueda = setTimeout(function () {
buscarPeliculas(texto);
}, 350);
});

async function buscarPeliculas(texto) {
    if (controladorPeticion) {
controladorPeticion.abort();
}
controladorPeticion = new AbortController();
cajaSugerencias.hidden = false;
cajaSugerencias.innerHTML = `
<p class="mensaje-sugerencias">
Buscando películas...
</p>
`;
try {
const respuesta = await fetch(
"buscar_sugerencias.php?buscar="
+ encodeURIComponent(texto),
{
signal: controladorPeticion.signal
}
);
if (!respuesta.ok) {
throw new Error(
"La respuesta del servidor no es correcta."
);
}
const peliculas = await respuesta.json();
console.log(peliculas);
mostrarSugerencias(peliculas);}
 catch (error) {
    if (error.name === "AbortError") {
return;
}
cajaSugerencias.hidden = false;
cajaSugerencias.innerHTML = `
<p class="mensaje-sugerencias error-sugerencias">
No se pudieron cargar las sugerencias.
</p>
`;
console.error(error);
}
}

function mostrarSugerencias(peliculas) {
cajaSugerencias.innerHTML = "";
if (peliculas.length === 0) {
cajaSugerencias.hidden = false;
cajaSugerencias.innerHTML = `
<p class="mensaje-sugerencias">
No se han encontrado coincidencias.
</p>
`;
return;
}
const lista = document.createElement("ul");
lista.classList.add("lista-sugerencias");
peliculas.forEach(function (pelicula) {
const elemento = document.createElement("li");
elemento.classList.add("sugerencia");
const enlace = document.createElement("a");
enlace.href =
"pelicula.php?id="
+ pelicula.id_peliculas;
const titulo = document.createElement("strong");
titulo.textContent = pelicula.titulo;
const informacion = document.createElement("span");
informacion.textContent =
pelicula.director
+ " · "
+ pelicula.categoria;
enlace.appendChild(titulo);
enlace.appendChild(informacion);
elemento.appendChild(enlace);
lista.appendChild(elemento);
});
cajaSugerencias.appendChild(lista);
cajaSugerencias.hidden = false;
}

function ocultarSugerencias() {
cajaSugerencias.hidden = true;
cajaSugerencias.innerHTML = "";
}

document.addEventListener("click", function (evento) {
const clicDentroDelBuscador =
evento.target.closest(".buscador-dinamico");
if (!clicDentroDelBuscador) {
ocultarSugerencias();
}
});
campoBuscar.addEventListener("focus", function () {
const texto = campoBuscar.value.trim();
if (
texto.length >= 2
&& cajaSugerencias.innerHTML !== ""
) {
cajaSugerencias.hidden = false;
}
});
campoBuscar.addEventListener("keydown", function (evento) {
if (evento.key === "Escape") {
ocultarSugerencias();
}
});
}