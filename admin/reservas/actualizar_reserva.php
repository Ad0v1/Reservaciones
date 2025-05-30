<?php
include '../../includes/db.php';

$id_reserva = $_POST['id_reserva'];
$fecha      = $_POST['fecha_reserva'];
$hora       = $_POST['hora_reserva'];
$cantidad   = $_POST['cantidad_personas'];
$total      = $_POST['total'];
$estado     = $_POST['estado'];
$info       = $_POST['info_adicional'] ?? '';

$sql = "UPDATE reservas SET fecha_reserva=?, hora_reserva=?, cantidad_personas=?, total=?, estado=?, info_adicional=? 
        WHERE id_reserva=?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("ssidssi", $fecha, $hora, $cantidad, $total, $estado, $info, $id_reserva);

$exito = $stmt->execute();
$error = $stmt->error;

include 'vista_actualizar_reserva.html';
