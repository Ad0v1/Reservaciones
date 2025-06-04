<?php
// Desactivar la visualización de errores en el navegador web
ini_set('display_errors', 0);
error_reporting(0);

// Función para registrar errores en el log sin mostrarlos al usuario
function logError($message, $error = null) {
    $errorLog = date('Y-m-d H:i:s') . " - " . $message;
    if ($error) {
        $errorLog .= " - " . $error;
    }
    error_log($errorLog);
}

error_reporting(E_ALL);
ini_set('display_errors', 1);
include "db.php"; // Conexión a la base de datos
if (!$con) {
    die("Error de conexión: " . mysqli_connect_error());
}
$error = '';
$success = '';

// Obtener fechas bloqueadas
$bloqueadas_sql = "SELECT fecha_reserva, hora_reserva, SUM(cantidad_personas) as total 
                    FROM reservas 
                    GROUP BY fecha_reserva, hora_reserva 
                    HAVING total >= 250";
$bloqueadas_result = mysqli_query($con, $bloqueadas_sql);
$fechas_bloqueadas = [];

while ($row = mysqli_fetch_assoc($bloqueadas_result)) {
    $fechas_bloqueadas[$row['fecha_reserva']][$row['hora_reserva']] = $row['total'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger datos del formulario
    $nombre = isset($_POST['rname']) ? mysqli_real_escape_string($con, $_POST['rname']) : '';
    $telefono = isset($_POST['rphone']) ? mysqli_real_escape_string($con, $_POST['rphone']) : '';
    $email = isset($_POST['email']) ? mysqli_real_escape_string($con, $_POST['email']) : null;
    $fecha = isset($_POST['rdate']) ? mysqli_real_escape_string($con, $_POST['rdate']) : '';
    $hora = isset($_POST['rtime']) ? mysqli_real_escape_string($con, $_POST['rtime']) : '';
    $personas = isset($_POST['rparty-size']) ? intval($_POST['rparty-size']) : 0;
    $info = isset($_POST['radd-info']) ? mysqli_real_escape_string($con, $_POST['radd-info']) : '';
    $cardholder_name = isset($_POST['cardholder_name']) ? mysqli_real_escape_string($con, $_POST['cardholder_name']) : '';
    $numero_tarjeta = isset($_POST['numero_tarjeta']) ? mysqli_real_escape_string($con, $_POST['numero_tarjeta']) : '';
    $fecha_expiracion = isset($_POST['fecha_expiracion']) ? mysqli_real_escape_string($con, $_POST['fecha_expiracion']) : '';
    $cvc = isset($_POST['cvc']) ? mysqli_real_escape_string($con, $_POST['cvc']) : '';

    // Debug - Mostrar los datos recibidos
    error_log("Datos recibidos: " . print_r($_POST, true));

    // Validar campos obligatorios
    $camposObligatorios = [
        'Nombre' => $nombre,
        'Teléfono' => $telefono,
        'Fecha' => $fecha,
        'Hora' => $hora,
        'Número de personas' => $personas,
        'Nombre del titular' => $cardholder_name,
        'Número de tarjeta' => $numero_tarjeta,
        'Fecha de expiración' => $fecha_expiracion,
        'CVC' => $cvc
    ];

    $camposFaltantes = [];
    foreach ($camposObligatorios as $campo => $valor) {
        if (empty($valor)) {
            $camposFaltantes[] = $campo;
        }
    }

    if (!empty($camposFaltantes)) {
        $error = "Los siguientes campos obligatorios están vacíos: " . implode(", ", $camposFaltantes);
        error_log("Campos faltantes: " . implode(", ", $camposFaltantes));
    } else {
        try {
            mysqli_begin_transaction($con);
            // Validar formato de teléfono
            if (!preg_match('/^9\d{8}$/', $telefono)) {
                throw new Exception("El teléfono debe tener 9 dígitos y empezar con 9.");
            }

            // Convertir fecha a formato MySQL
            $fecha_obj = DateTime::createFromFormat('Y-m-d', $fecha);
            if (!$fecha_obj) {
                throw new Exception("Formato de fecha inválido.");
            }
            $fecha_mysql = $fecha_obj->format('Y-m-d');

            // Validar fecha y hora no pasadas
            $fecha_actual = new DateTime();
            $fecha_reserva = DateTime::createFromFormat('Y-m-d H:i', $fecha . ' ' . $hora);

            if ($fecha_reserva < $fecha_actual) {
                throw new Exception("No se puede reservar en fechas/horas pasadas.");
            }

            // Validar capacidad
            $capacidad_sql = "SELECT SUM(cantidad_personas) as total FROM reservas WHERE fecha_reserva = ? AND hora_reserva = ?";
            $stmt_cap = mysqli_prepare($con, $capacidad_sql);
            mysqli_stmt_bind_param($stmt_cap, "ss", $fecha_mysql, $hora);
            mysqli_stmt_execute($stmt_cap);
            $result_cap = mysqli_stmt_get_result($stmt_cap);
            $row_cap = mysqli_fetch_assoc($result_cap);
            $total_personas = ($row_cap['total'] ?? 0) + $personas;

            if ($total_personas > 250) {
                throw new Exception("¡No hay reservaciones disponibles para esa hora! El aforo está completo (250 personas).");
            }

            // Registrar/obtener usuario (con verificación de error)
            if (isset($usuario) && $usuario) {
                $user_id = $usuario['id_usuario'];
            } else {
                // Crear un nuevo usuario si no existe
                $insert_user = "INSERT INTO usuarios (nombre, telefono, email) VALUES (?, ?, ?)";
                $stmt_user = mysqli_prepare($con, $insert_user);
                mysqli_stmt_bind_param($stmt_user, "sss", $nombre, $telefono, $email);
                
                if (!mysqli_stmt_execute($stmt_user)) {
                    throw new Exception("Error al registrar usuario: " . mysqli_error($con));
                }
                $user_id = mysqli_insert_id($con);
            }

            // Procesar menús y calcular total
            $total = 0;
            $detalles = [];

            // Procesar Desayuno
            if (!empty($_POST['desayuno']) && !empty($_POST['pan_desayuno'])) {
                $insert_desayuno = "INSERT INTO desayuno (bebida, pan) VALUES (?, ?)";
                $stmt_desayuno = mysqli_prepare($con, $insert_desayuno);
                mysqli_stmt_bind_param($stmt_desayuno, "ss", $_POST['desayuno'], $_POST['pan_desayuno']);
                
                if (!mysqli_stmt_execute($stmt_desayuno)) {
                    throw new Exception("Error al registrar desayuno: " . mysqli_error($con));
                }
                
                $id_desayuno = mysqli_insert_id($con);
                $subtotal = 9.00 * $personas;
                $total += $subtotal;

                $detalles[] = [
                    'tipo' => 'desayuno',
                    'id' => $id_desayuno,
                    'subtotal' => $subtotal
                ];
            }

            // Almuerzo
            if (!empty($_POST['almuerzo_entrada']) && !empty($_POST['almuerzo_fondo'])) {
                $insert_almuerzo = "INSERT INTO almuerzo (entrada, plato_fondo, postre, bebida) VALUES (?, ?, ?, ?)";
                $stmt_almuerzo = mysqli_prepare($con, $insert_almuerzo);
                $postre = !empty($_POST['almuerzo_postre']) ? $_POST['almuerzo_postre'] : '';
                $bebida = !empty($_POST['almuerzo_bebida']) ? $_POST['almuerzo_bebida'] : '';
                mysqli_stmt_bind_param($stmt_almuerzo, "ssss", $_POST['almuerzo_entrada'], $_POST['almuerzo_fondo'], $postre, $bebida);
                
                if (!mysqli_stmt_execute($stmt_almuerzo)) {
                    throw new Exception("Error al registrar almuerzo: " . mysqli_error($con));
                }
                
                $id_almuerzo = mysqli_insert_id($con);
                $subtotal = 14.50 * $personas;
                $total += $subtotal;

                $detalles[] = [
                    'tipo' => 'almuerzo',
                    'id' => $id_almuerzo,
                    'subtotal' => $subtotal
                ];
            }

            // Cena
            if (!empty($_POST['cena'])) {
                $insert_cena = "INSERT INTO cena (plato, postre, bebida) VALUES (?, ?, ?)";
                $stmt_cena = mysqli_prepare($con, $insert_cena);
                $postre = !empty($_POST['cena_postre']) ? $_POST['cena_postre'] : '';
                $bebida = !empty($_POST['cena_bebida']) ? $_POST['cena_bebida'] : '';
                mysqli_stmt_bind_param($stmt_cena, "sss", $_POST['cena'], $postre, $bebida);
                
                if (!mysqli_stmt_execute($stmt_cena)) {
                    throw new Exception("Error al registrar cena: " . mysqli_error($con));
                }
                
                $id_cena = mysqli_insert_id($con);
                $subtotal = 16.50 * $personas;
                $total += $subtotal;

                $detalles[] = [
                    'tipo' => 'cena',
                    'id' => $id_cena,
                    'subtotal' => $subtotal
                ];
            }

            // Insertar reserva principal
            $sql_reserva = "INSERT INTO reservas (id_usuario, fecha_reserva, hora_reserva, cantidad_personas, total, estado, info_adicional) 
                VALUES (?, ?, ?, ?, ?, 'Confirmado', ?)";
            $stmt_reserva = mysqli_prepare($con, $sql_reserva);
            mysqli_stmt_bind_param($stmt_reserva, "issdds", $user_id, $fecha_mysql, $hora, $personas, $total, $info);
            
            if (!mysqli_stmt_execute($stmt_reserva)) {
                throw new Exception("Error al registrar reserva: " . mysqli_error($con));
            }
            
            $reserva_id = mysqli_insert_id($con);

            // Insertar detalles de la reserva
            foreach ($detalles as $detalle) {
                $sql_detalle = "INSERT INTO detalle_reserva (id_reserva, id_{$detalle['tipo']}, cantidad, subtotal) 
                                VALUES (?, ?, ?, ?)";
                $stmt_detalle = mysqli_prepare($con, $sql_detalle);
                mysqli_stmt_bind_param($stmt_detalle, "iiid", $reserva_id, $detalle['id'], $personas, $detalle['subtotal']);
                
                if (!mysqli_stmt_execute($stmt_detalle)) {
                    throw new Exception("Error al registrar detalle de reserva: " . mysqli_error($con));
                }
            }

            // Registrar pago - Usando consulta dinámica para adaptarse a la estructura de la tabla
            $columnas_pagos = [];
            $valores_pagos = [];
            $tipos_pagos = "";
            $params_pagos = [];

            // Agregar campos básicos que sabemos que existen
            $columnas_pagos[] = "id_reserva";
            $valores_pagos[] = "?";
            $tipos_pagos .= "i";
            $params_pagos[] = $reserva_id;

            $columnas_pagos[] = "metodo_pago";
            $valores_pagos[] = "?";
            $tipos_pagos .= "s";
            $params_pagos[] = "Tarjeta";

            $columnas_pagos[] = "monto_pagado";
            $valores_pagos[] = "?";
            $tipos_pagos .= "d";
            $params_pagos[] = $total;

            // Intentar agregar campos adicionales si existen en la tabla
            // Verificar si la tabla tiene la columna numero_tarjeta
            $check_column = mysqli_query($con, "SHOW COLUMNS FROM pagos LIKE 'numero_tarjeta'");
            if (mysqli_num_rows($check_column) > 0) {
                $columnas_pagos[] = "numero_tarjeta";
                $valores_pagos[] = "?";
                $tipos_pagos .= "s";
                $params_pagos[] = substr($numero_tarjeta, -4); // Últimos 4 d��gitos
            }

            // Verificar si la tabla tiene la columna fecha_expiracion
            $check_column = mysqli_query($con, "SHOW COLUMNS FROM pagos LIKE 'fecha_expiracion'");
            if (mysqli_num_rows($check_column) > 0) {
                $columnas_pagos[] = "fecha_expiracion";
                $valores_pagos[] = "?";
                $tipos_pagos .= "s";
                $params_pagos[] = $fecha_expiracion;
            }

            // Verificar si la tabla tiene la columna cvc
            $check_column = mysqli_query($con, "SHOW COLUMNS FROM pagos LIKE 'cvc'");
            if (mysqli_num_rows($check_column) > 0) {
                $columnas_pagos[] = "cvc";
                $valores_pagos[] = "?";
                $tipos_pagos .= "s";
                $params_pagos[] = $cvc;
            }

            // Verificar si la tabla tiene la columna cardholder_name
            $check_column = mysqli_query($con, "SHOW COLUMNS FROM pagos LIKE 'cardholder_name'");
            if (mysqli_num_rows($check_column) > 0) {
                $columnas_pagos[] = "cardholder_name";
                $valores_pagos[] = "?";
                $tipos_pagos .= "s";
                $params_pagos[] = $cardholder_name;
            }

            // Construir la consulta dinámica
            $sql_pago = "INSERT INTO pagos (" . implode(", ", $columnas_pagos) . ") VALUES (" . implode(", ", $valores_pagos) . ")";
            $stmt_pago = mysqli_prepare($con, $sql_pago);

            // Enlazar parámetros dinámicamente
            if ($stmt_pago) {
                // Crear array de referencias para bind_param
                $refs = [];
                $refs[] = &$tipos_pagos;
                foreach ($params_pagos as $key => $value) {
                    $refs[] = &$params_pagos[$key];
                }
                
                call_user_func_array([$stmt_pago, 'bind_param'], $refs);
                
                if (!mysqli_stmt_execute($stmt_pago)) {
                    // Registrar el error pero no mostrarlo al usuario
                    logError("Error al registrar pago", mysqli_error($con));
                    // Continuar con la ejecución sin mostrar error técnico
                }
            } else {
                logError("Error al preparar la consulta de pago", mysqli_error($con));
            }

            // Commit de la transacción
            mysqli_commit($con);
            $success = "¡Reserva exitosa! Tu reserva ha sido confirmada.";
            error_log("Reserva exitosa con ID: " . $reserva_id);
        } catch (Exception $e) {
            mysqli_rollback($con); // Revertir en caso de error
            $error = "Error: " . $e->getMessage();
            logError("Error en la reserva", $e->getMessage());
        }
    }
}

// Variables para JavaScript
$esPost = ($_SERVER["REQUEST_METHOD"] == "POST") ? 'true' : 'false';
$mostrarDesayuno = (!empty($_POST['desayuno']) || !empty($_POST['pan_desayuno'])) ? 'true' : 'false';
$mostrarAlmuerzo = (!empty($_POST['almuerzo_entrada']) || !empty($_POST['almuerzo_fondo']) || !empty($_POST['almuerzo_postre']) || !empty($_POST['almuerzo_bebida'])) ? 'true' : 'false';
$mostrarCena = (!empty($_POST['cena']) || !empty($_POST['cena_postre']) || !empty($_POST['cena_bebida'])) ? 'true' : 'false';
?>

<!DOCTYPE html>
<html lang="es" class="no-js">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservaciones - Kawaii</title>
    
    <link rel="stylesheet" href="CSS/estilo.css">
    <link rel="stylesheet" href="CSS/proveedor.css">
    <link rel="stylesheet" href="CSS/Reserva.css">

    <link rel="apple-touch-icon" sizes="180x180" href="Logo-apple.png">
    <link rel="Logo-32x32" type="image/png" sizes="32x32" href="Logo-32x32.png">
    <link rel="Logo-16x16" type="image/png" sizes="16x16" href="Logo-16x16.png">
    <link rel="manifest" href="site.webmanifest">
</head>

<body id="top">

    <!--Precarga-->
    <div id="preloader">
        <div id="loader" class="dots-fade">
            <div></div>
            <div></div>
            <div></div>
        </div>
    </div>

    <!--Ajuste de página-->
    <div id="page" class="s-pagewrap">
        <div class="menu-underlay"></div>
        <!--Encabezado-->
        <header class="s-header">
            <div class="s-header__block">
                <div class="s-header__logo">
                    <a class="logo" href="Index.html">
                        <img src="MULTIMEDIA/LOGO/LOGO.jpg" alt="Página de inicio">
                    </a>
                </div>
                <a class="s-header__menu-toggle" href="#0"><span>Navegar</span></a>
                <div class="s-header__cta">
                    <a href="Index.html" class="btn btn--primary s-header__cta-btn">Volver</a>
                </div>
            </div>
            <nav class="s-header__nav">
                <a href="#0" class="s-header__nav-close-btn" title="Cerrar"><span>Cerrar</span></a>
                <div class="s-header__nav-logo">
                    <a href="Index.html">
                        <img src="MULTIMEDIA/LOGO/LOGO.jpg" alt="Página de inicio">
                    </a>
                </div>
                <ul class="s-header__nav-links">
                    <li><a href="Index.html">Inicio</a></li>
                    <li><a href="Carta.html">Carta</a></li>
                    <li><a href="About.html">Acerca de</a></li>
                    <li class="current"><a href="SeleccionMenu.html">Reservaciones</a></li>
                </ul>
                <div class="s-header__nav-bottom">
                    <h6>Solicitud de Reserva</h6>
                    <div class="s-header__booking">
                        <div class="s-header__booking-no"><a href="tel:+51980436234">+51 980 436 234</a></div>
                    </div>
                    <ul class="s-header__nav-social social-list">
                        <li>
                            <a href="https://www.facebook.com/unionbiblicadelperu" target="_blank">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill:rgba(0, 0, 0, 1);">
                                    <path d="M20,3H4C3.447,3,3,3.448,3,4v16c0,0.552,0.447,1,1,1h8.615v-6.96h-2.338v-2.725h2.338v-2c0-2.325,1.42-3.592,3.5-3.592 
                                        c0.699-0.002,1.399,0.034,2.095,0.107v2.42h-1.435c-1.128,0-1.348,0.538-1.348,1.325v1.735h2.697l-0.35,2.725h-2.348V21H20 
                                        c0.553,0,1-0.448,1-1V4C21,3.448,20.553,3,20,3z"></path></svg>
                                <span class="u-screen-reader-text">Facebook</span>
                            </a>

                            <a href="https://wa.me/+51980436234" target="_blank">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M17.472 14.51c-.292-.147-1.728-.853-1.995-.95-.266-.098-.46-.147-.654.147-.196.293-.75.95-.92 1.147-.17.196-.34.22-.632.073-.292-.146-1.233-.455-2.35-1.45-.867-.773-1.45-1.732-1.617-2.024-.17-.293-.017-.45.13-.598.133-.132.292-.34.437-.51.146-.17.196-.293.292-.487.097-.196.05-.366-.024-.51-.073-.146-.654-1.593-.896-2.182-.236-.568-.477-.49-.654-.5h-.555c-.196 0-.51.073-.776.366-.266.293-1.017.996-1.017 2.43s1.042 2.82 1.188 3.013c.147.195 2.04 3.115 4.946 4.243.69.298 1.227.475 1.646.608.692.22 1.323.189 1.82.114.555-.085 1.728-.707 1.97-1.39.243-.683.243-1.268.17-1.39-.073-.121-.266-.194-.555-.34z"/>
                                    <path d="M12 2C6.485 2 2 6.485 2 12c0 1.85.503 3.68 1.457 5.265L2 22l4.956-1.243A9.947 9.947 0 0012 22c5.515 0 10-4.485 10-10S17.515 2 12 2zm0 18c-1.63 0-3.228-.433-4.606-1.252l-.331-.195-2.945.736.79-2.798-.212-.336A7.925 7.925 0 014 12c0-4.411 3.589-8 8-8s8 3.589 8 8-3.589 8-8 8z"/></svg>
                                <span class="u-screen-reader-text">WhatsApp</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
        </header>

        <!--Contenido principal-->
        <article class="s-content">
            <!--Encabezado de página-->
            <section class="s-pageheader pageheader" style="background-image:url(MULTIMEDIA/PAGEHEADER/Restaurante.jpg)">
                <div class="row">
                    <div class="column xl-12 s-pageheader__content">
                        <h1 class="page-title">
                            Reservaciones
                        </h1>
                        <!-- Agregar después del <h1> en la sección de encabezado -->
                        <?php if (!empty($error)): ?>
                        <div class="alert error">
                            <?php 
                            // Mostrar un mensaje genérico si es un error técnico
                            if (strpos($error, "Unknown column") !== false) {
                                echo "Ha ocurrido un problema técnico. Por favor, inténtelo de nuevo más tarde.";
                            } else {
                                echo $error;
                            }
                            ?>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($success)): ?>
                        <div class="alert success"><?php echo $success; ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
            <!--Contenido de la página-->
            <section class="s-pagecontent pagecontent">
                <div class="row width-narrower pageintro text-center">
                    <div class="column xl-12">
                        <p class="lead">
                            Haz tu reserva y vive una experiencia inolvidable. Asegura tu mesa con anticipación y disfruta de nuestra cocina en un ambiente acogedor. Ya sea para una cena especial, una reunión con amigos o una celebración, estamos listos para recibirte. ¡Reserva ahora y déjanos hacer de tu visita un momento único!
                        </p>
                    </div>
                </div>
            </section>

            <!-- Agrega un indicador de pasos -->
            <div class="progress-steps">
                <div class="step active" data-step="Menú">1</div>
                <div class="step" data-step="Datos">2</div>
                <div class="step" data-step="Resumen">3</div>
                <div class="step" data-step="Pago">4</div>
            </div>

            <!-- Formulario único -->
            <form id="rform" method="post" action="Reserva.php" autocomplete="off">
                <!-- Paso 1: Selección del Menú -->
                <div id="step1" class="form-step active">
                    <h1>Selecciona tu Menú</h1>
                    <?php if($error && !$success): ?>
                    <div class="alert error"><?= $error ?></div>
                    <?php endif; ?>
                    <?php if($success): ?>
                    <div class="alert success"><?= $success ?></div>
                    <?php endif; ?>
                    <div class="controles-menu">
                        <button type="button" class="boton-menu" onclick="mostrarSeccion('desayuno')">Desayuno</button>
                        <button type="button" class="boton-menu" onclick="mostrarSeccion('almuerzo')">Almuerzo</button>
                        <button type="button" class="boton-menu" onclick="mostrarSeccion('cena')">Cena</button>
                    </div>

                    <!-- Sección Desayuno -->
                    <div id="seccion-desayuno" class="seccion-menu">
                        <h2>Desayuno</h2>
                        <label for="desayuno">Bebida:</label>
                        <select id="desayuno" name="desayuno">
                            <?php $selected = $_POST['desayuno'] ?? ''; ?>
                            <option value="">No incluir</option>
                            <option value="Café con leche" <?= $selected == 'Café con leche' ? 'selected' : '' ?>>Café con leche</option>
                            <option value="Quaker con manzana" <?= $selected == 'Quaker con manzana' ? 'selected' : '' ?>>Quaker con manzana</option>
                            <option value="Chocolate caliente" <?= $selected == 'Chocolate caliente' ? 'selected' : '' ?>>Chocolate caliente</option>
                            <option value="Quinua con piña" <?= $selected == 'Quinua con piña' ? 'selected' : '' ?>>Quinua con piña</option>
                        </select>

                        <label for="pan_desayuno">Pan (elige un relleno):</label>
                        <select id="pan_desayuno" name="pan_desayuno">
                            <?php $selected = $_POST['pan_desayuno'] ?? ''; ?>
                            <option value="">No incluir</option>
                            <option value="Tortilla de verduras" <?= $selected == 'Tortilla de verduras' ? 'selected' : '' ?>>Tortilla de verduras</option>
                            <option value="Huevo revuelto" <?= $selected == 'Huevo revuelto' ? 'selected' : '' ?>>Huevo revuelto</option>
                            <option value="Huevo frito" <?= $selected == 'Huevo frito' ? 'selected' : '' ?>>Huevo frito</option>
                            <option value="Mantequilla y mermelada" <?= $selected == 'Mantequilla y mermelada' ? 'selected' : '' ?>>Mantequilla y mermelada</option>
                            <option value="Camote" <?= $selected == 'Camote' ? 'selected' : '' ?>>Camote</option>
                            <option value="Jamonada" <?= $selected == 'Jamonada' ? 'selected' : '' ?>>Jamonada</option>
                            <option value="Pollo" <?= $selected == 'Pollo' ? 'selected' : '' ?>>Pollo</option>
                        </select>
                    </div>

                    <!-- Sección Almuerzo -->
                    <div id="seccion-almuerzo" class="seccion-menu">
                        <h2>Almuerzo</h2>
                        <label for="almuerzo_entrada">Entrada:</label>
                        <select id="almuerzo_entrada" name="almuerzo_entrada">
                            <?php $selected = $_POST['almuerzo_entrada'] ?? ''; ?>
                            <option value="">No incluir</option>
                            <option value="Papa a la Huancaína" <?= $selected == 'Papa a la Huancaína' ? 'selected' : '' ?>>Papa a la Huancaína</option>
                            <option value="Ocopa Arequipeña" <?= $selected == 'Ocopa Arequipeña' ? 'selected' : '' ?>>Ocopa Arequipeña</option>
                            <option value="Ensalada de fideo" <?= $selected == 'Ensalada de fideo' ? 'selected' : '' ?>>Ensalada de fideo</option>
                            <option value="Crema de rocoto" <?= $selected == 'Crema de rocoto' ? 'selected' : '' ?>>Crema de rocoto</option>
                            <option value="Sopa de casa" <?= $selected == 'Sopa de casa' ? 'selected' : '' ?>>Sopa de casa</option>
                        </select>

                        <label for="almuerzo_fondo">Plato de Fondo:</label>
                        <select id="almuerzo_fondo" name="almuerzo_fondo">
                            <?php $selected = $_POST['almuerzo_fondo'] ?? ''; ?>
                            <option value="">No incluir</option>
                            <option value="Arroz con Pollo" <?= $selected == 'Arroz con Pollo' ? 'selected' : '' ?>>Arroz con Pollo</option>
                            <option value="Ají de Gallina" <?= $selected == 'Ají de Gallina' ? 'selected' : '' ?>>Ají de Gallina</option>
                            <option value="Pollo al Sillao" <?= $selected == 'Pollo al Sillao' ? 'selected' : '' ?>>Pollo al Sillao</option>
                            <option value="Estofado de Pollo" <?= $selected == 'Estofado de Pollo' ? 'selected' : '' ?>>Estofado de Pollo</option>
                            <option value="Frijoles con seco" <?= $selected == 'Frijoles con seco' ? 'selected' : '' ?>>Frijoles con seco</option>
                            <option value="Pollo al horno con ensalada rusa" <?= $selected == 'Pollo al horno con ensalada rusa' ? 'selected' : '' ?>>Pollo al horno con ensalada rusa</option>
                        </select>

                        <label for="almuerzo_postre">Postre:</label>
                            <select id="almuerzo_postre" name="almuerzo_postre">
                            <?php $selected = $_POST['almuerzo_postre'] ?? ''; ?>
                            <option value="">No incluir</option>
                            <option value="Plátano" <?= $selected == 'Plátano' ? 'selected' : '' ?>>Plátano</option>
                            <option value="Manzana" <?= $selected == 'Manzana' ? 'selected' : '' ?>>Manzana</option>
                            <option value="Sandía" <?= $selected == 'Sandía' ? 'selected' : '' ?>>Sandía</option>
                        </select>

                        <label for="almuerzo_bebida">Bebida:</label>
                            <select id="almuerzo_bebida" name="almuerzo_bebida">
                            <?php $selected = $_POST['almuerzo_bebida'] ?? ''; ?>
                            <option value="">No incluir</option>
                            <option value="Té" <?= $selected == 'Té' ? 'selected' : '' ?>>Té</option>
                            <option value="Anís" <?= $selected == 'Anís' ? 'selected' : '' ?>>Anís</option>
                            <option value="Manzanilla" <?= $selected == 'Manzanilla' ? 'selected' : '' ?>>Manzanilla</option>
                            <option value="Chicha morada" <?= $selected == 'Chicha morada' ? 'selected' : '' ?>>Chicha morada</option>
                            <option value="Maracuyá" <?= $selected == 'Maracuyá' ? 'selected' : '' ?>>Maracuyá</option>
                        </select>
                    </div>
        
                    <!-- Sección Cena -->
                    <div id="seccion-cena" class="seccion-menu">
                        <h2>Cena</h2>
                        <label for="cena">Plato Principal:</label>
                        <select id="cena" name="cena">
                            <?php $selected = $_POST['cena'] ?? ''; ?>
                            <option value="">No incluir</option>
                            <option value="Hamburguesa con papas fritas" <?= $selected == 'Hamburguesa con papas fritas' ? 'selected' : '' ?>>Hamburguesa con papas fritas</option>
                            <option value="Pan con Hamburguesa y Papas Fritas" <?= $selected == 'Pan con Hamburguesa y Papas Fritas' ? 'selected' : '' ?>>Pan con Hamburguesa y Papas Fritas</option>
                            <option value="Tallarines Rojos con Bistec" <?= $selected == 'Tallarines Rojos con Bistec' ? 'selected' : '' ?>>Tallarines Rojos con Bistec</option>
                            <option value="Sopa de Verduras y Pan con Pollo" <?= $selected == 'Sopa de Verduras y Pan con Pollo' ? 'selected' : '' ?>>Sopa de Verduras y Pan con Pollo</option>
                            <option value="Pollo al horno con ensalada rusa" <?= $selected == 'Pollo al horno con ensalada rusa' ? 'selected' : '' ?>>Pollo al horno con ensalada rusa</option>
                        </select>

                        <label for="cena_postre">Postre:</label>
                        <select id="cena_postre" name="cena_postre">
                            <?php $selected = $_POST['cena_postre'] ?? ''; ?>
                            <option value="">No incluir</option>
                            <option value="Pudín de Chocolate" <?= $selected == 'Pudín de Chocolate' ? 'selected' : '' ?>>Pudín de Chocolate</option>
                            <option value="Flan" <?= $selected == 'Flan' ? 'selected' : '' ?>>Flan</option>
                            <option value="Gelatina" <?= $selected == 'Gelatina' ? 'selected' : '' ?>>Gelatina</option>
                            <option value="Mazamorra Morada" <?= $selected == 'Mazamorra Morada' ? 'selected' : '' ?>>Mazamorra Morada</option>
                            <option value="Arroz con Leche" <?= $selected == 'Arroz con Leche' ? 'selected' : '' ?>>Arroz con Leche</option>
                            <option value="Torta de Chocolate" <?= $selected == 'Torta de Chocolate' ? 'selected' : '' ?>>Torta de Chocolate</option>
                            <option value="Torta de Limón" <?= $selected == 'Torta de Limón' ? 'selected' : '' ?>>Torta de Limón</option>
                            <option value="Alfajores" <?= $selected == 'Alfajores' ? 'selected' : '' ?>>Alfajores</option>
                            <option value="Mazamorra de Piña" <?= $selected == 'Mazamorra de Piña' ? 'selected' : '' ?>>Mazamorra de Piña</option>
                            <option value="Sandía" <?= $selected == 'Sandía' ? 'selected' : '' ?>>Sandía</option>
                        </select>

                        <label for="cena_bebida">Bebida:</label>
                        <select id="cena_bebida" name="cena_bebida">
                            <?php $selected = $_POST['cena_bebida'] ?? ''; ?>
                            <option value="">No incluir</option>
                            <option value="Té" <?= $selected == 'Té' ? 'selected' : '' ?>>Té</option>
                            <option value="Anís" <?= $selected == 'Anís' ? 'selected' : '' ?>>Anís</option>
                            <option value="Manzanilla" <?= $selected == 'Manzanilla' ? 'selected' : '' ?>>Manzanilla</option>
                            <option value="Chicha morada" <?= $selected == 'Chicha morada' ? 'selected' : '' ?>>Chicha morada</option>
                            <option value="Maracuyá" <?= $selected == 'Maracuyá' ? 'selected' : '' ?>>Maracuyá</option>
                        </select>
                    </div>
                    <button type="button" onclick="nextStep(2)">Continuar</button>
                </div>

                <!-- Paso 2: Datos de la Reserva -->
                <div id="step2" class="form-step">
                    <h1>Datos de la Reserva</h1>
                    <fieldset class="row">
                    <div class="row width-narrower content-block">
                        <div class="column xl-12">
                            <fieldset class="row">
                                <div class="column xl-6 tab-12">
                                    <label for="rname">Nombre: </label>
                                    <input type="text" name="rname" id="rname" class="u-fullwidth" placeholder="Ingresa tu nombre" value="<?php echo isset($_POST['rname']) ? htmlspecialchars($_POST['rname']) : ''; ?>">
                                </div>

                                <div class="column xl-6 tab-12">
                                    <label for="rphone">Número de contacto: </label>
                                    <input type="tel" name="rphone" id="rphone" class="u-fullwidth"  placeholder="Ej: 980436234"  pattern="[9][0-9]{8}"  title="Ingrese un número de 9 dígitos empezando">
                                </div>

                                <div class="column xl-6 tab-12">
                                    <label for="rdate">Fecha: </label>
                                    <input type="date" name="rdate" id="rdate" class="u-fullwidth" min="<?php echo date('Y-m-d'); ?>" required>
                                </div>

                                <div class="column xl-6 tab-12">
                                    <label for="rtime">Hora: </label>
                                    <select name="rtime" id="rtime" class="u-fullwidth" required>
                                        <?php
                                        $horario_apertura = '07:00';
                                        $horario_cierre = '23:00';
                                        $intervalo = 30; // minutos

                                        $hora_actual = strtotime($horario_apertura);
                                        $hora_fin = strtotime($horario_cierre);
    
                                        while ($hora_actual <= $hora_fin) {
                                            $hora= date('H:i', $hora_actual);
                                            echo "<option value='$hora'>$hora</option>";
                                            $hora_actual += $intervalo * 60;
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="column xl-6 tab-12">
                                    <label for="rparty-size">Tamaño del grupo (aprox.): </label>
                                    <input type="number" name="rparty-size" id="rparty-size" class="u-fullwidth" placeholder="Ej.: 50" value="">
                                </div>

                                <div class="column xl-6 tab-12">
                                    <label for="email">Correo electrónico: </label>
                                    <input type="email" name="email" id="email" placeholder="Ej: usuario@correo.com">
                                </div>

                                <div class="column xl-12 message u-add-bottom">
                                    <label for="radd-info">Información adicional: </label>
                                    <textarea name="radd-info" id="radd-info" class="u-fullwidth" placeholder="Escribe aquí tu información adicional"></textarea>
                                </div>
                            </fieldset>
                        </div>
                    </div>
                    </fieldset>
                    <div class="botones-navegacion">
                        <button type="button" class="btn-volver" onclick="prevStep(1)">Volver</button>
                        <button type="button" onclick="nextStep(3)">Ver Resumen</button>
                    </div>
                </div>

                <!-- Paso 3: Resumen -->
                <div id="step3" class="form-step">
                    <h1>RESUMEN DE TU RESERVA</h1>
                    <div class="resumen">
                        <div id="resumenContenido"></div>
                    </div>
                    <div class="botones-navegacion">
                        <button type="button" class="btn-volver" onclick="prevStep(2)">Volver</button>
                        <button type="button" onclick="nextStep(4)">Proceder al Pago</button>
                    </div>
                </div>

                <!-- Paso 4: Método de Pago -->
                <div id="step4" class="form-step">
                    <h1>Información de Pago</h1>
                    
                    <!-- Sección de Tarjeta -->
                    <div class="seccion-tarjeta">
                        <div class="form-row">
                            <label for="nombre-titular">Nombre del titular</label>
                            <input type="text" id="nombre-titular" name="cardholder_name" placeholder="Como aparece en la tarjeta" required autocomplete="off">
                        </div>
                        
                        <div class="form-row">
                            <label for="numero-tarjeta">Número de tarjeta</label>
                            <input type="text" id="numero-tarjeta" name="numero_tarjeta" placeholder="1234 5678 9012 3456" required autocomplete="off" maxlength="19">
                        </div>
                        
                        <div class="form-row-group">
                            <div class="form-row">
                                <label for="fecha-expiracion">Fecha de expiración (MM/AA)</label>
                                <input type="text" id="fecha-expiracion" name="fecha_expiracion" placeholder="MM/AA" required autocomplete="off" maxlength="5">
                            </div>
                            
                            <div class="form-row">
                                <label for="cvc">CVC</label>
                                <input type="text" id="cvc" name="cvc" placeholder="123" required autocomplete="off" maxlength="4">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <small>Nota: Esta es una versión de prueba. En un entorno de producción, se recomienda usar HTTPS para proteger la información de pago.</small>
                        </div>
                    </div>

                    <!-- Botones de Navegación -->
                    <div class="botones-navegacion">
                        <button type="button" class="btn-volver" onclick="prevStep(3)">← Volver</button>
                        <button type="button" class="btn-pagar" onclick="submitPaymentForm()">CONFIRMAR PAGO</button>
                    </div>
                </div>
            </form>

        </article>

        <!--Footer-->
        <footer id="footer" class="s-footer">
            <div class="row s-footer__top row-x-center">
                <div class="column xl-4 lg-6 tab-8 mob-12">
                    <a href="Index.html" class="btn btn--primary btn--large u-fullwidth">Volver al inicio</a>
                </div>
            </div>

            <div class="row s-footer__main-content">
                <div class="column xl-6 md-12 s-footer__block s-footer__about">
                    <div class="s-footer__logo">
                        <a class="logo" href="Index.html">
                            <img src="MULTIMEDIA/LOGO/LOGO.jpg" alt="Página de inicio">
                        </a>
                    </div>
                    <p>La Casona es el lugar perfecto para disfrutar de platos deliciosos en un ambiente acogedor. Te esperamos con la mejor atención y sabores únicos.</p>
                </div>

                <div class="column xl-6 md-12 s-footer__block s-footer__info">
                    <div class="row">
                        <div class="column xl-6 lg-12">
                            <h5>Ubicación</h5>
                            <p>
                            Antigua panamericana, <br>
                            Club Kawai Unión Biblíca del Perú - km 88.8
                            </p>
                        </div>
                        <div class="column xl-6 lg-12">
                            <h5>Contactos</h5>
                            <ul class="link-list">
                                <li><a href="casonaKawai@gmail.com">casonaKawai@gmail.com</a></li>
                                <li><a href="tel:+51980436234">+51 980 436 234</a></li>
                            </ul>
                        </div>
                        <div class="column">
                            <h5>Horario de Atención</h5>
                            <ul class="opening-hours">
                                <li><span class="opening-hours__days">De lunes a domingo</span><span class="opening-hours__time"> 7:00am - 12:00am</span></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row s-footer__bottom">
                <div class="column xl-6 lg-12">
                    <ul class="s-footer__social social-list">
                        <li>
                            <a href="https://www.facebook.com/unionbiblicadelperu" target="_blank">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill:rgba(0, 0, 0, 1);">
                                    <path d="M20,3H4C3.447,3,3,3.448,3,4v16c0,0.552,0.447,1,1,1h8.615v-6.96h-2.338v-2.725h2.338v-2c0-2.325,1.42-3.592,3.5-3.592 
                                        c0.699-0.002,1.399,0.034,2.095,0.107v2.42h-1.435c-1.128,0-1.348,0.538-1.348,1.325v1.735h2.697l-0.35,2.725h-2.348V21H20 
                                        c0.553,0,1-0.448,1-1V4C21,3.448,20.553,3,20,3z"></path></svg>
                                <span class="u-screen-reader-text">Facebook</span>
                            </a>
                        </li>
                        <li>
                            <a href="https://wa.me/+51980436234" target="_blank">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M17.472 14.51c-.292-.147-1.728-.853-1.995-.95-.266-.098-.46-.147-.654.147-.196.293-.75.95-.92 1.147-.17.196-.34.22-.632.073-.292-.146-1.233-.455-2.35-1.45-.867-.773-1.45-1.732-1.617-2.024-.17-.293-.017-.45.13-.598.133-.132.292-.34.437-.51.146-.17.196-.293.292-.487.097-.196.05-.366-.024-.51-.073-.146-.654-1.593-.896-2.182-.236-.568-.477-.49-.654-.5h-.555c-.196 0-.51.073-.776.366-.266.293-1.017.996-1.017 2.43s1.042 2.82 1.188 3.013c.147.195 2.04 3.115 4.946 4.243.69.298 1.227.475 1.646.608.692.22 1.323.189 1.82.114.555-.085 1.728-.707 1.97-1.39.243-.683.243-1.268.17-1.39-.073-.121-.266-.194-.555-.34z"/>
                                    <path d="M12 2C6.485 2 2 6.485 2 12c0 1.85.503 3.68 1.457 5.265L2 22l4.956-1.243A9.947 9.947 0 0012 22c5.515 0 10-4.485 10-10S17.515 2 12 2zm0 18c-1.63 0-3.228-.433-4.606-1.252l-.331-.195-2.945.736.79-2.798-.212-.336A7.925 7.925 0 014 12c0-4.411 3.589-8 8-8s8 3.589 8 8-3.589 8-8 8z"/></svg>
                                <span class="u-screen-reader-text">WhatsApp</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="column xl-6 lg-12">
                    <p class="ss-copyright">
                        <span>© Copyright Kawaii 2025</span>
                    </p>
                </div>
            </div>

            <div class="ss-go-top">
                <a class="smoothscroll" title="Volver arriba" href="#top">
                    <svg clip-rule="evenodd" fill-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="m14.523 18.787s4.501-4.505 6.255-6.26c.146-.146.219-.338.219-.53s-.073-.383-.219-.53c-1.753-1.754-6.255-6.258-6.255-6.258-.144-.145-.334-.217-.524-.217-.193 0-.385.074-.532.221-.293.292-.295.766-.004 1.056l4.978 4.978h-14.692c-.414 0-.75.336-.75.75s.336.75.75.75h14.692l-4.979 4.979c-.289.289-.286.762.006 1.054.148.148.341.222.533.222.19 0 .378-.072.522-.215z" fill-rule="nonzero"/></svg>
                </a>
                <span>Volver arriba</span>
            </div>
        </footer>
    </div>
 
    <!--Java Script-->
    <script src="JAVA SCRIPT/Complementos.js"></script>
    <script src="JAVA SCRIPT/Main.js"></script>
    <script src="JAVA SCRIPT/Reserva.js"></script>

    <!-- Modal de notificación -->
    <div class="modal-overlay" id="notificationModal">
        <div class="modal-content">
            <p id="modal-message"></p>
            <button class="modal-close" onclick="closeModal()">Aceptar</button>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($esPost) && $esPost == 'true'): ?>
            <?php if (isset($mostrarDesayuno) && $mostrarDesayuno == 'true'): ?> mostrarSeccion('desayuno'); <?php endif; ?>
            <?php if (isset($mostrarAlmuerzo) && $mostrarAlmuerzo == 'true'): ?> mostrarSeccion('almuerzo'); <?php endif; ?>
            <?php if (isset($mostrarCena) && $mostrarCena == 'true'): ?> mostrarSeccion('cena'); <?php endif; ?>
            <?php endif; ?>
        });
    </script>
</body>
</html>

