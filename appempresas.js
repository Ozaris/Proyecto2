// Selección de elementos del DOM mediante querySelector y jQuery
const nav = document.querySelector("#nav"); // Elemento del menú (nav)
const abrir = document.querySelector("#abrir"); // Botón para abrir el menú
const cerrar = document.querySelector("#cerrar"); // Botón para cerrar el menú
const body = document.querySelector("Body"); // El body del documento

// Evento para abrir el menú cuando se hace clic en el botón "abrir"
abrir.addEventListener("click", () => {
    nav.classList.add("visible"); // Añadir la clase 'visible' al nav para mostrarlo
    body.classList.add("no-scroll"); // Añadir la clase 'no-scroll' al body para evitar que la página se desplace mientras el menú está abierto
});

// Evento para cerrar el menú cuando se hace clic en el botón "cerrar"
cerrar.addEventListener("click", () => {
    nav.classList.remove("visible"); // Eliminar la clase 'visible' para ocultar el nav
    body.classList.remove("no-scroll"); // Eliminar la clase 'no-scroll' para permitir el desplazamiento de nuevo en el body
});

// Código relacionado con el buscador de usuarios
$(document).ready(function() {
    let debounceTimer; // Variable para controlar el tiempo de espera para evitar llamadas excesivas al servidor

    // Evento cuando se escribe en el input del buscador
    $("#buscador").keyup(function() {
        clearTimeout(debounceTimer); // Limpiar el temporizador anterior para no hacer solicitudes innecesarias

        var input = $(this).val(); // Obtener el valor ingresado en el campo de búsqueda

        debounceTimer = setTimeout(function() {
            // Solo hacer la solicitud si el input no está vacío
            if (input != "") {
                $.ajax({
                    url: "RF_buscar_user.php", // URL del archivo PHP que procesará la búsqueda
                    method: "POST", // Método de la solicitud (POST)
                    data: { input: input }, // Enviar el valor del input como parámetro
                    success: function(data) {
                        // Mostrar los resultados de búsqueda en el contenedor correspondiente
                        $("#resultado_busqueda").html(data).css("display", "block");
                    },
                    error: function() {
                        // Si ocurre un error, mostrar un mensaje de error
                        $("#resultado_busqueda").html("Error en la búsqueda").css("display", "block");
                    }
                });
            } else {
                // Si el input está vacío, ocultar los resultados de búsqueda
                $("#resultado_busqueda").css("display", "none");
            }
        }, 300); // Esperar 300ms después de que el usuario deje de escribir para hacer la solicitud AJAX
    });

    // Evento cuando se cambia la categoría seleccionada
    $("#categoriaSelect").change(function() {
        var categoriaSeleccionada = $(this).val(); // Obtener el valor de la categoría seleccionada
        
        // Actualizar el texto de las categorías de las publicaciones en la interfaz
        $(".card-text .text-muted").each(function() {
            $(this).text("Categoría: " + categoriaSeleccionada); // Cambiar el texto a la nueva categoría
        });
    });
});

// Código para la funcionalidad de las tarjetas de recomendaciones
$(document).ready(function() {
    // Añadir evento de clic a las tarjetas de recomendados
    $('.cartaderecomendados').click(function() {
        // Eliminar la clase 'selected' de todas las tarjetas
        $('.cartaderecomendados').removeClass('selected');
        
        // Añadir la clase 'selected' solo a la tarjeta que se ha clickeado
        $(this).addClass('selected');
        
        // Obtener el valor de la categoría desde el atributo 'data-categoria' de la tarjeta
        var categoria = $(this).data('categoria');
        
        // Llamar a la función para filtrar las publicaciones por la categoría seleccionada
        filtrarPublicaciones(categoria);
    });
});

// Función para filtrar las publicaciones por categoría
function filtrarPublicaciones(categoria) {
    // Verificamos si la categoría seleccionada es 'Todos'
    if (categoria === 'Todos') {
        // Si la categoría es 'Todos', mostramos todas las publicaciones sin filtrar
        $.ajax({
            url: 'filtrar_publicaciones.php', // URL para obtener las publicaciones
            method: 'POST', // Método de la solicitud (POST)
            data: { categoria: 'Todos' }, // Enviar 'Todos' para obtener todas las publicaciones
            success: function(data) {
                $('#publicacionesContainer').html(data); // Mostrar todas las publicaciones en el contenedor
            },
            error: function() {
                // Si ocurre un error al filtrar, mostrar un mensaje de alerta
                alert("Error al filtrar las publicaciones.");
            }
        });
    } else {
        // Si no es 'Todos', filtramos por la categoría específica
        $.ajax({
            url: 'filtrar_publicaciones.php', // URL para obtener las publicaciones filtradas
            method: 'POST', // Método de la solicitud (POST)
            data: { categoria: categoria }, // Enviar la categoría seleccionada
            success: function(data) {
                $('#publicacionesContainer').html(data); // Mostrar las publicaciones filtradas en el contenedor
            },
            error: function() {
                // Si ocurre un error al filtrar, mostrar un mensaje de alerta
                alert("Error al filtrar las publicaciones.");
            }
        });
    }
}
