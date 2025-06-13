<?php
// Iniciar sesión para manejar mensajes
session_start();

// Incluir archivos necesarios
require_once(__DIR__ . '/controllers/ReservaControllers.php');

// Instanciar el controlador
$controlador = new ReservaControllers();

// Variables para el formulario
$paso = isset($_GET['paso']) ? (int)$_GET['paso'] : 1;
$mensaje = '';
$error = '';
$reservaExitosa = false;
$notificacionEnviada = false;
$datosReserva = [];
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'reserva';

// Establecer el título de la página según la pestaña activa
$page_title = $activeTab == 'pago' ? 'Registrar Pago' : 'Reserva tu Mesa';

// Procesar el formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar si es registro de pago
    if (isset($_POST['registro_pago'])) {
        $telefono = trim($_POST['phone']);
        $fechaReserva = $_POST['reservation_date'];
        $metodoPago = $_POST['payment_method'];
        $numeroOperacion = trim($_POST['operation_number']);
        $nombrePagador = trim($_POST['payer_name']);
        $codigoSeguridad = ($metodoPago == 'yape') ? trim($_POST['security_code']) : '';
        $comentarios = isset($_POST['comments']) ? trim($_POST['comments']) : '';
        
        // Validar datos
        if (empty($telefono) || !preg_match('/^9\d{8}$/', $telefono)) {
            $error = "Por favor, introduce un número de teléfono válido (9 dígitos comenzando con 9)";
            $activeTab = 'pago';
        } elseif (empty($fechaReserva)) {
            $error = "La fecha de reserva es obligatoria";
            $activeTab = 'pago';
        } elseif (empty($metodoPago)) {
            $error = "Debes seleccionar un método de pago";
            $activeTab = 'pago';
        } elseif (empty($numeroOperacion)) {
            $error = "El número de operación es obligatorio";
            $activeTab = 'pago';
        } elseif (empty($nombrePagador)) {
            $error = "El nombre del pagador es obligatorio";
            $activeTab = 'pago';
        } elseif ($metodoPago == 'yape' && empty($codigoSeguridad)) {
            $error = "El código de seguridad es obligatorio para pagos con Yape";
            $activeTab = 'pago';
        } else {
            // Procesar el registro de pago
            $resultado = $controlador->registrarPago($telefono, $fechaReserva, $metodoPago, $numeroOperacion, $nombrePagador, $codigoSeguridad, $comentarios);
            
            if ($resultado) {
                $mensaje = "¡Pago registrado correctamente! Recibirás una confirmación cuando sea verificado.";
                $activeTab = 'pago';
                
                // Enviar notificación al administrador
                $datosNotificacion = [
                    'telefono' => $telefono,
                    'fechaReserva' => $fechaReserva,
                    'metodoPago' => $metodoPago,
                    'numeroOperacion' => $numeroOperacion,
                    'nombrePagador' => $nombrePagador,
                    'codigoSeguridad' => $codigoSeguridad,
                    'comentarios' => $comentarios
                ];
                
                $controlador->enviarNotificacion('pago_registrado', $datosNotificacion);
                
                // Limpiar los datos del formulario
                $_POST = array();
            } else {
                $error = "No se encontró ninguna reserva con ese número de teléfono y fecha. Por favor, verifica los datos.";
                $activeTab = 'pago';
            }
        }
    } 
    // Verificar qué paso del formulario se está procesando
    elseif (isset($_POST['paso'])) {
        $paso = (int)$_POST['paso'];
        
        // Guardar datos del formulario en la sesión para mantenerlos entre pasos
        foreach ($_POST as $key => $value) {
            $_SESSION['form_reserva'][$key] = $value;
        }
        
        // Validar según el paso actual
        switch ($paso) {
            case 1: // Validar selección del menú
                $menuTipo = $_POST['menuType'];
                $valido = true;
                
                // Validar que se hayan seleccionado las opciones obligatorias según el tipo de menú
                if ($menuTipo == 'desayuno') {
                    if (empty($_POST['desayunoBebida']) || empty($_POST['desayunoPan'])) {
                        $error = "Debes seleccionar todas las opciones obligatorias del desayuno";
                        $valido = false;
                    }
                } elseif ($menuTipo == 'almuerzo') {
                    if (empty($_POST['almuerzoEntrada']) || empty($_POST['almuerzoFondo'])) {
                        $error = "Debes seleccionar todas las opciones obligatorias del almuerzo";
                        $valido = false;
                    }
                } elseif ($menuTipo == 'cena') {
                    if (empty($_POST['cenaPlato'])) {
                        $error = "Debes seleccionar el plato principal de la cena";
                        $valido = false;
                    }
                }
                
                if ($valido) {
                    $paso = 2;
                }
                break;
                
            case 2: // Validar datos personales y de la reserva
                $nombre = trim($_POST['name']);
                $telefono = trim($_POST['phone']);
                $fecha = $_POST['date'];
                $hora = $_POST['time'];
                $personas = (int)$_POST['partySize'];
                
                // Validaciones básicas
                if (empty($nombre)) {
                    $error = "El nombre es obligatorio";
                } elseif (!preg_match('/^9\d{8}$/', $telefono)) {
                    $error = "El teléfono debe tener 9 dígitos y comenzar con 9";
                } elseif (empty($fecha)) {
                    $error = "La fecha es obligatoria";
                } elseif (empty($hora)) {
                    $error = "La hora es obligatoria";
                } elseif ($personas < 1) {
                    $error = "El número de personas debe ser al menos 1";
                } else {
                    // Calcular el precio total
                    $menuTipo = $_SESSION['form_reserva']['menuType'];
                    $precioBase = 0;
                    
                    switch ($menuTipo) {
                        case 'desayuno':
                            $precioBase = 9.0;
                            break;
                        case 'almuerzo':
                            $precioBase = 14.5;
                            break;
                        case 'cena':
                            $precioBase = 16.5;
                            break;
                    }
                    
                    $precioTotal = $precioBase * $personas;
                    $_SESSION['form_reserva']['precioTotal'] = $precioTotal;
                    
                    $paso = 3;
                }
                break;
                
            case 3: // Procesar la reserva final
                // Recopilar todos los datos de la sesión
                $datosReserva = $_SESSION['form_reserva'];
                
                // Crear la reserva en la base de datos
                $nombre = $datosReserva['name'];
                $telefono = $datosReserva['phone'];
                $email = isset($datosReserva['email']) ? $datosReserva['email'] : null;
                $fecha = $datosReserva['date'];
                $hora = $datosReserva['time'];
                $personas = (int)$datosReserva['partySize'];
                $menuTipo = $datosReserva['menuType'];
                $precioTotal = $datosReserva['precioTotal'];
                $infoAdicional = isset($datosReserva['additionalInfo']) ? $datosReserva['additionalInfo'] : null;
                
                // Crear la reserva
                $resultado = $controlador->crearReserva(
                    $nombre, 
                    $telefono, 
                    $email, 
                    $fecha, 
                    $hora, 
                    $personas, 
                    $menuTipo, 
                    'Pendiente', 
                    $precioTotal, 
                    $infoAdicional
                );
                
                if ($resultado) {
                    // Calcular la fecha límite de pago (2 días después)
                    $fechaReserva = new DateTime($fecha);
                    $fechaLimite = clone $fechaReserva;
                    $fechaLimite->sub(new DateInterval('P2D'));
                    $fecha_limite_str = $fechaLimite->format('Y-m-d');
                    
                    // Enviar notificación
                    if ($email || $telefono) {
                        $datosNotificacion = [
                            'nombre' => $nombre,
                            'telefono' => $telefono,
                            'email' => $email,
                            'fecha' => date('d/m/Y', strtotime($fecha)),
                            'hora' => $hora,
                            'menuTipo' => $menuTipo,
                            'personas' => $personas,
                            'precioTotal' => $precioTotal,
                            'montoAdelanto' => $precioTotal * 0.5,
                            'fechaLimite' => $fechaLimite->format('d/m/Y')
                        ];
                        
                        $controlador->enviarNotificacion('reserva_creada', $datosNotificacion);
                        $notificacionEnviada = true;
                    }
                    
                    $reservaExitosa = true;
                    $mensaje = "¡Tu reserva ha sido registrada correctamente en nuestra base de datos!";
                    $paso = 4;
                    
                    // Limpiar los datos del formulario de la sesión
                    unset($_SESSION['form_reserva']);
                } else {
                    $error = "Hubo un problema al procesar tu reserva en la base de datos. Por favor, intenta nuevamente.";
                }
                break;
        }
    }
}

