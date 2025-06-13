// Funciones generales
document.documentElement.classList.remove("no-js")
document.documentElement.classList.add("js")
let currentStep = 1

// Función para mostrar modal con animación
function showModal(message) {
  const modal = document.getElementById("notificationModal")
  const messageElement = document.getElementById("modal-message")
  if (modal && messageElement) {
    messageElement.textContent = message
    modal.style.display = "flex"
    // Añadir clase para la animación
    setTimeout(() => {
      modal.classList.add("show")
    }, 10)
  }
}

// Función para cerrar modal con animación
function closeModal() {
  const modal = document.getElementById("notificationModal")
  if (modal) {
    modal.classList.remove("show")
    // Esperar a que termine la animación antes de ocultar
    setTimeout(() => {
      modal.style.display = "none"
    }, 300)
  }
}

// Función para calcular y actualizar el total en tiempo real
function calcularTotal() {
  let total = 0
  const personas = Number.parseInt(document.getElementById("rparty-size").value) || 0

  // Verificar desayuno completo
  if (document.getElementById("desayuno").value && document.getElementById("pan_desayuno").value) {
    total += 9.0 * personas
  }

  // Verificar almuerzo completo (entrada y plato de fondo son obligatorios)
  if (document.getElementById("almuerzo_entrada").value && document.getElementById("almuerzo_fondo").value) {
    total += 14.5 * personas
  }

  // Verificar cena (plato principal es obligatorio)
  if (document.getElementById("cena").value) {
    total += 16.5 * personas
  }

  return total.toFixed(2)
}

// Función para validar el paso actual
function validateStep(step) {
  if (step === 1) {
    // Verificar que al menos un menú esté seleccionado completamente
    const desayunoCompleto = document.getElementById("desayuno").value && document.getElementById("pan_desayuno").value
    const almuerzoCompleto =
      document.getElementById("almuerzo_entrada").value && document.getElementById("almuerzo_fondo").value
    const cenaCompleta = document.getElementById("cena").value

    if (!desayunoCompleto && !almuerzoCompleto && !cenaCompleta) {
      showModal("Debes seleccionar al menos un menú completo para continuar.")
      return false
    }

    // Validar que si se selecciona parte del desayuno, se complete
    if (
      (document.getElementById("desayuno").value && !document.getElementById("pan_desayuno").value) ||
      (!document.getElementById("desayuno").value && document.getElementById("pan_desayuno").value)
    ) {
      showModal("Debes completar todos los campos del Desayuno.")
      return false
    }

    // Validar que si se selecciona parte del almuerzo, se completen los campos obligatorios
    if (
      (document.getElementById("almuerzo_entrada").value && !document.getElementById("almuerzo_fondo").value) ||
      (!document.getElementById("almuerzo_entrada").value && document.getElementById("almuerzo_fondo").value)
    ) {
      showModal("Debes seleccionar tanto la entrada como el plato de fondo para el Almuerzo.")
      return false
    }
  }

  if (step === 2) {
    // Validar campos requeridos
    const requiredFields = [
      { id: "rname", label: "Nombre" },
      { id: "rphone", label: "Número de contacto" },
      { id: "rdate", label: "Fecha" },
      { id: "rtime", label: "Hora" },
      { id: "rparty-size", label: "Tamaño del grupo" },
    ]

    for (const field of requiredFields) {
      const element = document.getElementById(field.id)
      if (!element || !element.value.trim()) {
        showModal(`El campo ${field.label} es obligatorio.`)
        if (element) {
          element.classList.add("error-field")
          element.focus()
        }
        return false
      } else {
        element.classList.remove("error-field")
      }
    }

    // Validar formato de teléfono (9 dígitos comenzando con 9)
    const phone = document.getElementById("rphone").value
    if (!/^9\d{8}$/.test(phone)) {
      showModal("El teléfono debe tener 9 dígitos y empezar con 9.")
      document.getElementById("rphone").classList.add("error-field")
      document.getElementById("rphone").focus()
      return false
    } else {
      document.getElementById("rphone").classList.remove("error-field")
    }

    // Validar que la fecha no sea pasada
    const selectedDate = new Date(document.getElementById("rdate").value + "T" + document.getElementById("rtime").value)
    const now = new Date()

    if (selectedDate < now) {
      showModal("No puedes reservar en fechas u horas pasadas.")
      document.getElementById("rdate").classList.add("error-field")
      document.getElementById("rtime").classList.add("error-field")
      return false
    } else {
      document.getElementById("rdate").classList.remove("error-field")
      document.getElementById("rtime").classList.remove("error-field")
    }

    // Validar número de personas
    const personas = Number.parseInt(document.getElementById("rparty-size").value)
    if (isNaN(personas) || personas <= 0 || personas > 250) {
      showModal("El número de personas debe ser entre 1 y 250.")
      document.getElementById("rparty-size").classList.add("error-field")
      document.getElementById("rparty-size").focus()
      return false
    } else {
      document.getElementById("rparty-size").classList.remove("error-field")
    }
  }

  return true
}

