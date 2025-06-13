document.addEventListener("DOMContentLoaded", function() {
    // Inicializar pestañas
    const tabBtns = document.querySelectorAll(".tab-btn");
    const tabPanes = document.querySelectorAll(".tab-pane");

    tabBtns.forEach((btn) => {
        btn.addEventListener("click", function() {
            const tabId = this.getAttribute("data-tab");

            // Desactivar todas las pestañas
            tabBtns.forEach((btn) => btn.classList.remove("active"));
            tabPanes.forEach((pane) => pane.classList.remove("active"));

            // Activar la pestaña seleccionada
            this.classList.add("active");
            document.getElementById(tabId).classList.add("active");
        });
    });

    // Inicializar calendario
    const calendarEl = document.getElementById("reservationCalendar");
    if (calendarEl) {
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: "dayGridMonth",
            headerToolbar: {
                left: "prev,next today",
                center: "title",
                right: "dayGridMonth,timeGridWeek,timeGridDay",
            },
            locale: "es",
            events: "../reservas/calendario_reserva.php",
            eventClick: function(info) {
                mostrarDetallesReserva(info.event.id);
            },
            eventClassNames: function(arg) {
                return ["fc-event-" + arg.event.extendedProps.estado.toLowerCase()];
            },
            eventTimeFormat: {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            }
        });

        calendar.render();
    }

    // Mostrar modal de detalles de reserva
    function mostrarDetallesReserva(id) {
        fetch(`../reservas/obtener_reservas.php?id=${id}`)
            .then(response => response.json())
            .then(reserva => {
                document.getElementById("id_reserva").value = reserva.id_reserva;
                document.getElementById("cliente").value = reserva.nombre_cliente;
                document.getElementById("telefono").value = reserva.telefono;
                document.getElementById("email").value = reserva.email || "";
                
                // Formatear fecha para input date (YYYY-MM-DD)
                const fechaPartes = reserva.fecha_reserva.split("-");
                document.getElementById("fecha").value = reserva.fecha_reserva;
                
                document.getElementById("hora").value = reserva.hora_reserva;
                document.getElementById("personas").value = reserva.cantidad_personas;
                document.getElementById("menu").value = reserva.tipo_menu;
                document.getElementById("total").value = "S/ " + parseFloat(reserva.total).toFixed(2);
                document.getElementById("nuevo_estado").value = reserva.estado;
                document.getElementById("notas").value = reserva.notas || "";
                
                // ID para el formulario de eliminación
                document.getElementById("id_reserva_eliminar").value = reserva.id_reserva;

                // Mostrar modal
                document.getElementById("reservaModal").style.display = "block";
            })
            .catch(error => {
                console.error("Error al obtener detalles de la reserva:", error);
                alert("Error al cargar los detalles de la reserva");
            });
    }

    // Mostrar modal de detalles de pago
    function mostrarDetallesPago(id) {
        fetch(`../reservas/obtener_pagos.php?id=${id}`)
            .then(response => response.json())
            .then(pago => {
                document.getElementById("id_pago").value = pago.id_pago;
                document.getElementById("pago_cliente").value = pago.nombre_cliente;
                document.getElementById("pago_monto").value = "S/ " + parseFloat(pago.monto_pagado).toFixed(2);
                document.getElementById("pago_metodo").value = pago.metodo_pago;
                document.getElementById("pago_operacion").value = pago.numero_operacion;
                document.getElementById("pago_fecha").value = pago.fecha_pago;
                document.getElementById("nuevo_estado_pago").value = pago.estado;
                document.getElementById("pago_nombre").value = pago.nombre_pagador;
                
                if (pago.metodo_pago === "Yape" && document.getElementById("pago_codigo")) {
                    document.getElementById("pago_codigo").value = pago.codigo_seguridad;
                }
                
                if (document.getElementById("pago_comentarios")) {
                    document.getElementById("pago_comentarios").value = pago.comentarios || "";
                }

                // Mostrar modal
                document.getElementById("pagoModal").style.display = "block";
            })
            .catch(error => {
                console.error("Error al obtener detalles del pago:", error);
                alert("Error al cargar los detalles del pago");
            });
    }

    // Botones para ver detalles de reserva
    const btnsVerReserva = document.querySelectorAll(".btn-view");
    btnsVerReserva.forEach((btn) => {
        btn.addEventListener("click", function() {
            const id = this.getAttribute("data-id");
            mostrarDetallesReserva(id);
        });
    });

    // Botones para ver detalles de pago
    const btnsVerPago = document.querySelectorAll(".btn-view-payment");
    btnsVerPago.forEach((btn) => {
        btn.addEventListener("click", function() {
            const id = this.getAttribute("data-id");
            mostrarDetallesPago(id);
        });
    });

    // Botón para nueva reserva
    const btnNuevaReserva = document.getElementById("btnNuevaReserva");
    if (btnNuevaReserva) {
        btnNuevaReserva.addEventListener("click", function() {
            document.getElementById("nuevaReservaModal").style.display = "block";
        });
    }

    // Botón para eliminar reserva
    const btnEliminarReserva = document.getElementById("btnEliminarReserva");
    if (btnEliminarReserva) {
        btnEliminarReserva.addEventListener("click", function() {
            document.getElementById("confirmarEliminarModal").style.display = "block";
        });
    }

    // Calcular precio automáticamente al cambiar tipo de menú y personas
    const tipoMenuSelect = document.getElementById("nuevo_tipo_menu");
    const personasInput = document.getElementById("nuevas_personas");
    
    if (tipoMenuSelect && personasInput) {
        tipoMenuSelect.addEventListener("change", calcularPrecio);
        personasInput.addEventListener("change", calcularPrecio);
        
        // Inicializar precio al cargar la página
        calcularPrecio();
    }

    function calcularPrecio() {
        const tipoMenu = document.getElementById("nuevo_tipo_menu").value;
        const personas = parseInt(document.getElementById("nuevas_personas").value) || 0;
        let precioBase = 0;

        switch (tipoMenu) {
            case "desayuno":
                precioBase = 9.0;
                break;
            case "almuerzo":
                precioBase = 14.5;
                break;
            case "cena":
                precioBase = 16.5;
                break;
        }

        const total = precioBase * personas;
        document.getElementById("nuevo_total").value = total.toFixed(2);
    }

    // Cerrar modales
    const closeButtons = document.querySelectorAll(".close, .modal-close");
    closeButtons.forEach((button) => {
        button.addEventListener("click", function() {
            const modal = this.closest(".modal");
            if (modal) {
                modal.style.display = "none";
            }
        });
    });

    // Cerrar modal al hacer clic fuera del contenido
    window.addEventListener("click", (event) => {
        const modales = document.querySelectorAll(".modal");
        modales.forEach((modal) => {
            if (event.target === modal) {
                modal.style.display = "none";
            }
        });
    });
    
    // Dropdown del menú de navegación
    const dropdowns = document.querySelectorAll('.admin-nav-dropdown');
    dropdowns.forEach(dropdown => {
        dropdown.addEventListener('click', function() {
            this.classList.toggle('active');
        });
    });
    
    // Ocultar alertas después de 5 segundos
    const alertas = document.querySelectorAll('.alert');
    alertas.forEach(alerta => {
        setTimeout(() => {
            alerta.style.opacity = '0';
            setTimeout(() => {
                alerta.style.display = 'none';
            }, 500);
        }, 5000);
    });
});