// Obtener datos guardados de la sesión
$formData = isset($_SESSION['form_reserva']) ? $_SESSION['form_reserva'] : [];

// Función para obtener el valor de un campo del formulario
function getValue($field, $default = '') {
    global $formData;
    return isset($formData[$field]) ? $formData[$field] : $default;
}

// Función para verificar si un valor está seleccionado
function isSelected($field, $value) {
    global $formData;
    return isset($formData[$field]) && $formData[$field] == $value ? 'selected' : '';
}

// Función para verificar si un radio/checkbox está marcado
function isChecked($field, $value) {
    global $formData;
    return isset($formData[$field]) && $formData[$field] == $value ? 'checked' : '';
}
?>
<!DOCTYPE html>
<html lang="es" class="no-js">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Casona - <?php echo $page_title; ?></title>

    <script>
        document.documentElement.classList.remove('no-js');
        document.documentElement.classList.add('js');
    </script>

    <!-- Estilos CSS -->
    <link rel="stylesheet" href="assets/css/normalize.css">
    <link rel="stylesheet" href="assets/css/proveedor.css">
    <link rel="stylesheet" href="assets/css/estilo.css">
    <link rel="stylesheet" href="assets/css/reserva.css">
    <link rel="stylesheet" href="assets/css/reserva-adicional.css">

    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="assets/imagenes/LOGO/Logo-apple.png">
    <link rel="Logo-32x32" type="image/png" sizes="32x32" href="assets/imagenes/LOGO/Logo-32x32.png">
    <link rel="Logo-16x16" type="image/png" sizes="16x16" href="assets/imagenes/LOGO/Logo-16x16.png">

    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <style>
        /* Estilos adicionales para animaciones y notificaciones */
        .hidden {
            display: none;
        }
        
        .menu-options {
            transition: opacity 0.3s ease;
        }
        
        .payment-tab-pane {
            transition: opacity 0.3s ease;
        }
        
        .file-selected {
            animation: pulse 1s ease;
        }
        
        .error-field {
            border: 2px solid var(--danger-color) !important;
            background-color: #fff8f8 !important;
            box-shadow: 0 0 0 3px rgba(244, 67, 54, 0.1) !important;
        }
        
        .error-message {
            color: var(--danger-color);
            font-size: 1.3rem;
            margin-top: 5px;
            animation: fadeIn 0.3s ease;
        }
        
        .ripple {
            position: absolute;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            transform: scale(0);
            animation: ripple 0.6s linear;
            pointer-events: none;
        }
        
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 5px;
            color: white;
            font-size: 1.6rem;
            z-index: 1000;
            opacity: 0;
            transform: translateY(-20px);
            transition: all 0.3s ease;
        }
        
        .notification.show {
            opacity: 1;
            transform: translateY(0);
        }
        
        .notification-success {
            background-color: var(--success-color);
        }
        
        .notification-error {
            background-color: var(--danger-color);
        }
        
        .alert-close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 2rem;
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.3s ease;
        }
        
        .alert-close:hover {
            opacity: 1;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
    </style>
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

    <div id="page" class="s-pagewrap">
        <div class="menu-underlay"></div>
        
        <header class="s-header">
            <div class="s-header__block">
                <a class="s-header__menu-toggle" href="#0"><span>Navegar</span></a>
                <div class="s-header__cta">
                    <a href="Reserva.php" class="btn btn--primary s-header__cta-btn">Reservar</a>
                </div>
            </div>

            <nav class="s-header__nav">
                <a href="#0" class="s-header__nav-close-btn" title="Cerrar"><span>Cerrar</span></a>
                <div class="s-header__nav-logo">
                    <a href="Index.html">
                        <img src="assets/imagenes/LOGO/LOGO.jpg" alt="Página de inicio">
                    </a>
                </div>

                <ul class="s-header__nav-links">
                    <li><a href="Index.html">Inicio</a></li>
                    <li><a href="Carta.html">Carta</a></li>
                    <li><a href="About.html">Acerca de</a></li>
                    <li class="current"><a href="Reserva.php">Reservaciones</a></li>
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

        <!-- Banner de página -->
        <div class="s-pageheader">
            <div class="row">
                <div class="column xl-12">
                    <h1 class="page-title">
                        <span class="page-title__small-type">La Casona</span>
                        <?php echo $page_title; ?>
                    </h1>
                </div>
            </div>
        </div>

        <!-- Contenido principal -->
        <main class="main-content">
            <div class="container">
                <!-- Pestañas de navegación -->
                <div class="reservation-tabs">
                    <a href="?tab=reserva" class="tab-link <?php echo $activeTab == 'reserva' ? 'active' : ''; ?>">Hacer una Reserva</a>
                    <a href="?tab=pago" class="tab-link <?php echo $activeTab == 'pago' ? 'active' : ''; ?>">Registrar Pago</a>
                </div>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($mensaje)): ?>
                    <div class="alert alert-success">
                        <?php echo $mensaje; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($activeTab == 'pago'): ?>
                    <!-- Sección de Registro de Pago -->
                    <div class="reservation-form-container">
                        <h2 class="section-title">Registrar Pago de Reserva</h2>
                        
                        <div class="payment-registration">
                            <p class="payment-intro">
                                Si ya realizaste el pago del adelanto de tu reserva, completa el siguiente formulario para registrarlo.
                                Nuestro equipo verificará tu pago y te enviaremos una confirmación.
                            </p>
                            
                            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?tab=pago" class="payment-form">
                                <input type="hidden" name="registro_pago" value="1">
                                
                                <div class="form-group">
                                    <label for="phone">Número de celular <span class="required">*</span></label>
                                    <input type="text" id="phone" name="phone" class="form-control" placeholder="Ej: 980436234" pattern="9[0-9]{8}" title="Debe ser un número de 9 dígitos que comience con 9" required>
                                    <small class="form-text">El mismo número que usaste al hacer la reserva</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="reservation_date">Fecha de la reserva <span class="required">*</span></label>
                                    <input type="date" id="reservation_date" name="reservation_date" class="form-control" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="payer_name">Nombre del pagador <span class="required">*</span></label>
                                    <input type="text" id="payer_name" name="payer_name" class="form-control" placeholder="Nombre que aparece en Yape o cuenta bancaria" required>
                                    <small class="form-text">Ingresa el nombre que aparece en tu cuenta de Yape o cuenta bancaria</small>
                                </div>
                                
                                <div class="form-group">
                                    <label>Método de pago <span class="required">*</span></label>
                                    <div class="payment-method-options">
                                        <div class="payment-method-option">
                                            <input type="radio" id="yape" name="payment_method" value="yape" required>
                                            <label for="yape" class="payment-label">
                                                <i class="fas fa-mobile-alt"></i> Yape
                                            </label>
                                        </div>
                                        <div class="payment-method-option">
                                            <input type="radio" id="transferencia" name="payment_method" value="transferencia" required>
                                            <label for="transferencia" class="payment-label">
                                                <i class="fas fa-credit-card"></i> Transferencia Bancaria
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group" id="security_code_group" style="display: none;">
                                    <label for="security_code">Código de seguridad <span class="required">*</span></label>
                                    <input type="text" id="security_code" name="security_code" class="form-control" placeholder="Código de seguridad de Yape">
                                    <small class="form-text">Ingresa el código de seguridad que aparece en tu comprobante de Yape</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="operation_number">Número de operación <span class="required">*</span></label>
                                    <input type="text" id="operation_number" name="operation_number" class="form-control" required>
                                    <small class="form-text">Ingresa el número de operación que aparece en tu comprobante de pago</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="voucher">Comprobante de pago (opcional)</label>
                                    <div class="file-upload">
                                        <input type="file" id="voucher" name="voucher" class="file-input" accept="image/*">
                                        <label for="voucher" class="file-label">
                                            <i class="fas fa-upload"></i> Seleccionar archivo
                                        </label>
                                        <span class="file-name">Sin archivos seleccionados</span>
                                    </div>
                                    <small class="form-text">Puedes adjuntar una imagen o PDF de tu comprobante de pago</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="comments">Comentarios adicionales</label>
                                    <textarea id="comments" name="comments" class="form-control" rows="3" placeholder="Cualquier información adicional sobre tu pago"></textarea>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">Registrar Pago</button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Instrucciones de Pago -->
                        <div class="payment-instructions">
                            <h3>Instrucciones de Pago</h3>
                            <p>Para confirmar tu reserva, debes realizar un adelanto del 50% del monto total.</p>
                            
                            <div class="payment-tabs">
                                <div class="payment-tab-header">
                                    <button class="payment-tab-btn active" data-tab="yape">
                                        <i class="fas fa-mobile-alt"></i> Yape
                                    </button>
                                    <button class="payment-tab-btn" data-tab="transferencia">
                                        <i class="fas fa-credit-card"></i> Transferencia
                                    </button>
                                </div>
                                
                                <div class="payment-tab-content">
                                    <div id="yape" class="payment-tab-pane active" style="opacity: 1;">
                                        <h4>Instrucciones para pago por Yape:</h4>
                                        <ol>
                                            <li>Abre tu aplicación Yape</li>
                                            <li>Escanea el QR o busca el número: <strong>980436234</strong></li>
                                            <li>Ingresa el monto exacto del adelanto (50% del total)</li>
                                            <li>En el mensaje escribe: <strong>Reserva + tu nombre</strong></li>
                                            <li>Guarda el comprobante de pago y el código de seguridad</li>
                                        </ol>
                                        <div class="qr-code">
                                            <img src="assets/imagenes/qr-yape.png" alt="QR Yape">
                                        </div>
                                    </div>
                                    
                                    <div id="transferencia" class="payment-tab-pane" style="opacity: 0;">
                                        <h4>Instrucciones para transferencia bancaria:</h4>
                                        <ul>
                                            <li><strong>Banco:</strong> BCP</li>
                                            <li><strong>Titular:</strong> Restaurante KAWAI</li>
                                            <li><strong>Cuenta Corriente:</strong> 123-4567890-1-23</li>
                                            <li><strong>CCI:</strong> 002-123-4567890123456-78</li>
                                            <li><strong>Monto:</strong> 50% del total de tu reserva</li>
                                            <li><strong>Concepto:</strong> Reserva + tu nombre</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Sección de Reserva -->
                    <div class="reservation-form-container">
                        <!-- Indicador de pasos -->
                        <div class="steps-indicator">
                            <div class="step <?php echo $paso >= 1 ? 'active' : ''; ?>">
                                <div class="step-number">1</div>
                                <div class="step-label">Menú</div>
                            </div>
                            <div class="step-line"></div>
                            <div class="step <?php echo $paso >= 2 ? 'active' : ''; ?>">
                                <div class="step-number">2</div>
                                <div class="step-label">Datos</div>
                            </div>
                            <div class="step-line"></div>
                            <div class="step <?php echo $paso >= 3 ? 'active' : ''; ?>">
                                <div class="step-number">3</div>
                                <div class="step-label">Resumen</div>
                            </div>
                        </div>
                        
                        <?php if ($paso == 1): ?>
                            <!-- Paso 1: Selección del Menú -->
                            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?tab=reserva" class="reservation-form">
                                <input type="hidden" name="paso" value="1">
                                
                                <div class="form-group">
                                    <label for="menuType">Tipo de Menú</label>
                                    <select id="menuType" name="menuType" class="form-control" required>
                                        <option value="">Selecciona un tipo de menú</option>
                                        <option value="desayuno" <?php echo isSelected('menuType', 'desayuno'); ?>>Desayuno</option>
                                        <option value="almuerzo" <?php echo isSelected('menuType', 'almuerzo'); ?>>Almuerzo</option>
                                        <option value="cena" <?php echo isSelected('menuType', 'cena'); ?>>Cena</option>
                                    </select>
                                </div>
                                
                                <!-- Opciones de Desayuno -->
                                <div id="opcionesDesayuno" class="menu-options <?php echo getValue('menuType') == 'desayuno' ? '' : 'hidden'; ?>">
                                    <h3>Opciones de Desayuno</h3>
                                    
                                    <div class="form-group">
                                        <label for="desayunoBebida">Bebida <span class="required">*</span></label>
                                        <select id="desayunoBebida" name="desayunoBebida" class="form-control">
                                            <option value="">Selecciona una bebida</option>
                                            <option value="Café con leche" <?php echo isSelected('desayunoBebida', 'Café con leche'); ?>>Café con leche</option>
                                            <option value="Quaker con manzana" <?php echo isSelected('desayunoBebida', 'Quaker con manzana'); ?>>Quaker con manzana</option>
                                            <option value="Chocolate caliente" <?php echo isSelected('desayunoBebida', 'Chocolate caliente'); ?>>Chocolate caliente</option>
                                            <option value="Quinua con piña" <?php echo isSelected('desayunoBebida', 'Quinua con piña'); ?>>Quinua con piña</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="desayunoPan">Pan (elige un relleno) <span class="required">*</span></label>
                                        <select id="desayunoPan" name="desayunoPan" class="form-control">
                                            <option value="">Selecciona un relleno</option>
                                            <option value="Tortilla de verduras" <?php echo isSelected('desayunoPan', 'Tortilla de verduras'); ?>>Tortilla de verduras</option>
                                            <option value="Huevo revuelto" <?php echo isSelected('desayunoPan', 'Huevo revuelto'); ?>>Huevo revuelto</option>
                                            <option value="Huevo frito" <?php echo isSelected('desayunoPan', 'Huevo frito'); ?>>Huevo frito</option>
                                            <option value="Mantequilla y mermelada" <?php echo isSelected('desayunoPan', 'Mantequilla y mermelada'); ?>>Mantequilla y mermelada</option>
                                            <option value="Camote" <?php echo isSelected('desayunoPan', 'Camote'); ?>>Camote</option>
                                            <option value="Jamonada" <?php echo isSelected('desayunoPan', 'Jamonada'); ?>>Jamonada</option>
                                            <option value="Pollo" <?php echo isSelected('desayunoPan', 'Pollo'); ?>>Pollo</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <!-- Opciones de Almuerzo -->
                                <div id="opcionesAlmuerzo" class="menu-options <?php echo getValue('menuType') == 'almuerzo' ? '' : 'hidden'; ?>">
                                    <h3>Opciones de Almuerzo</h3>
                                    
                                    <div class="form-group">
                                        <label for="almuerzoEntrada">Entrada <span class="required">*</span></label>
                                        <select id="almuerzoEntrada" name="almuerzoEntrada" class="form-control">
                                            <option value="">Selecciona una entrada</option>
                                            <option value="Papa a la Huancaína" <?php echo isSelected('almuerzoEntrada', 'Papa a la Huancaína'); ?>>Papa a la Huancaína</option>
                                            <option value="Ocopa Arequipeña" <?php echo isSelected('almuerzoEntrada', 'Ocopa Arequipeña'); ?>>Ocopa Arequipeña</option>
                                            <option value="Ensalada de fideo" <?php echo isSelected('almuerzoEntrada', 'Ensalada de fideo'); ?>>Ensalada de fideo</option>
                                            <option value="Crema de rocoto" <?php echo isSelected('almuerzoEntrada', 'Crema de rocoto'); ?>>Crema de rocoto</option>
                                            <option value="Sopa de casa" <?php echo isSelected('almuerzoEntrada', 'Sopa de casa'); ?>>Sopa de casa</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="almuerzoFondo">Plato de Fondo <span class="required">*</span></label>
                                        <select id="almuerzoFondo" name="almuerzoFondo" class="form-control">
                                            <option value="">Selecciona un plato de fondo</option>
                                            <option value="Arroz con Pollo" <?php echo isSelected('almuerzoFondo', 'Arroz con Pollo'); ?>>Arroz con Pollo</option>
                                            <option value="Ají de Gallina" <?php echo isSelected('almuerzoFondo', 'Ají de Gallina'); ?>>Ají de Gallina</option>
                                            <option value="Pollo al Sillao" <?php echo isSelected('almuerzoFondo', 'Pollo al Sillao'); ?>>Pollo al Sillao</option>
                                            <option value="Estofado de Pollo" <?php echo isSelected('almuerzoFondo', 'Estofado de Pollo'); ?>>Estofado de Pollo</option>
                                            <option value="Frijoles con seco" <?php echo isSelected('almuerzoFondo', 'Frijoles con seco'); ?>>Frijoles con seco</option>
                                            <option value="Pollo al horno con ensalada rusa" <?php echo isSelected('almuerzoFondo', 'Pollo al horno con ensalada rusa'); ?>>Pollo al horno con ensalada rusa</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="almuerzoPostre">Postre (opcional)</label>
                                        <select id="almuerzoPostre" name="almuerzoPostre" class="form-control">
                                            <option value="no_incluir" <?php echo isSelected('almuerzoPostre', 'no_incluir'); ?>>No incluir</option>
                                            <option value="Plátano" <?php echo isSelected('almuerzoPostre', 'Plátano'); ?>>Plátano</option>
                                            <option value="Manzana" <?php echo isSelected('almuerzoPostre', 'Manzana'); ?>>Manzana</option>
                                            <option value="Sandía" <?php echo isSelected('almuerzoPostre', 'Sandía'); ?>>Sandía</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="almuerzoBebida">Bebida (opcional)</label>
                                        <select id="almuerzoBebida" name="almuerzoBebida" class="form-control">
                                            <option value="no_incluir" <?php echo isSelected('almuerzoBebida', 'no_incluir'); ?>>No incluir</option>
                                            <option value="Té" <?php echo isSelected('almuerzoBebida', 'Té'); ?>>Té</option>
                                            <option value="Anís" <?php echo isSelected('almuerzoBebida', 'Anís'); ?>>Anís</option>
                                            <option value="Manzanilla" <?php echo isSelected('almuerzoBebida', 'Manzanilla'); ?>>Manzanilla</option>
                                            <option value="Chicha morada" <?php echo isSelected('almuerzoBebida', 'Chicha morada'); ?>>Chicha morada</option>
                                            <option value="Maracuyá" <?php echo isSelected('almuerzoBebida', 'Maracuyá'); ?>>Maracuyá</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <!-- Opciones de Cena -->
                                <div id="opcionesCena" class="menu-options <?php echo getValue('menuType') == 'cena' ? '' : 'hidden'; ?>">
                                    <h3>Opciones de Cena</h3>
                                    
                                    <div class="form-group">
                                        <label for="cenaPlato">Plato Principal <span class="required">*</span></label>
                                        <select id="cenaPlato" name="cenaPlato" class="form-control">
                                            <option value="">Selecciona un plato principal</option>
                                            <option value="Hamburguesa con papas fritas" <?php echo isSelected('cenaPlato', 'Hamburguesa con papas fritas'); ?>>Hamburguesa con papas fritas</option>
                                            <option value="Pan con Hamburguesa y Papas Fritas" <?php echo isSelected('cenaPlato', 'Pan con Hamburguesa y Papas Fritas'); ?>>Pan con Hamburguesa y Papas Fritas</option>
                                            <option value="Tallarines Rojos con Bistec" <?php echo isSelected('cenaPlato', 'Tallarines Rojos con Bistec'); ?>>Tallarines Rojos con Bistec</option>
                                            <option value="Sopa de Verduras y Pan con Pollo" <?php echo isSelected('cenaPlato', 'Sopa de Verduras y Pan con Pollo'); ?>>Sopa de Verduras y Pan con Pollo</option>
                                            <option value="Pollo al horno con ensalada rusa" <?php echo isSelected('cenaPlato', 'Pollo al horno con ensalada rusa'); ?>>Pollo al horno con ensalada rusa</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="cenaPostre">Postre (opcional)</label>
                                        <select id="cenaPostre" name="cenaPostre" class="form-control">
                                            <option value="no_incluir" <?php echo isSelected('cenaPostre', 'no_incluir'); ?>>No incluir</option>
                                            <option value="Pudín de Chocolate" <?php echo isSelected('cenaPostre', 'Pudín de Chocolate'); ?>>Pudín de Chocolate</option>
                                            <option value="Flan" <?php echo isSelected('cenaPostre', 'Flan'); ?>>Flan</option>
                                            <option value="Gelatina" <?php echo isSelected('cenaPostre', 'Gelatina'); ?>>Gelatina</option>
                                            <option value="Mazamorra Morada" <?php echo isSelected('cenaPostre', 'Mazamorra Morada'); ?>>Mazamorra Morada</option>
                                            <option value="Arroz con Leche" <?php echo isSelected('cenaPostre', 'Arroz con Leche'); ?>>Arroz con Leche</option>
                                            <option value="Torta de Chocolate" <?php echo isSelected('cenaPostre', 'Torta de Chocolate'); ?>>Torta de Chocolate</option>
                                            <option value="Torta de Limón" <?php echo isSelected('cenaPostre', 'Torta de Limón'); ?>>Torta de Limón</option>
                                            <option value="Alfajores" <?php echo isSelected('cenaPostre', 'Alfajores'); ?>>Alfajores</option>
                                            <option value="Mazamorra de Piña" <?php echo isSelected('cenaPostre', 'Mazamorra de Piña'); ?>>Mazamorra de Piña</option>
                                            <option value="Sandía" <?php echo isSelected('cenaPostre', 'Sandía'); ?>>Sandía</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="cenaBebida">Bebida (opcional)</label>
                                        <select id="cenaBebida" name="cenaBebida" class="form-control">
                                            <option value="no_incluir" <?php echo isSelected('cenaBebida', 'no_incluir'); ?>>No incluir</option>
                                            <option value="Té" <?php echo isSelected('cenaBebida', 'Té'); ?>>Té</option>
                                            <option value="Anís" <?php echo isSelected('cenaBebida', 'Anís'); ?>>Anís</option>
                                            <option value="Manzanilla" <?php echo isSelected('cenaBebida', 'Manzanilla'); ?>>Manzanilla</option>
                                            <option value="Chicha morada" <?php echo isSelected('cenaBebida', 'Chicha morada'); ?>>Chicha morada</option>
                                            <option value="Maracuyá" <?php echo isSelected('cenaBebida', 'Maracuyá'); ?>>Maracuyá</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">Continuar <i class="fas fa-chevron-right"></i></button>
                                </div>
                            </form>
                            
                        <?php elseif ($paso == 2): ?>
                            <!-- Paso 2: Datos de la Reserva -->
                            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?tab=reserva" class="reservation-form">
                                <input type="hidden" name="paso" value="2">
                                <input type="hidden" name="menuType" value="<?php echo getValue('menuType'); ?>">
                                
                                <?php if (getValue('menuType') == 'desayuno'): ?>
                                    <input type="hidden" name="desayunoBebida" value="<?php echo getValue('desayunoBebida'); ?>">
                                    <input type="hidden" name="desayunoPan" value="<?php echo getValue('desayunoPan'); ?>">
                                <?php elseif (getValue('menuType') == 'almuerzo'): ?>
                                    <input type="hidden" name="almuerzoEntrada" value="<?php echo getValue('almuerzoEntrada'); ?>">
                                    <input type="hidden" name="almuerzoFondo" value="<?php echo getValue('almuerzoFondo'); ?>">
                                    <input type="hidden" name="almuerzoPostre" value="<?php echo getValue('almuerzoPostre'); ?>">
                                    <input type="hidden" name="almuerzoBebida" value="<?php echo getValue('almuerzoBebida'); ?>">
                                <?php elseif (getValue('menuType') == 'cena'): ?>
                                    <input type="hidden" name="cenaPlato" value="<?php echo getValue('cenaPlato'); ?>">
                                    <input type="hidden" name="cenaPostre" value="<?php echo getValue('cenaPostre'); ?>">
                                    <input type="hidden" name="cenaBebida" value="<?php echo getValue('cenaBebida'); ?>">
                                <?php endif; ?>
                                
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="name">Nombre <span class="required">*</span></label>
                                        <input type="text" id="name" name="name" class="form-control" value="<?php echo getValue('name'); ?>" required>
                                    </div>
                                    
                                    <div class="form-group col-md-6">
                                        <label for="phone">Número de contacto <span class="required">*</span></label>
                                        <input type="text" id="phone" name="phone" class="form-control" value="<?php echo getValue('phone'); ?>" placeholder="Ej: 980436234" pattern="9[0-9]{8}" title="Debe ser un número de 9 dígitos que comience con 9" required>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">Correo electrónico</label>
                                    <input type="email" id="email" name="email" class="form-control" value="<?php echo getValue('email'); ?>" placeholder="Ej: usuario@correo.com">
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="date">Fecha <span class="required">*</span></label>
                                        <input type="date" id="date" name="date" class="form-control" value="<?php echo getValue('date'); ?>" min="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                    
                                    <div class="form-group col-md-6">
                                        <label for="time">Hora <span class="required">*</span></label>
                                        <select id="time" name="time" class="form-control" required>
                                            <option value="">Selecciona una hora</option>
                                            <?php
                                            // Generar horas desde las 7:00 hasta las 23:00 en intervalos de 30 minutos
                                            for ($hora = 7; $hora <= 23; $hora++) {
                                                for ($minuto = 0; $minuto < 60; $minuto += 30) {
                                                    if ($hora == 23 && $minuto > 0) continue;
                                                    $tiempo = sprintf("%02d:%02d", $hora, $minuto);
                                                    $selected = getValue('time') == $tiempo ? 'selected' : '';
                                                    echo "<option value=\"$tiempo\" $selected>$tiempo</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="partySize">Tamaño del grupo <span class="required">*</span></label>
                                    <input type="number" id="partySize" name="partySize" class="form-control" value="<?php echo getValue('partySize', 1); ?>" min="1" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="additionalInfo">Información adicional</label>
                                    <textarea id="additionalInfo" name="additionalInfo" class="form-control" rows="3"><?php echo getValue('additionalInfo'); ?></textarea>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="button" class="btn btn-outline" onclick="window.location.href='?tab=reserva&paso=1'">Volver</button>
                                    <button type="submit" class="btn btn-primary">Ver Resumen <i class="fas fa-chevron-right"></i></button>
                                </div>
                            </form>
                            
                        <?php elseif ($paso == 3): ?>
                            <!-- Paso 3: Resumen -->
                            <div class="reservation-summary">
                                <h3>Resumen de tu Reserva</h3>
                                
                                <div class="summary-details">
                                    <div class="summary-row">
                                        <div class="summary-label">Nombre:</div>
                                        <div class="summary-value"><?php echo getValue('name'); ?></div>
                                    </div>
                                    
                                    <div class="summary-row">
                                        <div class="summary-label">Teléfono:</div>
                                        <div class="summary-value"><?php echo getValue('phone'); ?></div>
                                    </div>
                                    
                                    <div class="summary-row">
                                        <div class="summary-label">Correo:</div>
                                        <div class="summary-value"><?php echo getValue('email') ? getValue('email') : 'No proporcionado'; ?></div>
                                    </div>
                                    
                                    <div class="summary-row">
                                        <div class="summary-label">Fecha:</div>
                                        <div class="summary-value"><?php echo date('d/m/Y', strtotime(getValue('date'))); ?></div>
                                    </div>
                                    
                                    <div class="summary-row">
                                        <div class="summary-label">Hora:</div>
                                        <div class="summary-value"><?php echo getValue('time'); ?></div>
                                    </div>
                                    
                                    <div class="summary-row">
                                        <div class="summary-label">Personas:</div>
                                        <div class="summary-value"><?php echo getValue('partySize'); ?></div>
                                    </div>
                                    
                                    <div class="summary-divider"></div>
                                    
                                    <div class="summary-row">
                                        <div class="summary-label">Menú seleccionado:</div>
                                        <div class="summary-value menu-details">
                                            <?php if (getValue('menuType') == 'desayuno'): ?>
                                                <strong>Desayuno</strong>
                                                <p>Bebida: <?php echo getValue('desayunoBebida'); ?></p>
                                                <p>Pan: <?php echo getValue('desayunoPan'); ?></p>
                                            <?php elseif (getValue('menuType') == 'almuerzo'): ?>
                                                <strong>Almuerzo</strong>
                                                <p>Entrada: <?php echo getValue('almuerzoEntrada'); ?></p>
                                                <p>Plato de fondo: <?php echo getValue('almuerzoFondo'); ?></p>
                                                <?php if (getValue('almuerzoPostre') && getValue('almuerzoPostre') != 'no_incluir'): ?>
                                                    <p>Postre: <?php echo getValue('almuerzoPostre'); ?></p>
                                                <?php endif; ?>
                                                <?php if (getValue('almuerzoBebida') && getValue('almuerzoBebida') != 'no_incluir'): ?>
                                                    <p>Bebida: <?php echo getValue('almuerzoBebida'); ?></p>
                                                <?php endif; ?>
                                            <?php elseif (getValue('menuType') == 'cena'): ?>
                                                <strong>Cena</strong>
                                                <p>Plato principal: <?php echo getValue('cenaPlato'); ?></p>
                                                <?php if (getValue('cenaPostre') && getValue('cenaPostre') != 'no_incluir'): ?>
                                                    <p>Postre: <?php echo getValue('cenaPostre'); ?></p>
                                                <?php endif; ?>
                                                <?php if (getValue('cenaBebida') && getValue('cenaBebida') != 'no_incluir'): ?>
                                                    <p>Bebida: <?php echo getValue('cenaBebida'); ?></p>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <?php if (getValue('additionalInfo')): ?>
                                        <div class="summary-row">
                                            <div class="summary-label">Información adicional:</div>
                                            <div class="summary-value"><?php echo getValue('additionalInfo'); ?></div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="summary-divider"></div>
                                    
                                    <div class="summary-row total-row">
                                        <div class="summary-label">Total:</div>
                                        <div class="summary-value">S/ <?php echo number_format(getValue('precioTotal'), 2); ?></div>
                                    </div>
                                    
                                    <div class="summary-row">
                                        <div class="summary-label">Adelanto requerido (50%):</div>
                                        <div class="summary-value">S/ <?php echo number_format(getValue('precioTotal') * 0.5, 2); ?></div>
                                    </div>
                                </div>
                                
                                <div class="important-notice">
                                    <h4>Importante:</h4>
                                    <ul>
                                        <li>Para confirmar tu reserva, debes realizar un adelanto del 50% (S/ <?php echo number_format(getValue('precioTotal') * 0.5, 2); ?>).</li>
                                        <li>Tienes un plazo máximo de 2 días para realizar el pago del adelanto.</li>
                                        <li>Una vez realizada la reserva, recibirás un mensaje con las instrucciones de pago.</li>
                                        <li>Después de realizar el pago, deberás registrarlo en la sección "Registrar Pago".</li>
                                    </ul>
                                </div>
                                
                                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?tab=reserva">
                                    <input type="hidden" name="paso" value="3">
                                    
                                    <!-- Mantener todos los datos del formulario -->
                                    <?php foreach ($formData as $key => $value): ?>
                                        <input type="hidden" name="<?php echo $key; ?>" value="<?php echo $value; ?>">
                                    <?php endforeach; ?>
                                    
                                    <div class="form-actions">
                                        <button type="button" class="btn btn-outline" onclick="window.location.href='?tab=reserva&paso=2'">Volver</button>
                                        <button type="submit" class="btn btn-primary">Confirmar Reserva</button>
                                    </div>
                                </form>
                            </div>
                            
                        <?php elseif ($paso == 4): ?>
                            <!-- Paso 4: Confirmación -->
                            <div class="reservation-confirmation">
                                <div class="confirmation-message">
                                    <i class="fas fa-check-circle"></i>
                                    <h3>¡Reserva Exitosa!</h3>
                                    <p>Tu reserva ha sido registrada correctamente en nuestra base de datos.</p>
                                    
                                    <div class="important-notice">
                                        <h4>Importante:</h4>
                                        <ul>
                                            <li>Tienes 2 días para realizar el pago del 50% de adelanto.</li>
                                            <li>Si no realizas el pago en ese plazo, tu reserva será cancelada automáticamente.</li>
                                            <li>Puedes realizar el pago por Yape o transferencia bancaria.</li>
                                            <li>Una vez realizado el pago, regístralo en la sección "Registrar Pago".</li>
                                        </ul>
                                    </div>
                                    
                                    <?php if ($notificacionEnviada): ?>
                                        <div class="notification-sent">
                                            <p><strong>¡Notificación enviada!</strong></p>
                                            <p>
                                                Te hemos enviado un resumen de tu reserva y las instrucciones de pago a tu
                                                <?php if (getValue('email')): ?>correo electrónico<?php endif; ?>
                                                <?php if (getValue('email') && getValue('phone')): ?> y <?php endif; ?>
                                                <?php if (getValue('phone')): ?>número de teléfono<?php endif; ?>.
                                            </p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="confirmation-actions">
                                        <a href="?tab=pago" class="btn btn-primary">Registrar Pago</a>
                                        <a href="Index.html" class="btn btn-outline">Volver al Inicio</a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>

        <!-- Footer -->
        <footer id="footer" class="s-footer">
            <div class="row s-footer__main-content">
                <div class="column xl-6 md-12 s-footer__block s-footer__about">
                    <div class="s-footer__logo">
                        <a class="logo" href="Index.html">
                            <img src="assets/imagenes/LOGO/LOGO.jpg" alt="Página de inicio">
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
                                <li><a href="mailto:casonaKawai@gmail.com">casonaKawai@gmail.com</a></li>
                                <li><a href="tel:+51980436234">+51 980 436 234</a></li>
                            </ul>
                        </div>
                        <div class="column">
                            <h5>Horarios de Atención</h5>
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
                        <a href="#" onclick="abrirLogin()" style="margin-left: 15px; color: #ff9800; text-decoration: underline;">
                            Acceso Administrativo
                        </a>
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

    <!-- Scripts -->
    <script src="assets/js/Complementos.js"></script>
    <script src="assets/js/Main.js"></script>
    <script src="assets/js/reserva.js"></script>

    <script>
        function abrirLogin() {
            window.open(
                'admin/login.php',
                'LoginAdmin',
                'width=500,height=600,top=100,left=100,toolbar=no,location=no,directories=no,status=no,menubar=no'
            );
        }
    </script>
</body>
</html>