// Función para actualizar resumen con animaciones
function updateResumen() {
  const resumenDiv = document.getElementById("resumenContenido")
  if (!resumenDiv) return

  // Obtener datos personales
  const nombre = document.getElementById("rname").value
  const telefono = document.getElementById("rphone").value
  const email = document.getElementById("email").value || "No especificado"
  const fecha = document.getElementById("rdate").value
  const hora = document.getElementById("rtime").value
  const personas = document.getElementById("rparty-size").value
  const info = document.getElementById("radd-info").value || "Ninguna"

  // Calcular el total
  const total = calcularTotal()

  // Construir HTML para el resumen
  let html = `
        <div class="resumen-seccion">
            <h3>Datos Personales</h3>
            <p><strong>Nombre:</strong> ${nombre}</p>
            <p><strong>Teléfono:</strong> ${telefono}</p>
            <p><strong>Email:</strong> ${email}</p>
            <p><strong>Fecha:</strong> ${fecha}</p>
            <p><strong>Hora:</strong> ${hora}</p>
            <p><strong>Personas:</strong> ${personas}</p>
            <p><strong>Información Adicional:</strong> ${info}</p>
        </div>
        
        <div class="resumen-seccion">
            <h3>Menú Seleccionado</h3>
    `

  // Verificar desayuno
  if (document.getElementById("desayuno").value && document.getElementById("pan_desayuno").value) {
    const bebida = document.getElementById("desayuno")
    const pan = document.getElementById("pan_desayuno")

    html += `
            <div class="menu-item">
                <h4>Desayuno - S/. 9.00 por persona</h4>
                <ul>
                    <li>Bebida: ${bebida.options[bebida.selectedIndex].text}</li>
                    <li>Pan: ${pan.options[pan.selectedIndex].text}</li>
                </ul>
            </div>
        `
  }

  // Verificar almuerzo
  if (document.getElementById("almuerzo_entrada").value && document.getElementById("almuerzo_fondo").value) {
    const entrada = document.getElementById("almuerzo_entrada")
    const fondo = document.getElementById("almuerzo_fondo")
    const postre = document.getElementById("almuerzo_postre")
    const bebida = document.getElementById("almuerzo_bebida")

    html += `
            <div class="menu-item">
                <h4>Almuerzo - S/. 14.50 por persona</h4>
                <ul>
                    <li>Entrada: ${entrada.options[entrada.selectedIndex].text}</li>
                    <li>Plato de fondo: ${fondo.options[fondo.selectedIndex].text}</li>
        `

    if (postre.value) {
      html += `<li>Postre: ${postre.options[postre.selectedIndex].text}</li>`
    }

    if (bebida.value) {
      html += `<li>Bebida: ${bebida.options[bebida.selectedIndex].text}</li>`
    }

    html += `
                </ul>
            </div>
        `
  }

  // Verificar cena
  if (document.getElementById("cena").value) {
    const plato = document.getElementById("cena")
    const postre = document.getElementById("cena_postre")
    const bebida = document.getElementById("cena_bebida")

    html += `
            <div class="menu-item">
                <h4>Cena - S/. 16.50 por persona</h4>
                <ul>
                    <li>Plato principal: ${plato.options[plato.selectedIndex].text}</li>
        `

    if (postre.value) {
      html += `<li>Postre: ${postre.options[postre.selectedIndex].text}</li>`
    }

    if (bebida.value) {
      html += `<li>Bebida: ${bebida.options[bebida.selectedIndex].text}</li>`
    }

    html += `
                </ul>
            </div>
        `
  }

  // Si no hay menús seleccionados
  if (
    !document.getElementById("desayuno").value &&
    !document.getElementById("almuerzo_entrada").value &&
    !document.getElementById("cena").value
  ) {
    html += "<p>No se ha seleccionado ningún menú.</p>"
  }

  // Agregar total
  html += `
        </div>
        
        <div class="resumen-seccion total">
            <h3>Total a Pagar</h3>
            <p class="precio-total">S/. ${total}</p>
            <p class="detalle-total">Para ${personas} persona${personas > 1 ? "s" : ""}</p>
        </div>
    `

  resumenDiv.innerHTML = html
}

