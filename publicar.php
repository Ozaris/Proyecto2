<?php
session_start();

// Obtener los datos de la sesión o cookies
$nom = $_COOKIE['nombre'] ?? 'Nombre no disponible';
$foto = $_COOKIE['foto'] ?? 'default.png';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publicar Producto</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="style/style.css">
    <link rel="icon" href="style/Imagenes/logoproyecto.png">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
    <link href="https://cdn.jsdelivr.net/npm/cropperjs/dist/cropper.min.css" rel="stylesheet">
    <style>
        /* Estilo para la vista previa de la imagen */
        #imagePreview {
            width: 100%;
            height: 200px;
            background-size: cover;
            background-position: center;
            display: none; /* Oculto por defecto */
        }

        .iconomaspublicacion {
            display: block;
            text-align: center;
        }

        .botoneliminarimagen {
            display: none; /* Oculto por defecto */
        }

        .caracteresletrasalerta {
            font-size: 12px;
            color: gray;
        }

        /* Estilos generales */
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 50px;
        }

        #image-container {
            margin-top: 20px;
            position: relative;
        }

        #image {
            max-width: 40%;
            height: auto;
        }

        /* Contenedor flotante para el panel de recorte */
        #crop-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: none; /* Oculto por defecto */
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }


        .cropper-box {
            background-color: white;
            max-width: 70%;
            max-height: 90%;
            overflow: hidden;
            position: relative;
        }


        button {
            padding: 10px 20px;
            margin-top: 20px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <!-- Modal -->
    <div class="modal modal-xl fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
        <form action="empresas.php" method="POST" enctype="multipart/form-data">
        <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalLabel">Publicar</h1>
                    </div>
                    <div class="modal-body">
                        <div class="divprincipalpublicacion">
                            <div class="divsubirimagen">
                                <label for="formFile" class="form-label" id="labelFile">
                                    <i class="fa-solid fa-2x fa-plus iconomaspublicacion"></i>
                                </label>
                                <button type="button" class="botoneliminarimagen" id="botonEliminarImagen" style="display: none;">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                                <input class="form-control form-control1" type="file" id="formFile" name="imagen_prod" accept="image/jpeg,jpg,png">
                                <div id="imagePreview" class="image-preview"></div> <!-- Vista previa -->
                            </div>

                            <div class="divubicacionempresa">
                                <div class="mapaempresas" id="map"></div> <!-- Mapa debajo -->
                            </div>

                            <div class="divsubirinformacion">
                                <div class="divdatosinformacion">
                                <input type="text" class="form-control inputpublicacion1" maxlength="30" id="titulo" placeholder="Título" name="titulo" oninput="validateInput()" required>                                    
                                    <select class="selectpublicar" id="categoriaSelect" name="categoria" required>
                                        <option value="Electrónica">Electrónica</option>
                                        <option value="Gaming">Gaming</option>
                                        <option value="Ropa">Ropa</option>
                                        <option value="Deporte">Deporte</option>
                                        <option value="Familia">Familia</option>
                                        <option value="Mascotas">Mascotas</option>
                                        <option value="Musica">Musica</option>
                                        <option value="Propiedades">Propiedades</option>
                                        <option value="Vehículos">Vehículos</option>
                                    </select>
                                    <input type="hidden" value="publicacion" id="publicacion" name="publicacion">
                                    
                                    <textarea class="form-control inputpublicacion3" placeholder="Descripción" id="descripcion" name="descripcion" maxlength="300" style="height: 100px" oninput="validateInput()" required></textarea>
                                    <span id="charCount">300 caracteres restantes</span> <!-- Contador de caracteres para descripción -->
                                    </div>

                                <p id="coordenadas"></p>
                                <input type="hidden" id="lat" name="lat" value="">
                                <input type="hidden" id="lon" name="lon" value="">

                                <div class="divinformacionempresa">
                                    <h6>Información de la empresa</h6>
                                    <div class="divnombrempresapublicacion">
                                        <img class="divfotopublicacion" src="<?php echo htmlspecialchars('img_usr/'.$foto); ?>" alt="img">
                                        <h5 class="nombrempresa" id="nombrempresa"><?php echo htmlspecialchars($nom); ?></h5>
                                    </div>
                                </div>

                                <div class="divbotonpublicar">
                                <button type="submit" class="botonsubirpublicacion" value="envio-pub" name="envio-pub">Subir publicación</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Contenedor para el panel de recorte -->
    <div id="crop-container">
        <div class="cropper-box">
            <h3>Recorte:</h3>
            <button id="save-button">Guardar Recorte</button>
            <button id="cancel-button">Cancelar</button>
            <canvas id="canvas"></canvas>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/cropperjs/dist/cropper.min.js"></script>
    <script src="apppublicar.js">
  
</script>
</body>
</html>
