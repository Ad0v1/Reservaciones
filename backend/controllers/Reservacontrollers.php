<?php
class ReservaControllers {
    private $conexion;
    
    public function __construct() {
        // Configuración de la base de datos
        $host = "localhost";
        $usuario = "usuario_db";
        $contrasena = "contrasena_db";
        $base_datos = "kawai_restaurant";
        
        // Conexión a la base de datos
        $this->conexion = new mysqli('db', 'root', '123456', 'reservaciones');
        
        // Verificar conexión
        if ($this->conexion->connect_error) {
            die("Error de conexión: " . $this->conexion->connect_error);
        }
        
        // Establecer charset
        $this->conexion->set_charset("utf8");
    }
    
    /**
     * Obtiene todas las reservas
     */
    public function obtenerReservas() {
        $sql = "SELECT 
                    r.id_reserva,
                    r.fecha_reserva,
                    r.hora_reserva,
                    r.cantidad_personas,
                    r.tipo_menu,
                    r.total,
                    r.estado,
                    r.estado_pago,
                    r.fecha_limite_pago,
                    r.notas,
                    u.nombre AS nombre_cliente,
                    u.telefono,
                    u.email
                FROM 
                    reservas r
                JOIN 
                    usuarios u ON r.id_usuario = u.id_usuario
                ORDER BY 
                    r.fecha_reserva DESC, r.hora_reserva ASC";
        
        $resultado = $this->conexion->query($sql);
        $reservas = [];
        
        if ($resultado->num_rows > 0) {
            while ($fila = $resultado->fetch_assoc()) {
                $reservas[] = $fila;
            }
        }
        
        return $reservas;
    }
    
    /**
     * Obtiene una reserva por su ID
     */
    public function obtenerReservaPorId($id_reserva) {
        $sql = "SELECT 
                    r.id_reserva,
                    r.fecha_reserva,
                    r.hora_reserva,
                    r.cantidad_personas,
                    r.tipo_menu,
                    r.total,
                    r.estado,
                    r.estado_pago,
                    r.fecha_limite_pago,
                    r.notas,
                    u.id_usuario,
                    u.nombre AS nombre_cliente,
                    u.telefono,
                    u.email
                FROM 
                    reservas r
                JOIN 
                    usuarios u ON r.id_usuario = u.id_usuario
                WHERE 
                    r.id_reserva = ?";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $id_reserva);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows > 0) {
            return $resultado->fetch_assoc();
        }
        