// Función para formatear el número de tarjeta mientras se escribe
function formatearNumeroTarjeta() {
  const input = document.getElementById("numero-tarjeta")
  if (!input) return

  let value = input.value.replace(/\D/g, "")

  // Limitar a 16 dígitos
  if (value.length > 16) {
    value = value.slice(0, 16)
  }

  // Formatear con espacios cada 4 dígitos
  const formattedValue = value.replace(/(\d{4})(?=\d)/g, "$1 ")
  input.value = formattedValue

  // Eliminar la clase de error si el campo tiene 16 dígitos
  if (value.length === 16) {
    input.classList.remove("error-field")
  } else {
    input.classList.add("error-field")
  }
}

// Función para formatear la fecha de expiración mientras se escribe
function formatearFechaExpiracion() {
  const input = document.getElementById("fecha-expiracion")
  if (!input) return

  let value = input.value.replace(/\D/g, "")

  // Limitar a 4 dígitos
  if (value.length > 4) {
    value = value.slice(0, 4)
  }

  // Formatear como MM/AA
  if (value.length > 2) {
    value = value.slice(0, 2) + "/" + value.slice(2)
  }

  input.value = value
}

// Función para cambiar al siguiente paso con animación
function nextStep(next) {
  if (validateStep(currentStep)) {
    // Ocultar paso actual con animación
    const currentStepElement = document.getElementById(`step${currentStep}`)
    currentStepElement.classList.remove("active")

    // Actualizar el paso actual
    currentStep = next

    // Mostrar nuevo paso con animación
    const nextStepElement = document.getElementById(`step${currentStep}`)
    setTimeout(() => {
      nextStepElement.classList.add("active")
    }, 300)

    // Actualizar progreso
    updateProgressSteps()

    // Scroll al inicio del formulario
    window.scrollTo({
      top: document.querySelector(".progress-steps").offsetTop - 50,
      behavior: "smooth",
    })

    if (currentStep === 3) updateResumen()
  }
}

// Función para volver al paso anterior con animación
function prevStep(prev) {
  // Ocultar paso actual con animación
  const currentStepElement = document.getElementById(`step${currentStep}`)
  currentStepElement.classList.remove("active")

  // Actualizar el paso actual
  currentStep = prev

  // Mostrar nuevo paso con animación
  const prevStepElement = document.getElementById(`step${currentStep}`)
  setTimeout(() => {
    prevStepElement.classList.add("active")
  }, 300)

  // Actualizar progreso
  updateProgressSteps()

  // Scroll al inicio del formulario
  window.scrollTo({
    top: document.querySelector(".progress-steps").offsetTop - 50,
    behavior: "smooth",
  })
}

// Función para actualizar el indicador de progreso
function updateProgressSteps() {
  const progressSteps = document.querySelector(".progress-steps")
  if (progressSteps) {
    progressSteps.setAttribute("data-step", currentStep)

    // Actualizar clases de los pasos
    document.querySelectorAll(".progress-steps .step").forEach((step) => {
      const stepNumber = Number.parseInt(step.dataset.step)
      step.classList.remove("active", "completed")

      if (stepNumber === currentStep) {
        step.classList.add("active")
      } else if (stepNumber < currentStep) {
        step.classList.add("completed")
      }
    })
  }
}

