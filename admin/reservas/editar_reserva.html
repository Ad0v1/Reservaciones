<?php
include 'conexion.php';

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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Reserva</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="styles.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0"><i class="fas fa-edit me-2"></i>Editar Reserva #<?= $reserva['id_reserva'] ?></h3>
            </div>
            <div class="card-body">
                <form action="actualizar_reserva.php" method="POST" id="editarForm" class="needs-validation" novalidate>
                    <input type="hidden" name="id_reserva" value="<?= $reserva['id_reserva'] ?>">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="fecha_reserva" class="form-label">Fecha:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                <input type="date" class="form-control" id="fecha_reserva" name="fecha_reserva" value="<?= $reserva['fecha_reserva'] ?>" required>
                                <div class="invalid-feedback">
                                    Por favor seleccione una fecha válida.
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="hora_reserva" class="form-label">Hora:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-clock"></i></span>
                                <input type="time" class="form-control" id="hora_reserva" name="hora_reserva" value="<?= $reserva['hora_reserva'] ?>" required>
                                <div class="invalid-feedback">
                                    Por favor seleccione una hora válida.
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="cantidad_personas" class="form-label">Cantidad de Personas:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-users"></i></span>
                                <input type="number" class="form-control" id="cantidad_personas" name="cantidad_personas" value="<?= $reserva['cantidad_personas'] ?>" min="1" required>
                                <div class="invalid-feedback">
                                    Por favor ingrese un número válido de personas.
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="total" class="form-label">Total:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                <input type="text" class="form-control" id="total" name="total" value="<?= $reserva['total'] ?>" required>
                                <div class="invalid-feedback">
                                    Por favor ingrese un monto válido.
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="estado" class="form-label">Estado:</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-tag"></i></span>
                            <select class="form-select" id="estado" name="estado" required>
                                <option value="Pendiente" <?= $reserva['estado'] == 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                <option value="Confirmado" <?= $reserva['estado'] == 'Confirmado' ? 'selected' : '' ?>>Confirmado</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="info_adicional" class="form-label">Información Adicional:</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-info-circle"></i></span>
                            <textarea class="form-control" id="info_adicional" name="info_adicional" rows="3"><?= $reserva['info_adicional'] ?? '' ?></textarea>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" onclick="window.close();">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Actualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Validación de formulario
        (function() {
            'use strict';
            
            const form = document.getElementById('editarForm');
            
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                
                form.classList.add('was-validated');
            }, false);
        })();
    </script>
</body>
</html>

