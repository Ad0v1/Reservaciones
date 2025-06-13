<?php
/**
 * Script para enviar recordatorios de pago y de reservas próximas
 * Este script debe ejecutarse diariamente mediante un cron job
 */

// Incluir archivos necesarios
require_once(__DIR__ . '/../controllers/ReservaControllers.php');

// Inicializar el controlador
$controlador = new ReservaControllers();

// Conexión a la base de datos
try {
    $db = new PDO(
        'mysql:host=localhost;dbname=restaurante_kawai;charset=utf8mb4',
        'usuario_db',
        'contraseña_db',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die('Error de conexión a la base de datos: ' . $e->getMessage());
}

// 1. Enviar recordatorios de pago para reservas pendientes
$sql = "SELECT * FROM reservas 
        WHERE estado = 'Pendiente' 
        AND fecha_limite_pago = DATE_ADD(CURDATE(), INTERVAL 1 DAY)";

$stmt = $db->query($sql);
$reservasPendientesPago = $stmt->fetchAll(PDO::FETCH_ASSOC);

$recordatoriosPagoEnviados = 0;

foreach ($reservasPendientesPago as $reserva) {
    // Preparar datos para la notificación
    $datosNotificacion = [
        'nombre' => $reserva['nombre'],
        'telefono' => $reserva['telefono'],
        'email' => $reserva['email'],
        'fecha' => date('d/m/Y', strtotime($reserva['fecha'])),
        'fechaLimite' => date('d/m/Y', strtotime($reserva['fecha_limite_pago'])),
        'precioTotal' => $reserva['precio_total'],
        'montoAdelanto' => $reserva['precio_total'] * 0.5
    ];
    
    // Enviar recordatorio de pago
    if ($controlador->enviarNotificacion('recordatorio_pago', $datosNotificacion)) {
        $recordatoriosPagoEnviados++;
    }
}

// 2. Enviar recordatorios para reservas confirmadas que son mañana
$sql = "SELECT * FROM reservas 
        WHERE estado IN ('Confirmada', 'Pago Verificado') 
        AND fecha = DATE_ADD(CURDATE(), INTERVAL 1 DAY)";

$stmt = $db->query($sql);
$reservasManana = $stmt->fetchAll(PDO::FETCH_ASSOC);

$recordatoriosReservaEnviados = 0;

foreach ($reservasManana as $reserva) {
    // Preparar datos para la notificación
    $datosNotificacion = [
        'nombre' => $reserva['nombre'],
        'telefono' => $reserva['telefono'],
        'email' => $reserva['email'],
        'fecha' => date('d/m/Y', strtotime($reserva['fecha'])),
        'hora' => date('H:i', strtotime($reserva['hora'])),
        'personas' => $reserva['personas'],
        'menuTipo' => $reserva['menu_tipo']
    ];
    
    // Enviar recordatorio de reserva
    if ($controlador->enviarNotificacion('recordatorio_reserva', $datosNotificacion)) {
        $recordatoriosReservaEnviados++;
    }
}

// Registrar la ejecución
$logFile = __DIR__ . '/../logs/enviar_recordatorios.log';
$mensaje = date('Y-m-d H:i:s') . " - Se enviaron $recordatoriosPagoEnviados recordatorios de pago y $recordatoriosReservaEnviados recordatorios de reserva.\n";

// Escribir en el archivo de log
file_put_contents($logFile, $mensaje, FILE_APPEND);

echo $mensaje;