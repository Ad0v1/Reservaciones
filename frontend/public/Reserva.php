<?php
// Iniciar sesión para manejar mensajes
session_start();

// Incluir archivos necesarios
require_once('controllers/ReservaControllers.php');

// Instanciar el controlador
$controlador = new ReservaControllers();

// Variables para el formulario
$paso = 1;
$mensaje = '';
$error = '';
$reservaExitosa = false;
$notificacionEnviada = false;
$datosReserva = [];

// Procesar el formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar qué paso del formulario se está procesando
    if (isset($_POST['paso'])) {
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
                    $paso = 4;
                    
                    // Limpiar los datos del formulario de la sesión
                    unset($_SESSION['form_reserva']);
                } else {
                    $error = "Hubo un problema al procesar tu reserva. Por favor, intenta nuevamente.";
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

// Incluir el encabezado
include('includes/header.php');
?>

<main class="main-content">
    <div class="container">
        <h1 class="page-title">Reserva tu Mesa</h1>
        
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
        
        <div class="reservation-form-container">
            <?php if ($paso == 1): ?>
                <!-- Paso 1: Selección del Menú -->
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="reservation-form">
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
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="reservation-form">
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
                        <button type="button" class="btn btn-outline" onclick="window.location.href='?paso=1'">Volver</button>
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
                    
                    <!-- Instrucciones de Pago -->
                    <div class="payment-instructions">
                        <h3>Instrucciones de Pago</h3>
                        <p>Para confirmar tu reserva, debes realizar un adelanto del 50% (S/ <?php echo number_format(getValue('precioTotal') * 0.5, 2); ?>) en un plazo máximo de 2 días.</p>
                        
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
                                <div id="yape" class="payment-tab-pane active">
                                    <h4>Instrucciones para pago por Yape:</h4>
                                    <ol>
                                        <li>Abre tu aplicación Yape</li>
                                        <li>Escanea el QR o busca el número: <strong>980436234</strong></li>
                                        <li>Ingresa el monto exacto: <strong>S/ <?php echo number_format(getValue('precioTotal') * 0.5, 2); ?></strong></li>
                                        <li>En el mensaje escribe: <strong>Reserva <?php echo getValue('name'); ?></strong></li>
                                        <li>Guarda el comprobante de pago</li>
                                    </ol>
                                    <div class="qr-code">
                                        <img src="assets/imagenes/qr-yape.png" alt="QR Yape">
                                    </div>
                                </div>
                                
                                <div id="transferencia" class="payment-tab-pane">
                                    <h4>Instrucciones para transferencia bancaria:</h4>
                                    <ul>
                                        <li><strong>Banco:</strong> BCP</li>
                                        <li><strong>Titular:</strong> Restaurante KAWAI</li>
                                        <li><strong>Cuenta Corriente:</strong> 123-4567890-1-23</li>
                                        <li><strong>CCI:</strong> 002-123-4567890123456-78</li>
                                        <li><strong>Monto:</strong> S/ <?php echo number_format(getValue('precioTotal') * 0.5, 2); ?></li>
                                        <li><strong>Concepto:</strong> Reserva <?php echo getValue('name'); ?></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <input type="hidden" name="paso" value="3">
                        
                        <!-- Mantener todos los datos del formulario -->
                        <?php foreach ($formData as $key => $value): ?>
                            <input type="hidden" name="<?php echo $key; ?>" value="<?php echo $value; ?>">
                        <?php endforeach; ?>
                        
                        <div class="form-actions">
                            <button type="button" class="btn btn-outline" onclick="window.location.href='?paso=2'">Volver</button>
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
                        <p>Tu reserva ha sido registrada correctamente.</p>
                        
                        <div class="important-notice">
                            <h4>Importante:</h4>
                            <ul>
                                <li>Tienes 2 días para realizar el pago del 50% de adelanto.</li>
                                <li>Si no realizas el pago en ese plazo, tu reserva será cancelada automáticamente.</li>
                                <li>Puedes realizar el pago por Yape o transferencia bancaria según las instrucciones mostradas.</li>
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
                            <a href="registrar_pago.php" class="btn btn-primary">Registrar Pago</a>
                            <a href="index.php" class="btn btn-outline">Volver al Inicio</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mostrar/ocultar opciones de menú según la selección
    const menuTypeSelect = document.getElementById('menuType');
    if (menuTypeSelect) {
        menuTypeSelect.addEventListener('change', function() {
            const menuType = this.value;
            document.querySelectorAll('.menu-options').forEach(function(el) {
                el.classList.add('hidden');
            });
            
            if (menuType) {
                document.getElementById('opciones' + menuType.charAt(0).toUpperCase() + menuType.slice(1)).classList.remove('hidden');
            }
        });
    }
    
    // Tabs de métodos de pago
    const paymentTabBtns = document.querySelectorAll('.payment-tab-btn');
    const paymentTabPanes = document.querySelectorAll('.payment-tab-pane');
    
    paymentTabBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            // Desactivar todas las pestañas
            paymentTabBtns.forEach(function(btn) {
                btn.classList.remove('active');
            });
            paymentTabPanes.forEach(function(pane) {
                pane.classList.remove('active');
            });
            
            // Activar la pestaña seleccionada
            this.classList.add('active');
            document.getElementById(tabId).classList.add('active');
        });
    });
});
</script>

<?php
// Incluir el pie de página
include('includes/footer.php');
?>