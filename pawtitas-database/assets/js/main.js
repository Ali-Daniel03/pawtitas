/**
 * PAWTITAS - JavaScript Principal
 * Funcionalidades interactivas para la página principal
 */

// Animación de números en las estadísticas
function animateNumbers() {
  const statNumbers = document.querySelectorAll(".stat-number")

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        const target = Number.parseInt(entry.target.getAttribute("data-target"))
        animateNumber(entry.target, target)
        observer.unobserve(entry.target)
      }
    })
  })

  statNumbers.forEach((number) => {
    observer.observe(number)
  })
}

function animateNumber(element, target) {
  let current = 0
  const increment = target / 50
  const timer = setInterval(() => {
    current += increment
    if (current >= target) {
      current = target
      clearInterval(timer)
    }
    element.textContent = Math.floor(current)
  }, 30)
}

// Smooth scroll para los enlaces internos
function initSmoothScroll() {
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      e.preventDefault()
      const target = document.querySelector(this.getAttribute("href"))
      if (target) {
        target.scrollIntoView({
          behavior: "smooth",
          block: "start",
        })
      }
    })
  })
}

// Efecto parallax suave para elementos decorativos
function initParallax() {
  window.addEventListener("scroll", () => {
    const scrolled = window.pageYOffset
    const parallaxElements = document.querySelectorAll(".paw-print, .bone, .heart")

    parallaxElements.forEach((element, index) => {
      const speed = 0.5 + index * 0.1
      element.style.transform = `translateY(${scrolled * speed}px)`
    })
  })
}

// Animación de entrada para las tarjetas
function initCardAnimations() {
  const cards = document.querySelectorAll(".about-card, .floating-card, .dog-card")

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.style.opacity = "1"
          entry.target.style.transform = "translateY(0)"
        }
      })
    },
    {
      threshold: 0.1,
    },
  )

  cards.forEach((card) => {
    card.style.opacity = "0"
    card.style.transform = "translateY(30px)"
    card.style.transition = "opacity 0.6s ease, transform 0.6s ease"
    observer.observe(card)
  })
}

// Efecto de hover mejorado para botones
function initButtonEffects() {
  const buttons = document.querySelectorAll(".btn")

  buttons.forEach((button) => {
    button.addEventListener("mouseenter", function () {
      this.style.transform = "translateY(-3px) scale(1.02)"
    })

    button.addEventListener("mouseleave", function () {
      this.style.transform = "translateY(0) scale(1)"
    })
  })
}

// Header con efecto de scroll
function initHeaderScroll() {
  const header = document.querySelector(".header")

  window.addEventListener("scroll", () => {
    if (window.scrollY > 100) {
      header.style.background = "rgba(255, 255, 255, 0.98)"
      header.style.boxShadow = "0 2px 30px rgba(0, 0, 0, 0.15)"
    } else {
      header.style.background = "rgba(255, 255, 255, 0.95)"
      header.style.boxShadow = "0 2px 20px rgba(0, 0, 0, 0.1)"
    }
  })
}

// Cargar estadísticas dinámicas (si están disponibles)
function initFormValidation() {
  const forms = document.querySelectorAll("form")

  forms.forEach((form) => {
    form.addEventListener("submit", (e) => {
      const requiredFields = form.querySelectorAll("[required]")
      let isValid = true

      requiredFields.forEach((field) => {
        if (!field.value.trim()) {
          isValid = false
          field.style.borderColor = "#dc3545"
        } else {
          field.style.borderColor = "#e0e0e0"
        }
      })

      if (!isValid) {
        e.preventDefault()
        showNotification("Por favor completa todos los campos obligatorios", "error")
      }
    })
  })
}

// Sistema de notificaciones
function showNotification(message, type = "info") {
  const notification = document.createElement("div")
  notification.className = `notification notification-${type}`
  notification.innerHTML = `
    <span>${message}</span>
    <button onclick="this.parentElement.remove()">&times;</button>
  `

  // Estilos para la notificación
  notification.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 15px 20px;
    border-radius: 8px;
    color: white;
    font-weight: 500;
    z-index: 10000;
    max-width: 400px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    animation: slideIn 0.3s ease;
  `

  // Colores según el tipo
  const colors = {
    success: "#28a745",
    error: "#dc3545",
    warning: "#ffc107",
    info: "#17a2b8",
  }

  notification.style.backgroundColor = colors[type] || colors.info

  document.body.appendChild(notification)

  // Auto-remover después de 5 segundos
  setTimeout(() => {
    if (notification.parentElement) {
      notification.remove()
    }
  }, 5000)
}

// Inicializar todas las funciones cuando el DOM esté listo
document.addEventListener("DOMContentLoaded", () => {
  animateNumbers()
  initSmoothScroll()
  initParallax()
  initCardAnimations()
  initButtonEffects()
  initHeaderScroll()
  initFormValidation()

  // Pequeña animación de bienvenida
  setTimeout(() => {
    document.body.style.opacity = "1"
  }, 100)
})

// Estilo inicial para la animación de entrada
document.body.style.opacity = "0"
document.body.style.transition = "opacity 0.5s ease"

// Funciones de utilidad
const PawtitasUtils = {
  // Formatear números con separadores de miles
  formatNumber: (num) => {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
  },

  // Validar email
  validateEmail: (email) => {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    return re.test(email)
  },

  validatePhone: (phone) => {
    const cleanPhone = phone.replace(/[^0-9]/g, "")
    return cleanPhone.length === 10
  },

  showLoading: (element) => {
    element.innerHTML = '<div class="loading">Cargando...</div>'
  },

  hideLoading: (element, originalContent) => {
    element.innerHTML = originalContent
  },
}

// Exportar para uso global
window.PawtitasUtils = PawtitasUtils
window.showNotification = showNotification

// Agregar estilos CSS para animaciones
const style = document.createElement("style")
style.textContent = `
  @keyframes slideIn {
    from {
      transform: translateX(100%);
      opacity: 0;
    }
    to {
      transform: translateX(0);
      opacity: 1;
    }
  }
  
  .loading {
    text-align: center;
    padding: 20px;
    color: #666;
  }
  
  .notification button {
    background: none;
    border: none;
    color: white;
    font-size: 18px;
    cursor: pointer;
    margin-left: 10px;
  }
`
document.head.appendChild(style)
