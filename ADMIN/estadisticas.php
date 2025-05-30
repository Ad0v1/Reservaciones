<?php
include 'conexion.php';

// Obtener total de reservas
$sql_total = "SELECT COUNT(*) as total FROM reservas";
$result_total = $conexion->query($sql_total);
$total = $result_total->fetch_assoc()['total'];

// Obtener reservas confirmadas
$sql_confirmadas = "SELECT COUNT(*) as confirmadas FROM reservas WHERE estado = 'Confirmado'";
$result_confirmadas = $conexion->query($sql_confirmadas);
$confirmadas = $result_confirmadas->fetch_assoc()['confirmadas'];

// Obtener reservas pendientes
$sql_pendientes = "SELECT COUNT(*) as pendientes FROM reservas WHERE estado = 'Pendiente'";
$result_pendientes = $conexion->query($sql_pendientes);
$pendientes = $result_pendientes->fetch_assoc()['pendientes'];

// Preparar respuesta
$respuesta = [
    'total' => $total,
    'confirmadas' => $confirmadas,
    'pendientes' => $pendientes
];

// Enviar respuesta como JSON
header('Content-Type: application/json');
echo json_encode($respuesta);
?>