// Función para validar datos de pago
function validarPago() {
  // Limpiar errores previos
  limpiarErrores()

  // Obtener valores de los campos
  const nombreTitular = document.getElementById("nombre-titular").value.trim()
  const numeroTarjeta = document.getElementById("numero-tarjeta").value.trim().replace(/\s/g, "")
  const fechaExpiracion = document.getElementById("fecha-expiracion").value.trim()
  const cvc = document.getElementById("cvc").value.trim()

  // Validar que los campos no estén vacíos
  if (!nombreTitular || !numeroTarjeta || !fechaExpiracion || !cvc) {
    showModal("Por favor, complete todos los campos de pago.")

    if (!nombreTitular) document.getElementById("nombre-titular").classList.add("error-field")
    if (!numeroTarjeta) document.getElementById("numero-tarjeta").classList.add("error-field")
    if (!fechaExpiracion) document.getElementById("fecha-expiracion").classList.add("error-field")
    if (!cvc) document.getElementById("cvc").classList.add("error-field")

    return false
  }

  // Validar formato de número de tarjeta (16 dígitos)
  if (!/^\d{16}$/.test(numeroTarjeta)) {
    showModal("El número de tarjeta debe tener 16 dígitos.")
    document.getElementById("numero-tarjeta").classList.add("error-field")
    document.getElementById("numero-tarjeta").focus()
    return false
  }

  // Validar formato de fecha de expiración (MM/AA)
  if (!/^(0[1-9]|1[0-2])\/\d{2}$/.test(fechaExpiracion)) {
    showModal("La fecha de expiración debe tener el formato MM/AA.")
    document.getElementById("fecha-expiracion").classList.add("error-field")
    document.getElementById("fecha-expiracion").focus()
    return false
  }

  // Validar CVC (3-4 dígitos)
  if (!/^\d{3,4}$/.test(cvc)) {
    showModal("El CVC debe tener 3 o 4 dígitos.")
    document.getElementById("cvc").classList.add("error-field")
    document.getElementById("cvc").focus()
    return false
  }

  // Validar que se haya seleccionado al menos un menú
  const desayunoSeleccionado =
    document.getElementById("desayuno").value && document.getElementById("pan_desayuno").value
  const almuerzoSeleccionado =
    document.getElementById("almuerzo_entrada").value && document.getElementById("almuerzo_fondo").value
  const cenaSeleccionada = document.getElementById("cena").value

  if (!desayunoSeleccionado && !almuerzoSeleccionado && !cenaSeleccionada) {
    showModal("Debe seleccionar al menos un menú para realizar la reserva.")
    return false
  }

  return true
}

// Controlador de método de pago
document.addEventListener("change", (e) => {
  if (e.target.name === "metodo_pago") {
    document.getElementById("datosTarjeta").style.display = e.target.value === "tarjeta" ? "block" : "none"
  }
})

// Funciones de menú con animación
function mostrarSeccion(tipo) {
  // Ocultar todas las secciones primero
  document.querySelectorAll(".seccion-menu").forEach((seccion) => {
    seccion.classList.remove("active")
  })

  // Mostrar la sección seleccionada con animación
  const seccionAMostrar = document.getElementById(`seccion-${tipo}`)
  if (seccionAMostrar) {
    setTimeout(() => {
      seccionAMostrar.classList.add("active")
    }, 100)
  }

  // Actualizar botones de menú
  document.querySelectorAll(".boton-menu").forEach((boton) => {
    boton.classList.remove("active")
    if (boton.getAttribute("onclick").includes(tipo)) {
      boton.classList.add("active")
    }
  })
}

// Función para limpiar errores
function limpiarErrores() {
  const campos = document.querySelectorAll(".error-field")
  campos.forEach((campo) => {
    campo.classList.remove("error-field")
  })
}

// Función para mostrar animación de carga en el botón de pago
function mostrarCargaPago(mostrar) {
  const btnPagar = document.querySelector(".btn-pagar")
  if (btnPagar) {
    if (mostrar) {
      btnPagar.classList.add("loading")
    } else {
      btnPagar.classList.remove("loading")
    }
  }
}

