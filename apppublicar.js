// Inicializar el mapa centrado en Paysandú
const map = L.map('map').setView([-32.3219, -58.0792], 13);

// Capa de CartoDB
L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
    maxZoom: 19,
    attribution: '© OpenStreetMap, © CartoDB',
}).addTo(map);

// Variables globales
let marcador;
let cropper;

// Evento de clic en el mapa
map.on('click', function (e) {
    const lat = e.latlng.lat;
    const lon = e.latlng.lng;

    // Establecer latitud y longitud en los campos correspondientes
    document.getElementById('lat').value = lat;
    document.getElementById('lon').value = lon;

    // Actualizar o crear el marcador
    if (marcador) {
        marcador.setLatLng(e.latlng);
    } else {
        marcador = L.marker(e.latlng).addTo(map);
    }
});

// Funcionalidad para mostrar la vista previa de la imagen seleccionada
document.getElementById('formFile').addEventListener('change', function (event) {
    const file = event.target.files[0];
    const reader = new FileReader();

    // Destruir el cropper anterior si existe
    if (cropper) {
        cropper.destroy();
        cropper = null;
    }

    reader.onload = function (e) {
        const img = document.createElement('img');
        img.id = 'image';
        img.src = e.target.result;

        // Limpiar la imagen previa si existe
        const cropperBox = document.querySelector('.cropper-box');
        const existingImage = cropperBox.querySelector('img');
        if (existingImage) {
            cropperBox.removeChild(existingImage);
        }

        // Mostrar el panel de recorte
        document.getElementById('crop-container').style.display = 'flex';
        cropperBox.style.display = 'block';
        cropperBox.appendChild(img);
        document.querySelector('label[for="formFile"]').style.display = 'none';

        // Inicializar Cropper.js con la nueva imagen
        cropper = new Cropper(img, {
            aspectRatio: 16 / 9,
            viewMode: 1,
            ready: function () {
                // Aquí puedes hacer cosas adicionales si es necesario
            }
        });
    };

    if (file) {
        reader.readAsDataURL(file); // Leer el archivo como una URL de datos
    }
});

// Función para eliminar la imagen seleccionada
document.getElementById('botonEliminarImagen').addEventListener('click', function () {
    // Limpiar los campos y ocultar elementos
    document.getElementById('formFile').value = '';
    document.getElementById('imagePreview').style.display = 'none';
    document.querySelector('label[for="formFile"]').style.display = 'block';
    document.getElementById('botonEliminarImagen').style.display = 'none';

    // Eliminar la imagen y reiniciar el cropper
    if (cropper) {
        cropper.destroy();
        cropper = null;
    }

    // Limpiar el contenedor del cropper
    const cropperBox = document.querySelector('.cropper-box');
    const existingImage = cropperBox.querySelector('img');
    if (existingImage) {
        cropperBox.removeChild(existingImage);
    }

    // Ocultar elementos relacionados al cropper
    cropperBox.style.display = 'none';
    document.getElementById('crop-container').style.display = 'none';
});

// Guardar el recorte cuando se haga clic en el botón
document.getElementById('save-button').addEventListener('click', function () {
    document.getElementById('formFile').value = '';
    const canvas = cropper.getCroppedCanvas();
    const croppedImage = canvas.toDataURL('image/jpeg');

    // Mostrar la vista previa de la imagen recortada
    document.getElementById('imagePreview').style.backgroundImage = `url(${croppedImage})`;
    document.getElementById('imagePreview').style.display = 'block';
    document.getElementById('crop-container').style.display = 'none';
    document.getElementById('botonEliminarImagen').style.display = 'block';

    // Crear un campo oculto con la imagen recortada y agregarlo al formulario
    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.name = 'imagen_prod_recortada';
    hiddenInput.value = croppedImage;
    document.querySelector('form').appendChild(hiddenInput);
});

// Cancelar el recorte
document.getElementById('cancel-button').addEventListener('click', function () {
    document.getElementById('crop-container').style.display = 'none';
    document.getElementById('formFile').value = '';
    document.getElementById('imagePreview').style.display = 'none';
    document.querySelector('label[for="formFile"]').style.display = 'block';
    document.getElementById('botonEliminarImagen').style.display = 'none';
});

// Lógica para el contador de caracteres
const textarea = document.getElementById('descripcion');
const charCount = document.getElementById('charCount');
const maxLength = textarea.getAttribute('maxlength');

// Actualizar el contador de caracteres restantes
textarea.addEventListener('input', () => {
    const remaining = maxLength - textarea.value.length;
    charCount.textContent = `${remaining} caracteres restantes`;
});

// Función para validar la entrada
function validateInput() {
    const textarea = document.getElementById('descripcion');
    const titleInput = document.getElementById('titulo');
    const maxLength = textarea.getAttribute('maxlength');

    // Filtrar palabras en la descripción y título que superen los 20 caracteres
    const wordsDescription = textarea.value.split(/\s+/).filter(word => word.length <= 20);
    const wordsTitle = titleInput.value.split(/\s+/).filter(word => word.length <= 20);

    // Si se encuentra alguna palabra demasiado larga en la descripción, actualiza el valor
    if (wordsDescription.length !== textarea.value.split(/\s+/).length) {
        textarea.value = wordsDescription.join(' ');
        alert('Las palabras en la descripción no pueden tener más de 20 caracteres.');
    }

    // Si se encuentra alguna palabra demasiado larga en el título, actualiza el valor
    if (wordsTitle.length !== titleInput.value.split(/\s+/).length) {
        titleInput.value = wordsTitle.join(' ');
        alert('Las palabras en el título no pueden tener más de 20 caracteres.');
    }
}
