<?php
include_once "conexion.php";
session_start();

// Conectar a la base de datos
$con = conectar_bd(); 

// Obtener los datos de la sesión o cookies
$nom = $_COOKIE['nombre'] ?? null;
$foto = $_COOKIE['user_picture'] ?? $_COOKIE['foto'] ?? null;
$rol = $_COOKIE['rol'] ?? null;

// Verificar si el formulario de publicación fue enviado
if (isset($_POST["envio-pub"])) {
    // Obtener los datos del formulario
    $titulo = $_POST["titulo"];
    $categoria = $_POST["categoria"];
    $descripcion = $_POST["descripcion"];
    $lat = $_POST["lat"]; // Obtener latitud
    $lon = $_POST["lon"]; // Obtener longitud
    $email_emp = $_COOKIE['email_emp'] ?? null;
    $tipo = $_POST["publicacion"];

    // Verifica si se ha enviado una imagen recortada
    if (isset($_POST['imagen_prod_recortada'])) {
        // Obtener la imagen recortada en formato base64
        $imagen_recortada = $_POST['imagen_prod_recortada'];

        // Procesar la imagen base64 para guardarla como un archivo
        $imagen_recortada = str_replace('data:image/jpeg;base64,', '', $imagen_recortada);
        $imagen_recortada = str_replace(' ', '+', $imagen_recortada);
        $data = base64_decode($imagen_recortada);

        // Generar un nombre único para el archivo
        $nombre_imagen = 'uploads/' . uniqid('recortada_', true) . '.jpg';

        // Guardar la imagen recortada en el servidor
        if (file_put_contents($nombre_imagen, $data)) {
            // Llamada a la función para crear la publicación con la imagen recortada
            crear_pub($con, $titulo, $categoria, $descripcion, $email_emp, $nombre_imagen, $lat, $lon, $tipo);
        } else {
            echo "Error al guardar la imagen recortada.";
        }
    } else {
        // Si no se ha enviado una imagen recortada, procesamos la imagen original
        if (isset($_FILES['imagen_prod']) && $_FILES['imagen_prod']['error'] == 0) {
            // Obtiene la información del archivo
            $imagen = $_FILES['imagen_prod'];
            $rutaDestino = 'uploads/' . basename($imagen['name']);

            // Mueve el archivo a la carpeta deseada
            if (move_uploaded_file($imagen['tmp_name'], $rutaDestino)) {
                // Llamada a la función para crear la publicación
                crear_pub($con, $titulo, $categoria, $descripcion, $email_emp, $rutaDestino, $lat, $lon, $tipo);
            } else {
                echo "Error al subir la imagen.";
            }
        } else {
            echo "No se ha seleccionado ninguna imagen o ha ocurrido un error.";
        }
    }
}

// Obtener los datos del usuario si está logueado
if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];
    $sql = "SELECT * FROM persona WHERE email='$email'";
    $resultado = $con->query($sql);

    if ($data = $resultado->fetch_assoc()) {
        $nombre_p = $data['nombre_p'];
        $foto2 = "img_usr/default.png";
        $email = $data['email'];
        $foto = $data['foto'] ?? $foto2;
        $rol = $data['rol'];

        // Guardar los datos en cookies para usarlos en otras páginas
        setcookie("nombre", $nombre_p, time() + 4200, "/");
        setcookie("foto", $foto, time() + (86400 * 30), "/");
        setcookie("email_emp", $email, time() + (86400 * 30), "/");
        setcookie("rol", $rol, time() + 4200, "/");
    } else {
        // Datos de usuario no encontrados
        $nombre_p = 'Nombre no disponible';
        $email = 'Email no disponible';
        $foto = 'img_usr/default.png';
        $rol ='inv';
    }
} else {
    // Usuario no logueado
    $nombre_p = 'Nombre no disponible';
    $email = 'Email no disponible';
    $foto = 'default.png';
    $rol = 'inv';
}

// Función para crear la publicación en la base de datos
function crear_pub($con, $titulo, $categoria, $descripcion, $email_emp, $img, $lat, $lon, $tipo) {
    // Consultar el ID del usuario basado en su correo
    $consulta_login = "SELECT * FROM persona WHERE email = '$email_emp'";
    $resultado_login = mysqli_query($con, $consulta_login);

    if ($resultado_login && mysqli_num_rows($resultado_login) > 0) {
        $fila = mysqli_fetch_assoc($resultado_login);
        $id_per = $fila['Id_per'];

        // Insertar la publicación en la tabla 'publicacion_prod'
        $consulta_insertar_persona = "INSERT INTO publicacion_prod (titulo, categoria, descripcion_prod, imagen_prod, Id_per, lat, lon, tipo) 
                                      VALUES ('$titulo', '$categoria', '$descripcion', '$img', '$id_per', '$lat', '$lon', '$tipo')";
        
        if (mysqli_query($con, $consulta_insertar_persona)) {
            echo "Publicación creada exitosamente.";
        } else {
            echo "Error al insertar en publicacion_prod: " . mysqli_error($con);
        }
    } else {
        echo "No se encontró ningún usuario con ese email.";
    }
}