// Inicialización cuando el DOM está cargado
document.addEventListener("DOMContentLoaded", () => {
  // Configurar fecha mínima
  const fechaInput = document.getElementById("rdate")
  if (fechaInput) {
    const hoy = new Date()
    const fechaFormateada = hoy.toISOString().split("T")[0]
    fechaInput.min = fechaFormateada

    // Si no hay fecha seleccionada, establecer la fecha actual
    if (!fechaInput.value) {
      fechaInput.value = fechaFormateada
    }
  }

  // Asignar eventos a los campos de tarjeta
  const numeroTarjeta = document.getElementById("numero-tarjeta")
  if (numeroTarjeta) {
    numeroTarjeta.addEventListener("input", formatearNumeroTarjeta)
    numeroTarjeta.addEventListener("focus", function () {
      this.classList.remove("error-field")
    })
  }

  const fechaExpiracion = document.getElementById("fecha-expiracion")
  if (fechaExpiracion) {
    fechaExpiracion.addEventListener("input", formatearFechaExpiracion)
    fechaExpiracion.addEventListener("focus", function () {
      this.classList.remove("error-field")
    })
  }

  const cvc = document.getElementById("cvc")
  if (cvc) {
    cvc.addEventListener("focus", function () {
      this.classList.remove("error-field")
    })
  }

  // Asignar evento al botón de pago
  const btnPagar = document.querySelector(".btn-pagar")
  if (btnPagar) {
    btnPagar.addEventListener("click", (e) => {
      limpiarErrores()
      if (!validarPago()) {
        e.preventDefault() // Evitar envío del formulario si la validación falla
      } else {
        // Mostrar animación de carga
        mostrarCargaPago(true)

        // Asegurarse de que el formulario se envíe correctamente
        const form = document.getElementById("rform")
        if (form) {
          // Asegurarse de que todos los campos necesarios estén incluidos
          const hiddenFields = [
            { name: "cardholder_name", id: "nombre-titular" },
            { name: "numero_tarjeta", id: "numero-tarjeta" },
            { name: "fecha_expiracion", id: "fecha-expiracion" },
            { name: "cvc", id: "cvc" },
          ]

          // Verificar si los campos ocultos ya existen, si no, crearlos
          hiddenFields.forEach((field) => {
            if (!document.querySelector(`input[name="${field.name}"]`)) {
              const input = document.createElement("input")
              input.type = "hidden"
              input.name = field.name
              input.value = document.getElementById(field.id).value.replace(/\s/g, "") // Eliminar espacios
              form.appendChild(input)
            } else {
              // Si ya existe, actualizar su valor
              document.querySelector(`input[name="${field.name}"]`).value = document
                .getElementById(field.id)
                .value.replace(/\s/g, "")
            }
          })

          console.log("Enviando formulario con todos los datos...")
          form.submit() // Enviar el formulario manualmente
        }
      }
    })
  }

  // Mostrar la primera sección de menú por defecto
  mostrarSeccion("desayuno")

  // Asignar eventos a los botones de menú
  document.querySelectorAll(".boton-menu").forEach((boton) => {
    boton.addEventListener("click", function () {
      const tipo = this.getAttribute("onclick").match(/'([^']+)'/)[1]
      mostrarSeccion(tipo)
    })
  })

  // Inicializar el indicador de progreso
  updateProgressSteps()

  // Mostrar el primer paso
  document.getElementById("step1").classList.add("active")

  // Asignar evento al formulario para asegurar que se envíen todos los datos
  const formulario = document.getElementById("rform")
  if (formulario) {
    formulario.addEventListener("submit", (e) => {
      // Si estamos en el paso de pago y la validación pasa, asegurarse de que se envíen todos los datos
      if (currentStep === 4 && validarPago()) {
        // Mostrar animación de carga
        mostrarCargaPago(true)

        // Asegurarse de que los campos de tarjeta se envíen correctamente
        const numeroTarjeta = document.getElementById("numero-tarjeta")
        if (numeroTarjeta) {
          // Crear o actualizar campo oculto para el número de tarjeta sin espacios
          let hiddenField = document.querySelector('input[name="numero_tarjeta"]')
          if (!hiddenField) {
            hiddenField = document.createElement("input")
            hiddenField.type = "hidden"
            hiddenField.name = "numero_tarjeta"
            formulario.appendChild(hiddenField)
          }
          hiddenField.value = numeroTarjeta.value.replace(/\s/g, "")
        }

        console.log("Formulario enviado correctamente")
      } else {
        e.preventDefault() // Evitar envío si no estamos en el paso de pago o la validación falla
        console.log("Formulario no enviado - validación fallida o paso incorrecto")
      }
    })
  }

  // Añadir eventos para animaciones en hover a elementos del resumen
  document.addEventListener("mouseover", (e) => {
    if (e.target.closest(".menu-item")) {
      e.target.closest(".menu-item").style.transform = "translateY(-5px)"
      e.target.closest(".menu-item").style.boxShadow = "var(--shadow-md)"
    }
  })

  document.addEventListener("mouseout", (e) => {
    if (e.target.closest(".menu-item")) {
      e.target.closest(".menu-item").style.transform = ""
      e.target.closest(".menu-item").style.boxShadow = ""
    }
  })
})

// Función para agregar efecto de onda al hacer clic en botones
function addRippleEffect(event) {
  const button = event.currentTarget

  const circle = document.createElement("span")
  const diameter = Math.max(button.clientWidth, button.clientHeight)

  circle.style.width = circle.style.height = `${diameter}px`
  circle.style.left = `${event.clientX - button.offsetLeft - diameter / 2}px`
  circle.style.top = `${event.clientY - button.offsetTop - diameter / 2}px`
  circle.classList.add("ripple")

  const ripple = button.querySelector(".ripple")
  if (ripple) {
    ripple.remove()
  }

  button.appendChild(circle)
}

// Agregar efecto de onda a todos los botones
document.addEventListener("DOMContentLoaded", () => {
  const buttons = document.querySelectorAll("button, .boton-menu, .btn-volver, .btn-pagar")
  buttons.forEach((button) => {
    button.addEventListener("click", addRippleEffect)
  })
})

