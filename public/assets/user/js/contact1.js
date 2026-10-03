// FAQ Toggle Functionality
function toggleFAQ(button) {
    const faqItem = button.parentElement
    const isActive = faqItem.classList.contains("active")
  
    // Close all FAQ items
    document.querySelectorAll(".faq-item").forEach((item) => {
      item.classList.remove("active")
    })
  
    // Open clicked item if it wasn't active
    if (!isActive) {
      faqItem.classList.add("active")
    }
  }
  
  
  // Map Click Handler
  document.querySelector(".map-placeholder").addEventListener("click", () => {
    // In a real implementation, this would open Google Maps
    window.open("https://maps.google.com", "_blank")
  })
  
  // Smooth scrolling for any anchor links
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
  // Add loading animation to buttons
  document.querySelectorAll("button").forEach((button) => {
    button.addEventListener("click", function () {
      if (!this.classList.contains("loading")) {
        this.style.transform = "scale(0.98)"
        setTimeout(() => {
          this.style.transform = "scale(1)"
        }, 150)
      }
    })
  })
  