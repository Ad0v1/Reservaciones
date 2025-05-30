<?php
include '../../includes/db.php';

$fecha    = $_POST['fecha_reserva'];
$hora     = $_POST['hora_reserva'];
$cantidad = $_POST['cantidad_personas'];
$total    = $_POST['total'];
$estado   = $_POST['estado'];
$info     = $_POST['info_adicional'] ?? '';

$sql = "INSERT INTO reservas (fecha_reserva, hora_reserva, cantidad_personas, total, estado, info_adicional)
        VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("ssidss", $fecha, $hora, $cantidad, $total, $estado, $info);

$exito = $stmt->execute();
$error = $stmt->error;

include 'vista_guardar_reserva.html';
