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
  const personas = Number.parseInt(document.getElementById("partySize").value) || 0 // Corregido de rparty-size a partySize

  // Verificar desayuno completo
  const menuType = document.getElementById("menuType").value; // Obtener el tipo de menú seleccionado
  
  if (menuType === 'desayuno') {
    if (document.getElementById("desayunoBebida").value && document.getElementById("desayunoPan").value) {
      total += 9.0 * personas
    }
  } else if (menuType === 'almuerzo') {
    if (document.getElementById("almuerzoEntrada").value && document.getElementById("almuerzoFondo").value) {
      total += 14.5 * personas
    }
  } else if (menuType === 'cena') {
    if (document.getElementById("cenaPlato").value) {
      total += 16.5 * personas
    }
  }

  return total.toFixed(2)
}

// Función para validar el paso actual
function validateStep(step) {
  // Limpiar errores previos
  limpiarErrores();

  if (step === 1) {
    const menuType = document.getElementById("menuType").value;
    if (!menuType) {
      showModal("Debes seleccionar un tipo de menú.");
      document.getElementById("menuType").classList.add("error-field");
      return false;
    }

    let valido = true;
    if (menuType === 'desayuno') {
      if (!document.getElementById("desayunoBebida").value || !document.getElementById("desayunoPan").value) {
        showModal("Debes seleccionar todas las opciones obligatorias del desayuno.");
        if (!document.getElementById("desayunoBebida").value) document.getElementById("desayunoBebida").classList.add("error-field");
        if (!document.getElementById("desayunoPan").value) document.getElementById("desayunoPan").classList.add("error-field");
        valido = false;
      }
    } else if (menuType === 'almuerzo') {
      if (!document.getElementById("almuerzoEntrada").value || !document.getElementById("almuerzoFondo").value) {
        showModal("Debes seleccionar todas las opciones obligatorias del almuerzo.");
        if (!document.getElementById("almuerzoEntrada").value) document.getElementById("almuerzoEntrada").classList.add("error-field");
        if (!document.getElementById("almuerzoFondo").value) document.getElementById("almuerzoFondo").classList.add("error-field");
        valido = false;
      }
    } else if (menuType === 'cena') {
      if (!document.getElementById("cenaPlato").value) {
        showModal("Debes seleccionar el plato principal de la cena.");
        if (!document.getElementById("cenaPlato").value) document.getElementById("cenaPlato").classList.add("error-field");
        valido = false;
      }
    }
    return valido;
  }

  if (step === 2) {
    // Validar campos requeridos
    const requiredFields = [
      { id: "name", label: "Nombre" }, // Corregido de rname a name
      { id: "phone", label: "Número de contacto" }, // Corregido de rphone a phone
      { id: "date", label: "Fecha" }, // Corregido de rdate a date
      { id: "time", label: "Hora" }, // Corregido de rtime a time
      { id: "partySize", label: "Tamaño del grupo" }, // Corregido de rparty-size a partySize
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
    const phone = document.getElementById("phone").value // Corregido de rphone a phone
    if (!/^9\d{8}$/.test(phone)) {
      showModal("El teléfono debe tener 9 dígitos y empezar con 9.")
      document.getElementById("phone").classList.add("error-field") // Corregido de rphone a phone
      document.getElementById("phone").focus() // Corregido de rphone a phone
      return false
    } else {
      document.getElementById("phone").classList.remove("error-field") // Corregido de rphone a phone
    }

    // Validar que la fecha no sea pasada
    const selectedDate = new Date(document.getElementById("date").value + "T" + document.getElementById("time").value) // Corregido de rdate, rtime
    const now = new Date()

    if (selectedDate < now) {
      showModal("No puedes reservar en fechas u horas pasadas.")
      document.getElementById("date").classList.add("error-field") // Corregido de rdate
      document.getElementById("time").classList.add("error-field") // Corregido de rtime
      return false
    } else {
      document.getElementById("date").classList.remove("error-field") // Corregido de rdate
      document.getElementById("time").classList.remove("error-field") // Corregido de rtime
    }

    // Validar número de personas
    const personas = Number.parseInt(document.getElementById("partySize").value) // Corregido de rparty-size
    if (isNaN(personas) || personas <= 0 || personas > 250) {
      showModal("El número de personas debe ser entre 1 y 250.")
      document.getElementById("partySize").classList.add("error-field") // Corregido de rparty-size
      document.getElementById("partySize").focus() // Corregido de rparty-size
      return false
    } else {
      document.getElementById("partySize").classList.remove("error-field") // Corregido de rparty-size
    }
  }

  return true
}

// Función para actualizar resumen con animaciones
function updateResumen() {
  const resumenDiv = document.getElementById("resumenContenido")
  if (!resumenDiv) return

  // Obtener datos personales
  const nombre = document.getElementById("name").value // Corregido de rname
  const telefono = document.getElementById("phone").value // Corregido de rphone
  const email = document.getElementById("email").value || "No especificado"
  const fecha = document.getElementById("date").value // Corregido de rdate
  const hora = document.getElementById("time").value // Corregido de rtime
  const personas = document.getElementById("partySize").value // Corregido de rparty-size
  const info = document.getElementById("additionalInfo").value || "Ninguna" // Corregido de radd-info

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
  if (document.getElementById("menuType").value === 'desayuno') {
    const bebida = document.getElementById("desayunoBebida")
    const pan = document.getElementById("desayunoPan")

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
  if (document.getElementById("menuType").value === 'almuerzo') {
    const entrada = document.getElementById("almuerzoEntrada")
    const fondo = document.getElementById("almuerzoFondo")
    const postre = document.getElementById("almuerzoPostre")
    const bebida = document.getElementById("almuerzoBebida")

    html += `
            <div class="menu-item">
                <h4>Almuerzo - S/. 14.50 por persona</h4>
                <ul>
                    <li>Entrada: ${entrada.options[entrada.selectedIndex].text}</li>
                    <li>Plato de fondo: ${fondo.options[fondo.selectedIndex].text}</li>
        `

    if (postre.value && postre.value !== 'no_incluir') {
      html += `<li>Postre: ${postre.options[postre.selectedIndex].text}</li>`
    }

    if (bebida.value && bebida.value !== 'no_incluir') {
      html += `<li>Bebida: ${bebida.options[bebida.selectedIndex].text}</li>`
    }

    html += `
                </ul>
            </div>
        `
  }

  // Verificar cena
  if (document.getElementById("menuType").value === 'cena') {
    const plato = document.getElementById("cenaPlato")
    const postre = document.getElementById("cenaPostre")
    const bebida = document.getElementById("cenaBebida")

    html += `
            <div class="menu-item">
                <h4>Cena - S/. 16.50 por persona</h4>
                <ul>
                    <li>Plato principal: ${plato.options[plato.selectedIndex].text}</li>
        `

    if (postre.value && postre.value !== 'no_incluir') {
      html += `<li>Postre: ${postre.options[postre.selectedIndex].text}</li>`
    }

    if (bebida.value && bebida.value !== 'no_incluir') {
      html += `<li>Bebida: ${bebida.options[bebida.selectedIndex].text}</li>`
    }

    html += `
                </ul>
            </div>
        `
  }

  // Si no hay menús seleccionados
  if (
    !document.getElementById("menuType").value
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
      top: document.querySelector(".steps-indicator").offsetTop - 50, // Corregido a steps-indicator
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
    top: document.querySelector(".steps-indicator").offsetTop - 50, // Corregido a steps-indicator
    behavior: "smooth",
  })
}

// Función para actualizar el indicador de progreso
function updateProgressSteps() {
  const progressSteps = document.querySelector(".steps-indicator") // Corregido a steps-indicator
  if (progressSteps) {
    progressSteps.setAttribute("data-step", currentStep)

    // Actualizar clases de los pasos
    document.querySelectorAll(".steps-indicator .step").forEach((step) => { // Corregido a steps-indicator
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
  const menuType = document.getElementById("menuType").value;
  let menuSeleccionado = false;
  if (menuType === 'desayuno' && document.getElementById("desayunoBebida").value && document.getElementById("desayunoPan").value) {
    menuSeleccionado = true;
  } else if (menuType === 'almuerzo' && document.getElementById("almuerzoEntrada").value && document.getElementById("almuerzoFondo").value) {
    menuSeleccionado = true;
  } else if (menuType === 'cena' && document.getElementById("cenaPlato").value) {
    menuSeleccionado = true;
  }

  if (!menuSeleccionado) {
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
  document.querySelectorAll(".menu-options").forEach((seccion) => { // Corregido de seccion-menu a menu-options
    seccion.classList.add("hidden") // Corregido de remove("active") a add("hidden")
  })

  // Mostrar la sección seleccionada con animación
  const seccionAMostrar = document.getElementById(`opciones${tipo.charAt(0).toUpperCase() + tipo.slice(1)}`) // Corregido de seccion-${tipo} a opciones${tipo}
  if (seccionAMostrar) {
    setTimeout(() => {
      seccionAMostrar.classList.remove("hidden") // Corregido de add("active") a remove("hidden")
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
  const errorMessages = document.querySelectorAll(".error-message");
  errorMessages.forEach(msg => msg.remove());
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
  const fechaInput = document.getElementById("date") // Corregido de rdate a date
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
        const form = document.getElementById("rform") // Assuming the form has id="rform"
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
  // This part is handled by PHP now based on the 'paso' variable
  // mostrarSeccion("desayuno") 

  // Asignar eventos a los botones de menú
  document.querySelectorAll(".boton-menu").forEach((boton) => {
    boton.addEventListener("click", function () {
      const tipo = this.getAttribute("onclick").match(/'([^']+)'/)[1]
      mostrarSeccion(tipo)
    })
  })

  // Inicializar el indicador de progreso
  updateProgressSteps()

  // Mostrar el primer paso (handled by PHP now)
  // document.getElementById("step1").classList.add("active")

  // Asignar evento al formulario para asegurar que se envíen todos los datos
  const formulario = document.querySelector("form.reservation-form") // Changed to select the current form
  if (formulario) {
    formulario.addEventListener("submit", (e) => {
      // If we are on the payment step and validation passes, ensure all data is sent
      // This logic is now handled by the PHP form submission directly for payment registration
      // For multi-step form, PHP handles validation on submit, and client-side validation is done before advancing step
      if (formulario.querySelector('input[name="registro_pago"]')) { // Check if it's the payment registration form
        // Client-side validation for payment registration form
        const phoneInput = formulario.querySelector('#phone');
        const dateInput = formulario.querySelector('#reservation_date');
        const payerNameInput = formulario.querySelector('#payer_name');
        const paymentMethodRadios = formulario.querySelectorAll('input[name="payment_method"]');
        const operationNumberInput = formulario.querySelector('#operation_number');
        const securityCodeInput = formulario.querySelector('#security_code');

        let isValid = true;
        limpiarErrores();

        if (!phoneInput.value.trim() || !/^9\d{8}$/.test(phoneInput.value.trim())) {
            showModal("Por favor, introduce un número de teléfono válido (9 dígitos comenzando con 9).");
            phoneInput.classList.add("error-field");
            isValid = false;
        }
        if (!dateInput.value.trim()) {
            showModal("La fecha de reserva es obligatoria.");
            dateInput.classList.add("error-field");
            isValid = false;
        }
        if (!payerNameInput.value.trim()) {
            showModal("El nombre del pagador es obligatorio.");
            payerNameInput.classList.add("error-field");
            isValid = false;
        }
        let paymentMethodSelected = false;
        paymentMethodRadios.forEach(radio => {
            if (radio.checked) paymentMethodSelected = true;
        });
        if (!paymentMethodSelected) {
            showModal("Debes seleccionar un método de pago.");
            isValid = false;
        }
        if (!operationNumberInput.value.trim()) {
            showModal("El número de operación es obligatorio.");
            operationNumberInput.classList.add("error-field");
            isValid = false;
        }
        if (formulario.querySelector('#yape').checked && !securityCodeInput.value.trim()) {
            showModal("El código de seguridad es obligatorio para pagos con Yape.");
            securityCodeInput.classList.add("error-field");
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
        }
      } else {
        // This is for the multi-step reservation form
        // The PHP handles the step advancement and validation on the server-side
        // Client-side validation is done by nextStep() before submitting
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

document.addEventListener("DOMContentLoaded", () => {
  // Mostrar/ocultar el campo de código de seguridad según el método de pago
  const yapeRadio = document.getElementById("yape")
  const securityCodeGroup = document.getElementById("security_code_group")
  const securityCodeInput = document.getElementById("security_code")

  if (yapeRadio && securityCodeGroup) {
    // Función para actualizar la visibilidad del campo de código de seguridad
    function updateSecurityCodeVisibility() {
      if (yapeRadio.checked) {
        securityCodeGroup.style.display = "block"
        securityCodeInput.setAttribute("required", "required")
        // Animación de aparición
        securityCodeGroup.style.opacity = "0"
        setTimeout(() => {
          securityCodeGroup.style.opacity = "1"
        }, 10)
      } else {
        // Animación de desaparición
        securityCodeGroup.style.opacity = "0"
        setTimeout(() => {
          securityCodeGroup.style.display = "none"
          securityCodeInput.removeAttribute("required")
        }, 300)
      }
    }

    // Verificar estado inicial
    updateSecurityCodeVisibility()

    // Agregar listeners a los radio buttons
    document.querySelectorAll('input[name="payment_method"]').forEach((radio) => {
      radio.addEventListener("change", updateSecurityCodeVisibility)
    })
  }

  // Manejar la carga de archivos
  const fileInput = document.getElementById("voucher")
  const fileName = document.querySelector(".file-name")

  if (fileInput && fileName) {
    fileInput.addEventListener("change", () => {
      if (fileInput.files.length > 0) {
        fileName.textContent = fileInput.files[0].name
        // Animación de cambio de texto
        fileName.classList.add("file-selected")
        setTimeout(() => {
          fileName.classList.remove("file-selected")
        }, 1000)
      } else {
        fileName.textContent = "Sin archivos seleccionados"
      }
    })
  }

  // Manejar las pestañas de métodos de pago
  const tabButtons = document.querySelectorAll(".payment-tab-btn")

  if (tabButtons.length > 0) {
    tabButtons.forEach((button) => {
      button.addEventListener("click", function () {
        // Remover clase active de todos los botones
        tabButtons.forEach((btn) => btn.classList.remove("active"))

        // Agregar clase active al botón clickeado
        this.classList.add("active")

        // Ocultar todos los paneles con animación
        document.querySelectorAll(".payment-tab-pane").forEach((pane) => {
          pane.style.opacity = "0"
          setTimeout(() => {
            pane.classList.remove("active")
          }, 300)
        })

        // Mostrar el panel correspondiente con animación
        const tabId = this.getAttribute("data-tab")
        const activePane = document.getElementById(tabId)
        setTimeout(() => {
          activePane.classList.add("active")
          setTimeout(() => {
            activePane.style.opacity = "1"
          }, 10)
        }, 300)
      })
    })
  }

  // Mostrar/ocultar opciones de menú según la selección con animación
  const menuTypeSelect = document.getElementById("menuType")

  if (menuTypeSelect) {
    menuTypeSelect.addEventListener("change", function () {
      const menuType = this.value

      // Ocultar todas las opciones con animación
      document.querySelectorAll(".menu-options").forEach((option) => {
        option.style.opacity = "0"
        setTimeout(() => {
          option.classList.add("hidden")
        }, 300)
      })

      // Mostrar la opción seleccionada con animación
      if (menuType) {
        const selectedOption = document.getElementById(
          "opciones" + menuType.charAt(0).toUpperCase() + menuType.slice(1),
        )
        setTimeout(() => {
          selectedOption.classList.remove("hidden")
          setTimeout(() => {
            selectedOption.style.opacity = "1"
          }, 10)
        }, 300)
      }
    })
  }

  // Efecto de onda para botones
  const buttons = document.querySelectorAll(".btn")
  buttons.forEach((button) => {
    button.addEventListener("click", function (e) {
      const x = e.clientX - e.target.getBoundingClientRect().left
      const y = e.clientY - e.target.getBoundingClientRect().top

      const ripple = document.createElement("span")
      ripple.classList.add("ripple")
      ripple.style.left = `${x}px`
      ripple.style.top = `${y}px`

      this.appendChild(ripple)

      setTimeout(() => {
        ripple.remove()
      }, 600)
    })
  })

  // Validación de formularios
  const forms = document.querySelectorAll("form")
  forms.forEach((form) => {
    form.addEventListener("submit", (e) => {
      let isValid = true
      const requiredFields = form.querySelectorAll("[required]")

      requiredFields.forEach((field) => {
        if (!field.value.trim()) {
          isValid = false
          field.classList.add("error-field")

          // Crear mensaje de error si no existe
          let errorMessage = field.nextElementSibling
          if (!errorMessage || !errorMessage.classList.contains("error-message")) {
            errorMessage = document.createElement("div")
            errorMessage.classList.add("error-message")
            errorMessage.textContent = "Este campo es obligatorio"
            field.parentNode.insertBefore(errorMessage, field.nextSibling)
          }
        } else {
          field.classList.remove("error-field")

          // Eliminar mensaje de error si existe
          const errorMessage = field.nextElementSibling
          if (errorMessage && errorMessage.classList.contains("error-message")) {
            errorMessage.remove()
          }
        }
      })

      if (!isValid) {
        e.preventDefault()

        // Scroll al primer campo con error
        const firstError = form.querySelector(".error-field")
        if (firstError) {
          firstError.scrollIntoView({ behavior: "smooth", block: "center" })
          firstError.focus()
        }
      }
    })
  })

  // Animación para alertas
  const alerts = document.querySelectorAll(".alert")
  alerts.forEach((alert) => {
    // Añadir botón de cierre
    const closeBtn = document.createElement("span")
    closeBtn.innerHTML = "&times;"
    closeBtn.classList.add("alert-close")
    alert.appendChild(closeBtn)

    // Funcionalidad para cerrar la alerta
    closeBtn.addEventListener("click", () => {
      alert.style.opacity = "0"
      setTimeout(() => {
        alert.style.display = "none"
      }, 300)
    })

    // Auto-ocultar después de 5 segundos
    setTimeout(() => {
      alert.style.opacity = "0"
      setTimeout(() => {
        alert.style.display = "none"
      }, 300)
    }, 5000)
  })

  // Animación para transiciones entre pasos
  const stepButtons = document.querySelectorAll(".form-actions .btn")
  stepButtons.forEach((button) => {
    if (!button.classList.contains("btn-outline")) {
      button.addEventListener("click", function () {
        const currentForm = this.closest("form")
        if (currentForm && currentForm.checkValidity()) {
          currentForm.style.opacity = "0"
          setTimeout(() => {
            currentForm.submit()
          }, 300)
        }
      })
    }
  })

  // Mostrar notificación de éxito después de enviar el formulario
  const urlParams = new URLSearchParams(window.location.search)
  const success = urlParams.get("success")
  const error = urlParams.get("error")

  if (success) {
    showNotification("¡Operación completada con éxito!", "success")
  } else if (error) {
    showNotification("Ha ocurrido un error. Por favor, inténtalo de nuevo.", "error")
  }

  function showNotification(message, type) {
    const notification = document.createElement("div")
    notification.classList.add("notification", `notification-${type}`)
    notification.textContent = message

    document.body.appendChild(notification)

    setTimeout(() => {
      notification.classList.add("show")
    }, 10)

    setTimeout(() => {
      notification.classList.remove("show")
      setTimeout(() => {
        notification.remove()
      }, 300)
    }, 3000)
  }
})
/**
 * Sistema completo de reservas con notificaciones y animaciones
 * Integra validación, navegación por pasos, notificaciones y efectos visuales
 */

// Variables globales

// Clase para el sistema de notificaciones
class ReservaNotificaciones {
  constructor() {
    this.apiUrl = "api/notificaciones.php"
  }

  /**
   * Envía una notificación por correo electrónico
   */
  async enviarNotificacionEmail(tipo, datos) {
    try {
      const response = await fetch(this.apiUrl, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          tipo: tipo,
          medio: "email",
          datos: datos,
        }),
      })
      const data = await response.json()
      console.log("Notificación por email enviada:", data)
      return data
    } catch (error) {
      console.error("Error al enviar notificación por email:", error)
      throw error
    }
  }

  /**
   * Envía una notificación por WhatsApp
   */
  async enviarNotificacionWhatsApp(tipo, datos) {
    try {
      const response = await fetch(this.apiUrl, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          tipo: tipo,
          medio: "whatsapp",
          datos: datos,
        }),
      })
      const data = await response.json()
      console.log("Notificación por WhatsApp enviada:", data)
      return data
    } catch (error) {
      console.error("Error al enviar notificación por WhatsApp:", error)
      throw error
    }
  }

  /**
   * Envía confirmación de pago
   */
  async enviarConfirmacionPago(reserva) {
    const mensaje = `¡Gracias ${reserva.nombre}! Hemos confirmado tu pago. Tu reserva para el ${reserva.fecha} ha sido confirmada. ¡Te esperamos!`

    const datos = {
      ...reserva,
      mensaje: mensaje,
    }

    const promesas = []

    if (reserva.email) {
      promesas.push(this.enviarNotificacionEmail("confirmacion_pago", datos))
    }

    if (reserva.telefono) {
      promesas.push(this.enviarNotificacionWhatsApp("confirmacion_pago", datos))
    }

    try {
      await Promise.all(promesas)
      return {
        success: true,
        message: "Confirmación de pago enviada",
      }
    } catch (error) {
      console.error("Error al enviar confirmaciones:", error)
      return {
        success: false,
        message: "Error al enviar confirmaciones",
      }
    }
  }
}

// Inicialización del DOM
document.addEventListener("DOMContentLoaded", () => {
  // Inicializar sistema de notificaciones
  notificationSystem = new ReservaNotificaciones()

  // Configurar validación de formularios
  setupFormValidation()

  // Configurar navegación por pasos
  setupStepNavigation()

  // Configurar animaciones
  setupAnimations()

  // Configurar eventos específicos
  setupSpecificEvents()

  // Configurar efectos visuales
  setupVisualEffects()
})

/**
 * Configuración de validación de formularios
 */
function setupFormValidation() {
  const forms = document.querySelectorAll("form")

  forms.forEach((form) => {
    form.addEventListener("submit", (e) => {
      if (!validateCurrentStep(form)) {
        e.preventDefault()
        return false
      }

      // Mostrar animación de carga
      showLoadingAnimation(form)
    })

    // Validación en tiempo real
    const inputs = form.querySelectorAll("input, select, textarea")
    inputs.forEach((input) => {
      input.addEventListener("blur", () => validateField(input))
      input.addEventListener("input", () => clearFieldError(input))
    })
  })
}

/**
 * Validación del paso actual
 */
function validateCurrentStep(form) {
  let isValid = true
  const requiredFields = form.querySelectorAll("[required]")

  clearAllErrors()

  requiredFields.forEach((field) => {
    if (!validateField(field)) {
      isValid = false
    }
  })

  // Validaciones específicas por paso
  if (form.querySelector('input[name="paso"]')) {
    const paso = Number.parseInt(form.querySelector('input[name="paso"]').value)

    switch (paso) {
      case 1:
        isValid = validateMenuSelection() && isValid
        break
      case 2:
        isValid = validatePersonalData() && isValid
        break
      case 3:
        isValid = validateSummary() && isValid
        break
    }
  }

  // Validación para registro de pago
  if (form.querySelector('input[name="registro_pago"]')) {
    isValid = validatePaymentRegistration() && isValid
  }

  if (!isValid) {
    showErrorNotification("Por favor, corrige los errores en el formulario")
    scrollToFirstError()
  }

  return isValid
}

/**
 * Validación de campo individual
 */
function validateField(field) {
  const value = field.value.trim()
  let isValid = true
  let errorMessage = ""

  // Validación de campos requeridos
  if (field.hasAttribute("required") && !value) {
    errorMessage = "Este campo es obligatorio"
    isValid = false
  }

  // Validaciones específicas por tipo
  if (value && isValid) {
    switch (field.type) {
      case "email":
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
          errorMessage = "Ingresa un email válido"
          isValid = false
        }
        break

      case "tel":
        if (!/^9\d{8}$/.test(value)) {
          errorMessage = "Debe ser un número de 9 dígitos que comience con 9"
          isValid = false
        }
        break

      case "number":
        const min = field.getAttribute("min")
        const max = field.getAttribute("max")
        const numValue = Number.parseInt(value)

        if (min && numValue < Number.parseInt(min)) {
          errorMessage = `El valor mínimo es ${min}`
          isValid = false
        }
        if (max && numValue > Number.parseInt(max)) {
          errorMessage = `El valor máximo es ${max}`
          isValid = false
        }
        break

      case "date":
        const selectedDate = new Date(value)
        const today = new Date()
        today.setHours(0, 0, 0, 0)

        if (selectedDate < today) {
          errorMessage = "No puedes seleccionar una fecha pasada"
          isValid = false
        }
        break
    }
  }

  // Mostrar/ocultar error
  if (!isValid) {
    showFieldError(field, errorMessage)
  } else {
    clearFieldError(field)
  }

  return isValid
}

/**
 * Validación de selección de menú
 */
function validateMenuSelection() {
  const menuType = document.getElementById("menuType")?.value

  if (!menuType) {
    showFieldError(document.getElementById("menuType"), "Debes seleccionar un tipo de menú")
    return false
  }

  let isValid = true

  switch (menuType) {
    case "desayuno":
      const bebida = document.getElementById("desayunoBebida")?.value
      const pan = document.getElementById("desayunoPan")?.value

      if (!bebida) {
        showFieldError(document.getElementById("desayunoBebida"), "Selecciona una bebida")
        isValid = false
      }
      if (!pan) {
        showFieldError(document.getElementById("desayunoPan"), "Selecciona un tipo de pan")
        isValid = false
      }
      break

    case "almuerzo":
      const entrada = document.getElementById("almuerzoEntrada")?.value
      const fondo = document.getElementById("almuerzoFondo")?.value

      if (!entrada) {
        showFieldError(document.getElementById("almuerzoEntrada"), "Selecciona una entrada")
        isValid = false
      }
      if (!fondo) {
        showFieldError(document.getElementById("almuerzoFondo"), "Selecciona un plato de fondo")
        isValid = false
      }
      break

    case "cena":
      const plato = document.getElementById("cenaPlato")?.value

      if (!plato) {
        showFieldError(document.getElementById("cenaPlato"), "Selecciona un plato principal")
        isValid = false
      }
      break
  }

  return isValid
}

/**
 * Validación de datos personales
 */
function validatePersonalData() {
  let isValid = true

  // Validar fecha y hora
  const fecha = document.getElementById("date")?.value
  const hora = document.getElementById("time")?.value

  if (fecha && hora) {
    const fechaHora = new Date(`${fecha}T${hora}`)
    const ahora = new Date()

    if (fechaHora <= ahora) {
      showFieldError(document.getElementById("date"), "La fecha y hora deben ser futuras")
      showFieldError(document.getElementById("time"), "La fecha y hora deben ser futuras")
      isValid = false
    }
  }

  return isValid
}

/**
 * Validación del resumen
 */
function validateSummary() {
  // Aquí puedes agregar validaciones adicionales para el resumen
  return true
}

/**
 * Validación de registro de pago
 */
function validatePaymentRegistration() {
  let isValid = true

  const metodoPago = document.querySelector('input[name="payment_method"]:checked')?.value
  const codigoSeguridad = document.getElementById("security_code")

  if (metodoPago === "yape" && codigoSeguridad && !codigoSeguridad.value.trim()) {
    showFieldError(codigoSeguridad, "El código de seguridad es obligatorio para Yape")
    isValid = false
  }

  return isValid
}

/**
 * Mostrar error en campo
 */
function showFieldError(field, message) {
  field.classList.add("error-field")

  // Remover mensaje de error existente
  const existingError = field.parentNode.querySelector(".error-message")
  if (existingError) {
    existingError.remove()
  }

  // Crear nuevo mensaje de error
  const errorDiv = document.createElement("div")
  errorDiv.classList.add("error-message")
  errorDiv.textContent = message

  field.parentNode.insertBefore(errorDiv, field.nextSibling)

  // Animación de aparición
  setTimeout(() => {
    errorDiv.style.opacity = "1"
    errorDiv.style.transform = "translateY(0)"
  }, 10)
}

/**
 * Limpiar error de campo
 */
function clearFieldError(field) {
  field.classList.remove("error-field")

  const errorMessage = field.parentNode.querySelector(".error-message")
  if (errorMessage) {
    errorMessage.style.opacity = "0"
    errorMessage.style.transform = "translateY(-10px)"
    setTimeout(() => {
      errorMessage.remove()
    }, 300)
  }
}

/**
 * Limpiar todos los errores
 */
function clearAllErrors() {
  document.querySelectorAll(".error-field").forEach((field) => {
    field.classList.remove("error-field")
  })

  document.querySelectorAll(".error-message").forEach((error) => {
    error.remove()
  })
}

/**
 * Scroll al primer error
 */
function scrollToFirstError() {
  const firstError = document.querySelector(".error-field")
  if (firstError) {
    firstError.scrollIntoView({
      behavior: "smooth",
      block: "center",
    })
    firstError.focus()
  }
}

/**
 * Configuración de navegación por pasos
 */
function setupStepNavigation() {
  // Mostrar/ocultar opciones de menú
  const menuTypeSelect = document.getElementById("menuType")
  if (menuTypeSelect) {
    menuTypeSelect.addEventListener("change", function () {
      const menuType = this.value

      // Ocultar todas las opciones con animación
      document.querySelectorAll(".menu-options").forEach((option) => {
        option.style.opacity = "0"
        setTimeout(() => {
          option.classList.add("hidden")
        }, 300)
      })

      // Mostrar la opción seleccionada con animación
      if (menuType) {
        const selectedOption = document.getElementById(
          "opciones" + menuType.charAt(0).toUpperCase() + menuType.slice(1),
        )
        if (selectedOption) {
          setTimeout(() => {
            selectedOption.classList.remove("hidden")
            setTimeout(() => {
              selectedOption.style.opacity = "1"
            }, 10)
          }, 300)
        }
      }
    })
  }
}

/**
 * Configuración de animaciones
 */
function setupAnimations() {
  // Animaciones para transiciones entre pasos
  const stepButtons = document.querySelectorAll(".form-actions .btn")
  stepButtons.forEach((button) => {
    if (!button.classList.contains("btn-outline")) {
      button.addEventListener("click", function (e) {
        const currentForm = this.closest("form")
        if (currentForm) {
          // Agregar clase de carga
          this.classList.add("loading")

          // Animación de salida del formulario
          currentForm.style.opacity = "0.7"
          currentForm.style.transform = "scale(0.98)"
        }
      })
    }
  })

  // Efecto de onda para botones
  const buttons = document.querySelectorAll(".btn")
  buttons.forEach((button) => {
    button.addEventListener("click", function (e) {
      const x = e.clientX - e.target.getBoundingClientRect().left
      const y = e.clientY - e.target.getBoundingClientRect().top

      const ripple = document.createElement("span")
      ripple.classList.add("ripple")
      ripple.style.left = `${x}px`
      ripple.style.top = `${y}px`

      this.appendChild(ripple)

      setTimeout(() => {
        ripple.remove()
      }, 600)
    })
  })
}

/**
 * Configuración de eventos específicos
 */
function setupSpecificEvents() {
  // Manejo de métodos de pago
  const paymentMethods = document.querySelectorAll('input[name="payment_method"]')
  const securityCodeGroup = document.getElementById("security_code_group")
  const securityCodeInput = document.getElementById("security_code")

  if (paymentMethods.length > 0 && securityCodeGroup) {
    paymentMethods.forEach((radio) => {
      radio.addEventListener("change", function () {
        if (this.value === "yape") {
          securityCodeGroup.style.display = "block"
          securityCodeInput.setAttribute("required", "required")

          // Animación de aparición
          securityCodeGroup.style.opacity = "0"
          setTimeout(() => {
            securityCodeGroup.style.opacity = "1"
          }, 10)
        } else {
          // Animación de desaparición
          securityCodeGroup.style.opacity = "0"
          setTimeout(() => {
            securityCodeGroup.style.display = "none"
            securityCodeInput.removeAttribute("required")
          }, 300)
        }
      })
    })
  }

  // Manejo de carga de archivos
  const fileInput = document.getElementById("voucher")
  const fileName = document.querySelector(".file-name")

  if (fileInput && fileName) {
    fileInput.addEventListener("change", () => {
      if (fileInput.files.length > 0) {
        fileName.textContent = fileInput.files[0].name
        fileName.classList.add("file-selected")

        setTimeout(() => {
          fileName.classList.remove("file-selected")
        }, 1000)
      } else {
        fileName.textContent = "Sin archivos seleccionados"
      }
    })
  }

  // Pestañas de métodos de pago
  const tabButtons = document.querySelectorAll(".payment-tab-btn")
  if (tabButtons.length > 0) {
    tabButtons.forEach((button) => {
      button.addEventListener("click", function () {
        // Remover clase active de todos los botones
        tabButtons.forEach((btn) => btn.classList.remove("active"))

        // Agregar clase active al botón clickeado
        this.classList.add("active")

        // Ocultar todos los paneles con animación
        document.querySelectorAll(".payment-tab-pane").forEach((pane) => {
          pane.style.opacity = "0"
          setTimeout(() => {
            pane.classList.remove("active")
          }, 300)
        })

        // Mostrar el panel correspondiente con animación
        const tabId = this.getAttribute("data-tab")
        const activePane = document.getElementById(tabId)
        if (activePane) {
          setTimeout(() => {
            activePane.classList.add("active")
            setTimeout(() => {
              activePane.style.opacity = "1"
            }, 10)
          }, 300)
        }
      })
    })
  }
}

/**
 * Configuración de efectos visuales
 */
function setupVisualEffects() {
  // Animación para alertas
  const alerts = document.querySelectorAll(".alert")
  alerts.forEach((alert) => {
    // Añadir botón de cierre
    const closeBtn = document.createElement("span")
    closeBtn.innerHTML = "&times;"
    closeBtn.classList.add("alert-close")
    alert.appendChild(closeBtn)

    // Funcionalidad para cerrar la alerta
    closeBtn.addEventListener("click", () => {
      alert.style.opacity = "0"
      alert.style.transform = "translateY(-20px)"
      setTimeout(() => {
        alert.style.display = "none"
      }, 300)
    })

    // Auto-ocultar después de 5 segundos
    setTimeout(() => {
      if (alert.style.display !== "none") {
        alert.style.opacity = "0"
        alert.style.transform = "translateY(-20px)"
        setTimeout(() => {
          alert.style.display = "none"
        }, 300)
      }
    }, 5000)
  })

  // Efectos hover para elementos interactivos
  document.addEventListener("mouseover", (e) => {
    if (e.target.closest(".menu-item")) {
      const item = e.target.closest(".menu-item")
      item.style.transform = "translateY(-2px)"
      item.style.boxShadow = "0 4px 12px rgba(0,0,0,0.15)"
    }
  })

  document.addEventListener("mouseout", (e) => {
    if (e.target.closest(".menu-item")) {
      const item = e.target.closest(".menu-item")
      item.style.transform = ""
      item.style.boxShadow = ""
    }
  })
}

/**
 * Mostrar animación de carga
 */
function showLoadingAnimation(form) {
  const submitBtn = form.querySelector('button[type="submit"]')
  if (submitBtn) {
    submitBtn.classList.add("loading")
    submitBtn.disabled = true

    const originalText = submitBtn.textContent
    submitBtn.textContent = "Procesando..."

    // Restaurar después de 3 segundos si no se redirige
    setTimeout(() => {
      submitBtn.classList.remove("loading")
      submitBtn.disabled = false
      submitBtn.textContent = originalText
    }, 3000)
  }
}

/**
 * Mostrar notificación de error
 */
function showErrorNotification(message) {
  showNotification(message, "error")
}

/**
 * Mostrar notificación de éxito
 */
function showSuccessNotification(message) {
  showNotification(message, "success")
}

/**
 * Mostrar notificación general
 */
function showNotification(message, type) {
  const notification = document.createElement("div")
  notification.classList.add("notification", `notification-${type}`)
  notification.textContent = message

  document.body.appendChild(notification)

  setTimeout(() => {
    notification.classList.add("show")
  }, 10)

  setTimeout(() => {
    notification.classList.remove("show")
    setTimeout(() => {
      notification.remove()
    }, 300)
  }, 4000)
}

/**
 * Función para abrir el login administrativo
 */
function abrirLogin() {
  window.open(
    "admin/login.php",
    "LoginAdmin",
    "width=500,height=600,top=100,left=100,toolbar=no,location=no,directories=no,status=no,menubar=no",
  )
}

// Exportar funciones globales
window.abrirLogin = abrirLogin
window.showSuccessNotification = showSuccessNotification
window.showErrorNotification = showErrorNotification
