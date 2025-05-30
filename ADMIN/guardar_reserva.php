<?php
include 'conexion.php';

$fecha = $_POST['fecha_reserva'];
$hora = $_POST['hora_reserva'];
$cantidad = $_POST['cantidad_personas'];
$total = $_POST['total'];
$estado = $_POST['estado'];
$info = $_POST['info_adicional'] ?? '';

$sql = "INSERT INTO reservas (fecha_reserva, hora_reserva, cantidad_personas, total, estado, info_adicional) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("ssidss", $fecha, $hora, $cantidad, $total, $estado, $info);

$exito = $stmt->execute();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $exito ? 'Reserva Guardada' : 'Error' ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="styles.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card shadow mx-auto" style="max-width: 500px;">
            <div class="card-body text-center p-5">
                <?php if ($exito): ?>
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                    </div>
                    <h2 class="mb-4">¡Reserva Registrada con Éxito!</h2>
                    <p class="mb-4">La reserva ha sido guardada correctamente en el sistema.</p>
                <?php else: ?>
                    <div class="mb-4">
                        <i class="fas fa-times-circle text-danger" style="font-size: 4rem;"></i>
                    </div>
                    <h2 class="mb-4">Error al Guardar la Reserva</h2>
                    <p class="mb-4">Ha ocurrido un error: <?= $stmt->error ?></p>
                <?php endif; ?>
                
                <div class="d-grid gap-2">
                    <button class="btn btn-primary" onclick="cerrarVentana()">
                        <i class="fas fa-check me-2"></i>Aceptar
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function cerrarVentana() {
            window.opener.location.reload();
            window.close();
        }
        
        // Mostrar alerta automáticamente
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($exito): ?>
            Swal.fire({
                title: '¡Éxito!',
                text: 'Reserva registrada correctamente',
                icon: 'success',
                confirmButtonText: 'Aceptar',
                confirmButtonColor: '#4e73df'
            }).then((result) => {
                if (result.isConfirmed) {
                    cerrarVentana();
                }
            });
            <?php else: ?>
            Swal.fire({
                title: 'Error',
                text: 'No se pudo registrar la reserva',
                icon: 'error',
                confirmButtonText: 'Aceptar',
                confirmButtonColor: '#4e73df'
            });
            <?php endif; ?>
        });
    </script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

