<?php
session_start();
// Verificar si el usuario está logueado como administrador
if (!isset($_SESSION['admin_logueado']) || $_SESSION['admin_logueado'] !== true) {
    header('Location: login.php');
    exit;
}

// Incluir archivo de conexión
include "../../public/db.php";

// Function to get all reservations
function getReservations($conn) {
    $sql = "SELECT id_reserva, id_usuario, fecha_reserva, hora_reserva, cantidad_personas, estado, info_adicional 
            FROM reservas ORDER BY fecha_reserva, hora_reserva";
    $result = $conn->query($sql);
    
    $reservations = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            // Obtener nombre del usuario si existe
            $client_name = "Cliente sin registrar";
            if ($row['id_usuario']) {
                $user_query = "SELECT nombre FROM usuarios WHERE id_usuario = " . $row['id_usuario'];
                $user_result = $conn->query($user_query);
                if ($user_result->num_rows > 0) {
                    $user_data = $user_result->fetch_assoc();
                    $client_name = $user_data['nombre'];
                }
            }
            
            // Formatear datos para el calendario
            $reservations[] = [
                'id' => $row['id_reserva'],
                'client_name' => $client_name,
                'date' => $row['fecha_reserva'],
                'time_start' => $row['hora_reserva'],
                'time_end' => date('H:i:s', strtotime($row['hora_reserva'] . ' +1 hour')), // Asumiendo 1 hora de duración
                'status' => $row['estado'],
                'notes' => $row['info_adicional']
            ];
        }
    }
    
    return $reservations;
}

// Handle form submissions
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        // Add new reservation
        if ($_POST['action'] == 'add') {
            $client_name = $_POST['client_name'];
            $date = $_POST['date'];
            $time_start = $_POST['time_start'];
            $cantidad_personas = $_POST['cantidad_personas'] ?? 1;
            $status = $_POST['status'];
            $notes = $_POST['notes'];
            
            // Primero verificar si existe el usuario o crear uno nuevo
            $id_usuario = null;
            if (!empty($client_name)) {
                $check_user = "SELECT id_usuario FROM usuarios WHERE nombre = ?";
                $stmt = $con->prepare($check_user);
                $stmt->bind_param("s", $client_name);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $user_data = $result->fetch_assoc();
                    $id_usuario = $user_data['id_usuario'];
                } else {
                    // Crear nuevo usuario
                    $insert_user = "INSERT INTO usuarios (nombre, telefono) VALUES (?, '')";
                    $stmt = $con->prepare($insert_user);
                    $stmt->bind_param("s", $client_name);
                    if ($stmt->execute()) {
                        $id_usuario = $con->insert_id;
                    }
                }
                $stmt->close();
            }
            
            // Calcular total (ejemplo simple)
            $total = $cantidad_personas * 20.00; // Precio base por persona
            
            $sql = "INSERT INTO reservas (id_usuario, id_admin, fecha_reserva, hora_reserva, cantidad_personas, total, estado, info_adicional) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $con->prepare($sql);
            $admin_id = $_SESSION['user_id'];
            $stmt->bind_param("iissidss", $id_usuario, $admin_id, $date, $time_start, $cantidad_personas, $total, $status, $notes);
            
            if ($stmt->execute()) {
                $message = "Reserva añadida correctamente.";
            } else {
                $message = "Error al añadir la reserva: " . $stmt->error;
            }
            
            $stmt->close();
        }
        
        // Update reservation
        if ($_POST['action'] == 'update' && isset($_POST['id'])) {
            $id = $_POST['id'];
            $client_name = $_POST['client_name'];
            $date = $_POST['date'];
            $time_start = $_POST['time_start'];
            $cantidad_personas = $_POST['cantidad_personas'] ?? 1;
            $status = $_POST['status'];
            $notes = $_POST['notes'];
            
            // Actualizar usuario si es necesario
            $id_usuario = null;
            if (!empty($client_name)) {
                $check_user = "SELECT id_usuario FROM usuarios WHERE nombre = ?";
                $stmt = $con->prepare($check_user);
                $stmt->bind_param("s", $client_name);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $user_data = $result->fetch_assoc();
                    $id_usuario = $user_data['id_usuario'];
                } else {
                    // Crear nuevo usuario
                    $insert_user = "INSERT INTO usuarios (nombre, telefono) VALUES (?, '')";
                    $stmt = $con->prepare($insert_user);
                    $stmt->bind_param("s", $client_name);
                    if ($stmt->execute()) {
                        $id_usuario = $con->insert_id;
                    }
                }
                $stmt->close();
            }
            
            // Calcular total (ejemplo simple)
            $total = $cantidad_personas * 20.00; // Precio base por persona
            
            $sql = "UPDATE reservas SET id_usuario=?, fecha_reserva=?, hora_reserva=?, cantidad_personas=?, total=?, estado=?, info_adicional=? WHERE id_reserva=?";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("issidssi", $id_usuario, $date, $time_start, $cantidad_personas, $total, $status, $notes, $id);
            
            if ($stmt->execute()) {
                $message = "Reserva actualizada correctamente.";
            } else {
                $message = "Error al actualizar la reserva: " . $stmt->error;
            }
            
            $stmt->close();
        }
        
        // Delete reservation
        if ($_POST['action'] == 'delete' && isset($_POST['id'])) {
            $id = $_POST['id'];
            
            $sql = "DELETE FROM reservas WHERE id_reserva=?";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $message = "Reserva eliminada correctamente.";
            } else {
                $message = "Error al eliminar la reserva: " . $stmt->error;
            }
            
            $stmt->close();
        }
    }
}

