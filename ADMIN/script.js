document.addEventListener("DOMContentLoaded", () => {
  // Initialize FullCalendar
  var calendarEl = document.getElementById("calendar")
  var calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: "dayGridMonth",
    headerToolbar: {
      left: "prev,next today",
      center: "title",
      right: "dayGridMonth,timeGridWeek,timeGridDay",
    },
    events: "obtener_reservas.php",
    selectable: true,
    select: (info) => {
      abrirFormulario("agregar", null, info.startStr)
    },
    eventClick: (info) => {
      mostrarOpciones(info.event.id)
    },
    eventTimeFormat: {
      hour: "2-digit",
      minute: "2-digit",
      hour12: true,
    },
    locale: "es",
    buttonText: {
      today: "Hoy",
      month: "Mes",
      week: "Semana",
      day: "Día",
    },
    dayMaxEvents: true,
    eventDidMount: (info) => {
      // Initialize Tooltip
      $(info.el).tooltip({
        title: info.event.title,
        placement: "top",
        trigger: "hover",
        container: "body",
      })
    },
  })
  calendar.render()

  // Cargar estadísticas
  cargarEstadisticas()
})

function abrirFormulario(tipo, id = null, fecha = null) {
  const url = tipo === "editar" ? `editar_reserva.php?id_reserva=${id}` : `agregar_reserva.php?fecha=${fecha}`
  const windowFeatures = "width=600,height=600,resizable=yes,scrollbars=yes"
  window.open(url, "_blank", windowFeatures)
}

function mostrarOpciones(id) {
  // SweetAlert
  Swal.fire({
    title: "Opciones de Reserva",
    icon: "info",
    showDenyButton: true,
    showCancelButton: true,
    confirmButtonText: '<i class="fas fa-edit"></i> Editar',
    denyButtonText: '<i class="fas fa-trash-alt"></i> Eliminar',
    cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
    buttonsStyling: true,
    customClass: {
      confirmButton: "btn btn-primary me-2",
      denyButton: "btn btn-danger me-2",
      cancelButton: "btn btn-secondary",
    },
  }).then((result) => {
    if (result.isConfirmed) {
      abrirFormulario("editar", id)
    } else if (result.isDenied) {
      eliminarReserva(id)
    }
  })
}

function eliminarReserva(id) {
  Swal.fire({
    title: "¿Seguro que deseas eliminar esta reserva?",
    text: "Esta acción no se puede deshacer",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: '<i class="fas fa-check"></i> Sí, eliminar',
    cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
    buttonsStyling: true,
    customClass: {
      confirmButton: "btn btn-danger me-2",
      cancelButton: "btn btn-secondary",
    },
  }).then((result) => {
    if (result.isConfirmed) {
      fetch("eliminar_reserva.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id_reserva=${id}`,
      })
        .then((response) => response.json())
        .then((result) => {
          Swal.fire({
            title: "Eliminado",
            text: result.mensaje,
            icon: "success",
            confirmButtonText: "Aceptar",
            customClass: {
              confirmButton: "btn btn-primary",
            },
          }).then(() => {
            location.reload()
            cargarEstadisticas()
          })
        })
    }
  })
}

function cargarEstadisticas() {
  fetch("estadisticas.php")
    .then((response) => response.json())
    .then((data) => {
      document.getElementById("total-reservas").textContent = data.total
      document.getElementById("reservas-confirmadas").textContent = data.confirmadas
      document.getElementById("reservas-pendientes").textContent = data.pendientes
    })
    .catch((error) => {
      console.error("Error al cargar estadísticas:", error)
    })
}