        return null;
    }
    
    /**
     * Obtiene todos los pagos
     */
    public function obtenerPagos() {
        $sql = "SELECT 
                    p.id_pago,
                    p.id_reserva,
                    p.monto_pagado,
                    p.metodo_pago,
                    p.numero_operacion,
                    p.codigo_seguridad,
                    p.fecha_pago,
                    p.estado,
                    p.nombre_pagador,
                    p.comentarios,
                    u.nombre AS nombre_cliente,
                    u.telefono
                FROM 
                    pagos p
                JOIN 
                    reservas r ON p.id_reserva = r.id_reserva
                JOIN 
                    usuarios u ON r.id_usuario = u.id_usuario
                ORDER BY 
                    p.fecha_pago DESC";
        
        $resultado = $this->conexion->query($sql);
        $pagos = [];
        
        if ($resultado->num_rows > 0) {
            while ($fila = $resultado->fetch_assoc()) {
                $pagos[] = $fila;
            }
        }
        
        return $pagos;
    }
    
    /**
     * Obtiene un pago por su ID
     */
    public function obtenerPagoPorId($id_pago) {
        $sql = "SELECT 
                    p.id_pago,
                    p.id_reserva,
                    p.monto_pagado,
                    p.metodo_pago,
                    p.numero_operacion,
                    p.codigo_seguridad,
                    p.fecha_pago,
                    p.estado,
                    p.nombre_pagador,
                    p.comentarios,
                    u.nombre AS nombre_cliente,
                    u.telefono
                FROM 
                    pagos p
                JOIN 
                    reservas r ON p.id_reserva = r.id_reserva
                JOIN 
                    usuarios u ON r.id_usuario = u.id_usuario
                WHERE 
                    p.id_pago = ?";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $id_pago);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows > 0) {
            return $resultado->fetch_assoc();
        }
        
        return null;
    }
    
    /**
     * Actualiza el estado de una reserva
     */
    public function actualizarEstadoReserva($id_reserva, $nuevo_estado) {
        $sql = "UPDATE reservas SET estado = ? WHERE id_reserva = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("si", $nuevo_estado, $id_reserva);
        
        return $stmt->execute();
    }
    
    /**
     * Actualiza el estado de un pago
     */
    public function actualizarEstadoPago($id_pago, $nuevo_estado) {
        $sql = "UPDATE pagos SET estado = ? WHERE id_pago = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("si", $nuevo_estado, $id_pago);
        
        return $stmt->execute();
    }
    
    /**
     * Crea una nueva reserva
     */
    public function crearReserva($nombre, $telefono, $email, $fecha, $hora, $personas, $tipo_menu, $estado, $total, $notas = null) {
        // Primero verificar si el usuario existe
        $id_usuario = $this->obtenerOCrearUsuario($nombre, $telefono, $email);
        
        if (!$id_usuario) {
            return false;
        }
        
        // Calcular fecha límite de pago (2 días después)
        $fecha_reserva = new DateTime($fecha);
        $fecha_limite = clone $fecha_reserva;
        $fecha_limite->sub(new DateInterval('P2D'));
        $fecha_limite_str = $fecha_limite->format('Y-m-d');
        
        // Crear la reserva
        $sql = "INSERT INTO reservas (
                    id_usuario, 
                    fecha_reserva, 
                    hora_reserva, 
                    cantidad_personas, 
                    tipo_menu, 
                    total, 
                    estado,
                    fecha_limite_pago,
                    notas
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param(
            "issiisdss",
            $id_usuario,
            $fecha,
            $hora,
            $personas,
            $tipo_menu,
            $total,
            $estado,
            $fecha_limite_str,
            $notas
        );
        
        return $stmt->execute();
    }
    
    /**
     * Registra un pago para una reserva
     */
    public function registrarPago($telefono, $fechaReserva, $metodoPago, $numeroOperacion, $nombrePagador, $codigoSeguridad = '', $comentarios = '') {
        // Buscar la reserva por teléfono y fecha
        $sql = "SELECT r.id_reserva, r.total 
                FROM reservas r 
                JOIN usuarios u ON r.id_usuario = u.id_usuario 
                WHERE u.telefono = ? AND r.fecha_reserva = ?";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("ss", $telefono, $fechaReserva);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows == 0) {
            return false;
        }
        
        $reserva = $resultado->fetch_assoc();
        $id_reserva = $reserva['id_reserva'];
        $monto_pagado = $reserva['total'] * 0.5; // 50% del total
        
        // Registrar el pago
        $sql = "INSERT INTO pagos (
                    id_reserva, 
                    monto_pagado, 
                    metodo_pago, 
                    numero_operacion, 
                    codigo_seguridad,
                    nombre_pagador,
                    fecha_pago, 
                    estado, 
                    comentarios
                ) VALUES (?, ?, ?, ?, ?, ?, NOW(), 'Pendiente', ?)";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param(
            "idssss",
            $id_reserva,
            $monto_pagado,
            $metodoPago,
            $numeroOperacion,
            $codigoSeguridad,
            $nombrePagador,
            $comentarios
        );
        
        $resultado = $stmt->execute();
        
        if ($resultado) {
            // Actualizar el estado de la reserva
            $sql = "UPDATE reservas SET estado_pago = 'Pago Registrado' WHERE id_reserva = ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("i", $id_reserva);
            $stmt->execute();
        }
        
        return $resultado;
    }
    
    /**
     * Elimina una reserva
     */
    public function eliminarReserva($id_reserva) {
        // Primero verificar si hay pagos asociados
        $sql = "SELECT COUNT(*) as total FROM pagos WHERE id_reserva = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $id_reserva);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $fila = $resultado->fetch_assoc();
        
        if ($fila['total'] > 0) {
            // Si hay pagos, actualizar estado a cancelado en lugar de eliminar
            return $this->actualizarEstadoReserva($id_reserva, 'Cancelado');
        } else {
            // Si no hay pagos, eliminar la reserva
            $sql = "DELETE FROM reservas WHERE id_reserva = ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("i", $id_reserva);
            return $stmt->execute();
        }
    }
    
    /**
     * Obtiene un usuario por teléfono o crea uno nuevo
     */
    private function obtenerOCrearUsuario($nombre, $telefono, $email) {
        // Buscar usuario por teléfono
        $sql = "SELECT id_usuario FROM usuarios WHERE telefono = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("s", $telefono);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows > 0) {
            $fila = $resultado->fetch_assoc();
            return $fila["id_usuario"];
        } else {
            // Crear nuevo usuario
            $sql = "INSERT INTO usuarios (nombre, telefono, email) VALUES (?, ?, ?)";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("sss", $nombre, $telefono, $email);
            
            if ($stmt->execute()) {
                return $this->conexion->insert_id;
            } else {
                return false;
            }
        }
    }
    
    /**
     * Envía notificaciones a los clientes
     */
    public function enviarNotificacion($tipo, $datos) {
        $mensaje = "";
        $asunto = "";
        $destinatario_email = isset($datos['email']) ? $datos['email'] : null;
        $destinatario_telefono = isset($datos['telefono']) ? $datos['telefono'] : null;
        
        switch ($tipo) {
            case 'reserva_creada':
                $asunto = "Confirmación de Reserva - Restaurante KAWAI";
                $mensaje = $this->generarMensajeReservaCreada($datos);
                break;
                
            case 'pago_registrado':
                $asunto = "Registro de Pago - Restaurante KAWAI";
                $mensaje = $this->generarMensajePagoRegistrado($datos);
                break;
                
            case 'pago_verificado':
                $asunto = "Pago Verificado - Restaurante KAWAI";
                $mensaje = $this->generarMensajePagoVerificado($datos);
                break;
                
            case 'recordatorio_pago':
                $asunto = "Recordatorio de Pago - Restaurante KAWAI";
                $mensaje = $this->generarMensajeRecordatorioPago($datos);
                break;
                
            case 'recordatorio_reserva':
                $asunto = "Recordatorio de Reserva - Restaurante KAWAI";
                $mensaje = $this->generarMensajeRecordatorioReserva($datos);
                break;
        }
        
        // Enviar notificación por email si hay destinatario
        if ($destinatario_email && !empty($mensaje)) {
            $this->enviarEmail($destinatario_email, $asunto, $mensaje);
        }
        
        // Enviar notificación por SMS si hay destinatario
        if ($destinatario_telefono && !empty($mensaje)) {
            $this->enviarSMS($destinatario_telefono, $mensaje);
        }
        
        // Guardar en log
        $log = fopen(__DIR__ . "/../logs/notificaciones.log", "a");
        fwrite($log, date('Y-m-d H:i:s') . " - " . $tipo . " - " . $mensaje . "\n");
        fclose($log);
        
        return true;
    }
    
    /**
     * Genera el mensaje para una reserva creada
     */
    private function generarMensajeReservaCreada($datos) {
        $mensaje = "¡Hola {$datos['nombre']}!\n\n";
        $mensaje .= "Tu reserva en Restaurante KAWAI ha sido registrada correctamente.\n\n";
        $mensaje .= "Detalles de la reserva:\n";
        $mensaje .= "- Fecha: {$datos['fecha']}\n";
        $mensaje .= "- Hora: {$datos['hora']}\n";
        $mensaje .= "- Personas: {$datos['personas']}\n";
        $mensaje .= "- Menú: " . ucfirst($datos['menuTipo']) . "\n";
        $mensaje .= "- Total: S/ " . number_format($datos['precioTotal'], 2) . "\n\n";
        $mensaje .= "IMPORTANTE: Para confirmar tu reserva, debes realizar un adelanto del 50% (S/ " . number_format($datos['montoAdelanto'], 2) . ") antes del {$datos['fechaLimite']}.\n\n";
        $mensaje .= "Puedes realizar el pago por:\n";
        $mensaje .= "1. Yape al número 980436234\n";
        $mensaje .= "2. Transferencia bancaria a la cuenta BCP: 123-4567890-1-23\n\n";
        $mensaje .= "Una vez realizado el pago, regístralo en nuestra web: https://restaurantekawai.com/registrar-pago\n\n";
        $mensaje .= "¡Gracias por elegirnos!\n";
        $mensaje .= "Restaurante KAWAI";
        
        return $mensaje;
    }
    
    /**
     * Genera el mensaje para un pago registrado
     */
    private function generarMensajePagoRegistrado($datos) {
        $mensaje = "¡Hola!\n\n";
        $mensaje .= "Hemos recibido el registro de tu pago para la reserva del {$datos['fechaReserva']}.\n\n";
        $mensaje .= "Detalles del pago:\n";
        $mensaje .= "- Método: " . ucfirst($datos['metodoPago']) . "\n";
        $mensaje .= "- Número de operación: {$datos['numeroOperacion']}\n";
        $mensaje .= "- Nombre del pagador: {$datos['nombrePagador']}\n\n";
        $mensaje .= "Nuestro equipo verificará el pago y te enviaremos una confirmación.\n\n";
        $mensaje .= "¡Gracias por tu preferencia!\n";
        $mensaje .= "Restaurante KAWAI";
        
        return $mensaje;
    }
    
    /**
     * Genera el mensaje para un pago verificado
     */
    private function generarMensajePagoVerificado($datos) {
        $mensaje = "¡Hola {$datos['nombre']}!\n\n";
        $mensaje .= "¡Buenas noticias! Hemos verificado tu pago para la reserva del {$datos['fecha']} a las {$datos['hora']}.\n\n";
        $mensaje .= "Tu reserva está confirmada. Te esperamos en nuestro restaurante.\n\n";
        $mensaje .= "¡Gracias por tu preferencia!\n";
        $mensaje .= "Restaurante KAWAI";
        
        return $mensaje;
    }
    
    /**
     * Genera el mensaje para un recordatorio de pago
     */
    private function generarMensajeRecordatorioPago($datos) {
        $mensaje = "¡Hola {$datos['nombre']}!\n\n";
        $mensaje .= "Te recordamos que tienes una reserva pendiente de pago para el {$datos['fecha']}.\n\n";
        $mensaje .= "El plazo para realizar el adelanto del 50% (S/ " . number_format($datos['montoAdelanto'], 2) . ") vence mañana ({$datos['fechaLimite']}).\n\n";
        $mensaje .= "Si ya realizaste el pago, por favor regístralo en nuestra web: https://restaurantekawai.com/registrar-pago\n\n";
        $mensaje .= "¡Gracias por tu preferencia!\n";
        $mensaje .= "Restaurante KAWAI";
        
        return $mensaje;
    }
    
    /**
     * Genera el mensaje para un recordatorio de reserva
     */
    private function generarMensajeRecordatorioReserva($datos) {
        $mensaje = "¡Hola {$datos['nombre']}!\n\n";
        $mensaje .= "Te recordamos que mañana {$datos['fecha']} a las {$datos['hora']} tienes una reserva en nuestro restaurante.\n\n";
        $mensaje .= "Detalles de la reserva:\n";
        $mensaje .= "- Personas: {$datos['personas']}\n";
        $mensaje .= "- Menú: " . ucfirst($datos['menuTipo']) . "\n\n";
        $mensaje .= "¡Te esperamos!\n";
        $mensaje .= "Restaurante KAWAI";
        
        return $mensaje;
    }
    
    /**
     * Envía un email
     */
    private function enviarEmail($destinatario, $asunto, $mensaje) {
        // En un entorno real, aquí se implementaría la lógica para enviar emails
        // Por ejemplo, usando PHPMailer o la función mail() de PHP
        
        // Simulación de envío de email
        $cabeceras = "From: reservas@restaurantekawai.com\r\n";
        $cabeceras .= "Reply-To: reservas@restaurantekawai.com\r\n";
        $cabeceras .= "X-Mailer: PHP/" . phpversion();
        
        // En un entorno de desarrollo, solo registramos el intento de envío
        $log = fopen(__DIR__ . "/../logs/emails.log", "a");
        fwrite($log, date('Y-m-d H:i:s') . " - Para: $destinatario - Asunto: $asunto - Mensaje: $mensaje\n");
        fclose($log);
        
        // En producción, descomentar la siguiente línea:
        // return mail($destinatario, $asunto, $mensaje, $cabeceras);
        
        return true;
    }
    
    /**
     * Envía un SMS
     */
    private function enviarSMS($destinatario, $mensaje) {
        // En un entorno real, aquí se implementaría la lógica para enviar SMS
        // Por ejemplo, usando un servicio como Twilio, Nexmo, etc.
        
        // Simulación de envío de SMS
        $log = fopen(__DIR__ . "/../logs/sms.log", "a");
        fwrite($log, date('Y-m-d H:i:s') . " - Para: $destinatario - Mensaje: $mensaje\n");
        fclose($log);
        
        return true;
    }
    
    public function __destruct() {
        $this->conexion->close();
    }
}
?>