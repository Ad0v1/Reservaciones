<?php
include '../../includes/db.php';

$id_reserva = $_GET['id_reserva'] ?? null;
if (!$id_reserva) {
    die("Error: No se ha proporcionado un ID de reserva.");
}

$sql = "SELECT * FROM reservas WHERE id_reserva = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_reserva);
$stmt->execute();
$resultado = $stmt->get_result();
$reserva = $resultado->fetch_assoc();

if (!$reserva) {
    die("Error: Reserva no encontrada.");
}

// Incluir la vista
include 'vista_editar_reserva.html';
?>