// Función para truncar el texto (si es necesario)
function truncateText($text, $maxWords) {
    $words = explode(' ', $text);
    if (count($words) > $maxWords) {
        return implode(' ', array_slice($words, 0, $maxWords)) . '...';
    }
    return $text;
}
?>



<!DOCTYPE html>
<html lang="en" class="htmlempresas">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="lib/bootstrap.min.css">
    <link rel="icon" href="style/Imagenes/logoproyecto.png">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
    <title>Empresas - Ozaris</title>
</head>
<body class="bodyempresas">

<!-- +++++++++++++++++++++++++++ HEADER +++++++++++++++++++++++++++ -->
<div class="divpadreempresas">
    <div class="headermenuempresas">
        <a href="index.php"><img class="logo" src="style/Imagenes/logoproyecto.png" alt="Logo"></a>
        <button id="abrir" class="abrirmenuinicio"><i class="fa-solid fa-bars"></i></button>
        <nav class="navheaderinicio" id="nav">
            <img class="logosheaderinicio" src="style/Imagenes/Logos.png" alt="img">
            <button class="cerrarmenuinicio" id="cerrar"><i class="fa-solid fa-x"></i></button>
            <ul class="navlistainicio">
                <li class="lismenu"><a class="asmenuinicio" href="index.php">Inicio</a></li>
                <li class="lismenu"><a class="psmenuinicio">|</a></li>
                <li class="lismenu"><a class="asmenuinicio" href="index.php#map">Ubicación</a></li>
                <li class="lismenu"><a class="psmenuinicio">|</a></li>
                <li class="lismenu"><a class="asmenuinicio" href="contacto.html">Contacto</a></li>
                <li class="lismenu"><a class="psmenuinicio">|</a></li>
                <li class="lismenu">
                    <div class="dropdown">
                        <button class="fotondeperfil" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="<?php echo htmlspecialchars("img_usr/$foto") ?? htmlspecialchars("$foto2") ; ?>" alt="img" class="imgpequeñoperfil">
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="javascript:void(0);" onclick="redireccion()">Perfil</a></li>
                            <?php 
                                if ($rol === 'empresa') {
                                    echo " <li><a class='dropdown-item item2' href='mispublicaciones.php'>Mis publicaciones</a></li>";
                                } elseif ($rol === 'admin') {
                                    echo " <li><a class='dropdown-item item2' href='admin.php'>Control</a></li>";
                                }
                            ?>
                        </ul>
                    </div>
                </li>
            </ul>
        </nav>
    </div>
<!-- +++++++++++++++++++++++++++ FIN DE HEADER +++++++++++++++++++++++++++ -->

<!-- +++++++++++++++++++++++++++ BUSCADOR +++++++++++++++++++++++++++ -->
    <div class="divprincipalbuscador" id="bus">
        <input type="text" name="buscador" id="buscador" maxlength="20" placeholder="Buscar">
        <div id="resultado_busqueda"></div>
    </div>
<!-- +++++++++++++++++++++++++++ FIN DE BUSCADOR +++++++++++++++++++++++++++ -->

<!-- +++++++++++++++++++++++++++ FILTROS +++++++++++++++++++++++++++ -->
    <div class="containerempresas">
        <div class="divrecomendacionesempresas" id="filtro">
            <div class="div1recomendaciones"><h3>Filtros</h3></div>
            <div class="div2recomendaciones">
                <div class="cartaderecomendados" data-categoria="Todos" onclick="filtrarPublicaciones('Todos')">
                    <img class="logorecomendados" src="style/Imagenes/todo.png" alt="img">
                    <p>Todos</p>
                </div>
                <div class="cartaderecomendados" data-categoria="Electronica" onclick="filtrarPublicaciones('Electronica')">
                    <img class="logorecomendados" src="style/Imagenes/Electronica.png" alt="img">
                    <p>Electronica</p>
                </div>
                <div class="cartaderecomendados" data-categoria="Gaming" onclick="filtrarPublicaciones('Gaming')">
                    <img class="logorecomendados" src="style/Imagenes/Entretenimiento.png" alt="img">
                    <p>Gaming</p>
                </div>
                <div class="cartaderecomendados" data-categoria="Ropa" onclick="filtrarPublicaciones('Ropa')">
                    <img class="logorecomendados" src="style/Imagenes/Ropa.png" alt="img">
                    <p>Ropa</p>
                </div>
                <div class="cartaderecomendados" data-categoria="Deporte" onclick="filtrarPublicaciones('Deporte')">
                    <img class="logorecomendados" src="style/Imagenes/Deporte.png" alt="img">
                    <p>Deporte</p>
                </div>
                <div class="cartaderecomendados" data-categoria="Familia" onclick="filtrarPublicaciones('Familia')">
                    <img class="logorecomendados" src="style/Imagenes/familia.png" alt="img">
                    <p>Familia</p>
                </div>
                <div class="cartaderecomendados" data-categoria="Mascotas" onclick="filtrarPublicaciones('Mascotas')">
                    <img class="logorecomendados" src="style/Imagenes/mascotas.png" alt="img">
                    <p>Mascotas</p>
                </div>
                <div class="cartaderecomendados" data-categoria="Musica" onclick="filtrarPublicaciones('Musica')">
                    <img class="logorecomendados" src="style/Imagenes/musica.png" alt="img">
                    <p>Musica</p>
                </div>
                <div class="cartaderecomendados" data-categoria="Propiedades" onclick="filtrarPublicaciones('Propiedades')">
                    <img class="logorecomendados" src="style/Imagenes/propiedad.png" alt="img">
                    <p>Propiedades</p>
                </div>
                <div class="cartaderecomendados" data-categoria="Vehiculos" onclick="filtrarPublicaciones('Vehiculos')">
                    <img class="logorecomendados" src="style/Imagenes/Vehiculos.png" alt="img">
                    <p>Vehiculos</p>
                </div>
            </div>
        </div>
    </div>
