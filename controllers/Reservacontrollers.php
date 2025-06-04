<?php
require_once __DIR__ . '/../includes/db.php';

class ReservaController {
    public static function guardarReserva($nombre, $fecha, $hora) {
        global $conn;
        $stmt = $conn->prepare("INSERT INTO reservas (nombre, fecha, hora) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nombre, $fecha, $hora);
        return $stmt->execute();
    }

    public static function obtenerReservas() {
        global $conn;
        $result = $conn->query("SELECT * FROM reservas");
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>
    