// Get all reservations
$reservations = getReservations($con);

// Cerrar conexión al final
// No cerramos aquí para que esté disponible en el resto del script
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de Reservas</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FullCalendar CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css">
    <!-- Custom CSS -->
    <style>
        .fc-event {
            cursor: pointer;
        }
        .status-pendiente {
            background-color: #ffc107;
            border-color: #ffc107;
        }
        .status-confirmado {
            background-color: #28a745;
            border-color: #28a745;
        }
        .status-cancelado {
            background-color: #dc3545;
            border-color: #dc3545;
            text-decoration: line-through;
        }
    </style>
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                locale: 'es',
                events: [
                    <?php foreach($reservations as $reservation): ?>
                    {
                        id: '<?php echo $reservation['id']; ?>',
                        title: '<?php echo $reservation['client_name']; ?>',
                        start: '<?php echo $reservation['date'] . 'T' . $reservation['time_start']; ?>',
                        end: '<?php echo $reservation['date'] . 'T' . $reservation['time_end']; ?>',
                        className: 'status-<?php echo strtolower($reservation['status']); ?>',
                        extendedProps: {
                            status: '<?php echo $reservation['status']; ?>',
                            notes: '<?php echo addslashes($reservation['notes']); ?>'
                        }
                    },
                    <?php endforeach; ?>
                ],
                eventClick: function(info) {
                    // Mostrar detalles de la reserva y opciones para editar/eliminar
                    Swal.fire({
                        title: 'Reserva #' + info.event.id,
                        html: `
                            <div class="text-start">
                                <p><strong>Cliente:</strong> ${info.event.title}</p>
                                <p><strong>Fecha:</strong> ${info.event.start.toLocaleDateString()}</p>
                                <p><strong>Hora:</strong> ${info.event.start.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</p>
                                <p><strong>Estado:</strong> ${info.event.extendedProps.status}</p>
                                <p><strong>Notas:</strong> ${info.event.extendedProps.notes || 'Sin notas'}</p>
                            </div>
                        `,
                        showCancelButton: true,
                        showDenyButton: true,
                        confirmButtonText: 'Editar',
                        denyButtonText: 'Eliminar',
                        cancelButtonText: 'Cerrar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Editar reserva
                            abrirFormulario('editar', info.event.id);
                        } else if (result.isDenied) {
                            // Eliminar reserva
                            confirmarEliminacion(info.event.id);
                        }
                    });
                }
            });
            calendar.render();
            
            // Función para abrir formulario de nueva reserva o edición
            window.abrirFormulario = function(accion, id = null) {
                let titulo = accion === 'agregar' ? 'Nueva Reserva' : 'Editar Reserva';
                let datos = {};
                
                if (accion === 'editar') {
                    // Obtener datos de la reserva para editar
                    let evento = calendar.getEventById(id);
                    if (evento) {
                        datos = {
                            id: evento.id,
                            client_name: evento.title,
                            date: evento.start.toISOString().split('T')[0],
                            time_start: evento.start.toISOString().split('T')[1].substring(0, 5),
                            status: evento.extendedProps.status,
                            notes: evento.extendedProps.notes || ''
                        };
                    }
                }
                
                Swal.fire({
                    title: titulo,
                    html: `
                        <form id="reservaForm" class="text-start">
                            <input type="hidden" name="action" value="${accion === 'agregar' ? 'add' : 'update'}">
                            ${accion === 'editar' ? `<input type="hidden" name="id" value="${datos.id}">` : ''}
                            
                            <div class="mb-3">
                                <label for="client_name" class="form-label">Nombre del Cliente</label>
                                <input type="text" class="form-control" id="client_name" name="client_name" value="${datos.client_name || ''}" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="date" class="form-label">Fecha</label>
                                <input type="date" class="form-control" id="date" name="date" value="${datos.date || ''}" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="time_start" class="form-label">Hora</label>
                                <input type="time" class="form-control" id="time_start" name="time_start" value="${datos.time_start || ''}" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="cantidad_personas" class="form-label">Cantidad de Personas</label>
                                <input type="number" class="form-control" id="cantidad_personas" name="cantidad_personas" value="${datos.cantidad_personas || '1'}" min="1" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="status" class="form-label">Estado</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="Pendiente" ${datos.status === 'Pendiente' ? 'selected' : ''}>Pendiente</option>
                                    <option value="Confirmado" ${datos.status === 'Confirmado' ? 'selected' : ''}>Confirmado</option>
                                    <option value="Cancelado" ${datos.status === 'Cancelado' ? 'selected' : ''}>Cancelado</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="notes" class="form-label">Notas</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3">${datos.notes || ''}</textarea>
                            </div>
                        </form>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Guardar',
                    cancelButtonText: 'Cancelar',
                    preConfirm: () => {
                        // Validar formulario
                        const form = document.getElementById('reservaForm');
                        if (!form.checkValidity()) {
                            form.reportValidity();
                            return false;
                        }
                        
                        // Enviar formulario
                        const formData = new FormData(form);
                        fetch(window.location.href, {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.text())
                        .then(html => {
                            // Recargar página para ver cambios
                            location.reload();
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire('Error', 'Hubo un problema al guardar la reserva', 'error');
                        });
                    }
                });
            };
            
            // Función para confirmar eliminación
            window.confirmarEliminacion = function(id) {
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "Esta acción no se puede deshacer",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Enviar solicitud para eliminar
                        const formData = new FormData();
                        formData.append('action', 'delete');
                        formData.append('id', id);
                        
                        fetch(window.location.href, {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.text())
                        .then(html => {
                            // Recargar página para ver cambios
                            location.reload();
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire('Error', 'Hubo un problema al eliminar la reserva', 'error');
                        });
                    }
                });
            };
        });
    </script>
</head>
<body>
    <div class="container-fluid">
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
            <div class="container">
                <a class="navbar-brand" href="#">
                    <i class="fas fa-calendar-alt me-2"></i>
                    Sistema de Reservas
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <span class="nav-link">Bienvenido, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                <i class="fas fa-sign-out-alt me-1"></i> Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        
        <div class="container">
            <?php if (!empty($message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card shadow">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h2 class="card-title">Calendario de Reservas</h2>
                                <button class="btn btn-primary" onclick="abrirFormulario('agregar')">
                                    <i class="fas fa-plus-circle me-2"></i>Nueva Reserva
                                </button>
                            </div>
                            <div id="calendar" class="mt-3"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <footer class="bg-dark text-white text-center py-3 mt-5">
            <div class="container">
                <p class="mb-0">© 2023 Sistema de Administración de Reservas</p>
                <p class="mb-0 mt-2">
                    <a href="../../public/index.html" class="text-white text-decoration-underline">
                        <i class="fas fa-home me-1"></i> Volver al sitio principal
                    </a>
                    <span class="mx-2">|</span>
                    <a href="logout.php" class="text-white text-decoration-underline">
                        <i class="fas fa-sign-out-alt me-1"></i> Cerrar Sesión
                    </a>

                </p>
            </div>
        </footer>
    </div>
</body>
</html>
<?php
// Cerrar conexión al final del script
mysqli_close($con);
?>

