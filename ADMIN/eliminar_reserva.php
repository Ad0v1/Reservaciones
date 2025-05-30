<?php
include 'conexion.php';
$id = $_POST['id_reserva'];
$sql = "DELETE FROM reservas WHERE id_reserva=?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
if ($stmt->execute()) {
    echo json_encode(["mensaje" => "Reserva eliminada con éxito"]);
} else {
    echo json_encode(["mensaje" => "Error: " . $stmt->error]);
}
?>