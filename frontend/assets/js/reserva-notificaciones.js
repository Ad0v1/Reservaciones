/**
 * Sistema de notificaciones para el proceso de reservas
 * Este archivo maneja las notificaciones y recordatorios para los clientes
 */

class ReservaNotificaciones {
    constructor() {
        this.apiUrl = 'api/notificaciones.php';
    }
    
    /**
     * Envía una notificación por correo electrónico
     * @param {string} tipo - Tipo de notificación (recordatorio, confirmacion, cancelacion)
     * @param {object} datos - Datos de la reserva
     * @returns {Promise} - Promesa con el resultado de la operación
     */
    enviarNotificacionEmail(tipo, datos) {
        return fetch(this.apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                tipo: tipo,
                medio: 'email',
                datos: datos
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Notificación por email enviada:', data);
            return data;
        })
        .catch(error => {
            console.error('Error al enviar notificación por email:', error);
            throw error;
        });
    }
    
    /**
     * Envía una notificación por WhatsApp
     * @param {string} tipo - Tipo de notificación (recordatorio, confirmacion, cancelacion)
     * @param {object} datos - Datos de la reserva
     * @returns {Promise} - Promesa con el resultado de la operación
     */
    enviarNotificacionWhatsApp(tipo, datos) {
        return fetch(this.apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                tipo: tipo,
                medio: 'whatsapp',
                datos: datos
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Notificación por WhatsApp enviada:', data);
            return data;
        })
        .catch(error => {
            console.error('Error al enviar notificación por WhatsApp:', error);
            throw error;
        });
    }
    
    /**
     * Programa un recordatorio para una reserva
     * @param {object} reserva - Datos de la reserva
     * @param {number} diasAntes - Días antes de la reserva para enviar el recordatorio
     * @returns {Promise} - Promesa con el resultado de la operación
     */
    programarRecordatorio(reserva, diasAntes = 1) {
        return fetch(this.apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                accion: 'programar_recordatorio',
                reserva: reserva,
                diasAntes: diasAntes
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Recordatorio programado:', data);
            return data;
        })
        .catch(error => {
            console.error('Error al programar recordatorio:', error);
            throw error;
        });
    }
    
    /**
     * Envía una notificación de pago pendiente
     * @param {object} reserva - Datos de la reserva
     * @returns {Promise} - Promesa con el resultado de la operación
     */
    enviarNotificacionPagoPendiente(reserva) {
        const mensaje = `Hola ${reserva.nombre}, te recordamos que debes pagar el 50% de tu reserva para el ${reserva.fecha}. Tienes plazo hasta el ${reserva.fechaLimite}.`;
        
        const datos = {
            ...reserva,
            mensaje: mensaje
        };
        
        // Enviar por email si está disponible
        if (reserva.email) {
            this.enviarNotificacionEmail('recordatorio_pago', datos);
        }
        
        // Enviar por WhatsApp si está disponible
        if (reserva.telefono) {
            this.enviarNotificacionWhatsApp('recordatorio_pago', datos);
        }
        
        return Promise.resolve({
            success: true,
            message: 'Notificación de pago pendiente enviada'
        });
    }
    
    /**
     * Envía una notificación de confirmación de pago
     * @param {object} reserva - Datos de la reserva
     * @returns {Promise} - Promesa con el resultado de la operación
     */
    enviarConfirmacionPago(reserva) {
        const mensaje = `¡Gracias ${reserva.nombre}! Hemos confirmado tu pago. Tu reserva para el ${reserva.fecha} ha sido confirmada. ¡Te esperamos!`;
        
        const datos = {
            ...reserva,
            mensaje: mensaje
        };
        
        // Enviar por email si está disponible
        if (reserva.email) {
            this.enviarNotificacionEmail('confirmacion_pago', datos);
        }
        
        // Enviar por WhatsApp si está disponible
        if (reserva.telefono) {
            this.enviarNotificacionWhatsApp('confirmacion_pago', datos);
        }
        
        return Promise.resolve({
            success: true,
            message: 'Confirmación de pago enviada'
        });
    }
    
    /**
     * Envía una notificación de cancelación por falta de pago
     * @param {object} reserva - Datos de la reserva
     * @returns {Promise} - Promesa con el resultado de la operación
     */
    enviarCancelacionPorFaltaPago(reserva) {
        const mensaje = `Lamentamos informarte que tu reserva para el ${reserva.fecha} ha sido cancelada por no recibir el pago dentro del plazo establecido.`;
        
        const datos = {
            ...reserva,
            mensaje: mensaje
        };
        
        // Enviar por email si está disponible
        if (reserva.email) {
            this.enviarNotificacionEmail('cancelacion', datos);
        }
        
        // Enviar por WhatsApp si está disponible
        if (reserva.telefono) {
            this.enviarNotificacionWhatsApp('cancelacion', datos);
        }
        
        return Promise.resolve({
            success: true,
            message: 'Notificación de cancelación enviada'
        });
    }
}

// Exportar la clase para su uso en otros archivos
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ReservaNotificaciones;
} else {
    // Para uso en el navegador
    window.ReservaNotificaciones = ReservaNotificaciones;
}