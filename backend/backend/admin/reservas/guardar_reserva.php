<?php
session_start();
include '../../public/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $fecha = $_POST['fecha'] ?? '';
    $hora = $_POST['hora'] ?? '';
    $personas = $_POST['personas'] ?? 1;
    $notas = $_POST['notas'] ?? '';
    $estado = 'Pendiente';

    if ($nombre && $telefono && $fecha && $hora) {
        // Verificar si el usuario ya existe
        $stmt = $con->prepare("SELECT id_usuario FROM usuarios WHERE nombre = ? AND telefono = ?");
        $stmt->bind_param("ss", $nombre, $telefono);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id_usuario);
            $stmt->fetch();
        } else {
            $stmt->close();
            $stmt = $con->prepare("INSERT INTO usuarios (nombre, telefono) VALUES (?, ?)");
            $stmt->bind_param("ss", $nombre, $telefono);
            $stmt->execute();
            $id_usuario = $stmt->insert_id;
        }
        $stmt->close();

        $precio_persona = 20.00;
        $total = $personas * $precio_persona;

        $stmt = $con->prepare("INSERT INTO reservas (id_usuario, fecha_reserva, hora_reserva, cantidad_personas, total, estado, info_adicional) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issidss", $id_usuario, $fecha, $hora, $personas, $total, $estado, $notas);
        $stmt->execute();
        $stmt->close();

        header("Location: ../public/Reserva.php?confirmacion=1");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Guardar Reserva</title>
    <link rel="stylesheet" href="../../assets/css/estilo.css">
</head>
<body>
    <div class="container">
        <h1>Reserva Registrada</h1>
        <p>Tu solicitud de reserva ha sido enviada exitosamente.</p>
        <a href="../../public/Index.html">Volver al inicio</a>
    </div>
</body>
</html>
