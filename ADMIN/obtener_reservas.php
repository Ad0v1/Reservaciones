<?php
include 'conexion.php';

$sql = "SELECT id_reserva, fecha_reserva, hora_reserva, cantidad_personas, estado FROM reservas";
$result = $conexion->query($sql);

$reservas = [];
while ($row = $result->fetch_assoc()) {
    $reservas[] = [
        'id' => $row['id_reserva'],
        'title' => "Reserva ({$row['cantidad_personas']} personas)",
        'start' => "{$row['fecha_reserva']}T{$row['hora_reserva']}",
        'backgroundColor' => ($row['estado'] == 'Confirmado') ? 'green' : 'yellow'
    ];
}

header('Content-Type: application/json');
echo json_encode($reservas);
?>