<!-- +++++++++++++++++++++++++++ FIN DE FILTROS +++++++++++++++++++++++++++ -->

<!-- +++++++++++++++++++++++++++ BOTON PUBLICAR +++++++++++++++++++++++++++ -->
    <?php
        if ($rol === 'empresa') {
            echo '<div class="divbtn-primary">
                    <a href="publicar.php" class="btn-primary">Subir publicación</a>
                  </div>';
        }
    ?>
<!-- +++++++++++++++++++++++++++ FIN DE BOTON PUBLICAR +++++++++++++++++++++++++++ -->

<!-- +++++++++++++++++++++++++++ PUBLICACIONES +++++++++++++++++++++++++++ -->
    <div>
        <h3 class="h3publiem">Publicaciones <i class="fa-solid fa-icons"></i></h3>
        <div class="divprincipalpublisem" id="publicacionesContainer">
            <?php
                // Obtener las publicaciones de la base de datos
                $consulta_publicaciones = "SELECT p.*, pe.* FROM publicacion_prod p JOIN persona pe ON p.Id_per = pe.Id_per  WHERE p.tipo = 'publicacion' ORDER BY p.created_at DESC";
                $resultado_publicaciones = mysqli_query($con, $consulta_publicaciones);

                if ($resultado_publicaciones && mysqli_num_rows($resultado_publicaciones) > 0) {
                    while ($publicacion = mysqli_fetch_assoc($resultado_publicaciones)) {
                        $id_prod = $publicacion['id_prod'];
                        $nom_empp = $publicacion['foto'];
                        $tituloTruncado = truncateText($publicacion['titulo'], 3);
                        $descripcionTruncada = truncateText($publicacion['descripcion_prod'], 3);
                        ?>
                        <form class="containerpublis" action="PublicacionD.php" method="POST">
                            <div class="cardempresas">
                                <img src="<?php echo htmlspecialchars($publicacion['imagen_prod']); ?>" class="imgcardpubliem" alt="Imagen de publicación">
                                <div class="cardempresasbody">
                                    <input type="hidden" name="id_prod" value="<?php echo htmlspecialchars($id_prod); ?>">
                                    <h5 class="card-title"><?php echo htmlspecialchars($tituloTruncado); ?></h5>
                                    <p class="card-text"><?php echo htmlspecialchars($descripcionTruncada); ?></p>
                                    <p class="card-text"><small class="text-muted"><i class="fa-solid fa-layer-group"></i> Categoría: <?php echo htmlspecialchars($publicacion['categoria']); ?></small></p>
                                    <p class="card-text">
                                        <small class="text-muted">
                                            <div class="textopublicadopor">
                                                <img class="imagenlogitopublicaciones" src="<?php echo 'img_usr/'.$nom_empp;?>" alt="img">
                                                <?php echo htmlspecialchars($publicacion['nombre_p']); ?>
                                            </div>
                                        </small>
                                    </p>
                                </div>
                                <input class="botonverpubliem" type="submit" value="Ver más" name="pub">
                            </div>
                        </form>
                        <?php
                    }
                } else {
                    echo "<p>No hay publicaciones disponibles.</p>";
                }
            ?>
        </div>
    </div>
<!-- +++++++++++++++++++++++++++ FIN DE PUBLICACIONES +++++++++++++++++++++++++++ -->

<!-- +++++++++++++++++++++++++++ SCRIPTS +++++++++++++++++++++++++++ -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="lib/jquery.js"></script>
    <script src="appempresas.js"></script>
    <script type="text/javascript">
        function redireccion() {
            <?php 
                if (isset($_SESSION['email'])) { 
                    echo "window.location.href = 'Perfil.php';"; 
                } else { 
                    echo "window.location.href = 'iniciodesesion.html';"; 
                }
            ?>
        }
    </script>
<!-- +++++++++++++++++++++++++++ FIN DE SCRIPTS +++++++++++++++++++++++++++ -->

</body>
</html>
